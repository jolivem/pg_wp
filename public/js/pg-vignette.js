(function( $ ) {
	'use strict';	

    function pg_add_marker_point( lmap, latitude, longitude) {
        //console.log("pg_add_marker_point country", {lmap, latitude, longitude});
        let flat = parseFloat(latitude);
        let flon = parseFloat(longitude)
        if (lmap != null && !isNaN(parseFloat(latitude)) && !isNaN(parseFloat(longitude))) {
            //console.log("pg_add_marker_point country", {lmap, latitude, longitude});
            var myIconClass = L.Icon.extend({
                options: {
                    iconSize:     [4, 4],
                    iconAnchor:   [2, 2]
                }
            });
            
            let coord = [flat.toString(), flon.toString()];
            //console.log("pg_add_marker_point coord:", coord);
            var mark = new myIconClass ({iconUrl: ays_vars.base_url + 'assets/markpoint.png'});
            L.marker(coord, {icon: mark}).addTo(lmap);
        }

    }

    function pg_add_leaflet_map( elemDiv, mapId, country) {
        //console.log("pg_add_leaflet_map country", {elemDiv, mapId, country});
        let zoom = country.zoom;
        let file = ays_vars.base_url + "assets/geojson/" + country.file;
 
        //var elemDiv = document.getElementById(mapId);
        //console.log("pg_add_leaflet_map", elemDiv);
        //console.log("pg_add_leaflet_map elemDiv", elemDiv);
        if (elemDiv == null || elemDiv.childElementCount > 0) {
            console.log("pg_add_leaflet_map map already added");
            return null;
        }
        elemDiv.style.display="block";
        
        var props = {
            attributionControl: false,
            zoomControl: false,
            doubleClickZoom: false, 
            closePopupOnClick: false, 
            dragging: false, 
            zoomSnap: false, 
            zoomDelta: false, 
            trackResize: false,
            touchZoom: false,
            scrollWheelZoom: false
        };

        var geostyle = {
            fillColor: 'white',
            //color: 'yellow',
            fillOpacity: 2,
            weight: 1
        }
        
        // console.log("css:", css);
        elemDiv.style.height = country.height;
        elemDiv.style.width = country.width;
        elemDiv.style.backgroundColor = 'white';
        elemDiv.style.borderStyle = 'solid';
        elemDiv.style.borderWidth = 'thin';
        elemDiv.style.borderColor = 'lightgray';
        elemDiv.style.flexBasis = country.width;
        elemDiv.id = mapId;
        //select.appendChild(elemDiv);
        
        //var mark = new myIconClass ({iconUrl: ays_vars.base_url + 'assets/markpoint.png'});
        let lmap = L.map(mapId, props);
        // Charger le fichier GeoJSON et l'ajouter à la carte
        fetch(file)  // Remplacez 'votre_fichier.geojson' par le chemin de votre fichier GeoJSON
            .then(response => response.json())
            .then(data => {
                // console.log("fetch", parent);
                L.geoJSON(data, {
                    clickable: false,
                    style: geostyle
                }).addTo(lmap);
                
                // get the center of the map
                let lon = data.features[0].properties.geo_point_2d.lon;
                let lat = data.features[0].properties.geo_point_2d.lat;
                let coord = [lat, lon];
                // console.log("coord:", coord);
                lmap.setView(coord, zoom);

            });

        return lmap;
    }

    window.pg_add_vignette_to_slider = function(father) {
        //console.log( "pg_add_vignette_to_slider IN", father);
        let elems = father.getElementsByClassName('pg-descr-vignette');
        if (elems.length > 0) {
            let elem = elems[0];

            //console.log( "pg_add_vignette_to_slider IN", elem);
            const lat = elem.getAttribute('data-lat');
            const lon = elem.getAttribute('data-lon');
            const filename = elem.getAttribute('data-country');
            //const id = elem.id;
            //console.log( "pg_add_vignette_to_slider id", id);
            const mapId = elem.id;
            pg_add_vignette(elem, mapId, filename, lon, lat);
        }
    }

    // handle map vignette    
    function pg_add_vignette (elemDiv, mapId, filename, lon, lat) {
        //console.log("pg_add_vignette IN ", {elemDiv, filename, lon, lat});
        //pg_remove_vignette( mapId)
        if (elemDiv.childElementCount > 0) {
            //console.log("pg_add_vignette map already added");
            return ;
        }

        //let ays_admin_url = $(document).find('#glp_admin_url').val();
        // if (g_world_data == null) {
        let file = ays_vars.base_url +'/assets/world.json';
        fetch(file)            
            .then(response => response.json())
            .then(data => {
                //console.log("data:", data);
                data.forEach(function (boundary) {

                    if (boundary.file == filename) {
                        let lmap = pg_add_leaflet_map(elemDiv, mapId, boundary);
                        // let lat = document.getElementById("latitude")?.value;
                        // let lon = document.getElementById("longitude")?.value;
                
                        if (lmap) {
                            pg_add_marker_point(lmap, lat, lon);
                        }
                    }
                });
            });
    }
    
})( jQuery );