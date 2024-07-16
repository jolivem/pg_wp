var g_selectedImageElem = null;
var g_selectedImageSrc = null; /*image src when clicked in the slider target*/
var g_map;
var g_lightbox;
var g_markers;
var g_LeafIcon = L.Icon.extend({
    options: {
        iconSize:     [60, 60],
        shadowSize:   [50, 64],
        shadowAnchor: [4, 62],
        popupAnchor:  [-3, -76],
        className: 'mydivicon'
    }
});


/* START grab and scroll image slider 

const slider = document.getElementById('imageSlider');

let isDragging = false;
let startPos = 0;
let scrollLeft = 0;

// Add mousedown event listener to start dragging
slider.addEventListener('mousedown', (event) => {
    console.log('slider mousedown');
    isDragging = true;
    startPos = event.clientX;
    scrollLeft = slider.scrollLeft;
    slider.classList.add('dragging');
    return false;
});

// Add mousemove event listener to track movement
slider.addEventListener('mousemove', (event) => {
    if (!isDragging) return;
    const distance = event.clientX - startPos;
    slider.scrollLeft = scrollLeft - distance;
});

// Add mouseup event listener to stop dragging
slider.addEventListener('mouseup', (event) => {
    console.log('slider mouseup');
    isDragging = false;
    slider.classList.remove('dragging');
    event.preventDefault()
});

// Add mouseleave event listener to stop dragging when mouse leaves the slider
slider.addEventListener('mouseleave', () => {
    console.log('slider mouseleave');
    isDragging = false;
    slider.classList.remove('dragging');
});

// Prevent default behavior of mouse events
slider.addEventListener('dragstart', (event) => event.preventDefault());        

/* END grab and scroll image slider */

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

var selectSliderImageBySrc =  function(mysrc, scroll) {
    // console.log('selectSliderImageBySrc IN', mysrc);
    const slider = document.getElementById('imageSlider');

    /* Remove classname from previously selected image */
    //console.log('selectSliderImageBySrc current g_selectedImageElem', g_selectedImageElem);
    if (g_selectedImageElem) {
        g_selectedImageElem.classList.remove('imgSelected');
        g_selectedImageElem.classList.add('imgNotSelected');
    }

    //let thumbnailSrc = makeThumbnailSrc(mysrc);
    var imageElement = document.querySelector(`#imageSlider img[src='${mysrc}']`);
    /* Add classname to the selected image */
    // console.log('selectSliderImageBySrc imageElement', imageElement);
    if (imageElement) {
        //console.log('slider ', slider);
        // console.log('imageElement ', imageElement);
        imageElement.classList.remove('imgNotSelected');
        imageElement.classList.add('imgSelected');
        g_selectedImageElem = imageElement;

        /* Scroll and center to the selected image */
        if (scroll == true) {
            centerInSlider(imageElement, slider);
        }

        //  remove prefix "slider-"
        let postid = imageElement.id.substring(7);
        displayPostDescription(postid);

    }
    // else {
    //     console.log('selectSliderImageBySrc NOT FOUND');
    // }
};

var selectSliderImageByElem =  function(imageElement, scroll) {
    // console.log('selectSliderImageByElem IN', imageElement);
    const slider = document.getElementById('imageSlider');

    /* Remove classname from previously selected image */
    //console.log('selectSliderImageByElem current g_selectedImageElem', g_selectedImageElem);
    if (g_selectedImageElem) {
        g_selectedImageElem.classList.remove('imgSelected');
        g_selectedImageElem.classList.add('imgNotSelected');
    }

    /* Add classname to the selected image */
    // console.log('selectSliderImageByElem imageElement', imageElement);
    if (imageElement) {
        //console.log('slider ', slider);
        imageElement.classList.remove('imgNotSelected');
        imageElement.classList.add('imgSelected');
        g_selectedImageElem = imageElement;
        //g_selectedImageSrc = g_selectedImageElem.getAttribute('src');

        /* Scroll and center to the selected image */
        if (scroll == true) {
            centerInSlider(imageElement, slider);
        }

        //  remove prefix "slider-"
        let postid = imageElement.id.substring(7);
        displayPostDescription(postid);

    }
    // else {
    //     console.log('selectSliderImageBySrc NOT FOUND');
    // }
};

