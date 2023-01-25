<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roof size calculator</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link href="https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.js"></script>
</head>

<body>
    <style>
    .calculation-box {
        height: 75px;
        width: 150px;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 15px;
        text-align: center;
        display: none;
    }

    p {
        font-family: 'Open Sans';
        margin: 0;
    }
    </style>

    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.2.2/mapbox-gl-draw.js"></script>
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.2.2/mapbox-gl-draw.css"
        type="text/css">
    <div class="card">
        <div class="card-body">
            <div class="mt-3">
                <input type="text" class="form-control" id="street-address"
                    placeholder="Enter a street address in the United States to start" onkeyup="searchLocation()">
                <div id="select-suggestions"></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div id='map' style='height: 300px;'>
                    </div>
                </div>

                <div class="col-6 calculation-box">
                    <p>Click the map to draw a polygon.</p>
                    <div id="calculated-area"></div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>

    <script>
    const accessToken = 'pk.eyJ1Ijoid2FpaHVyYSIsImEiOiJja2ViYXhrb3QwNzNpMnNwN3E1cjJqc2FiIn0.hRhDToVXvpHz57fPueVtdg'

    function searchLocation() {
        let searchText = $('#street-address').val(),
            url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'

        const endpoint = url + searchText + '.json?access_token=' + accessToken +
            '&country=US&autocomplete=true&types=address'

        const wesPromise = fetch(endpoint)

        wesPromise
            .then((response) => response.json())
            .then((data) => {
                console.log(data.features)

                let suggestions = data.features,
                    html = '',
                    options = document.getElementById("select-suggestions")
                for (var i = 0; i < suggestions.length; i++) {
                    html += "<div class='selector' id=" + suggestions[i].geometry.coordinates + ">" +
                        suggestions[i]
                        .place_name +
                        "</div>";
                }

                options.innerHTML = html

                const selectors = [].slice.call(document.getElementsByClassName('selector'))

                selectors.forEach((element, index) => {
                    element.addEventListener('click', (event) => {
                        handleOnclick(event.target.id)
                        $('#street-address').val(event.target.textContent)
                        options.innerHTML = ''
                    })
                })
            })

            .catch(handleError)
    }

    function handleError(err) {
        console.log("Error fetching data, ", err)
    }

    function handleOnclick(value) {
        let LngLat = value.split(",")
        const lng = LngLat[0]
        const lat = LngLat[1]
        mapboxgl.accessToken = accessToken;
        const map = new mapboxgl.Map({
            container: 'map', // container ID
            style: 'mapbox://styles/mapbox/satellite-v9', // style URL
            center: [lng, lat], // starting position [lng, lat]
            zoom: 18, // starting zoom
        });

        const draw = new MapboxDraw({
            displayControlsDefault: false,
            // Select which mapbox-gl-draw control buttons to add to the map.
            controls: {
                polygon: true,
                trash: true
            },
            // Set mapbox-gl-draw to draw by default.
            // The user does not have to click the polygon control button first.
            defaultMode: 'draw_polygon'
        });

        const calculationBox = document.getElementsByClassName('calculation-box')

        calculationBox[0].style.display = 'block'

        map.addControl(draw)
        map.addControl(new mapboxgl.NavigationControl())

        map.on('draw.create', updateArea)
        map.on('draw.delete', updateArea)
        map.on('draw.update', updateArea)

        function updateArea(e) {
            const data = draw.getAll()
            const answer = document.getElementById('calculated-area')
            if (data.features.length > 0) {
                const area = turf.area(data)
                // Restrict the area to 2 decimal points.
                const rounded_area = Math.round(area * 100) / 100

                const areaInCM = rounded_area * 10000
                answer.innerHTML = `<p><strong>${rounded_area}</strong></p><p>square meters</p>`;
            } else {
                answer.innerHTML = ''
                if (e.type !== 'draw.delete')
                    alert('Click the map to draw a polygon.')
            }
        }
    }
    </script>
</body>

</html>