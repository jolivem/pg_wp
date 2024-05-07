var g_selectedImageElem = null;
var g_selectedImageSrc = null; /*image src when clicked in the slider target*/
var g_map;
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

var setSliderPhotoCss = function (element, class_) {

    switch( class_) {
        case "imgNotSelected":
            element.classList.remove('imgSelected');
            element.classList.add(class_);
            break;
        case "imgSelected":
            element.classList.remove('imgNotSelected');
            element.classList.add(class_);
            break;
        }
};

var selectSliderImage =  function(mysrc, scroll) {
    console.log('selectSliderImage IN', mysrc);
    var slider = document.getElementById('imageSlider');

    /* Remove classname from previously selected image */
    //console.log('selectSliderImage current g_selectedImageElem', g_selectedImageElem);
    if (g_selectedImageElem) {
        g_selectedImageElem.classList.remove('imgSelected');
        g_selectedImageElem.classList.add('imgNotSelected');
    }

    //let thumbnailSrc = makeThumbnailSrc(mysrc);
    var imageElement = document.querySelector(`#imageSlider img[src='${mysrc}']`);
    /* Add classname to the selected image */
    console.log('selectSliderImage imageElement', imageElement);
    if (imageElement) {
        console.log('slider ', slider);
        console.log('imageElement ', imageElement);
        imageElement.classList.remove('imgNotSelected');
        imageElement.classList.add('imgSelected');
        g_selectedImageElem = imageElement;

        /* Scroll and center to the selected image */
        if (scroll == true) {
            centerInSlider(imageElement, slider);
        }

        //  remove prefix "slider-"
        let postid = imageElement.id.substring(7);
        displayPostDesription(postid);

    }
    else {
        console.log('selectSliderImage NOT FOUND');
    }
};

var displayPostDesription = function(postid) {
    // hide all descriptions
    let alldescs = document.querySelectorAll('.desc-all');
    for (let i = 0; i < alldescs.length; i++) {
        alldescs[i].style.display = "none";
    }

    // find description
    if (postid != null) {
        const descid = "desc-"+postid;
        var descr = document.getElementById(descid);
        console.log('descid ', descid);
        if (descr) {
            descr.style.display='block';
        }
    }
};

var centerInSlider = function ( image, slider) {
    console.log('centerInSlider IN', slider.scrollLeft);

    const parent = image.parentElement;
    const currentLeft = slider.scrollLeft;
    //slider.scrollLeft = parent.offsetLeft - slider.offsetLeft + (parent.clientWidth - slider.offsetWidth) / 2;
    const newLeft = parent.offsetLeft - slider.offsetLeft + (parent.clientWidth - slider.offsetWidth) / 2;
    slider.scrollBy({
        left: newLeft - currentLeft,
        top: 0,
        behavior: 'smooth'
    })
    console.log('centerInSlider OUT', slider.scrollLeft);

    // for centering:
    // iOL + iOW/2 = sOL + sSL + sOW/2

    // console.log('slider.offsetWidth ', slider.offsetWidth);
    // console.log('slider.offsetLeft ', slider.offsetLeft);
    // console.log('slider.scrollLeft ', slider.scrollLeft);
    // console.log('element.offsetWidth ', element.offsetWidth);
    // console.log('element.offsetLeft ', element.offsetLeft);
};

