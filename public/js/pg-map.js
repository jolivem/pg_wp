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

    /*map = L.map('map').setView([0,0], zoom);*/
    map = L.map('map');

    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);    

    /* when clicked on marker */
    markers.on('click', function (a) {
        console.log('marker ', a.layer.options.icon.options.iconUrl);
        selectSliderImage(a.layer.options.icon.options.iconUrl, true); 
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

    function selectSliderImage(mysrc, scroll) {
        console.log('selectSliderImage IN', mysrc);
        var slider = document.getElementById('imageSlider');

        /* Remove classname from previously selected image */
        console.log('selectSliderImage', selectedImage);
        if (selectedImage) {
            selectedImage.classList.remove('imgSelected');
            selectedImage.classList.add('imgNotSelected');
        }

        var imageElement = document.querySelector(`#imageSlider img[src='${mysrc}']`);
        /* Add classname to the selected image */
        console.log('selectSliderImage', imageElement);
        if (imageElement) {
            console.log('slider ', slider);
            console.log('imageElement ', imageElement);
            imageElement.classList.remove('imgNotSelected');
            imageElement.classList.add('imgSelected');
            selectedImage = imageElement;

            /* Scroll and center to the selected image */
            if (scroll == true) {

                centerInSlider(imageElement, slider);
            }
        }
        else {
            console.log('selectSliderImage NOT FOUND');
        }
    }

    function centerInSlider( element, slider) {
        slider.scrollLeft = element.offsetLeft - slider.offsetLeft + (element.clientWidth - slider.offsetWidth) / 2;
        // for centering:
        // iOL + iOW/2 = sOL + sSL + sOW/2

        // console.log('slider.offsetWidth ', slider.offsetWidth);
        // console.log('slider.offsetLeft ', slider.offsetLeft);
        // console.log('slider.scrollLeft ', slider.scrollLeft);
        // console.log('element.offsetWidth ', element.offsetWidth);
        // console.log('element.offsetLeft ', element.offsetLeft);
    }

    var gal = document.getElementById('imageSlider');
    console.log('gal:', gal);

    /* When user clicked on the slider gallery */
    gal.addEventListener('click', function(event){
        event.preventDefault();
        /* console.log('gal.on 'click' event ', event);
        console.log('gal.on 'click' target', event.target);
        console.log('gal.on 'click' src', event.target.attributes.src); */
        imageSelected = event.target.getAttribute('src');
        console.log('gal.on click imageSelected = '+imageSelected);
        selectSliderImage(imageSelected, false); 
    
        let layers = markers.getLayers();
        console.log( 'layers_', layers);
        for (let i in layers) {
            let layer = layers[i];
            let iconUrl = layer?.options?.icon?.options?.iconUrl;
            //console.log( 'iconUrl', layer.options.icon.options.iconUrl);
            if (iconUrl != null && iconUrl == imageSelected) {
                //console.log( `FFOOUUNNDD iconUrl`, layer.options.icon.options.iconUrl);
                let visibleOne = markers.getVisibleParent(layer);
                //console.log('visibleOne', visibleOne);
                let position = visibleOne.getLatLng();
                //console.log('position', position);
                map.setView(new L.latLng(position));

                if (visibleOne._childCount != undefined) {
                    console.log('THIS IS A CLUSTER',visibleOne);
                    markers.refreshClusters(visibleOne);
                }
                else {
                    console.log('THIS IS A MARKER', visibleOne);
                    imageSelected = null;
                    //console.log( 'imageSelected NOT null');                        
                    visibleOne.refreshIconOptions({
                        /*shadowUrl: 'leaf-shadow.png',*/
                        iconSize:     [100, 100],
                    }, true); 
                    // let savZIndex = visibleOne.zIndexOffset;
                    // visibleOne.zIndexOffset = 999999;
                    
                    setTimeout(function(){
                        // come back to normal size after timeout
                        visibleOne.refreshIconOptions({
                            /*shadowUrl: 'leaf-shadow.png',*/
                            iconSize:     [60, 60],
                        }, true);
                        // visibleOne.zIndexOffset = savZIndex;
                    }, 400);
                }
            
            }
        }
    });

    $(document).find('.user-photo-option').on('click', function(e){
        console.log("user-photo-option click", e)
        if (e.target.classList.contains("fa-edit")) {
            const postid = e.target.dataset.postid;
            console.log("user-photo-option postid=", postid)
        }
        e.preventDefault();
    });

    // Process when user click on step-forward or step-backward
    // and when user click on angle-double-right or angle-double-left
    $(document).find('.show-gallery-option').on('click', function(e){
        console.log("show-gallery-option click", e)
        e.preventDefault();
        if (selectedImage) {
            setSliderPhotoClass(selectedImage, 'imgNotSelected');

            if (e.target.classList.contains("fa-step-forward")) {
                // find the following image
                const nextImage = selectedImage.nextElementSibling;
                if (nextImage){
                    selectedImage = nextImage;
                }
                selectedImage.click();
                //setSliderPhotoClass(selectedImage, 'imgSelected');
            }
            else if (e.target.classList.contains("fa-step-backward")) {
                console.log("show-gallery-option backward");
                // find the following image
                const previousImage = selectedImage.previousElementSibling;
                if (previousImage){
                    selectedImage = previousImage;
                }
                selectedImage.click();
                //setSliderPhotoClass(selectedImage, 'imgSelected');
            }
            else if (e.target.classList.contains("fa-angle-double-right")) {
                // find the following image
                const nextImage = selectedImage.nextElementSibling;
                if (nextImage){
                    selectedImage = nextImage;
                }
                setSliderPhotoClass(selectedImage, 'imgCentered');
            }
            else if (e.target.classList.contains("fa-angle-double-left")) {
                console.log("show-gallery-option backward");
                // find the following image
                const previousImage = selectedImage.previousElementSibling;
                if (previousImage){
                    selectedImage = previousImage;
                }
                setSliderPhotoClass(selectedImage, 'imgCentered');
            }

            var slider = document.getElementById('imageSlider');
            centerInSlider(selectedImage, slider);
        }
        else {

        }

    });

    function setSliderPhotoClass(element, class_) {

        switch( class_) {
            case "imgNotSelected":
                element.classList.remove('imgSelected');
                element.classList.remove('imgCentered');
                element.classList.add(class_);
                break;
            case "imgSelected":
                element.classList.remove('imgNotSelected');
                element.classList.remove('imgCentered');
                element.classList.add(class_);
                break;
            case "imgCentered":
                element.classList.remove('imgSelected');
                element.classList.remove('imgNotSelected');
                element.classList.add(class_);
                break;
            }
    }

})( jQuery );