var displayPostDescription = function(postid) {
    // hide all descriptions
    let alldescs = document.querySelectorAll('.desc-display');
    for (let i = 0; i < alldescs.length; i++) {
        alldescs[i].style.display = "none";
    }

    // find description
    if (postid != null) {
        const descid = "desc-"+postid;
        var descr = document.getElementById(descid);
        // console.log('descid ', descid);
        if (descr) {
            descr.style.display='block';
        }
    }
};

var centerInSlider = function ( image, slider) {
    // console.log('centerInSlider IN', image);
    // console.log('centerInSlider IN', slider.scrollLeft);

    const parent = image.parentElement.parentElement;
    const currentLeft = slider.scrollLeft;
    //slider.scrollLeft = parent.offsetLeft - slider.offsetLeft + (parent.clientWidth - slider.offsetWidth) / 2;
    const newLeft = parent.offsetLeft - slider.offsetLeft + (parent.clientWidth - slider.offsetWidth) / 2;
    slider.scrollBy({
        left: newLeft - currentLeft,
        top: 0,
        behavior: 'smooth'
    })
    // console.log('centerInSlider newLeft', newLeft);
    // console.log('centerInSlider OUT', slider.scrollLeft);

    // for centering:
    // iOL + iOW/2 = sOL + sSL + sOW/2

    // console.log('slider.offsetWidth ', slider.offsetWidth);
    // console.log('slider.offsetLeft ', slider.offsetLeft);
    // console.log('slider.scrollLeft ', slider.scrollLeft);
    // console.log('element.offsetWidth ', element.offsetWidth);
    // console.log('element.offsetLeft ', element.offsetLeft);
};

var processClickOnTarget = function(elem) {
    // console.log('processClickOnTarget elem', elem);
    
    const img = elem.parentElement.parentElement.getElementsByTagName("img")[0];

    const imageSrc = img.getAttribute('src');
    g_selectedImageSrc = imageSrc; //  used
    //console.log('gal.on click imageSrc = '+imageSrc);
    selectSliderImageByElem(img, false);
    animateMarkerByImage(img);
};

var processClickOnText = function( elem) {
    //console.log('gal.on click elem', elem);
    const img = elem.parentElement?.parentElement?.getElementsByTagName("img")[0];
    if (img){
        selectSliderImageByElem(img, false);
    }
}

