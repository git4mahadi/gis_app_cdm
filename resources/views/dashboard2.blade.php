<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GIS</title>

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

        .district-icon {
            width: 28px;
            height: 28px;
            background: #8adb34;
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
            </main>
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
        const districtIcon = L.divIcon({
            html: '<div class="custom-icon district-icon">⁎</div>',
            className: '',
            iconSize: [22, 22],
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
                type: 'district',
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
                type: 'district',
                pop: '349K',
                desc: 'Industrial suburb of Dhaka'
            },
            {
                name: 'Bogura',
                coords: [24.8510, 89.3696],
                type: 'district',
                pop: '398K',
                desc: 'North-central city'
            },
            {
                name: 'Jessore',
                coords: [23.1667, 89.2167],
                type: 'district',
                pop: '237K',
                desc: 'SW border city'
            },
            {
                name: 'Dinajpur',
                coords: [25.6279, 88.6338],
                type: 'district',
                pop: '215K',
                desc: 'N border & rice city'
            },
        ];

        // Build a marker for each city and add it to the cluster group
        bangladeshCities.forEach(city => {
            const icon = city.type === 'capital' ? capitalIcon :
                city.type === 'port' ? portIcon :
                city.type === 'division' ? divisionIcon :
                districtIcon;

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
            <span class="legend-color" style="background:#3498db;opacity:.5;border:1px solid #cf4668"></span>
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

        /* ════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████
           SECTION 13 ─ SHAPEFILE UPLOAD & AUTO-LOAD
           (same logic as before – unchanged)
           ██████████████████████████████████████████████████████████████████ */

        const SHAPEFILE_UPLOAD_URL = "{{ route('shapefile.upload') }}";
        const SHAPEFILE_CURRENT_URL = "{{ route('shapefile.current') }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        /* ── Upload file to Laravel storage ── */
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

        /* ── Get info about the currently stored file ── */
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

        /* ── Fetch the .gz file and decompress it in the browser ──
           TEACHING NOTE:
           DecompressionStream('deflate') is a native browser API (Chrome 80+,
           Firefox 102+). It decompresses zlib-format data (0x78 header) without
           any external library. 'deflate-raw' handles raw DEFLATE, 'gzip' handles .gz.
           ─────────────────────────────────────────────────────────────── */
        async function fetchAndDecompressFromUrl(url) {
            const bustedUrl = url + (url.includes('?') ? '&' : '?') + 'v=' + Date.now();
            const res = await fetch(bustedUrl);
            if (!res.ok) throw new Error('Could not fetch shape file from storage.');
            const buffer = await res.arrayBuffer();

            // Try deflate first (zlib), fall back to gzip
            try {
                const stream = new Blob([buffer]).stream()
                    .pipeThrough(new DecompressionStream('deflate'));
                return JSON.parse(await new Response(stream).text());
            } catch {
                const stream = new Blob([buffer]).stream()
                    .pipeThrough(new DecompressionStream('gzip'));
                return JSON.parse(await new Response(stream).text());
            }
        }

        /* ── Render district polygons on the map ──
           TEACHING NOTE:
           L.geoJSON(data, options) converts GeoJSON FeatureCollections into
           Leaflet layers. Key options:
             style(feature)             → return a path-options object per feature
             onEachFeature(feature, layer) → bind popups/tooltips, attach events
           ─────────────────────────────────────────────────────────────── */
        function renderDistricts(shapeData) {
            districtsGroup.clearLayers(); // remove any previous boundaries

            const fc = {
                type: 'FeatureCollection',
                features: (shapeData.features || []).map(f => ({
                    type: 'Feature',
                    geometry: f.geometry,
                    properties: f.info || f.properties || {}
                }))
            };

            L.geoJSON(fc, {
                style: () => ({
                    color: '#b70214',
                    weight: 1.5,
                    fillColor: '#3498db',
                    fillOpacity: 0.8
                }),
                onEachFeature: (feature, layer) => {
                    const name = feature.properties?.name || feature.properties?.DISTNAME || 'Unnamed District';
                    layer.bindTooltip(name, {
                        sticky: true
                    });
                    layer.bindPopup(`<b>${name}</b>`);
                    layer.on('mouseover', () => layer.setStyle({
                        fillOpacity: 0.35,
                        weight: 2
                    }));
                    layer.on('mouseout', () => layer.setStyle({
                        fillOpacity: 0.8,
                        weight: 1.5
                    }));
                }
            }).addTo(districtsGroup);

            if (fc.features.length) {
                map.fitBounds(districtsGroup.getBounds(), {
                    padding: [20, 20]
                });
            }

            // Update sidebar label
            const label = document.getElementById('sideDistrictLabel');
            if (label) label.textContent = `${fc.features.length} districts loaded`;
        }

        /* ── Upload modal handler ── */
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
                renderDistricts(shapeData);

                statusEl.className = 'text-success';
                statusEl.textContent = `✅ Saved & loaded ${shapeData.features.length} districts.`;

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

        /* ── On page load: try to restore saved shape file ── */
        (async () => {
            try {
                const info = await fetchCurrentShapeFileInfo();
                if (info.exists) {
                    const shapeData = await fetchAndDecompressFromUrl(info.url);
                    renderDistricts(shapeData);
                }
            } catch (e) {
                console.warn('No shape file in storage yet.', e);
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