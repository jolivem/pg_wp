(function( $ ) {
	'use strict';	
	$(document).ready(function(){        
    
    }); // end document ready

 
    // leaflet map instance for user edit photo page
    var g_lmap; 
    
    $('#select-country').on('input', function (e){
        //console.log("selection", $('#select-country'));
        // get the selected file name
        let selectedValue = $('#select-country').val();
        console.log("selection", selectedValue);

        if (selectedValue == "None") {
            let mapId = "leaflet-map";
            ays_remove_vignette( mapId);
        }
        else {
            ays_handle_country( selectedValue);
        }

    });

    function ays_refresh_marker() {
        //let postId = document.getElementById("post_ID").value;
        let lat = document.getElementById("latitude")?.value;
        let lon = document.getElementById("longitude")?.value;

        ays_update_marker_point(lat, lon);
    }

    function ays_delete_markers() {
        //console.log("g_lmap", g_lmap);
        g_lmap?.eachLayer(function (layer) { 
            //console.log("layer", layer);
            // find the layer with latlng
            if (layer._leaflet_id != undefined && layer._latlng != undefined) {
                //console.log("FOUND IT !!!!");
                //layer.setLatLng([newLat,newLon])
                g_lmap.removeLayer(layer);
            } 
        });
    }

    function ays_add_marker_point( latitude, longitude) {
        let flat = parseFloat(latitude);
        let flon = parseFloat(longitude)
        if (g_lmap != null && !isNaN(parseFloat(latitude)) && !isNaN(parseFloat(longitude))) {
            var myIconClass = L.Icon.extend({
                options: {
                    iconSize:     [4, 4],
                    iconAnchor:   [2, 2]
                }
            });
            
            let coord = [flat.toString(), flon.toString()];
            //console.log("ays_add_marker_point coord:", coord);
            var mark = new myIconClass ({iconUrl: ays_vars.base_url + 'assets/markpoint.png'});
            L.marker(coord, {icon: mark}).addTo(g_lmap);
        }

    }

    function ays_update_marker_point( latitude, longitude) {
        ays_delete_markers();
        if (latitude && longitude) {

            ays_add_marker_point(latitude, longitude);
        }
    }    

    function ays_remove_vignette( mapId) {
        console.log("ays_remove_vignette IN", mapId);
        let previous_map = document.getElementById(mapId);
        console.log("ays_remove_vignette", previous_map);
        previous_map.style.display="none";
        //previous_map?.remove();
        if (g_lmap && g_lmap.remove) {
            g_lmap.off();
            g_lmap.remove();
            g_lmap = null;
        }        
    }

    function ays_add_vignette( mapId, country) {
        console.log("ays_add_vignette country", {mapId, country});
        let zoom = country.zoom;
        let file = ays_vars.base_url + "assets/geojson/" + country.file;
 
        let select = document.getElementById("select-country");
        console.log("select", select);
        //console.log("BABAauRHUM", parent);
        // select.appendChild(p);
        var elemDiv = document.getElementById(mapId);
        elemDiv.style.display="block";
        console.log("ays_add_vignette mapId", elemDiv);
        //elemDiv.id = mapId;
        
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
            fillColor: 'yellow',
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
        //select.appendChild(elemDiv);
        
        //var mark = new myIconClass ({iconUrl: ays_vars.base_url + 'assets/markpoint.png'});
        g_lmap = L.map(mapId, props);
        // Charger le fichier GeoJSON et l'ajouter à la carte
        fetch(file)  // Remplacez 'votre_fichier.geojson' par le chemin de votre fichier GeoJSON
            .then(response => response.json())
            .then(data => {
                console.log("fetch", parent);
                L.geoJSON(data, {
                    clickable: false,
                    style: geostyle
                }).addTo(g_lmap);
                
                // get the center of the map
                let lon = data.features[0].properties.geo_point_2d.lon;
                let lat = data.features[0].properties.geo_point_2d.lat;
                let coord = [lat, lon];
                console.log("coord:", coord);
                g_lmap.setView(coord, zoom);

            });

    }

    // handle map vignette    
    function ays_handle_country( filename) {
        console.log("ays_handle_country base_url", ays_vars.base_url);
        let mapId = "leaflet-map";
        ays_remove_vignette( mapId)

        //let ays_admin_url = $(document).find('#glp_admin_url').val();
        let file = ays_vars.base_url +'/assets/world.json';
        fetch(file)            
            .then(response => response.json())
            .then(data => {
                //console.log("data:", data);
                data.forEach(function (boundary) {

                    if (boundary.file == filename) {
                        ays_add_vignette(mapId, boundary);
                        ays_refresh_marker();
                    }
                });
            });
    }

    let selectedValue = $('#select-country').val();
    ays_handle_country(selectedValue);

    
    // WHen changing 
    $('#worldmap').on('change', function(e) {
        e.stopPropagation();
        this.value = this.checked ? "on" : "off";
      });
    
    // When clicked on submit button
    $(document).find('#edit-photo').on('click', function(event){
        console.log("edit-photo IN");
        event.preventDefault();
        let error = false;
        let post_id = document.getElementById('post_id').value;
        let nonce = document.getElementById('pg_nonce').value;
        let admin_url = document.getElementById('pg_admin_ajax_url').value;

        const title = document.getElementById('photo-title').value;
        const description = document.getElementById('photo-description').value;
        const worldmap = document.getElementById('worldmap').value;
        const vignette = document.getElementById("select-country").value;

        const formData = new FormData();
        formData.append('action', 'user_edit_photo');
        formData.append('nonce', nonce);
        formData.append('post_id', post_id);
        formData.append('title', title);
        formData.append('desc', description);
        formData.append('vignette', vignette);
        formData.append('worldmap', worldmap);
        jQuery.ajax({
            method: 'POST',
            url: admin_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                console.log("upload done");
            }
            // TODO handle error
        });
    });


})( jQuery );