var animateMarkerByImage = function(img) {

    // console.log('animateMarkerByImage IN', g_selectedImageSrc);
    //console.log('animateMarkerByImage g_markers', g_markers);
    let imageSrc= img.getAttribute('src');

    // animate the marker on the map
    let layers = g_markers.getLayers();
    //console.log( 'animateMarkerByImage layers', layers);
    for (let i in layers) {
        let layer = layers[i];
        //console.log( 'animateMarkerByImage loop on layers', {i, layer});
        let has = g_markers.hasLayer(layer);
        //console.log( 'animateMarkerByImage hasLayer', has);
        let iconUrl = layer?.options?.icon?.options?.iconUrl;
        //console.log( 'iconUrl', layer.options.icon.options.iconUrl);
        if (iconUrl != null && iconUrl == imageSrc) {
            // console.log( `animateMarkerByImage FFOOUUNNDD layer`, layer);
            // console.log( `animateMarkerByImage iconUrl`, layer.options.icon.options.iconUrl);
            // console.log( `animateMarkerByImage options`, layer.options);
            //console.log( `animateMarkerByImage iconUrl FOUND`);
            if (layer._icon != null) {

                layer._icon.style.width="100px";
                layer._icon.style.height="100px";
                layer._icon.style.zIndex += 1000;
                // console.log( 'animateMarkerByImage layer._icon FOUND for iconUrl');
                //g_selectedImageSrc = null;
                setTimeout(function(){
                    // come back to normal size after timeout
                    layer._icon.style.width="60px";
                    layer._icon.style.height="60px";
                    layer._icon.style.zIndex -= 1000;
                    }, 400);
            }
            else {
                let visibleOne = g_markers.getVisibleParent(layer);
                if (visibleOne != null) {
                
                    // move map only for user gallery, not for planet 
                    const imageSlider = document.getElementById("imageSlider");
                    if (imageSlider.classList.contains("gallery-slider")) {
                        let position = visibleOne.getLatLng();
                        //console.log('position', position);
                        g_map.setView(new L.latLng(position));
                    }
                    //console.log('animateMarkerByImage YYYYYYYYYYYYYYYYYYYYYYY  _childCount',visibleOne._childCount);
                    //console.log('animateMarkerByImage THIS IS A MARKER visibleone', visibleOne);
                    //g_selectedImageSrc = null;
                    //console.log( 'animateMarkerByImage visibleOne FOUND BBBBBBBBBBBBBBBBBB');
                    
                    // leads to call iconCreateFunction for cluster
                    // iconCreateFunction set g_selectedImageSrc=null
                    visibleOne.refreshIconOptions({
                        iconSize:     [60, 60],
                    }, true);
                }
            }
            // else {
            //     // TODO display "dezoom to show the image on the map"
            //     //console.log( `parent not visible`);
            // }
        }
    }
    g_selectedImageSrc = null;
    // console.log('animateMarkerByImage break OUT', g_selectedImageSrc);
};



