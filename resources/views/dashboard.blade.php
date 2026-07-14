<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>GIS</title>
        <link href="{{ asset('frontend/css/styles.css') }}" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Leaflet CSS -->
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
          <style>
        body{
            margin:0;
            font-family:Arial, Helvetica, sans-serif;
        }

        #map{
            height:100vh;
        }

        .controls{
            position:absolute;
            top:10px;
            left:10px;
            z-index:1000;
            background:white;
            padding:10px;
            border-radius:5px;
            box-shadow:0 0 10px rgba(0,0,0,.3);
        }

        .controls button{
            display:block;
            width:180px;
            margin-bottom:8px;
            padding:8px;
            cursor:pointer;
        }

        .controls button.upload-btn{
            background:#2c3e50;
            color:#fff;
            border:none;
            border-radius:4px;
        }

        #info{
            margin-top:10px;
            font-size:13px;
            color:#333;
            max-width:180px;
        }

        #uploadStatus{
            margin-top:10px;
            font-size:13px;
            min-height:20px;
        }

        .district-badge{
            display:inline-block;
            background:#eef3f8;
            border-radius:4px;
            padding:2px 6px;
            font-size:11px;
            color:#2c3e50;
        }
    </style>
    </head>
    <body>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="{{ url('/dashboard') }}">GIS Dashboard</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="#!">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Core</div>
                            <a class="nav-link" href="{{ url('dashboard') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>

                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Start Bootstrap
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <div class="controls">
                            <button onclick="goDhaka()">Dhaka</button>
                            <button onclick="goChattogram()">Chattogram</button>
                            <button onclick="goKhulna()">Khulna</button>
                            <button class="upload-btn" type="button" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload"></i> Upload Shape File
                            </button>
                            <div id="info"></div>
                        </div>

                        <div id="map"></div>
                        <div style="height: 100vh"></div>
                        <div class="card mb-4"><div class="card-body">When scrolling, the navigation stays at the top of the page. This is the end of the static navigation demo.</div></div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2023</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Upload Shape File Modal -->
        <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload Shape File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Select the compressed shape file (e.g. <code>shape.json.gz</code>) containing the
                            64 district boundaries. It will be uploaded and saved to
                            <code>storage/app/public/shape</code>, then decompressed in your browser and
                            rendered on the map. On future visits the districts will load automatically
                            straight from storage — no re-upload needed.
                        </p>
                        <div class="mb-3">
                            <label for="shapeFileInput" class="form-label">Shape file (.gz)</label>
                            <input class="form-control" type="file" id="shapeFileInput" accept=".gz" />
                        </div>
                        <div id="uploadStatus" class="text-muted"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="uploadSubmitBtn" onclick="handleShapeFileUpload()">
                            Load &amp; Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

/* ---------------------------------------------------------
   Map setup
--------------------------------------------------------- */
const map = L.map('map').setView([23.8103, 90.4125], 8);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

const marker = L.marker([23.8103, 90.4125]).addTo(map);
marker.bindPopup(`
    <h3>Dhaka</h3>
    <p>Capital of Bangladesh</p>
`);
marker.bindTooltip("Dhaka");

function goDhaka() {
    map.flyTo([23.8103, 90.4125], 12);
}

function goChattogram() {
    map.flyTo([22.3569, 91.7832], 12);
}

function goKhulna() {
    map.flyTo([22.8456, 89.5403], 12);
}

/* ---------------------------------------------------------
   Server storage endpoints
   The shape file is uploaded to, and served back from,
   storage/app/public/shape via a small Laravel controller.
   See ShapeFileController.php + routes-additions.php.
--------------------------------------------------------- */
const SHAPEFILE_UPLOAD_URL = "{{ route('shapefile.upload') }}";
const SHAPEFILE_CURRENT_URL = "{{ route('shapefile.current') }}";
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function uploadShapeFileToServer(file) {
    const formData = new FormData();
    formData.append('shapefile', file);

    const response = await fetch(SHAPEFILE_UPLOAD_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: formData
    });

    const data = await response.json();
    if (!response.ok || !data.success) {
        throw new Error(data.message || 'Upload failed.');
    }
    return data; // { success, path, url, size }
}

