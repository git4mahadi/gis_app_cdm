<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * DistrictMapController
 * ─────────────────────────────────────────────────────────────────────────────
 * Manages individual district JSON files stored at:
 *   storage/app/public/district-maps/{filename}.json
 *
 * ════════════════════════════════════════════════════════════════════════════
 * FILENAME CONVENTION  ← primary source of UUID and district name
 * ════════════════════════════════════════════════════════════════════════════
 *
 * Expected pattern:
 *   {DistrictName}_District_{UUID}_{AnyNumber}.json
 *
 * Examples:
 *   Rajbari_District_WODgQhGGAgs_3082.json
 *     → name : "Rajbari"
 *     → uuid : "WODgQhGGAgs"
 *
 *   Cox_s_Bazar_District_XyZ123_9000.json
 *     → name : "Cox s Bazar"    (underscores become spaces for matching)
 *     → uuid : "XyZ123"
 *
 * If the filename does NOT follow this pattern, the controller falls back to
 * reading the UUID from the JSON content (root-level 'uuid', 'id', etc.).
 *
 * Routes (added in web.php):
 *   GET    /district-map           → index()   – list all uploaded district maps
 *   POST   /district-map/upload    → upload()  – upload a single district JSON
 *   DELETE /district-map           → destroy() – delete a district JSON by filename
 */
class DistrictMapController extends Controller
{
    protected string $disk      = 'public';
    protected string $directory = 'district-maps';

