<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GIS – Advanced Leaflet Demo</title>

    <!-- ─────────────────────────────────────────────────────────────
         STYLESHEETS (load order matters – Leaflet first, then plugins)
    ───────────────────────────────────────────────────────────────── -->

    <!-- 1. Core Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- 2. MarkerCluster CSS  ← needed for cluster bubble styling -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <!-- 3. Leaflet Draw CSS  ← toolbar icons + drawn-shape styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

    <!-- 4. Leaflet Geocoder (Search) CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

    <!-- 5. SB-Admin sidebar CSS -->
    <link href="{{ asset('frontend/css/styles.css') }}" rel="stylesheet" />

    <!-- 6. FontAwesome icons -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        /* ── Layout fixes so the map fills available height ─────── */
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        #layoutSidenav_content {
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            padding: 0 !important;
        }

        #map {
            height: calc(100vh - 56px);
            width: 100%;
        }

        /* ── CUSTOM ICONS ────────────────────────────────────────── */
        /*
         * TEACHING NOTE – Custom Icons:
         * Leaflet supports two types of custom marker icons:
         *   L.icon()    – uses image files (png/svg) as the icon
         *   L.divIcon() – uses raw HTML/CSS; perfect when you have no image
         *
         * Key properties:
         *   iconSize    : [width, height] in pixels
         *   iconAnchor  : pixel [x,y] of the icon that sits on the map coordinate
         *   popupAnchor : pixel [x,y] offset where the popup opens from
         */
        .custom-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .35);
            border: 2px solid #fff;
            cursor: pointer;
        }

        .capital-icon {
            width: 36px;
            height: 36px;
            background: #e74c3c;
            color: #fff;
            font-size: 18px;
        }

        .division-icon {
            width: 28px;
            height: 28px;
            background: #3498db;
            color: #fff;
            font-size: 13px;
        }

        .port-icon {
            width: 30px;
            height: 30px;
            background: #1abc9c;
            color: #fff;
            font-size: 15px;
        }

        .draw-marker-icon {
            width: 32px;
            height: 32px;
            background: #9b59b6;
            color: #fff;
        }

        /* ── CLUSTER BUBBLES ─────────────────────────────────────── */
        /*
         * TEACHING NOTE – Clustering:
         * The iconCreateFunction in L.markerClusterGroup() lets you
         * return a completely custom icon for each cluster bubble.
         * 'cluster.getChildCount()' gives the number of markers inside.
         */
        .cluster-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(108, 92, 231, .5);
        }

        /* ── LEGEND ──────────────────────────────────────────────── */
        /*
         * TEACHING NOTE – Legends:
         * Leaflet has no built-in legend. You create one with L.control(),
         * give it a position ('bottomright' etc.), then implement onAdd()
         * which must return a DOM <div> element.
         */
        .map-legend {
            background: rgba(255, 255, 255, .95);
            padding: 12px 16px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
            min-width: 190px;
            font-size: 13px;
            line-height: 1.7;
            backdrop-filter: blur(8px);
        }

        .map-legend h4 {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .map-legend hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 8px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 3px 0;
        }

        .legend-color {
            display: inline-block;
            width: 18px;
            height: 12px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .legend-icon {
            font-size: 15px;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
        }

        .legend-hint {
            font-size: 11px;
            color: #888;
            font-style: italic;
        }

        /* ── COORDINATE DISPLAY ──────────────────────────────────── */
        .coords-display {
            background: rgba(0, 0, 0, .7);
            color: #00ff88;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-family: monospace;
            white-space: nowrap;
        }

        /* ── MEASUREMENT POPUP ───────────────────────────────────── */
        .measure-popup {
            font-size: 13px;
            line-height: 1.6;
        }

        .measure-popup b {
            color: #2c3e50;
        }

        /* ── CITY POPUP ──────────────────────────────────────────── */
        .city-popup h4 {
            margin: 0 0 4px;
            color: #2c3e50;
            font-size: 15px;
        }

        .city-popup p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }

        /* ── DISTRICT BADGE ──────────────────────────────────────── */
        .district-badge {
            display: inline-block;
            background: #eef3f8;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 11px;
            color: #2c3e50;
        }

        /* ── UPLOAD STATUS ───────────────────────────────────────── */
        #uploadStatus {
            margin-top: 10px;
            font-size: 13px;
            min-height: 20px;
        }

        /* ── STATS PANEL ─────────────────────────────────────────── */
        .stats-panel {
            background: rgba(255, 255, 255, .95);
            padding: 10px 14px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .12);
            font-size: 12px;
            min-width: 160px;
        }

        .stats-panel b {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
            color: #2c3e50;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .stats-val {
            font-weight: 700;
            color: #3498db;
        }
    </style>
</head>