(function( $ ) {
	'use strict';	
	//$(document).ready(function(){     
    //console.log('COUCOU ready map.js', toto);
    g_selectedImageElem = null;
    g_selectedImageSrc = null; /*image clicked in the slider */

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
            console.log( 'g_selectedImageSrc', g_selectedImageSrc);
            if (g_selectedImageSrc != null) {
                iicoon.options.iconSize = [100,100];
                iicoon.options.iconUrl = g_selectedImageSrc;
                g_selectedImageSrc = null;
                console.log( 'g_selectedImageSrc = null');
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
    g_map = L.map('map');

    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(g_map);    

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

    // var gal = document.getElementById('imageSlider');
    // console.log('gal:', gal);

    function animateMarkerByImage(img) {

        console.log('animateMarkerByImage IN', img);
        let imageSrc= img.getAttribute('src');

        // animate the marker on the map
        let layers = markers.getLayers();
        //console.log( 'layers_', layers);
        for (let i in layers) {
            let layer = layers[i];
            let iconUrl = layer?.options?.icon?.options?.iconUrl;
            //console.log( 'iconUrl', layer.options.icon.options.iconUrl);
            if (iconUrl != null && iconUrl == imageSrc) {
                console.log( `FFOOUUNNDD iconUrl`, layer.options.icon.options.iconUrl);
                let visibleOne = markers.getVisibleParent(layer);
                if (visibleOne != null) {
                
                    // move map only for user gallery, not for planet 
                    if ($("#imageSlider").hasClass("gallery-slider")) {
                        console.log('visibleOne', visibleOne);
                        let position = visibleOne.getLatLng();
                        //console.log('position', position);h
                        g_map.setView(new L.latLng(position));
                    }

                    if (visibleOne._childCount != undefined) {
                        console.log('THIS IS A CLUSTER',visibleOne);
                        markers.refreshClusters(visibleOne);
                    }
                    else {
                        console.log('THIS IS A MARKER', visibleOne);
                        g_selectedImageSrc = null;
                        //console.log( 'imageSelected NOT null');                        
                        visibleOne.refreshIconOptions({
                            /*shadowUrl: 'leaf-shadow.png',*/
                            iconSize:     [100, 100],
                        }, true); 
                        //visibleOne._zIndex +=10000;
                        // let savZIndex = visibleOne.zIndexOffset;
                        // visibleOne.zIndexOffset = 999999;
                        
                        setTimeout(function(){
                            // come back to normal size after timeout
                            visibleOne.refreshIconOptions({
                                /*shadowUrl: 'leaf-shadow.png',*/
                                iconSize:     [60, 60],
                            }, true);
                            //visibleOne._zIndex -=10000;
                            // visibleOne.zIndexOffset = savZIndex;
                        }, 400);
                    }
                }
                else {
                    // TODO display "dezoom to show the image on the map"
                    console.log( `parent not visible`);
                }
            }
        }
    }

    /* When user clicked on the slider gallery */
    $(".slider-overlay-circle").on('click', function(event){
        //event.preventDefault();
        console.log('gal.on click event ', event);
        console.log('gal.on click target parent', event.target.parentElement.parentElement);
        console.log('gal.on click src', event.target.attributes.src);
        const img = event.target.parentElement.parentElement.getElementsByTagName("img")[0];
        console.log('gal.on click img', img);
        const imageSrc = img.getAttribute('src');
        g_selectedImageSrc = imageSrc; // imageSelected used
        console.log('gal.on click imageSrc = '+imageSrc);
        selectSliderImage(imageSrc, false);
        animateMarkerByImage(img);

    });


    /* When user clicked on the slider gallery */
    $(".slider-overlay-text").on('click', function(event){
        //event.preventDefault();
        console.log('gal.on click target parent', event.target.parentElement.parentElement);
        console.log('gal.on click src', event.target.attributes.src);
        const img = event.target.parentElement?.parentElement?.getElementsByTagName("img")[0];
        if (img){
            console.log('gal.on click img', img);
            const imageSrc = img.getAttribute('src');
            selectSliderImage(imageSrc, false);

            // setSliderPhotoCss(img, 'imgSelected');
            // var slider = document.getElementById('imageSlider');
            // centerInSlider(img, slider);
            // let postid = img.id.substring(7);
            // displayPostDesription(postid);
        }

        //animateMarkerByImage(img);

    });

     // Process when user click on step-forward or step-backward
    // and when user click on angle-double-right or angle-double-left
    $(document).find('.show-gallery-option').on('click', function(e){
        console.log("show-gallery-option click", e);
        e.preventDefault();
        if (g_selectedImageElem) {
            setSliderPhotoCss(g_selectedImageElem, 'imgNotSelected');
            let selected = false;

            if (e.target.classList.contains("fa-step-forward")) {
                console.log("show-gallery-option forward");
                // find the following image
                const nextImage = g_selectedImageElem?.parentElement?.nextElementSibling?.children[0];
                console.log("show-gallery-option forward", nextImage);
                if (nextImage){
                    g_selectedImageElem = nextImage;
                    g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                    selectSliderImage(g_selectedImageSrc, true);
                    animateMarkerByImage(g_selectedImageElem);
                    selected = true;
                }
                else {
                    // go to the first image
                    const firstImage = g_selectedImageElem?.parentElement?.parentElement?.firstElementChild?.children[0];
                    if (firstImage) {
                        g_selectedImageElem = firstImage;
                        g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                        selectSliderImage(g_selectedImageSrc, true);
                        animateMarkerByImage(g_selectedImageElem);
                        selected = true;
                    }
                }
            }
            else if (e.target.classList.contains("fa-step-backward")) {
                console.log("show-gallery-option backward g_selectedImageElem", g_selectedImageElem);
                // find the following image
                const previousImage = g_selectedImageElem?.parentElement?.previousElementSibling?.children[0];
                console.log("show-gallery-option backward", previousImage);
                if (previousImage){
                    g_selectedImageElem = previousImage;
                    g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                    selectSliderImage(g_selectedImageSrc, true);
                    animateMarkerByImage(g_selectedImageElem);
                    selected = true;
                }
                else {
                    // go to the last image
                    const lastImage = g_selectedImageElem?.parentElement?.parentElement?.lastElementChild?.children[0];
                    if (lastImage) {
                        g_selectedImageElem = lastImage;
                        g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                        selectSliderImage(g_selectedImageSrc, true);
                        animateMarkerByImage(g_selectedImageElem);
                        selected = true;
                    }
                }
            }
            else if (e.target.classList.contains("fa-angle-double-right")) {
                console.log("show-gallery-option right");
                // find the following image
                const nextImage = g_selectedImageElem?.parentElement?.nextElementSibling?.children[0];
                console.log("show-gallery-option right", nextImage);
                if (nextImage){
                    g_selectedImageElem = nextImage;
                    const imageSrc = g_selectedImageElem.getAttribute('src');
                    selectSliderImage(imageSrc, true);
                    selected = true;
                } else {
                    // go to the first image
                    const firstImage = g_selectedImageElem?.parentElement?.parentElement?.firstElementChild?.children[0];
                    if (firstImage) {
                        g_selectedImageElem = firstImage;
                        g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                        selectSliderImage(g_selectedImageSrc, true);
                        selected = true;
                    }
                }

            }
            else if (e.target.classList.contains("fa-angle-double-left")) {
                console.log("show-gallery-option left");
                // find the following image
                const previousImage = g_selectedImageElem?.parentElement?.previousElementSibling?.children[0];
                if (previousImage){
                    g_selectedImageElem = previousImage;
                    const imageSrc = g_selectedImageElem.getAttribute('src');
                    selectSliderImage(imageSrc, true);
                    selected = true;
                }else {
                    // go to the last image
                    const lastImage = g_selectedImageElem?.parentElement?.parentElement?.lastElementChild?.children[0];
                    if (lastImage) {
                        g_selectedImageElem = lastImage;
                        g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                        selectSliderImage(g_selectedImageSrc, true);
                        selected = true;
                    }
                }
            }

            if (!selected) {
                displayPostDesription(null);
            }

        }
        else {
            console.log("show-gallery-option nothing selected");
        }

    });

    let searchElem = document.getElementById("searchInput");
    if (searchElem) {
        document.getElementById("searchInput").addEventListener("keypress", function(event) {
            // Check if the pressed key is 'Enter' (key code 13)
            console.log('searchInput IN', event);
            if (event.key === 'Enter') {
                event.preventDefault();

                // Call the function to handle the 'Enter' key press
                searchAddress();
            }
        });

        document.getElementById('searchButton').addEventListener('click', function(event) {
            event.preventDefault();
            searchAddress();
        });                

        function searchAddress() {
            console.log('searchAddress IN');
            var address = document.getElementById('searchInput').value;

            if (address == '') {
                return;
            }

            // Utilisation de l'API Nominatim pour géocoder l'adresse
            var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + address;
            console.log('searchAddress url', url);

            fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('searchAddress response', data);
                if (data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lon = parseFloat(data[0].lon);
                    
                    // Création de la carte OpenStreetMap
                    g_map.setView([lat, lon], 12);

                    L.marker([lat, lon]).addTo(g_map)
                        .bindPopup(address)
                        .openPopup();
                } else {
                    alert('Adresse introuvable');
                }
            })
            .catch(error => console.error('Erreur :', error));
        }
    }

})( jQuery );