    /* ─────────────────────────────────────────────────────────────────────
       index()
       Returns a JSON array of all uploaded district map files, each with:
         name         : district name (from filename pattern or stripped filename)
         uuid         : UUID (from filename pattern, then JSON content fallback)
         uuid_source  : where the UUID came from: "filename" | "json" | null
         filename     : original filename stored
         url          : public URL to the file
         last_modified: Unix timestamp of the last write
       ───────────────────────────────────────────────────────────────────── */
    public function index()
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($this->directory)) {
            return response()->json([]);
        }

        $files   = $disk->files($this->directory);
        $results = [];

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $parsed   = $this->parseFilename($filename);

            // UUID from filename takes priority; fallback to JSON content
            $uuid       = $parsed['uuid'];
            $uuidSource = $uuid ? 'filename' : null;

            if (! $uuid) {
                try {
                    $raw     = $disk->get($filePath);
                    $content = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                    $uuid    = $this->extractUuidFromJson($content);
                    if ($uuid) $uuidSource = 'json';
                } catch (\Throwable) {
                    // Not valid JSON – uuid stays null
                }
            }

            $results[] = [
                'filename'      => $filename,
                'name'          => $parsed['name'],   // used for matching against shapefile feature names
                'uuid'          => $uuid,
                'uuid_source'   => $uuidSource,       // "filename" | "json" | null
                'url'           => $disk->url($filePath),
                'size'          => $disk->size($filePath),
                'last_modified' => $disk->lastModified($filePath),
            ];
        }

        return response()->json($results);
    }

    /* ─────────────────────────────────────────────────────────────────────
       upload()
       Accepts a single JSON file (multipart field name: "district_map").
       The UUID and district name are extracted from the filename first.
       The file is stored as-is under its original name.
       ───────────────────────────────────────────────────────────────────── */
    public function upload(Request $request)
    {
        $request->validate([
            'district_map' => ['required', 'file', 'max:51200'],
        ]);

        $file         = $request->file('district_map');
        $originalName = $file->getClientOriginalName();

        // Sanitise: allow letters, digits, underscores, hyphens, dots, spaces
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\'. ]/', '', $originalName);
        if (! $safeName || ! str_ends_with(strtolower($safeName), '.json')) {
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\'. ]/', '', pathinfo($originalName, PATHINFO_FILENAME))
                . '.json';
        }
        if (! $safeName || $safeName === '.json') {
            $safeName = 'district_' . time() . '.json';
        }

        $path   = $file->storeAs($this->directory, $safeName, $this->disk);
        $disk   = Storage::disk($this->disk);
        $parsed = $this->parseFilename($safeName);

        // UUID priority: filename pattern → JSON content
        $uuid       = $parsed['uuid'];
        $uuidSource = $uuid ? 'filename' : null;

        if (! $uuid) {
            try {
                $content = json_decode($disk->get($path), true, 512, JSON_THROW_ON_ERROR);
                $uuid    = $this->extractUuidFromJson($content);
                if ($uuid) $uuidSource = 'json';
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'File stored but is not valid JSON and filename does not contain a UUID: '
                        . $e->getMessage(),
                ], 422);
            }
        }

        return response()->json([
            'success'     => true,
            'filename'    => $safeName,
            'name'        => $parsed['name'],
            'uuid'        => $uuid,
            'uuid_source' => $uuidSource,
            'url'         => $disk->url($path),
            'size'        => $disk->size($path),
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────────
       destroy()
       Deletes a single district JSON file identified by its filename.
       Body param: { filename: "Rajbari_District_WODgQhGGAgs_3082.json" }
       ───────────────────────────────────────────────────────────────────── */
    public function destroy(Request $request)
    {
        $request->validate(['filename' => 'required|string|max:255']);

        $filename = basename($request->filename); // prevent path traversal
        $path     = $this->directory . '/' . $filename;
        $disk     = Storage::disk($this->disk);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        return response()->json(['success' => true]);
    }

    /* ─────────────────────────────────────────────────────────────────────
       parseFilename()
       ─────────────────────────────────────────────────────────────────────
       Parses a filename that follows the convention:
         {DistrictName}_District_{UUID}_{Number}.json

       Returns an array:
         [
           'name' => 'Rajbari',          // part before "_District_"
           'uuid' => 'WODgQhGGAgs',      // part between "_District_" and last "_\d+"
         ]

       If the filename does NOT match the pattern, returns:
         [
           'name' => <basename without extension>,
           'uuid' => null,
         ]

       Examples:
         "Rajbari_District_WODgQhGGAgs_3082.json"
           → name="Rajbari", uuid="WODgQhGGAgs"

         "Cox_s_Bazar_District_XyZ123abc_9001.json"
           → name="Cox s Bazar", uuid="XyZ123abc"

         "rajbari.json"  (simple format)
           → name="rajbari", uuid=null  (falls back to JSON content)
       ───────────────────────────────────────────────────────────────────── */
    private function parseFilename(string $filename): array
    {
        $basename = pathinfo($filename, PATHINFO_FILENAME); // strip .json

        // Pattern: {Name}_District_{UUID}_{Number}
        // The separator "_District_" is case-insensitive.
        // UUID is everything between "_District_" and the trailing "_\d+".
        if (preg_match('/^(.+)_[Dd]istrict_([^_]+)_\d+$/i', $basename, $m)) {
            $namePart = str_replace('_', ' ', $m[1]); // "Cox_s_Bazar" → "Cox s Bazar"
            $uuid     = $m[2];

            return [
                'name' => trim($namePart),
                'uuid' => $uuid,
            ];
        }

        // Fallback: return the full basename as name, uuid=null
        return [
            'name' => $basename,
            'uuid' => null,
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
       extractUuidFromJson()
       Fallback: walks common field names inside the JSON content.
       Only used when the filename does NOT follow the naming convention.

       Field priority:
         Root-level: uuid, UUID, id, ID, district_id
         Admin codes: ADM2_PCODE, pcode, GID_2, OBJECTID
         GeoJSON:     features[0].properties.{above fields}
       ───────────────────────────────────────────────────────────────────── */
    private function extractUuidFromJson(array $data): ?string
    {
        $candidates = [
            'uuid',
            'UUID',
            'id',
            'ID',
            'district_id',
            'ADM2_PCODE',
            'adm2_pcode',
            'pcode',
            'PCODE',
            'GID_2',
            'OBJECTID',
        ];

        foreach ($candidates as $key) {
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
                return (string) $data[$key];
            }
        }

        if (! empty($data['features'][0]['properties'])) {
            $props = $data['features'][0]['properties'];
            foreach ($candidates as $key) {
                if (isset($props[$key]) && $props[$key] !== '' && $props[$key] !== null) {
                    return (string) $props[$key];
                }
            }
        }

        return null;
    }
}