async function fetchCurrentShapeFileInfo() {
    const response = await fetch(SHAPEFILE_CURRENT_URL, {
        headers: { 'Accept': 'application/json' }
    });
    if (!response.ok) return { exists: false };
    return response.json(); // { exists, url, last_modified, size }
}

async function fetchAndDecompressFromUrl(url) {
    // Cache-bust so a freshly re-uploaded file isn't served stale.
    const bustedUrl = url + (url.includes('?') ? '&' : '?') + 'v=' + Date.now();
    const response = await fetch(bustedUrl);
    if (!response.ok) throw new Error('Could not fetch shape file from storage.');
    const buffer = await response.arrayBuffer();
    return decompressShapeFile(buffer);
}

/* ---------------------------------------------------------
   Decompression
   The shape file is zlib-compressed JSON (RFC 1950 header
   0x78 0xda), which the native DecompressionStream('deflate')
   API understands directly — no external library required.
--------------------------------------------------------- */
async function decompressShapeFile(arrayBuffer) {
    const stream = new Blob([arrayBuffer]).stream().pipeThrough(new DecompressionStream('deflate'));
    const text = await new Response(stream).text();
    return JSON.parse(text);
}

/* ---------------------------------------------------------
   Rendering district boundaries
--------------------------------------------------------- */
let districtsLayer = null;

function renderDistricts(shapeData) {
    if (districtsLayer) {
        map.removeLayer(districtsLayer);
        districtsLayer = null;
    }

    const featureCollection = {
        type: 'FeatureCollection',
        features: (shapeData.features || []).map(f => ({
            type: 'Feature',
            geometry: f.geometry,
            properties: f.info || f.properties || {}
        }))
    };

    districtsLayer = L.geoJSON(featureCollection, {
        style: () => ({
            color: '#2c3e50',
            weight: 1.5,
            fillColor: '#3498db',
            fillOpacity: 0.15
        }),
        onEachFeature: (feature, layer) => {
            const name = (feature.properties && feature.properties.name) || 'Unknown district';
            layer.bindTooltip(name, { sticky: true });
            layer.bindPopup(`<b>${name}</b>`);
            layer.on('mouseover', () => layer.setStyle({ fillOpacity: 0.4, weight: 2 }));
            layer.on('mouseout', () => layer.setStyle({ fillOpacity: 0.15, weight: 1.5 }));
        }
    }).addTo(map);

    if (featureCollection.features.length) {
        map.fitBounds(districtsLayer.getBounds());
    }

    document.getElementById('info').innerHTML =
        `<span class="district-badge">${featureCollection.features.length} districts loaded</span>`;
}

/* ---------------------------------------------------------
   Upload modal handler
--------------------------------------------------------- */
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
        statusEl.textContent = 'Uploading to storage/app/public/shape...';
        const uploadResult = await uploadShapeFileToServer(file);

        statusEl.textContent = 'Fetching saved file from storage...';
        const shapeData = await fetchAndDecompressFromUrl(uploadResult.url);

        if (!shapeData || !Array.isArray(shapeData.features)) {
            throw new Error('File does not look like a valid shape file (missing "features").');
        }

        statusEl.textContent = 'Rendering districts on the map...';
        renderDistricts(shapeData);

        statusEl.className = 'text-success';
        statusEl.textContent = `Saved to ${uploadResult.path} and loaded ${shapeData.features.length} districts.`;

        setTimeout(() => {
            const modalEl = document.getElementById('uploadModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
        }, 900);
    } catch (err) {
        console.error(err);
        statusEl.className = 'text-danger';
        statusEl.textContent = 'Error: ' + err.message;
    } finally {
        submitBtn.disabled = false;
    }
}

/* ---------------------------------------------------------
   On page load: check storage/app/public/shape via the
   Laravel endpoint, and if a shape file is already saved
   there, fetch + render it automatically.
--------------------------------------------------------- */
(async () => {
    try {
        const info = await fetchCurrentShapeFileInfo();
        if (info.exists) {
            const shapeData = await fetchAndDecompressFromUrl(info.url);
            renderDistricts(shapeData);
        }
    } catch (e) {
        console.warn('No shape file found in storage yet.', e);
    }
})();

</script>
    </body>
</html>