<body>
    <!-- ══════════════════════════════════════════════
         NAV BAR
    ══════════════════════════════════════════════════ -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="{{ url('/dashboard') }}">
            🗺️ GIS Advanced Demo
        </a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <form class="d-none d-md-inline-block form-inline ms-auto me-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." />
                <button class="btn btn-primary" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item">
                <button class="btn btn-sm btn-outline-light me-2"
                    type="button" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-upload"></i> Upload Shape
                </button>
            </li>
            <li class="nav-item">
                <button class="btn btn-sm btn-outline-warning me-2"
                    type="button" data-bs-toggle="modal" data-bs-target="#districtMapModal">
                    <i class="fas fa-file-import"></i> District Maps
                </button>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#!">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- ══════════════════════════════════════════════
         SIDEBAR + CONTENT
    ══════════════════════════════════════════════════ -->
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Navigation</div>
                        <a class="nav-link" href="{{ url('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                            Map Dashboard
                        </a>
                        <div class="sb-sidenav-menu-heading">Quick Fly-To</div>
                        <a class="nav-link" href="#" onclick="flyTo([23.8103,90.4125],12);return false;">
                            <div class="sb-nav-link-icon"><i class="fas fa-location-dot"></i></div>
                            Dhaka
                        </a>
                        <a class="nav-link" href="#" onclick="flyTo([22.3569,91.7832],12);return false;">
                            <div class="sb-nav-link-icon"><i class="fas fa-anchor"></i></div>
                            Chattogram
                        </a>
                        <a class="nav-link" href="#" onclick="flyTo([22.8456,89.5403],12);return false;">
                            <div class="sb-nav-link-icon"><i class="fas fa-industry"></i></div>
                            Khulna
                        </a>
                        <a class="nav-link" href="#" onclick="flyTo([21.4272,92.0058],12);return false;">
                            <div class="sb-nav-link-icon"><i class="fas fa-umbrella-beach"></i></div>
                            Cox's Bazar
                        </a>
                        <div class="sb-sidenav-menu-heading">Info</div>
                        <a class="nav-link" href="#" id="sideDistrictCount">
                            <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                            <span id="sideDistrictLabel">No shape loaded</span>
                        </a>
                        <div class="sb-sidenav-menu-heading">District Maps</div>
                        <!-- Populated dynamically by refreshDistrictMapSidebar() -->
                        <div id="sideDistrictMaps">
                            <a class="nav-link text-muted" href="#">
                                <div class="sb-nav-link-icon"><i class="fas fa-spinner fa-spin"></i></div>
                                Loading…
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Advanced Leaflet Demo</div>
                    GIS Training Portal
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <!-- THE MAP ELEMENT -->
                <div id="map"></div>

                <!--
                    UUID TABLE CONTAINER
                    ════════════════════════════════════════════════════════
                    TEACHING NOTE:
                    This empty div is a "mount point". The JavaScript function
                    buildUuidTable() writes the full HTML table into this div
                    after every shapefile upload or auto-load.

                    Keeping rendering logic in JS (not in Blade) means we
                    don't need a full page reload to show the table — it
                    appears instantly as part of the upload flow.
                    ════════════════════════════════════════════════════════
                -->
                <div id="uuidTableContainer" class="px-4"></div>
            </main>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         DISTRICT MAP MODAL
         Upload individual district JSON files.
         Each file's name (without .json) is the district key.
         The UUID is extracted from the file's content.
    ══════════════════════════════════════════════════ -->
    <div class="modal fade" id="districtMapModal" tabindex="-1"
        aria-labelledby="districtMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="districtMapModalLabel">
                        <i class="fas fa-file-import me-2"></i>District Map Manager
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Upload new district JSON -->
                    <div class="card mb-3">
                        <div class="card-header"><i class="fas fa-upload me-2"></i>Upload District JSON</div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">
                                Upload a <code>.json</code> file for a single district.
                                The UUID is read from the <strong>filename</strong> — no need to put it inside the file.
                            </p>
                            <div class="alert alert-info py-2 px-3 mb-2" style="font-size:12px">
                                <strong>📛 Filename format:</strong><br>
                                <code>{DistrictName}_District_{UUID}_{AnyNumber}.json</code><br>
                                <span class="text-muted">Example:</span>
                                <code>Rajbari_District_WODgQhGGAgs_3082.json</code><br>
                                → District: <strong>Rajbari</strong> &nbsp;|&nbsp; UUID: <strong>WODgQhGGAgs</strong><br><br>
                                The part before <code>_District_</code> is the district name (matched
                                against the boundary shapefile). The part between <code>_District_</code>
                                and the trailing number is the UUID.<br><br>
                                <span class="text-secondary">Fallback:</span> if the filename doesn't follow this pattern,
                                the UUID is read from inside the JSON (fields: <code>uuid</code>, <code>id</code>,
                                <code>ADM2_PCODE</code>, etc.).
                            </div>
                            <div class="input-group">
                                <input class="form-control" type="file" id="districtMapFileInput"
                                    accept=".json,application/json" />
                                <button class="btn btn-warning" type="button"
                                    id="districtMapUploadBtn"
                                    onclick="handleDistrictMapUpload()">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                            <div id="districtMapUploadStatus" class="mt-2 small"></div>
                        </div>
                    </div>

                    <!-- List of uploaded district JSONs -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-list me-2"></i>Uploaded District Maps</span>
                            <span class="badge bg-primary" id="districtMapCount">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div id="districtMapList" style="max-height:320px;overflow-y:auto">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-folder-open"></i> No district maps uploaded yet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         EPI DATA OFFCANVAS PANEL
         Slides in from the right when "Pull Target Data" or
         "Pull Coverage Data" is clicked on a district popup.

         TEACHING NOTE — Bootstrap Offcanvas:
         An offcanvas is like a drawer that slides in from any edge.
         It is activated via data-bs-toggle="offcanvas" OR via JS:
           bootstrap.Offcanvas.getOrCreateInstance(el).show()
         It is better than a modal here because the map stays visible
         underneath, so the user can still see which district they selected.
    ══════════════════════════════════════════════════════════════════ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="epiDataPanel"
        aria-labelledby="epiPanelLabel" style="width:min(680px,95vw)">

        <div class="offcanvas-header border-bottom"
            style="background:linear-gradient(135deg,#1a3a5c,#2980b9);color:#fff">
            <div>
                <h5 class="offcanvas-title mb-0" id="epiPanelLabel">
                    <i class="fas fa-syringe me-2"></i>
                    <span id="epiPanelDistrictName">EPI Data</span>
                </h5>
                <small id="epiPanelUuid" class="opacity-75" style="font-family:monospace;font-size:10px"></small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- API health badge -->
                <span id="epiHealthBadge" class="badge bg-secondary" style="font-size:10px">
                    <i class="fas fa-circle-notch fa-spin me-1"></i>Checking API…
                </span>
                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>

        <!-- Tab nav -->
        <div class="offcanvas-header py-0 border-bottom bg-light">
            <ul class="nav nav-tabs border-0 pt-2" id="epiTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="targetTabBtn"
                        data-bs-toggle="tab" data-bs-target="#targetTabPane"
                        type="button" role="tab">
                        <i class="fas fa-bullseye me-1"></i>Target Data
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="coverageTabBtn"
                        data-bs-toggle="tab" data-bs-target="#coverageTabPane"
                        type="button" role="tab">
                        <i class="fas fa-shield-virus me-1"></i>Coverage Data
                    </button>
                </li>
            </ul>
        </div>

        <div class="offcanvas-body p-0 tab-content">

            <!-- TARGET TAB -->
            <div class="tab-pane fade show active p-3" id="targetTabPane" role="tabpanel">
                <div id="targetDataContent">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-bullseye fa-2x mb-2 d-block opacity-50"></i>
                        Click <strong>Pull Target Data</strong> on a district popup to load data.
                    </div>
                </div>
            </div>

            <!-- COVERAGE TAB -->
            <div class="tab-pane fade p-3" id="coverageTabPane" role="tabpanel">
                <div id="coverageDataContent">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-shield-virus fa-2x mb-2 d-block opacity-50"></i>
                        Click <strong>Pull Coverage Data</strong> on a district popup to load data.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         UPLOAD MODAL
    ══════════════════════════════════════════════════ -->
    <div class="modal fade" id="uploadModal" tabindex="-1"
        aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="fas fa-upload me-2"></i>Upload Shape File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">
                        Select the compressed shape file (<code>districts.json.gz</code>).
                        It will be stored in <code>storage/app/public/shape</code> and
                        automatically reloaded on future visits.
                    </p>
                    <div class="mb-3">
                        <label for="shapeFileInput" class="form-label">Shape file (.gz)</label>
                        <input class="form-control" type="file" id="shapeFileInput" accept=".gz" />
                    </div>
                    <div id="uploadStatus" class="text-muted"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary"
                        id="uploadSubmitBtn" onclick="handleShapeFileUpload()">
                        Load &amp; Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         JS LIBRARIES  (load order matters!)
    ══════════════════════════════════════════ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ asset('frontend/js/scripts.js') }}"></script>

    <!-- 1. Core Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- 2. MarkerCluster – must come AFTER Leaflet -->
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <!-- 3. Leaflet Draw – must come AFTER Leaflet -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

    <!-- 4. Geocoder (Search) – must come AFTER Leaflet -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        /* ════════════════════════════════════════════════════════════════════
   ██████████████████████████████████████████████████████████████████
   SECTION 1 ─ BASE (TILE) LAYERS
   ██████████████████████████████████████████████████████████████████

   TEACHING NOTE:
   A TileLayer is a grid of raster image tiles served by a tile server.
   The URL template uses {z}/{x}/{y} placeholders:
     z = zoom level  (0 = whole world, 18 = street level)
     x = tile column
     y = tile row
   Different providers give different visual styles.
   You add attribution to credit the data source (required by most ToS).
   ════════════════════════════════════════════════════════════════════ */

        const osmLayer = L.tileLayer(
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }
        );

        const darkLayer = L.tileLayer(
            'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '© <a href="https://carto.com/">CartoDB</a>',
                maxZoom: 19,
                subdomains: 'abcd'
            }
        );

        const satelliteLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles © Esri — Esri, USGS, NOAA',
                maxZoom: 19
            }
        );

        /* ════════════════════════════════════════════════════════════════════
           SECTION 2 ─ MAP INITIALIZATION
           ════════════════════════════════════════════════════════════════════

           TEACHING NOTE:
           L.map(id, options) creates the map inside the <div id="map"> element.
           The 'layers' option sets which layers are active on start.
           We only put one base layer here – the others are toggled via the
           Layer Control (Section 6).
           ════════════════════════════════════════════════════════════════════ */

        const map = L.map('map', {
            center: [23.8103, 90.4125], // Bangladesh centre (lat, lng)
            zoom: 7,
            layers: [osmLayer], // start with OSM street map
            zoomControl: true
        });

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 3 ─ CUSTOM ICONS
           ██████████████████████████████████████████████████████████████████

           TEACHING NOTE – Two approaches:

           A) L.icon({ iconUrl, iconSize, iconAnchor, popupAnchor })
              → Uses an external image file (PNG/SVG).
              → Good when you have designed assets.

           B) L.divIcon({ html, className, iconSize, iconAnchor })
              → Renders arbitrary HTML/CSS as the marker.
              → className:'' removes the default white box Leaflet adds.
              → Great for emoji, FontAwesome icons, or CSS shapes.

           iconAnchor: the pixel [x,y] inside the icon image that sits exactly
                       ON the map coordinate. [0,0] = top-left corner.
           popupAnchor: offset from iconAnchor where the popup callout opens.
           ════════════════════════════════════════════════════════════════════ */

        // Capital city – red star, 36×36 px, centred on coord
        const capitalIcon = L.divIcon({
            html: '<div class="custom-icon capital-icon">★</div>',
            className: '', // ← important: clears leaflet default styling
            iconSize: [36, 36],
            iconAnchor: [18, 18], // [half-width, half-height] = true centre
            popupAnchor: [0, -22]
        });

        // Division HQ – blue dot
        const divisionIcon = L.divIcon({
            html: '<div class="custom-icon division-icon">●</div>',
            className: '',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -18]
        });

        // Port / coastal city – teal anchor
        const portIcon = L.divIcon({
            html: '<div class="custom-icon port-icon">⚓</div>',
            className: '',
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -18]
        });

        // Icon used by the Draw toolbar's marker tool
        const drawMarkerIcon = L.divIcon({
            html: '<div class="custom-icon draw-marker-icon">📍</div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 32], // bottom-centre so tip touches coords
            popupAnchor: [0, -34]
        });

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 4 ─ MARKER CLUSTERING
           ██████████████████████████████████████████████████████████████████

           TEACHING NOTE:
           When you have many markers, the map becomes unreadable. Clustering
           groups nearby markers into a single "cluster" bubble at lower zoom
           levels. As you zoom in, clusters split into smaller groups or
           individual markers.

           L.markerClusterGroup(options) from the Leaflet.markercluster plugin:
             maxClusterRadius    – pixel radius within which markers are merged
             iconCreateFunction  – customise the cluster bubble appearance
             disableClusteringAtZoom – switch to individual markers at this zoom

           The cluster group is itself treated as a single Leaflet layer, so
           you can add/remove it from the map just like a normal layer.
           ════════════════════════════════════════════════════════════════════ */

        const cityClusterGroup = L.markerClusterGroup({
            maxClusterRadius: 60,
            disableClusteringAtZoom: 12, // show individual markers at zoom 12+
            spiderfyOnMaxZoom: true, // spread markers when zoomed in fully

            // Return a custom L.divIcon for each cluster bubble
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                // Change colour based on cluster size
                const colour = count < 5 ? '#6c5ce7' : count < 10 ? '#e17055' : '#d63031';
                return L.divIcon({
                    html: `<div class="cluster-icon" style="background:${colour}">${count}</div>`,
                    className: '',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });
            }
        });

        // ── City dataset ─────────────────────────────────────────────────────
        const bangladeshCities = [{
                name: 'Dhaka',
                coords: [23.8103, 90.4125],
                type: 'capital',
                pop: '22M',
                desc: 'Capital & largest city'
            },
            {
                name: 'Chattogram',
                coords: [22.3569, 91.7832],
                type: 'port',
                pop: '9.8M',
                desc: 'Main seaport city'
            },
            {
                name: 'Khulna',
                coords: [22.8456, 89.5403],
                type: 'division',
                pop: '1.5M',
                desc: 'Southern industrial city'
            },
            {
                name: 'Rajshahi',
                coords: [24.3745, 88.6042],
                type: 'division',
                pop: '953K',
                desc: 'NW silk-producing city'
            },
            {
                name: 'Sylhet',
                coords: [24.8949, 91.8687],
                type: 'division',
                pop: '527K',
                desc: 'NE tea region'
            },
            {
                name: 'Barisal',
                coords: [22.7010, 90.3535],
                type: 'division',
                pop: '340K',
                desc: 'River delta city'
            },
            {
                name: 'Rangpur',
                coords: [25.7439, 89.2752],
                type: 'division',
                pop: '340K',
                desc: 'N agricultural city'
            },
            {
                name: 'Mymensingh',
                coords: [24.7471, 90.4203],
                type: 'division',
                pop: '487K',
                desc: 'Central city'
            },
            {
                name: "Cox's Bazar",
                coords: [21.4272, 92.0058],
                type: 'port',
                pop: '249K',
                desc: "World's longest sea beach"
            },
            {
                name: 'Comilla',
                coords: [23.4607, 91.1809],
                type: 'division',
                pop: '389K',
                desc: 'Eastern trade city'
            },
            {
                name: 'Narayanganj',
                coords: [23.6238, 90.4996],
                type: 'port',
                pop: '223K',
                desc: 'River port near Dhaka'
            },
            {
                name: 'Gazipur',
                coords: [23.9999, 90.4203],
                type: 'division',
                pop: '349K',
                desc: 'Industrial suburb of Dhaka'
            },
            {
                name: 'Bogura',
                coords: [24.8510, 89.3696],
                type: 'division',
                pop: '398K',
                desc: 'North-central city'
            },
            {
                name: 'Jessore',
                coords: [23.1667, 89.2167],
                type: 'division',
                pop: '237K',
                desc: 'SW border city'
            },
            {
                name: 'Dinajpur',
                coords: [25.6279, 88.6338],
                type: 'division',
                pop: '215K',
                desc: 'N border & rice city'
            },
        ];

        // Build a marker for each city and add it to the cluster group
        bangladeshCities.forEach(city => {
            const icon = city.type === 'capital' ? capitalIcon :
                city.type === 'port' ? portIcon :
                divisionIcon;

            const marker = L.marker(city.coords, {
                    icon
                })
                .bindPopup(`
            <div class="city-popup">
                <h4>${city.name}</h4>
                <p>${city.desc}</p>
                <p>Population ≈ <strong>${city.pop}</strong></p>
            </div>
        `, {
                    maxWidth: 220
                })
                .bindTooltip(city.name, {
                    sticky: true
                });

            cityClusterGroup.addLayer(marker); // ← add to cluster, NOT directly to map
        });

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 5 ─ LAYER GROUPS  (for district boundaries + drawings)
           ██████████████████████████████████████████████████████████████════

           TEACHING NOTE:
           L.layerGroup([layers])    – a simple container; no extra features.
           L.featureGroup([layers])  – extends layerGroup with:
               .getBounds()   → get the bounding box of all child layers
               .setStyle()    → apply style to all GeoJSON children at once
               events bubble up from children to the featureGroup
           Prefer featureGroup when you need bounds or style helpers.

           drawnItems is the layer that Leaflet.draw uses to store shapes.
           We add it to the map directly AND reference it in the Draw control.
           ════════════════════════════════════════════════════════════════════ */

        //  District boundary layer – filled by shapefile upload
        const districtsGroup = L.featureGroup().addTo(map);

        // Storage for all user-drawn shapes  ← Leaflet.draw requires this
        const drawnItems = new L.FeatureGroup().addTo(map);

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 6 ─ LAYER CONTROL (base layers + overlays)
           ██████████████████████████████████████████████████████████████████

           TEACHING NOTE:
           L.control.layers(baseLayers, overlays, options)

           baseLayers  : { 'Label': layer } – radio buttons, only one active
           overlays    : { 'Label': layer } – checkboxes, many can be active

           The labels are plain strings (HTML is allowed too).
           The control collapses to a layers icon on small screens by default.
           Call layerControl.addOverlay(layer, 'Label') later to add more layers.
           ════════════════════════════════════════════════════════════════════ */

        const baseLayers = {
            '🗺️ Street (OSM)': osmLayer,
            '🌑 Dark (CartoDB)': darkLayer,
            '🛰️ Satellite (Esri)': satelliteLayer
        };

        const overlayLayers = {
            '🏙️ Cities (Clustered)': cityClusterGroup,
            '🗺️ District Boundaries': districtsGroup,
            '✏️ My Drawings': drawnItems
        };

        L.control.layers(baseLayers, overlayLayers, {
            position: 'topright',
            collapsed: true // collapses to icon; expand on hover
        }).addTo(map);

        // Add city cluster to map by default (it's also in overlayLayers)
        cityClusterGroup.addTo(map);

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 7 ─ SEARCH (Geocoder)
           ██████████████████████████████████████████████████████████████████

           TEACHING NOTE:
           L.Control.geocoder() from the leaflet-control-geocoder plugin adds a
           search box that converts text addresses → map coordinates (geocoding)
           and coordinates → addresses (reverse geocoding).

           Built-in providers: Nominatim (OSM), Google, Bing, Mapbox, etc.
           We use Nominatim (free, no API key) restricted to Bangladesh via:
             countrycodes: 'bd'
             viewbox:      Bangladesh bounding box [W,S,E,N]
             bounded: 1   → only return results inside the viewbox

           defaultMarkGeocode:true  → automatically drop a blue marker on result.
           You can listen to 'markgeocode' event for custom behaviour.
           ════════════════════════════════════════════════════════════════════ */

        const geocoder = L.Control.geocoder({
            defaultMarkGeocode: true,
            placeholder: 'Search address in Bangladesh…',
            errorMessage: 'No results found.',
            position: 'topleft',
            geocoder: L.Control.Geocoder.nominatim({
                geocodingQueryParams: {
                    countrycodes: 'bd',
                    viewbox: '88.0,20.5,92.7,26.7',
                    bounded: 1
                }
            })
        }).addTo(map);

        // Optional: when a result is selected, fly smoothly instead of jumping
        geocoder.on('markgeocode', function(e) {
            map.flyToBounds(e.geocode.bbox, {
                maxZoom: 14,
                duration: 1.2
            });
        });

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 8 ─ DRAWING TOOLS (Leaflet.Draw)
           ██████████████████████████████████████████████████████████████████

           TEACHING NOTE:
           L.Control.Draw adds a toolbar (pencil icon group) to the map.

           The 'draw' object configures each shape type:
             false         → disable that tool
             {}            → enable with defaults
             { options }   → enable with custom settings

           'edit.featureGroup' MUST be the same layer you use to store shapes.
           Without it, the Edit and Delete buttons won't work.

           Key draw options:
             allowIntersection:false → prevents self-crossing polygons
             showArea / showLength / showRadius → live measurement tooltip
             shapeOptions: { color, fillColor, … } → drawing appearance
           ════════════════════════════════════════════════════════════════════ */

        const drawControl = new L.Control.Draw({
            position: 'topleft',
            draw: {
                polygon: {
                    allowIntersection: false,
                    showArea: true,
                    shapeOptions: {
                        color: '#e74c3c',
                        fillColor: '#e74c3c',
                        fillOpacity: 0.15
                    }
                },
                polyline: {
                    showLength: true,
                    metric: true, // show metres/km (false = miles/feet)
                    shapeOptions: {
                        color: '#3498db',
                        weight: 3
                    }
                },
                circle: {
                    showRadius: true,
                    metric: true,
                    shapeOptions: {
                        color: '#2ecc71',
                        fillColor: '#2ecc71',
                        fillOpacity: 0.15
                    }
                },
                rectangle: {
                    showArea: true,
                    shapeOptions: {
                        color: '#f39c12',
                        fillColor: '#f39c12',
                        fillOpacity: 0.15
                    }
                },
                marker: {
                    icon: drawMarkerIcon // use our custom purple marker
                },
                circlemarker: false // disable – not useful for this demo
            },
            edit: {
                featureGroup: drawnItems, // ← must match overlay layer above
                remove: true // allow deletion via trash icon
            }
        });
        map.addControl(drawControl);

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████════════════
           SECTION 9 ─ MEASUREMENT (using Draw events)
           ████████████████████████████████████████████████████████████████

           TEACHING NOTE:
           Leaflet.draw fires these events on the map object:
             L.Draw.Event.CREATED   → user finished drawing a shape
             L.Draw.Event.EDITED    → user finished editing shapes
             L.Draw.Event.DELETED   → user deleted shapes

           In the CREATED handler:
             e.layer     → the newly created Leaflet layer
             e.layerType → 'polygon','polyline','circle','rectangle','marker'

           Measurement helpers:
             L.GeometryUtil.geodesicArea(latlngs) → area in m² (geodesic = accounts for Earth's curvature)
             latlng.distanceTo(other)             → Haversine distance in metres
             layer.getRadius()                    → circle radius in metres
           ════════════════════════════════════════════════════════════════════ */

        map.on(L.Draw.Event.CREATED, function(e) {
            const layer = e.layer;
            const type = e.layerType;
            let popupHtml = '';

            if (type === 'polygon' || type === 'rectangle') {
                const latlngs = layer.getLatLngs()[0];
                const m2 = L.GeometryUtil.geodesicArea(latlngs);
                const km2 = (m2 / 1_000_000).toFixed(4);
                const ha = (m2 / 10_000).toFixed(2);
                popupHtml = `
            <div class="measure-popup">
                <b>📐 Area Measurement</b><br>
                ${km2} km²<br>
                ${ha} hectares<br>
                <small>${Math.round(m2).toLocaleString()} m²</small>
            </div>`;

            } else if (type === 'polyline') {
                const pts = layer.getLatLngs();
                let total = 0;
                for (let i = 0; i < pts.length - 1; i++) {
                    total += pts[i].distanceTo(pts[i + 1]);
                }
                const km = (total / 1000).toFixed(3);
                popupHtml = `
            <div class="measure-popup">
                <b>📏 Distance Measurement</b><br>
                ${km} km<br>
                <small>${Math.round(total).toLocaleString()} metres</small>
            </div>`;

            } else if (type === 'circle') {
                const r = layer.getRadius();
                const m2 = Math.PI * r * r;
                popupHtml = `
            <div class="measure-popup">
                <b>⭕ Circle</b><br>
                Radius: ${r.toFixed(1)} m<br>
                Area: ${(m2 / 1_000_000).toFixed(4)} km²
            </div>`;

            } else if (type === 'marker') {
                const ll = layer.getLatLng();
                popupHtml = `
            <div class="measure-popup">
                <b>📍 Placed Marker</b><br>
                Lat: ${ll.lat.toFixed(6)}<br>
                Lng: ${ll.lng.toFixed(6)}
            </div>`;
            }

            if (popupHtml) {
                layer.bindPopup(popupHtml, {
                    maxWidth: 200
                }).openPopup();
            }

            drawnItems.addLayer(layer); // ← store in our featureGroup
            updateStatsPanel();
        });

        // Update stats when shapes are edited or removed
        map.on(L.Draw.Event.EDITED, updateStatsPanel);
        map.on(L.Draw.Event.DELETED, updateStatsPanel);

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 10 ─ LEGEND CONTROL
           ██████████████████████████████████████████████████████████████████

           TEACHING NOTE:
           L.control({ position }) creates an empty control at a corner.
           You MUST implement .onAdd(map) which returns a DOM element.
           Use L.DomUtil.create(tag, className) to get a properly prepared div.

           Positions: 'topleft' | 'topright' | 'bottomleft' | 'bottomright'

           L.DomEvent.disableClickPropagation(div) prevents map clicks from
           firing when clicking inside your custom control.
           ════════════════════════════════════════════════════════════════════ */

        const legend = L.control({
            position: 'bottomright'
        });
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'map-legend');
            L.DomEvent.disableClickPropagation(div); // stop map clicks inside legend
            div.innerHTML = `
        <h4>🗺️ Map Legend</h4>
        <div class="legend-item">
            <span class="legend-color" style="background:#3498db;opacity:.5;border:1px solid #2c3e50"></span>
            District boundary
        </div>
        <div class="legend-item"><span class="legend-icon">★</span> Capital city (Dhaka)</div>
        <div class="legend-item"><span class="legend-icon">⚓</span> Port / coastal city</div>
        <div class="legend-item"><span class="legend-icon">●</span> Division headquarters</div>
        <hr>
        <div class="legend-item">
            <span class="legend-color" style="background:#e74c3c;opacity:.5"></span> Drawn polygon
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background:#3498db;height:4px"></span> Drawn polyline
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background:#2ecc71;opacity:.5"></span> Drawn circle
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background:#f39c12;opacity:.5"></span> Drawn rectangle
        </div>
        <hr>
        <div class="legend-hint">Use toolbar (top-left) to draw &amp; measure</div>
    `;
            return div;
        };
        legend.addTo(map);

        /* ════════════════════════════════════════════════════════════════════
           SECTION 11 ─ STATS PANEL (drawing count)
           ════════════════════════════════════════════════════════════════════ */

        const statsControl = L.control({
            position: 'bottomleft'
        });
        statsControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'stats-panel');
            L.DomEvent.disableClickPropagation(div);
            div.id = 'statsPanel';
            div.innerHTML = `<b>✏️ Drawing Stats</b>
        <div class="stats-row">
            <span>Shapes:</span><span class="stats-val" id="statShapes">0</span>
        </div>`;
            return div;
        };
        statsControl.addTo(map);

        function updateStatsPanel() {
            const el = document.getElementById('statShapes');
            if (el) el.textContent = drawnItems.getLayers().length;
        }

        /* ════════════════════════════════════════════════════════════════════
           SECTION 12 ─ COORDINATE DISPLAY (live cursor position)
           ════════════════════════════════════════════════════════════════════

           TEACHING NOTE:
           map.on('mousemove', fn) fires continuously as the mouse moves.
           e.latlng gives you the geographic coordinate under the cursor.
           This is useful for debugging and for teaching users how coordinates work.
           ════════════════════════════════════════════════════════════════════ */

        const coordsControl = L.control({
            position: 'topleft'
        });
        coordsControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'coords-display');
            div.id = 'coordsDisplay';
            div.innerHTML = 'Move mouse over map…';
            return div;
        };
        coordsControl.addTo(map);

        map.on('mousemove', function(e) {
            const d = document.getElementById('coordsDisplay');
            if (d) d.innerHTML = `Lat: ${e.latlng.lat.toFixed(5)} | Lng: ${e.latlng.lng.toFixed(5)}`;
        });



        /* ═══════════════════════════════════════════════════════════════════
           ███████████████████████████████████████████████████████████████████
           SECTION 13 ─ DISTRICT MAP SYSTEM
           ███████████████████████████████████████████████████████████████████

           TEACHING NOTE – Two separate upload layers:

           1. districts.json.gz  →  BOUNDARY SHAPEFILE
              Uploaded via “Upload Shape” button.
              Contains the 64 polygon geometries used to draw district borders.
              Does NOT control UUIDs for this system.

           2. {name}.json        →  INDIVIDUAL DISTRICT MAP FILES
              Uploaded via “District Maps” button, one file per district.
              MUST contain a UUID in their content.
              The filename (without .json) is the district key.
              Matched to boundary polygons by normalised name comparison.

           This separation lets you update boundaries and district data
           independently. A district boundary can exist on the map without
           a data file (shows “district map not built”). A data file can
           exist before the boundary is drawn.
           ═══════════════════════════════════════════════════════════════════ */

        const DISTRICT_MAP_INDEX_URL = "{{ route('district-map.index') }}";
        const DISTRICT_MAP_UPLOAD_URL = "{{ route('district-map.upload') }}";
        const DISTRICT_MAP_DELETE_URL = "{{ route('district-map.destroy') }}";

        /**
         * normalizeKey(name)
         * ────────────────────────────────────────────────────────────
         * Converts a district name to a stable lookup key:
         *   "Rajbari"      → "rajbari"
         *   "Cox's Bazar"  → "coxsbazar"
         *   "B. Baria"     → "bbaria"
         *
         * This is applied to BOTH the shapefile feature names AND the uploaded
         * filenames so they match even if capitalisation or punctuation differs.
         */
        function normalizeKey(name) {
            return String(name)
                .toLowerCase()
                .replace(/[^a-z0-9]/g, ''); // keep only a-z and digits
        }

        /**
         * loadDistrictMaps()
         * ────────────────────────────────────────────────────────────
         * Fetches the list of uploaded district JSON files from the backend
         * and returns a lookup Map keyed by normalised district name:
         *   Map { "rajbari" → { uuid, filename, url, ... }, ... }
         *
         * If a file was uploaded but has no UUID, the entry still exists
         * in the map but its uuid will be null.
         */
        async function loadDistrictMaps() {
            try {
                const res = await fetch(DISTRICT_MAP_INDEX_URL, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return new Map();
                const list = await res.json();
                const lookup = new Map();
                list.forEach(entry => {
                    lookup.set(normalizeKey(entry.name), entry);
                });
                return lookup;
            } catch {
                return new Map();
            }
        }

        /* ── Render district polygons on the map ───────────────────────────────────
           TEACHING NOTE:
           renderDistricts now takes a second argument: districtMaps
           which is a Map<normalizedName, {uuid, filename, …}> built
           by loadDistrictMaps().

           For each boundary polygon:
             • normalise feature name  →  look up in districtMaps
             • found   → uuid from individual district JSON
             • not found → uuid = null, display “district map not built”
           ─────────────────────────────────────────────────────────────── */
        function renderDistricts(shapeData, districtMaps = new Map()) {
            districtsGroup.clearLayers();

            const fc = {
                type: 'FeatureCollection',
                features: (shapeData.features || []).map(f => {
                    const props = f.info || f.properties || {};
                    const name = props.name || props.NAME || props.NAME_2 || '';
                    const key = normalizeKey(name);
                    const entry = districtMaps.get(key); // undefined if not uploaded

                    return {
                        type: 'Feature',
                        geometry: f.geometry,
                        properties: {
                            ...props,
                            _name: name,
                            _uuid: entry ? (entry.uuid || null) : null,
                            _built: !!entry // true if a JSON file was uploaded for this district
                        }
                    };
                })
            };

            L.geoJSON(fc, {
                style: feature => ({
                    color: '#2c3e50',
                    weight: 1.5,
                    // Blue = district map built; grey = not built
                    fillColor: feature.properties._built ? '#3498db' : '#95a5a6',
                    fillOpacity: feature.properties._built ? 0.15 : 0.08
                }),
                onEachFeature: (feature, layer) => {
                    const p = feature.properties;
                    const name = p._name || 'Unknown district';
                    const uuid = p._uuid;
                    const built = p._built;

                    // Store on the Leaflet layer for programmatic access
                    layer.districtName = name;
                    layer.districtUuid = uuid;
                    layer.districtBuilt = built;

                    // Tooltip shows name + quick status
                    layer.bindTooltip(
                        `<b>${name}</b><br><small>${built ? '✅ Map built' : '❌ Map not built'}</small>`, {
                            sticky: true
                        }
                    );

                    // Popup shows full details
                    if (built && uuid) {
                        layer.bindPopup(`
                            <div class="district-popup">
                                <h5 style="margin:0 0 6px;color:#2c3e50;font-size:14px">${name}</h5>
                                <table style="font-size:11px;border-collapse:collapse;width:100%;margin-bottom:8px">
                                    <tr>
                                        <td style="color:#888;padding:2px 6px 2px 0;white-space:nowrap">UUID</td>
                                        <td style="font-family:monospace;font-size:10px;
                                                   word-break:break-all;color:#2980b9">${uuid}</td>
                                    </tr>
                                </table>
                                <div style="display:flex;flex-direction:column;gap:5px">
                                    <button onclick="navigator.clipboard.writeText('${uuid}')"
                                        style="font-size:10px;padding:3px 8px;border:1px solid #3498db;
                                               border-radius:3px;background:#ecf5fb;cursor:pointer;color:#2980b9;
                                               text-align:left">
                                        📋 Copy UUID
                                    </button>
                                    <button onclick="pullTargetData('${uuid}','${name}')"
                                        style="font-size:10px;padding:3px 8px;border:1px solid #27ae60;
                                               border-radius:3px;background:#eafaf1;cursor:pointer;color:#1e8449;
                                               text-align:left">
                                        📊 Pull Target Data
                                    </button>
                                    <button onclick="pullCoverageData('${uuid}','${name}')"
                                        style="font-size:10px;padding:3px 8px;border:1px solid #8e44ad;
                                               border-radius:3px;background:#f5eef8;cursor:pointer;color:#7d3c98;
                                               text-align:left">
                                        🛡️ Pull Coverage Data
                                    </button>
                                </div>
                            </div>`, {
                            maxWidth: 290
                        });
                    } else if (built && !uuid) {
                        layer.bindPopup(`
                            <div class="district-popup">
                                <h5 style="margin:0 0 6px;color:#2c3e50">${name}</h5>
                                <span class="badge bg-warning text-dark">
                                    ⚠️ JSON uploaded but no UUID field found
                                </span>
                            </div>`, {
                            maxWidth: 250
                        });
                    } else {
                        // District boundary exists but no individual JSON was uploaded
                        layer.bindPopup(`
                            <div class="district-popup">
                                <h5 style="margin:0 0 6px;color:#7f8c8d">${name}</h5>
                                <span class="badge bg-secondary">
                                    🚧 district map not built
                                </span>
                                <p style="font-size:11px;color:#95a5a6;margin-top:6px">
                                    Upload <code>${normalizeKey(name)}.json</code>
                                    via the District Maps button.
                                </p>
                            </div>`, {
                            maxWidth: 250
                        });
                    }

                    const builtStyle = {
                        fillColor: '#3498db',
                        fillOpacity: 0.35,
                        weight: 2
                    };
                    const defaultStyle = {
                        fillColor: built ? '#3498db' : '#95a5a6',
                        fillOpacity: built ? 0.15 : 0.08,
                        weight: 1.5
                    };

                    layer.on('mouseover', () => layer.setStyle(builtStyle));
                    layer.on('mouseout', () => layer.setStyle(defaultStyle));
                }
            }).addTo(districtsGroup);

            if (fc.features.length) {
                map.fitBounds(districtsGroup.getBounds(), {
                    padding: [20, 20]
                });
            }

            const label = document.getElementById('sideDistrictLabel');
            if (label) label.textContent = `${fc.features.length} districts loaded`;

            buildUuidTable(fc.features);
            return fc;
        }

        /* ── UUID Reference Table ────────────────────────────────────────────────────
           Shows a table below the map listing every district with:
             • UUID  (if its JSON was uploaded)
             • "🚧 district map not built" (if no JSON uploaded yet)
           ─────────────────────────────────────────────────────────────── */
        function buildUuidTable(features) {
            const container = document.getElementById('uuidTableContainer');
            if (!container || !features.length) return;

            const builtCount = features.filter(f => f.properties._built).length;
            const notBuiltCount = features.length - builtCount;

            const rows = features.map((f, i) => {
                const name = f.properties._name || `District ${i + 1}`;
                const uuid = f.properties._uuid;
                const built = f.properties._built;

                if (built && uuid) {
                    return `<tr>
                        <td class="text-muted">${i + 1}</td>
                        <td><strong>${name}</strong></td>
                        <td style="font-family:monospace;font-size:11px;color:#2980b9">${uuid}</td>
                        <td>
                            <button class="btn py-0 px-1" style="font-size:10px"
                                    onclick="navigator.clipboard.writeText('${uuid}')" title="Copy UUID">
                                📋
                            </button>
                        </td>
                        <td><span class="badge bg-success">✅ Built</span></td>
                    </tr>`;
                } else if (built && !uuid) {
                    return `<tr class="table-warning">
                        <td class="text-muted">${i + 1}</td>
                        <td><strong>${name}</strong></td>
                        <td colspan="2" style="font-size:11px;color:#856404">⚠️ File uploaded, no UUID field</td>
                        <td><span class="badge bg-warning text-dark">⚠️ No UUID</span></td>
                    </tr>`;
                } else {
                    return `<tr class="table-secondary">
                        <td class="text-muted">${i + 1}</td>
                        <td><strong>${name}</strong></td>
                        <td colspan="2" class="text-muted" style="font-size:11px">
                            <code>${normalizeKey(name)}.json</code> not uploaded
                        </td>
                        <td><span class="badge bg-secondary">🚧 Not built</span></td>
                    </tr>`;
                }
            }).join('');

            container.innerHTML = `
                <div class="card mt-3 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center"
                         style="cursor:pointer" onclick="toggleUuidTable()">
                        <span>
                            <i class="fas fa-id-card me-2"></i>
                            District UUID Reference
                            <span class="badge bg-success ms-2">${builtCount} built</span>
                            <span class="badge bg-secondary ms-1">${notBuiltCount} not built</span>
                        </span>
                        <span id="uuidToggleIcon">▼ Show</span>
                    </div>
                    <div class="card-body" id="uuidTableBody" style="display:none">
                        <div style="overflow-x:auto">
                            <table class="table table-sm table-hover table-bordered" style="font-size:12px">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th><th>District</th>
                                        <th>UUID</th><th></th><th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyAllUuids()">
                            📋 Copy built UUIDs as JSON
                        </button>
                    </div>
                </div>`;
        }

        function toggleUuidTable() {
            const body = document.getElementById('uuidTableBody');
            const icon = document.getElementById('uuidToggleIcon');
            if (!body) return;
            const hidden = body.style.display === 'none';
            body.style.display = hidden ? 'block' : 'none';
            if (icon) icon.textContent = hidden ? '▲ Hide' : '▼ Show';
        }

        function copyAllUuids() {
            const result = {};
            districtsGroup.eachLayer(layer => {
                const collect = l => {
                    if (l.districtBuilt && l.districtUuid) result[l.districtName] = l.districtUuid;
                    if (l.getLayers) l.getLayers().forEach(collect);
                };
                collect(layer);
            });
            navigator.clipboard.writeText(JSON.stringify(result, null, 2))
                .then(() => alert('✅ Built UUIDs copied as JSON!'))
                .catch(() => alert('❌ Clipboard access denied.'));
        }

        /* ══════════════════════════════════════════════════════════════════
           DISTRICT MAP CRUD – upload, list in modal, delete
           ══════════════════════════════════════════════════════════════════ */

        /** Upload a single district JSON and refresh the UI */
        async function handleDistrictMapUpload() {
            const input = document.getElementById('districtMapFileInput');
            const statusEl = document.getElementById('districtMapUploadStatus');
            const btn = document.getElementById('districtMapUploadBtn');
            const file = input.files[0];

            if (!file) {
                statusEl.className = 'text-danger';
                statusEl.textContent = 'Please choose a .json file first.';
                return;
            }

            btn.disabled = true;
            statusEl.className = 'text-muted';
            statusEl.textContent = 'Uploading…';

            const formData = new FormData();
            formData.append('district_map', file);

            try {
                const res = await fetch(DISTRICT_MAP_UPLOAD_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: formData
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Upload failed.');
                }

                const uuidMsg = data.uuid ?
                    `UUID: <code>${data.uuid}</code>` :
                    `<span class="text-warning">⚠️ No UUID field found in file.</span>`;

                statusEl.className = 'text-success';
                statusEl.innerHTML = `✅ Saved <strong>${data.filename}</strong>. ${uuidMsg}`;
                input.value = '';

                // Reload district maps and re-render boundaries to reflect new status
                await refreshAll();

            } catch (err) {
                statusEl.className = 'text-danger';
                statusEl.textContent = '❌ ' + err.message;
            } finally {
                btn.disabled = false;
            }
        }

        /** Delete a district JSON file by filename */
        async function deleteDistrictMap(filename) {
            if (!confirm(`Delete ${filename}? The district will show "not built" again.`)) return;
            try {
                const res = await fetch(DISTRICT_MAP_DELETE_URL, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        filename
                    })
                });
                if (!res.ok) throw new Error('Delete failed.');
                await refreshAll();
            } catch (err) {
                alert('❌ ' + err.message);
            }
        }

        /**
         * refreshDistrictMapModal(maps)
         * Rebuilds the list inside the District Map Manager modal.
         * maps = Map<normalizedKey, entry> from loadDistrictMaps().
         */
        function refreshDistrictMapModal(maps) {
            const list = document.getElementById('districtMapList');
            const countBadge = document.getElementById('districtMapCount');
            if (!list) return;

            if (countBadge) countBadge.textContent = maps.size;

            if (!maps.size) {
                list.innerHTML = `<div class="text-center text-muted py-3">
                    <i class="fas fa-folder-open"></i> No district maps uploaded yet.</div>`;
                return;
            }

            const items = [...maps.values()].map(entry => {
                let uuidHtml;
                if (entry.uuid) {
                    const sourceBadge = entry.uuid_source === 'filename' ?
                        `<span class="badge bg-success ms-1" style="font-size:9px">from filename</span>` :
                        `<span class="badge bg-info text-dark ms-1" style="font-size:9px">from JSON</span>`;
                    uuidHtml = `<code style="font-size:10px;color:#2980b9">${entry.uuid}</code>${sourceBadge}`;
                } else {
                    uuidHtml = `<span class="badge bg-warning text-dark">⚠️ No UUID found</span>`;
                }
                return `
                    <div class="d-flex align-items-start justify-content-between px-3 py-2
                                border-bottom" style="gap:8px">
                        <div style="flex:1;min-width:0">
                            <strong>${entry.name}</strong>
                            <span class="text-muted" style="font-size:10px"> · ${entry.filename}</span><br>
                            ${uuidHtml}
                        </div>
                        <button class="btn btn-sm btn-outline-danger py-0"
                                onclick="deleteDistrictMap('${entry.filename}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
            }).join('');

            list.innerHTML = items;
        }

        /**
         * refreshDistrictMapSidebar(maps)
         * Updates the sidebar "District Maps" section with a quick status
         * list of uploaded district files.
         */
        function refreshDistrictMapSidebar(maps) {
            const sidebar = document.getElementById('sideDistrictMaps');
            if (!sidebar) return;

            if (!maps.size) {
                sidebar.innerHTML = `<a class="nav-link text-muted" href="#"
                    data-bs-toggle="modal" data-bs-target="#districtMapModal">
                    <div class="sb-nav-link-icon"><i class="fas fa-plus"></i></div>
                    Upload district maps
                </a>`;
                return;
            }

            const items = [...maps.values()].map(entry => {
                const icon = entry.uuid ? '✅' : '⚠️';
                return `<a class="nav-link py-1" href="#" style="font-size:12px">
                    <div class="sb-nav-link-icon">${icon}</div>
                    ${entry.name}
                </a>`;
            }).join('');

            sidebar.innerHTML = items;
        }

        /* ══════════════════════════════════════════════════════════════════
           EPI TRACKER API — Target & Coverage Integration
           Proxied through Laravel /epi/analytics to avoid CORS.
           ══════════════════════════════════════════════════════════════════ */

        const EPI_API = {
            analyticsUrl: "{{ route('epi.analytics') }}",
            healthUrl: "{{ route('epi.health') }}",
            dataSet: 'lyLU2wR22tC',
            timeout: 120000,
            lastUpdatedHours: 24,
        };

        const TARGET_DATA_ELEMENTS = {
            female: 'ECYFfDlmn6x.MViY8dENSwS',
            male: 'ECYFfDlmn6x.JEskOTPddjb',
        };

        const VACCINE_MAPPING = {
            'x3aIDdpR65a': { name: 'BCG', female: 'x3aIDdpR65a.MViY8dENSwS', male: 'x3aIDdpR65a.JEskOTPddjb' },
            'HOq1Ax6xB19': { name: 'Penta - 1', female: 'HOq1Ax6xB19.MViY8dENSwS', male: 'HOq1Ax6xB19.JEskOTPddjb' },
            'NCu55gLH6Te': { name: 'Penta - 2', female: 'NCu55gLH6Te.MViY8dENSwS', male: 'NCu55gLH6Te.JEskOTPddjb' },
            'b3FM2S2oaAd': { name: 'Penta - 3', female: 'b3FM2S2oaAd.MViY8dENSwS', male: 'b3FM2S2oaAd.JEskOTPddjb' },
            'xyVY5CmifZP': { name: 'MR - 1', female: 'xyVY5CmifZP.MViY8dENSwS', male: 'xyVY5CmifZP.JEskOTPddjb' },
            'nHwxXPJziO2': { name: 'MR - 2', female: 'nHwxXPJziO2.MViY8dENSwS', male: 'nHwxXPJziO2.JEskOTPddjb' },
        };

        const DEMOGRAPHIC_DATA = {
            population: { female: 'TLEJEfexUYw.MViY8dENSwS', male: 'TLEJEfexUYw.JEskOTPddjb' },
            child_0_15_month: { female: 'Kffn1czgHoS.MViY8dENSwS', male: 'Kffn1czgHoS.JEskOTPddjb' },
            child_0_11_month: { female: 'ECYFfDlmn6x.MViY8dENSwS', male: 'ECYFfDlmn6x.JEskOTPddjb' },
            number_of_sessions_in_year: 'YluGjI7JcxN',
            women_15_to_49: 'd8r3mR6oIuA',
            ha_vaccinator_designation1: 'FkF8y84Z3OO',
            ha_vaccinator_name1: 'VjRxNpL1CUb',
            ha_vaccinator_designation2: 'QMQ70B2aztC',
            ha_vaccinator_name2: 'cg8C5iBorBI',
            supervisor1_designation: 'BoQ8rOAKFQW',
            supervisor1_name: 'kUqReVELzVn',
            epi_center_name_address: 'IRQHGfUcquk',
            epi_center_implementer_name: 'KAkJhBdhdlG',
            distance_from_cc_to_epi_center: 'iZ4zJfEOcES',
            mode_of_transportation_distribution: 'vnb8vMsdceG',
            mode_of_transportation_uhc: 'VK0ynF6yMfP',
            time_to_reach_distribution_point: 'I6Fs0JFrTJJ',
            time_to_reach_epi_center: 'DJigIkk23jm',
            porter_name: 'DysowKIp2DD',
            porter_mobile: 'lxTGXRL4mmL',
            epi_center_type: 'MHLPf2fQJGh',
        };

        const STRING_DATA_ELEMENT_UIDS = [
            'VjRxNpL1CUb', 'cg8C5iBorBI', 'kUqReVELzVn', 'DysowKIp2DD',
            'FkF8y84Z3OO', 'QMQ70B2aztC', 'BoQ8rOAKFQW', 'IRQHGfUcquk',
            'KAkJhBdhdlG', 'vnb8vMsdceG', 'VK0ynF6yMfP', 'MHLPf2fQJGh', 'lxTGXRL4mmL',
        ];

        function isNumeric(val) {
            return val != null && val !== '' && !isNaN(val);
        }

        function getLastUpdatedTimestamp(hoursAgo = 24) {
            const d = new Date(Date.now() - hoursAgo * 60 * 60 * 1000);
            const pad = n => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}.000`;
        }

        function getAllCoverageDataElements() {
            const elements = [];
            Object.values(VACCINE_MAPPING).forEach(v => {
                elements.push(v.female, v.male);
            });
            Object.values(DEMOGRAPHIC_DATA).forEach(v => {
                if (typeof v === 'object') {
                    elements.push(...Object.values(v));
                } else {
                    elements.push(v);
                }
            });
            return [...new Set(elements)];
        }

        function buildCoveragePeriods(startYear = 2024) {
            const periods = [];
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1;
            for (let y = startYear; y <= currentYear; y++) {
                periods.push(String(y));
                for (let m = 1; m <= 12; m++) {
                    if (y === currentYear && m > currentMonth) {
                        continue;
                    }
                    periods.push(`${y}${String(m).padStart(2, '0')}`);
                }
            }
            return periods.join(';');
        }

        function buildTargetYears() {
            const years = [];
            const cy = new Date().getFullYear();
            for (let i = 2024; i <= cy; i++) {
                years.push(i);
            }
            return years;
        }

        function parseTargetRows(rows) {
            const byArea = {};
            rows.forEach(([dx, ou, pe, raw]) => {
                if (!ou || !pe || !isNumeric(raw)) {
                    return;
                }
                if (!byArea[ou]) {
                    byArea[ou] = { child_0_to_11_month: {} };
                }
                if (!byArea[ou].child_0_to_11_month[pe]) {
                    byArea[ou].child_0_to_11_month[pe] = { male: 0, female: 0 };
                }
                const val = Math.round(parseFloat(raw) * 100) / 100;
                if (dx === TARGET_DATA_ELEMENTS.female) {
                    byArea[ou].child_0_to_11_month[pe].female = val;
                }
                if (dx === TARGET_DATA_ELEMENTS.male) {
                    byArea[ou].child_0_to_11_month[pe].male = val;
                }
            });
            return byArea;
        }

        function mapDataElementsToVaccines(dataElements) {
            const vaccines = [];
            Object.entries(VACCINE_MAPPING).forEach(([uid, m]) => {
                vaccines.push({
                    vaccine_uid: uid,
                    vaccine_name: m.name,
                    male: dataElements[m.male] ?? null,
                    female: dataElements[m.female] ?? null,
                });
            });
            const demographics = {};
            Object.entries(DEMOGRAPHIC_DATA).forEach(([key, val]) => {
                if (typeof val === 'object') {
                    demographics[key] = {
                        female: dataElements[val.female] ?? null,
                        male: dataElements[val.male] ?? null,
                    };
                } else {
                    let v = dataElements[val] ?? null;
                    if (STRING_DATA_ELEMENT_UIDS.includes(val) && v != null) {
                        v = String(v);
                    }
                    demographics[key] = v;
                }
            });
            return { vaccines, demographics };
        }

        function parseCoverageRows(rows) {
            const organized = {};
            rows.forEach(([dx, ou, pe, raw]) => {
                if (!ou || !pe) {
                    return;
                }
                let value;
                if (STRING_DATA_ELEMENT_UIDS.includes(dx)) {
                    value = raw != null ? String(raw) : null;
                } else {
                    if (raw == null || raw === '' || isNaN(raw)) {
                        return;
                    }
                    value = Math.round(parseFloat(raw) * 100) / 100;
                }
                if (!organized[ou]) {
                    organized[ou] = {};
                }
                if (!organized[ou][pe]) {
                    organized[ou][pe] = {};
                }
                organized[ou][pe][dx] = value;
            });

            const result = {};
            Object.entries(organized).forEach(([ou, periods]) => {
                result[ou] = { child_0_to_11_month: {}, demographics: {} };
                Object.entries(periods).forEach(([pe, dataElements]) => {
                    const mapped = mapDataElementsToVaccines(dataElements);
                    if (pe.length === 4) {
                        result[ou].child_0_to_11_month[pe] = { vaccine: mapped.vaccines };
                        result[ou].demographics[pe] = mapped.demographics;
                    } else if (pe.length === 6) {
                        const year = pe.slice(0, 4);
                        const month = parseInt(pe.slice(4, 6), 10);
                        if (!result[ou].child_0_to_11_month[year]) {
                            result[ou].child_0_to_11_month[year] = { vaccine: [] };
                        }
                        if (!result[ou].child_0_to_11_month[year].months) {
                            result[ou].child_0_to_11_month[year].months = {};
                        }
                        result[ou].child_0_to_11_month[year].months[month] = { vaccine: mapped.vaccines };
                    }
                });
            });
            return result;
        }

        function buildEpiCommonQuery() {
            const params = new URLSearchParams({
                dataSet: EPI_API.dataSet,
                lastUpdated: getLastUpdatedTimestamp(EPI_API.lastUpdatedHours),
                showHierarchy: 'false',
                hierarchyMeta: 'false',
                includeMetadataDetails: 'true',
                includeNumDen: 'true',
                skipRounding: 'false',
                completedOnly: 'false',
                outputIdScheme: 'UID',
            });
            return params;
        }

        /**
         * Send dx/ou/pe as separate params. The Laravel proxy rebuilds the
         * DHIS2 dimension=dx:…&dimension=ou:…&dimension=pe:… query so PHP
         * never collapses repeated "dimension" keys.
         */
        function buildEpiUrl(dx, ou, pe) {
            const params = buildEpiCommonQuery();
            params.set('dx', dx);
            params.set('ou', ou);
            params.set('pe', pe);
            return `${EPI_API.analyticsUrl}?${params.toString()}`;
        }

        async function epiAnalyticsFetch(url, maxRetries = 3) {
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), EPI_API.timeout);

            for (let attempt = 0; attempt <= maxRetries; attempt++) {
                try {
                    const res = await fetch(url, {
                        headers: { Accept: 'application/json' },
                        signal: controller.signal,
                    });

                    if (res.status === 429 && attempt < maxRetries) {
                        await new Promise(r => setTimeout(r, 30000 * Math.pow(2, attempt)));
                        continue;
                    }

                    if (!res.ok) {
                        let detail = '';
                        try {
                            const body = await res.json();
                            detail = body.message ? `: ${body.message}` : '';
                        } catch { /* ignore */ }
                        throw new Error(`EPI API returned HTTP ${res.status}${detail}`);
                    }

                    clearTimeout(timer);
                    return await res.json();
                } catch (err) {
                    if (attempt === maxRetries) {
                        clearTimeout(timer);
                        throw err;
                    }
                    if (err.name === 'AbortError') {
                        clearTimeout(timer);
                        throw new Error('EPI API request timed out');
                    }
                }
            }
        }

        async function fetchTarget(areaUids, years = null) {
            const y = years || buildTargetYears();
            const url = buildEpiUrl(
                `${TARGET_DATA_ELEMENTS.female};${TARGET_DATA_ELEMENTS.male}`,
                areaUids.join(';'),
                y.join(';')
            );
            const data = await epiAnalyticsFetch(url);
            return parseTargetRows(data.rows || []);
        }

        async function fetchCoverage(areaUids) {
            const url = buildEpiUrl(
                getAllCoverageDataElements().join(';'),
                areaUids.join(';'),
                buildCoveragePeriods(2024)
            );
            const data = await epiAnalyticsFetch(url);
            return parseCoverageRows(data.rows || []);
        }

        function openEpiPanel(uuid, name, activeTab = 'target') {
            document.getElementById('epiPanelDistrictName').textContent = name;
            document.getElementById('epiPanelUuid').textContent = uuid;
            const tabId = activeTab === 'coverage' ? 'coverageTabBtn' : 'targetTabBtn';
            bootstrap.Tab.getOrCreateInstance(document.getElementById(tabId)).show();
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('epiDataPanel')).show();
        }

        function renderLoading(containerId, label) {
            document.getElementById(containerId).innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-3 d-block"></i>
                    Fetching ${label}…
                </div>`;
        }

        function renderError(containerId, message) {
            document.getElementById(containerId).innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i>${message}
                </div>`;
        }

        function renderTargetData(uuid, data) {
            const area = data[uuid];
            const container = document.getElementById('targetDataContent');
            if (!area?.child_0_to_11_month || !Object.keys(area.child_0_to_11_month).length) {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-1"></i>No target data found for this district UUID.
                    </div>`;
                return;
            }

            const years = Object.keys(area.child_0_to_11_month).sort();
            const rows = years.map(year => {
                const t = area.child_0_to_11_month[year];
                return `<tr>
                    <td><strong>${year}</strong></td>
                    <td class="text-end">${t.male ?? '—'}</td>
                    <td class="text-end">${t.female ?? '—'}</td>
                    <td class="text-end">${(t.male ?? 0) + (t.female ?? 0)}</td>
                </tr>`;
            }).join('');

            container.innerHTML = `
                <p class="small text-muted mb-2">Child (0–11 month) targets by year</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-success">
                            <tr><th>Year</th><th class="text-end">Male</th><th class="text-end">Female</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
                <details class="mt-3">
                    <summary class="small text-muted" style="cursor:pointer">Raw JSON</summary>
                    <pre class="small bg-light p-2 mt-2 rounded" style="max-height:300px;overflow:auto">${JSON.stringify(area, null, 2)}</pre>
                </details>`;
        }

        function renderCoverageData(uuid, data) {
            const area = data[uuid];
            const container = document.getElementById('coverageDataContent');
            if (!area) {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-1"></i>No coverage data found for this district UUID.
                    </div>`;
                return;
            }

            const years = Object.keys(area.child_0_to_11_month || {}).sort().reverse();
            let html = '';

            years.forEach(year => {
                const yearData = area.child_0_to_11_month[year];
                const vaccines = yearData?.vaccine || [];
                const demo = area.demographics?.[year] || {};

                const vaccineRows = vaccines.map(v => `
                    <tr>
                        <td>${v.vaccine_name}</td>
                        <td class="text-end">${v.male ?? '—'}</td>
                        <td class="text-end">${v.female ?? '—'}</td>
                    </tr>`).join('');

                const demoRows = Object.entries(demo).map(([key, val]) => {
                    let display;
                    if (val && typeof val === 'object' && ('male' in val || 'female' in val)) {
                        display = `♂ ${val.male ?? '—'} / ♀ ${val.female ?? '—'}`;
                    } else {
                        display = val ?? '—';
                    }
                    return `<tr><td style="font-size:11px">${key.replace(/_/g, ' ')}</td><td style="font-size:11px">${display}</td></tr>`;
                }).join('');

                const monthCount = yearData?.months ? Object.keys(yearData.months).length : 0;

                html += `
                    <div class="card mb-3">
                        <div class="card-header py-2"><strong>${year}</strong>
                            ${monthCount ? `<span class="badge bg-secondary ms-2">${monthCount} months</span>` : ''}
                        </div>
                        <div class="card-body p-2">
                            <p class="small fw-semibold mb-1">Vaccines (yearly)</p>
                            <div class="table-responsive mb-2">
                                <table class="table table-sm table-bordered mb-0" style="font-size:11px">
                                    <thead class="table-light">
                                        <tr><th>Vaccine</th><th class="text-end">Male</th><th class="text-end">Female</th></tr>
                                    </thead>
                                    <tbody>${vaccineRows || '<tr><td colspan="3" class="text-muted">No vaccine data</td></tr>'}</tbody>
                                </table>
                            </div>
                            <p class="small fw-semibold mb-1">Demographics</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size:11px">
                                    <tbody>${demoRows || '<tr><td class="text-muted">No demographic data</td></tr>'}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>`;
            });

            if (!html) {
                html = `<div class="alert alert-warning">No coverage records returned.</div>`;
            }

            container.innerHTML = html + `
                <details class="mt-2">
                    <summary class="small text-muted" style="cursor:pointer">Raw JSON</summary>
                    <pre class="small bg-light p-2 mt-2 rounded" style="max-height:300px;overflow:auto">${JSON.stringify(area, null, 2)}</pre>
                </details>`;
        }

        async function pullTargetData(uuid, name) {
            openEpiPanel(uuid, name, 'target');
            renderLoading('targetDataContent', 'target data');
            try {
                const data = await fetchTarget([uuid]);
                renderTargetData(uuid, data);
            } catch (err) {
                console.error(err);
                renderError('targetDataContent', err.message || 'Failed to fetch target data.');
            }
        }

        async function pullCoverageData(uuid, name) {
            openEpiPanel(uuid, name, 'coverage');
            renderLoading('coverageDataContent', 'coverage data');
            try {
                const data = await fetchCoverage([uuid]);
                renderCoverageData(uuid, data);
            } catch (err) {
                console.error(err);
                renderError('coverageDataContent', err.message || 'Failed to fetch coverage data.');
            }
        }

        async function checkEpiHealth() {
            const badge = document.getElementById('epiHealthBadge');
            if (!badge) {
                return;
            }
            try {
                const res = await fetch(EPI_API.healthUrl, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                if (data.status === 'ok') {
                    badge.className = 'badge bg-success';
                    badge.innerHTML = '<i class="fas fa-check-circle me-1"></i>EPI API OK';
                } else {
                    badge.className = 'badge bg-danger';
                    badge.innerHTML = '<i class="fas fa-times-circle me-1"></i>EPI Unreachable';
                }
            } catch {
                badge.className = 'badge bg-danger';
                badge.innerHTML = '<i class="fas fa-times-circle me-1"></i>EPI Unreachable';
            }
        }

        checkEpiHealth();

        /* ══════════════════════════════════════════════════════════════════
           SHAPEFILE UPLOAD & AUTO-LOAD
           (the big districts.json.gz boundary file)
           ══════════════════════════════════════════════════════════════════ */

        const SHAPEFILE_UPLOAD_URL = "{{ route('shapefile.upload') }}";
        const SHAPEFILE_CURRENT_URL = "{{ route('shapefile.current') }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function uploadShapeFileToServer(file) {
            const formData = new FormData();
            formData.append('shapefile', file);
            const res = await fetch(SHAPEFILE_UPLOAD_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: formData
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Upload failed.');
            return data;
        }

        async function fetchCurrentShapeFileInfo() {
            const res = await fetch(SHAPEFILE_CURRENT_URL, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) return {
                exists: false
            };
            return res.json();
        }

        async function fetchAndDecompressFromUrl(url) {
            const bustedUrl = url + (url.includes('?') ? '&' : '?') + 'v=' + Date.now();
            const res = await fetch(bustedUrl);
            if (!res.ok) throw new Error('Could not fetch shape file from storage.');
            const buffer = await res.arrayBuffer();
            try {
                const stream = new Blob([buffer]).stream().pipeThrough(new DecompressionStream('deflate'));
                return JSON.parse(await new Response(stream).text());
            } catch {
                const stream = new Blob([buffer]).stream().pipeThrough(new DecompressionStream('gzip'));
                return JSON.parse(await new Response(stream).text());
            }
        }

        /** Latest loaded ShapeData (so re-renders can access it) */
        let _lastShapeData = null;

        /**
         * refreshAll()
         * ────────────────────────────────────────────────────────────
         * Called after any district-map upload or delete.
         * Reloads the district-map index and, if boundaries are loaded,
         * re-renders them so popup colours and UUIDs update instantly.
         */
        async function refreshAll() {
            const maps = await loadDistrictMaps();
            refreshDistrictMapModal(maps);
            refreshDistrictMapSidebar(maps);
            // Re-render boundaries if we have shape data
            if (_lastShapeData) {
                renderDistricts(_lastShapeData, maps);
            }
        }

        async function handleShapeFileUpload() {
            const fileInput = document.getElementById('shapeFileInput');
            const statusEl = document.getElementById('uploadStatus');
            const submitBtn = document.getElementById('uploadSubmitBtn');
            const file = fileInput.files[0];

            if (!file) {
                statusEl.textContent = 'Please choose a .gz shape file first.';
                statusEl.className = 'text-danger';
                return;
            }

            submitBtn.disabled = true;
            try {
                statusEl.className = 'text-muted';
                statusEl.textContent = 'Uploading to storage…';
                const uploadResult = await uploadShapeFileToServer(file);

                statusEl.textContent = 'Decompressing and rendering…';
                const shapeData = await fetchAndDecompressFromUrl(uploadResult.url);

                if (!shapeData?.features) throw new Error('Invalid shape file – missing "features".');
                _lastShapeData = shapeData;

                const maps = await loadDistrictMaps();
                refreshDistrictMapModal(maps);
                refreshDistrictMapSidebar(maps);
                renderDistricts(shapeData, maps);

                statusEl.className = 'text-success';
                statusEl.textContent = `✅ Loaded ${shapeData.features.length} districts.`;

                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal')) ||
                        new bootstrap.Modal(document.getElementById('uploadModal'));
                    modal.hide();
                }, 900);

            } catch (err) {
                console.error(err);
                statusEl.className = 'text-danger';
                statusEl.textContent = '❌ ' + err.message;
            } finally {
                submitBtn.disabled = false;
            }
        }

        /* ── On page load: load district maps, then boundaries ──────────────────
           TEACHING NOTE – Sequencing:
           We FIRST fetch district-map UUIDs, THEN load the boundary shapefile.
           This ensures renderDistricts() has the lookup map available
           on the very first render (not just after re-uploads).
           ─────────────────────────────────────────────────────────────── */
        (async () => {
            try {
                // 1. load district-map UUIDs first
                const maps = await loadDistrictMaps();
                refreshDistrictMapModal(maps);
                refreshDistrictMapSidebar(maps);

                // 2. then load and render the boundary shapefile
                const info = await fetchCurrentShapeFileInfo();
                if (info.exists) {
                    const shapeData = await fetchAndDecompressFromUrl(info.url);
                    _lastShapeData = shapeData;
                    renderDistricts(shapeData, maps);
                }
            } catch (e) {
                console.warn('Startup error.', e);
            }
        })();

        /* ── Fly-to helper used by sidebar links ── */
        function flyTo(coords, zoom = 12) {
            map.flyTo(coords, zoom, {
                duration: 1.5
            });
        }
    </script>
</body>

</html>