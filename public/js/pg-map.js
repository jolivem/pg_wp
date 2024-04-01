var selectedImage = null;
var imageSelected = null; /*image clicked in the slider */
var map;
var markers;
var toto='montoto';
var LeafIcon = L.Icon.extend({
    options: {
        iconSize:     [60, 60],
        shadowSize:   [50, 64],
        shadowAnchor: [4, 62],
        popupAnchor:  [-3, -76],
        className: 'mydivicon'
    }
});

(function( $ ) {
	'use strict';	
	//$(document).ready(function(){     
        //console.log('COUCOU ready map.js', toto);

        selectedImage = null;
        imageSelected = null; /*image clicked in the slider */

        markers = L.markerClusterGroup({
            zoomToBoundsOnClick: true,
            iconCreateFunction: function(cluster) {
                console.log('iconCreateFunction cluster:', cluster);

                var children = cluster.getAllChildMarkers()[0];

                console.log('icon', children.options.icon);
                var iicoon = new L.Icon(children.options.icon.options);
                var count = cluster.getChildCount();
                if (count < 6) {
                    iicoon.options.className = 'mydivmarker6';    
                }
                else if (count < 20) {
                    iicoon.options.className = 'mydivmarker9';    
                }
                else {
                    iicoon.options.className = 'mydivmarker12';    
                }
                if (imageSelected != null) {
                    iicoon.options.iconSize = [100,100];
                    iicoon.options.iconUrl = imageSelected;
                    imageSelected = null;
                    console.log( 'imageSelected = null');
                    setTimeout(function(){
                        cluster.refreshIconOptions({
                            //shadowUrl: 'leaf-shadow.png',
                            iconSize:     [60, 60],
                        }, true); 
                    }, 400);                        
                }

                /*iicoon.options.className = 'mydivmarker';*/
                console.log('iicoon', iicoon);
                /*return L.divIcon({ html: '<b>' + cluster.getChildCount() + '</b>' });*/
                return iicoon;
            }
        }); 

        map = L.map('map').setView([45.437859,7.085], 7);

        L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);    

        /* when clicked on marker */
        markers.on('click', function (a) {
            console.log('marker ', a.layer.options.icon.options.iconUrl);
            selectImage(a.layer.options.icon.options.iconUrl, true); 
            let visibleOne = markers.getVisibleParent(a.layer);
            console.log('visibleOne', visibleOne);
        });

        markers.on('clusterclick', function (a) {
            /* a.layer is actually a cluster
            console.log('clusterclick ', a);
            console.log('L ', L); */
            let visibleOne = markers.getVisibleParent(a.layer);
            console.log('clusterclick: visibleOne', visibleOne);
            visibleOne.refreshIconOptions({
                        iconSize:     [100, 100],
                    }, true);
            markers.refreshClusters();
            /*markers.refreshClusters(visibleOne);*/
        });            
    
        function selectImage(mysrc, scroll) {
            console.log('selectImage IN', mysrc);
            var slider = document.getElementById('imageSlider');

            /* Remove classname from previously selected image */
            console.log('selectedImage', selectedImage);
            if (selectedImage) {
                selectedImage.classList.remove('imgSelected');
                selectedImage.classList.add('imgNotSelected');
            }

            var imageElement = document.querySelector(`#imageSlider img[src='${mysrc}']`);
            /* Add classname to the selected image */
            console.log('selectImage', imageElement);
            if (imageElement) {
                console.log('imageElement ', imageElement);
                imageElement.classList.remove('imgNotSelected');
                imageElement.classList.add('imgSelected');
                selectedImage = imageElement;

                /* Scroll to the selected image */
                if (scroll == true) {
                    slider.scrollLeft = imageElement.offsetLeft - (slider.clientWidth - imageElement.clientWidth) / 2;
                }
            }
            else {
                console.log('selectImage NOT FOUND');
            }
        }

        // function changeImageStyles() {
        //     var allImages = document.querySelectorAll('#imageSlider img');

        //     allImages.forEach(function (imgElement) {
        //         /* Change styles for all images */
        //         imgElement.style.border = '2px dashed blue'; /* Adjust the border style as needed */
        //     });
        // }
        var gal = document.getElementById('imageSlider');
        console.log('gal:', gal);

        /* When clicked on the slider gallery */
        gal.addEventListener('click', function(event){
            event.preventDefault();
            /* console.log('gal.on 'click' event ', event);
            console.log('gal.on 'click' target', event.target);
            console.log('gal.on 'click' src', event.target.attributes.src); */
            imageSelected = event.target.getAttribute('src');
            console.log('gal.on click imageSelected = '+imageSelected);
            selectImage(imageSelected, false); 
        
            let layers = markers.getLayers();
            console.log( 'layers_', layers);
            for (let i in layers) {
                let layer = layers[i];
                let iconUrl = layer?.options?.icon?.options?.iconUrl;
                console.log( 'iconUrl', layer.options.icon.options.iconUrl);
                if (iconUrl != null && iconUrl == imageSelected) {
                    console.log( `FFOOUUNNDD iconUrl`, layer.options.icon.options.iconUrl);
                    let visibleOne = markers.getVisibleParent(layer);
                    console.log('visibleOne', visibleOne);

                    if (visibleOne._childCount != undefined) {
                        console.log('THIS IS A CLUSTER');
                        markers.refreshClusters(visibleOne);
                    }
                    else {
                        console.log('THIS IS A MARKER');
                        imageSelected = null;
                        console.log( 'imageSelected NOT null');                        
                        visibleOne.refreshIconOptions({
                            /*shadowUrl: 'leaf-shadow.png',*/
                            iconSize:     [100, 100],
                        }, true); 
                        
                        setTimeout(function(){
                            visibleOne.refreshIconOptions({
                                /*shadowUrl: 'leaf-shadow.png',*/
                                iconSize:     [60, 60],
                            }, true); 
                        }, 400);
                    }
                
                }
            }
        });
    //})

})( jQuery );