(function( $ ) {
	'use strict';	
	//$(document).ready(function(){     
    g_selectedImageElem = null;
    g_selectedImageSrc = null; /*image clicked in the slider */

    g_markers = L.markerClusterGroup({
        zoomToBoundsOnClick: true,
        iconCreateFunction: function(cluster) {
            // Called on zoom end and move end
            // console.log('iconCreateFunction IN ');
            //console.log('iconCreateFunction getChildCount', cluster.getChildCount());

            var children = cluster.getAllChildMarkers()[0];

            //console.log('icon', children.options.icon);
            var iicoon = new L.Icon(children.options.icon.options);
            //console.log('iicoon', iicoon);
            var count = cluster.getChildCount();
            if (count < 6) {
                iicoon.options.className = 'mydivmarker6';    
            }
            else {
                iicoon.options.className = 'mydivmarker9';    
            }
            //console.log( 'g_selectedImageSrc', g_selectedImageSrc);
            if (g_selectedImageSrc != null) {
                iicoon.options.iconSize = [100,100];
                iicoon.options.iconUrl = g_selectedImageSrc;
                g_selectedImageSrc = null;
                
                setTimeout(function(){
                    cluster.refreshIconOptions({
                        //shadowUrl: 'leaf-shadow.png',
                        iconSize:     [60, 60],
                    }, true); 
                }, 400);                        
            }

            return iicoon;
        },
        //spiderfyOnMaxZoom: false, 
        //showCoverageOnHover: true
        //zoomToBoundsOnClick: false 
    }); 

    /*map = L.map('map').setView([0,0], zoom);*/
    g_map = L.map('map');

    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(g_map);    

    /* when clicked on marker */
    g_markers.on('click', function (a) {
        // console.log('click marker ', a.layer.options.icon.options.iconUrl);
        selectSliderImageBySrc(a.layer.options.icon.options.iconUrl, true); 
        let visibleOne = g_markers.getVisibleParent(a.layer);
        // console.log('click visibleOne', visibleOne);
    });

    g_markers.on('clusterclick', function (a) {
        /* a.layer is actually a cluster
        console.log('clusterclick ', a);
        console.log('L ', L); */
        let visibleOne = g_markers.getVisibleParent(a.layer);
        visibleOne.refreshIconOptions({
                    iconSize:     [100, 100],
                }, true);
        g_markers.refreshClusters();
        /*g_markers.refreshClusters(visibleOne);*/
    });            

    /* When user clicked on the target area */
    $(".slider-overlay-circle").on('click', function(event){
        // console.log('gal.on click target', event.target);
        event.preventDefault();
        processClickOnTarget( event.target);
    });


    /* When user clicked on the text icon */
    $(".slider-overlay-text").on('click', function(event){
        //event.preventDefault();
        //console.log('gal.on click target parent', event.target.parentElement.parentElement);
        event.preventDefault();
        processClickOnText( event.target);
    });

     // Process when user click on step-forward or step-backward
    // and when user click on angle-double-right or angle-double-left
    $(document).find('.show-gallery-option').on('click', function(e){
        //console.log("show-gallery-option click", e);
        e.preventDefault();
        const slider = document.getElementById('imageSlider');
        
        // if no image in the slider
        if (slider.childElementCount == 0) {
            g_selectedImageElem = null;
            g_selectedImageSrc = null;
            displayPostDescription(null);
            return;
        }

        //console.log("show-gallery-option imageSlider", slider);
        let selected = false;
        //console.log("show-gallery-option IN g_selectedImageElem", g_selectedImageElem);
        // if selection exists
        if (g_selectedImageElem) {
            setSliderPhotoCss(g_selectedImageElem, 'imgNotSelected');

            if (e.target.classList.contains("fa-step-forward") || e.target.classList.contains("fa-angle-double-right")) {
                // find the following image
                const nextSibling = g_selectedImageElem?.parentElement?.parentElement?.nextElementSibling;
                if (nextSibling){
                    g_selectedImageElem = nextSibling.getElementsByTagName("img")[0];
                }
                else {
                    // go to the first image
                    g_selectedImageElem = slider.querySelectorAll('img')[0];
                }
                selected = true;
            }
            else if (e.target.classList.contains("fa-step-backward") || e.target.classList.contains("fa-angle-double-left")) {
                const previousSibling = g_selectedImageElem?.parentElement?.parentElement?.previousElementSibling;
                // find the following image
                if (previousSibling){
                    g_selectedImageElem = previousSibling.getElementsByTagName("img")[0];
                }
                else {
                    const all = slider.querySelectorAll('img');
                    g_selectedImageElem = slider.querySelectorAll('img')[all.length-1];
                }
                selected = true;
            }
        }
        else {
            //console.log("show-gallery-option nothing selected");
            //console.log("show-gallery-option slider", slider);
            g_selectedImageElem = slider.querySelectorAll('img')[0];
            selected = true;
        }

        if (selected) {
            selectSliderImageByElem(g_selectedImageElem, true);
            if (e.target.classList.contains("fa-step-forward") || e.target.classList.contains("fa-step-backward")) {
                g_selectedImageSrc = g_selectedImageElem.getAttribute('src');
                animateMarkerByImage(g_selectedImageElem);
            }
        }
        else {
            displayPostDescription(null);
        }

        // console.log("show-gallery-option OUT g_selectedImage", {g_selectedImageElem, g_selectedImageSrc});

    });

    let searchElem = document.getElementById("searchInput");
    if (searchElem) {
        document.getElementById("searchInput").addEventListener("keypress", function(event) {
            // Check if the pressed key is 'Enter' (key code 13)
            // console.log('searchInput IN', event);
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
            // console.log('searchAddress IN');
            var address = document.getElementById('searchInput').value;

            if (address == '') {
                return;
            }

            // Utilisation de l'API Nominatim pour géocoder l'adresse
            var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + address;
            // console.log('searchAddress url', url);

            fetch(url)
            .then(response => response.json())
            .then(data => {
                // console.log('searchAddress response', data);
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

    // console.log("getImagesFromBB IN", {ne_lat, ne_lng, sw_lat, sw_lng, zoom});
    
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
            // console.log("success", response);
            updatePlanetSlider(response.data);
            

        },
        error: function(response) {
            console.log("error", response);
        }
    });
}

function updatePlanetSlider(datas) {
    //console.log("updatePlanetSlider IN", datas);
    //console.log("updatePlanetSlider close g_lightbox", g_lightbox);
    // close the lightbox if opened
    //g_lightbox.close();
    
    const slider = document.getElementById('imageSlider');
    const descr = document.getElementById('imageDescr');
    slider.innerHTML="";
    descr.innerHTML="";
        //console.log("updatePlanetSlider descr avant", descr);
    //g_lightbox.destroy();
    //let newHtml="";
    if (datas) {
        let num=0;
        datas.forEach(function(image) {
            //console.log("updatePlanetSlider image", image);

            
            let sliderHtml = "<div class='slider-item'>";
            sliderHtml +=       "<div class='slider-lb' data-full='"+image.url_full+"'>";
            sliderHtml +=           "<img src='"+image.url_medium+"' id='slider-"+image.id+"' class='imgNotSelected'>";
            sliderHtml +=           "<div class='slider-descr'>";
            
            // description of the photo INSIDE the lightbox
            if (image.content != "") {
                sliderHtml +=           "<div class='desc-lightbox-title'>"+image.content+"</div>";
            }
            if (image.address != "") {
                sliderHtml +=           "<div class='desc-lightbox-address'><i class='fas fa-map-marker-alt pg-tab'></i>"+image.address+"</div>";
            }
            if (image.user != "") {
                if (image.user_url != "") {
                    let domain= new URL(image.user_url).origin
                    sliderHtml +=       "<div class='desc-lightbox-user'>";
                    sliderHtml +=           "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, <a style='color: white;' href='"+image.user_url+"'>"+domain+"</a></div>";
                    //sliderHtml +=           "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, "+image.date+"</div>";
                    sliderHtml +=           "<div class='desc-lightbox-address'>"+image.date+"</div>";
                    //sliderHtml +=           "<div class='desc-lightbox-address'><i class='fas fa-globe pg-tab'></i><a style='color: white;' href='"+image.user_url+"'>"+domain+"</a></div>";
                    sliderHtml +=       "</div>";
                }
                else {
                    sliderHtml +=       "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, "+image.date+"</div>";
                }
            }
            else {
                sliderHtml +=           "<div class='desc-lightbox-address'>"+image.date+"</div>";
            }
            sliderHtml +=           "</div>";
            sliderHtml +=       "</div>";
            sliderHtml +=       "<div class='slider-overlay-circle'>";
            sliderHtml +=           "<i class='far fa-dot-circle slider-icon' data-num='"+num+"'></i>";
            sliderHtml +=       "</div>";
            sliderHtml +=       "<div class='slider-overlay-text'>";
            sliderHtml +=           "<i class='fas fa-align-center slider-icon' data-num='"+num+"'></i>";
            sliderHtml +=       "</div>";
            sliderHtml +=    "</div>";
            
            num = num + 1;            

            slider.innerHTML += sliderHtml;

            let descrHtml = "<div id='desc-"+image.id+"' class='desc-slider desc-display'>";
            if (image.content != "") {
                descrHtml +="<div class='desc-slider-title'>"+image.content+"</div>";
            }
            if (image.address != "") {
                descrHtml +="<div class='desc-slider-address'><i class='fas fa-map-marker-alt'></i> "+image.address+"</div>";
            }
            descrHtml +="</div>";            

            descr.innerHTML += descrHtml;
        });

        //console.log("updatePlanetSlider descr après", descr);
        const div_targets = slider.querySelectorAll('.slider-overlay-circle');
        div_targets.forEach(el => el.addEventListener('click', event => {
            processClickOnTarget(event.target);
            event.preventDefault();
        }));

        const div_texts = slider.querySelectorAll('.slider-overlay-text');
        div_texts.forEach(el => el.addEventListener('click', event => {
            processClickOnText(event.target);
            event.preventDefault();
        }));


        g_lightbox.refresh();
    
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