function getImagesFromBB(ne_lat, ne_lng, sw_lat, sw_lng, zoom) {

    console.log("getImagesFromBB IN", {ne_lat, ne_lng, sw_lat, sw_lng, zoom});
    
    let admin_url = document.getElementById('pg_admin_ajax_url').value;
    let nonce = document.getElementById('page_nonce').value;
    //console.log("uploadPhotos admin_url=", admin_url);

    const formData = new FormData();
    formData.append('action', 'get_bb_images');
    formData.append('nonce', nonce);
    formData.append('ne_lat', ne_lat);
    formData.append('ne_lng', ne_lng);
    formData.append('sw_lat', sw_lat);
    formData.append('sw_lng', sw_lng);
    formData.append('zoom', zoom);
    jQuery.ajax({
        method: 'POST',
        url: admin_url,
        data: formData,
        contentType: false,
        processData: false,
        success: function(response){
            console.log("success", response);
            updateSlider(response.data);

        },
        error: function(response) {
            console.log("error", response);
        }
    });
}

function updateSlider(datas) {
    console.log("updateSlider IN", datas);
    const slider = document.getElementById('imageSlider');
    slider.innerHTML="";
    if (datas) {
        datas.forEach(function(image) {
            //console.log("updateSlider image", image);
            const img = createImageElement(image.url);
            slider.appendChild(img);
        });
    }

}

// Function to create image elements
function createImageElement(url) {
    //console.log("createImageElement IN", url);
    const img = document.createElement('img');
    img.src = url;
    img.classList.add('ImgNotSelected');
    return img;
}


