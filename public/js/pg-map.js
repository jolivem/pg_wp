(function( $ ) {
	'use strict';	
	$(document).ready(function(){        
    

        var map = L.map("map").setView([51.505, -0.09], 13);
            
        L.tileLayer("https://{s}.tile.osm.org/{z}/{x}/{y}.png", {
            attribution: "&copy; <a href=\"https://osm.org/copyright\">OpenStreetMap</a> contributors"
        }).addTo(map);

        var LeafIcon = L.Icon.extend({
            options: {
                iconSize:     [60, 60],
                shadowSize:   [50, 64],
                shadowAnchor: [4, 62],
                popupAnchor:  [-3, -76],
                className: "mydivicon"
            }
        });  
        
        var markers = L.markerClusterGroup({
            zoomToBoundsOnClick: true,
            iconCreateFunction: function(cluster) {

                var children = cluster.getAllChildMarkers()[0];

                var iicoon = new L.Icon(children.options.icon.options);
                var count = cluster.getChildCount();
                if (count < 6) {
                    iicoon.options.className = "mydivmarker6";    
                }
                else if (count < 20) {
                    iicoon.options.className = "mydivmarker9";    
                }
                else {
                    iicoon.options.className = "mydivmarker12";    
                }
                if (imageSelected != null) {
                    iicoon.options.iconSize = [100,100];
                    iicoon.options.iconUrl = imageSelected;
                    imageSelected = null;
                    setTimeout(function(){
                        cluster.refreshIconOptions({
                            iconSize:     [60, 60],
                        }, true); 
                    }, 400);                        
                }

                return iicoon;
            }
        });
                
    }); // end document ready

 
    // leaflet map instance for user edit photo page
    var g_lmap; 
    
 
})( jQuery );