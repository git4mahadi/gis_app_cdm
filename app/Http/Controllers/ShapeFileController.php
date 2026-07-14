<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShapeFileController extends Controller
{
    /**
     * Disk / path config.
     * File ends up at: storage/app/public/shape/districts.json.gz
     * Publicly served at: /storage/shape/districts.json.gz
     * (requires `php artisan storage:link` to be run once)
     */
    protected string $disk = 'public';
    protected string $directory = 'shape';
    protected string $filename = 'districts.json.gz';

    /**
     * Handle the shape file upload from the modal and save it to
     * storage/app/public/shape.
     */
    public function upload(Request $request)
    {
        $request->validate([
            // Laravel's mimetype sniffing can be unreliable for raw
            // zlib/gz blobs, so we just check extension + size here.
            'shapefile' => ['required', 'file', 'max:51200'], // 50MB max
        ]);

        $file = $request->file('shapefile');

        if (strtolower($file->getClientOriginalExtension()) !== 'gz') {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a .gz shape file.',
            ], 422);
        }

        // Always overwrite the same filename so there's one current
        // "active" shape file for the map to load.
        $path = $file->storeAs($this->directory, $this->filename, $this->disk);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::disk($this->disk)->url($path),
            'size' => Storage::disk($this->disk)->size($path),
        ]);
    }

    /**
     * Tell the frontend whether a shape file already exists in storage,
     * and give it the URL to fetch, so the map can load districts on
     * page load without requiring a re-upload.
     */
    public function current()
    {
        $path = $this->directory . '/' . $this->filename;
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($path)) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'url' => $disk->url($path),
            'last_modified' => $disk->lastModified($path),
            'size' => $disk->size($path),
        ]);
    }

    /**
     * Optional: remove the current shape file from storage.
     */
    public function destroy()
    {
        $path = $this->directory . '/' . $this->filename;
        $disk = Storage::disk($this->disk);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        return response()->json(['success' => true]);
    }
}