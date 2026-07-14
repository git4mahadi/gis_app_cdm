<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaflet Basic Exercise</title>

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
            width:150px;
            margin-bottom:8px;
            padding:8px;
            cursor:pointer;
        }

        #info{
            margin-top:10px;
            font-size:14px;
        }
    </style>
</head>
<body>

<div class="controls">


</div>

<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

const map = L.map('map').setView([23.8103, 90.4125], 8);

const marker = L.marker([23.8103, 90.4125]).addTo(map);

marker.bindPopup(`
    <h3>Dhaka</h3>
    <p>Capital of Bangladesh</p>
`);


L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap contributors'
}).addTo(map);
</script>

</body>
</html>

