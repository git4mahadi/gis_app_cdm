<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * EpiProxyController
 * ─────────────────────────────────────────────────────────────────────────────
 * Acts as a server-side proxy to the EPI Tracker DHIS2 analytics API at:
 *   https://epietracker.dghs.gov.bd/api
 *
 * WHY A PROXY?
 * The EPI Tracker API does not send CORS headers, so browser-side fetch()
 * would be blocked. By routing all calls through this Laravel controller,
 * the browser only talks to our own domain and CORS is never an issue.
 *
 * SECURITY:
 * The auth token is kept server-side and never exposed to the browser.
 *
 * Routes:
 *   GET  /epi/analytics  → analytics()  – forward analytics.json query
 *   GET  /epi/health     → health()     – ping the EPI system/info endpoint
 */
class EpiProxyController extends Controller
{
    /** Base URL of the Bangladesh EPI Tracker DHIS2 API */
    const EPI_BASE = 'https://epietracker.dghs.gov.bd/api';

    /**
     * Basic-auth token.
     * Base64 of "eqms:Eqms@1234"
     * In production move this to .env / config/api.php:
     *   EPI_API_TOKEN=ZXFtczpFcW1zQDEyMzQ=
     */
    const AUTH_TOKEN = 'ZXFtczpFcW1zQDEyMzQ=';

    /* ─────────────────────────────────────────────────────────────────────
       analytics()
       Proxies all query parameters straight through to analytics.json.
       The browser sends e.g.:
         GET /epi/analytics?dimension=dx:...&dimension=ou:UUID&...
       and this method forwards it verbatim to the EPI server.
       ───────────────────────────────────────────────────────────────────── */
    public function analytics(Request $request)
    {
        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Basic ' . self::AUTH_TOKEN,
                'Accept'        => 'application/json',
            ])
            ->get(self::EPI_BASE . '/analytics.json', $request->query());

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json')
            ->header('X-EPI-Status', $response->status());
    }

    /* ─────────────────────────────────────────────────────────────────────
       health()
       Pings the EPI system/info endpoint and returns a simple status JSON.
       Used by the frontend to show the API connection badge.
       ───────────────────────────────────────────────────────────────────── */
    public function health()
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Basic ' . self::AUTH_TOKEN,
                    'Accept'        => 'application/json',
                ])
                ->get(self::EPI_BASE . '/system/info');

            return response()->json([
                'status'  => $response->successful() ? 'ok' : 'error',
                'code'    => $response->status(),
                'message' => $response->successful() ? 'EPI Tracker reachable' : 'EPI Tracker returned ' . $response->status(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 0,
                'message' => 'Could not reach EPI Tracker: ' . $e->getMessage(),
            ], 503);
        }
    }
}
