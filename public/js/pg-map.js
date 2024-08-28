var g_selectedImageSrc = null; /*image src when clicked in the slider target*/
var g_map;
var g_lightbox;
var g_markers;
var g_listImageIds = []; /* list image ids currently displayed */
var g_LeafIcon = L.Icon.extend({
    options: {
        iconSize:     [45, 45],
        shadowAnchor: [4, 47],
        popupAnchor:  [-3, -76],
        className: 'mydivicon'
    }
});
var g_slick;


/* brief move to slide image when map image is clicked */
var selectSlideById =  function(id) {
    //console.log('selectSlideById IN', {id});

    //let thumbnailSrc = makeThumbnailSrc(mysrc);
    //var imageElement = document.querySelector(`#imageSlider img[src='${mysrc}']`);
    var imageElement = document.getElementById(id);
    /* Add classname to the selected image */
    //console.log('selectSlideById imageElement', imageElement);
    if (imageElement) {
        //console.log('selectSlideById imageElement ', imageElement);

        const index = g_slick.findIndexBySrc(imageElement.src);
        if (index != -1) {
            g_slick.slickGoTo(index, false);
        }
    }
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

// brief: show map image when wlick on slide target
var processClickOnTarget = function(elem) {
    //console.log('processClickOnTarget elem', elem);
    
    const img = elem.parentElement.parentElement.getElementsByTagName("img")[0];
    //console.log('processClickOnTarget img', img);

    //const imageSrc = img.getAttribute('id');
    g_selectedImageSrc = img.getAttribute('src'); //  used to focus on clustered image in the map
    //console.log('processClickOnTarget imageSrc = '+g_selectedImageSrc);
    //selectSliderImageByElem(img, false);
    animateMarkerById(img.getAttribute('id'));
};

// click on ban, admin feature to ban a plublic image
var processClickOnBan = function( elem) {
    //console.log('processClickOnBan elem', elem);
    const img = elem.parentElement?.parentElement?.getElementsByTagName("img")[0];
    if (img){
        //console.log('processClickOnBan img', img);
        //  remove prefix "slider-"
        let postid = img.id.substring(7);
        // console.log('processClickOnBan postid', postid);

        let admin_url = document.getElementById('pg_admin_ajax_url').value;
        let nonce = document.getElementById('page_nonce').value;
        //console.log("uploadPhotos admin_url=", admin_url);
    
        const formData = new FormData();
        formData.append('action', 'ban_image');
        formData.append('nonce', nonce);
        formData.append('pid', postid);
        jQuery.ajax({
            method: 'POST',
            url: admin_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                // console.log("success", response);
                
                // build toast list
                var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                toastElList.map(function(toastEl) {
                    return new bootstrap.Toast(toastEl)
                })
                
                var myToastEl = document.getElementById('ban-photo-success')
                var myToast = bootstrap.Toast.getInstance(myToastEl);
                myToast.show();                
            
    
            },
            error: function(response) {
                
            }
        });        
    }
}

// animate the maker in the map
var animateMarkerById = function(id) {

    //console.log('animateMarkerById IN', g_selectedImageSrc);
    //console.log('animateMarkerById g_markers', g_markers);
    //console.log('animateMarkerById id', id);
    //let imageSrc= img.getAttribute('src');

    // animate the marker on the map
    let layers = g_markers.getLayers();
    //console.log( 'animateMarkerById layers', layers);
    for (let i in layers) {
        let layer = layers[i];
        //console.log( 'animateMarkerById loop on layers', {i, layer});
        //let has = g_markers.hasLayer(layer);
        //console.log( 'animateMarkerById options', layer?.options?.icon?.options);
        //let iconUrl = layer?.options?.icon?.options?.iconUrl;
        let data = layer?.options?.icon?.options?.data;
        //console.log( 'animateMarkerById data', data);
        if (data != null && data == id) {
            // console.log( `animateMarkerById FFOOUUNNDD layer`, layer);
            // console.log( `animateMarkerById iconUrl`, layer.options.icon.options.iconUrl);
            // console.log( `animateMarkerById options`, layer.options);
            //console.log( `animateMarkerById iconUrl FOUND`);
            if (layer._icon != null) {

                layer._icon.style.width="60px";
                layer._icon.style.height="60px";
                layer._icon.style.zIndex += 1000;
                //console.log( 'animateMarkerById layer._icon FOUND for iconUrl');
                setTimeout(function(){
                    // come back to normal size after timeout
                    layer._icon.style.width="45px";
                    layer._icon.style.height="45px";
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
                    
                    // leads to call iconCreateFunction for cluster
                    // iconCreateFunction set g_selectedImageSrc=null
                    visibleOne.refreshIconOptions({
                        iconSize:     [45, 45],
                    }, true);
                }
            }
        }
    }
    g_selectedImageSrc = null;
    // console.log('animateMarkerById break OUT', g_selectedImageSrc);
};

(function( $ ) {
	'use strict';	
	//$(document).ready(function(){     
    g_selectedImageSrc = null; /*image clicked in the slider */

    g_markers = L.markerClusterGroup({
        zoomToBoundsOnClick: true,
        iconCreateFunction: function(cluster) {
            // Called on zoom end and move end
            // Called when clicking on cluster image
            //console.log('iconCreateFunction IN ');
            //console.log('iconCreateFunction getChildCount', cluster.getChildCount());

            var children = cluster.getAllChildMarkers()[0];

            //console.log('icon', children.options.icon);
            var iicoon = new L.Icon(children.options.icon.options);
            //console.log('iicoon', iicoon);
            var count = cluster.getChildCount();
            // if (count < 6) {
            iicoon.options.className = 'mydivmarker';
            // }
            // else {
            //    iicoon.options.className = 'mydivmarker9';    
            //}
            
            if (g_selectedImageSrc != null) {
                //console.log( 'g_selectedImageSrc', g_selectedImageSrc);
                iicoon.options.iconSize = [70,70];
                iicoon.options.iconUrl = g_selectedImageSrc;
                g_selectedImageSrc = null;
                
                setTimeout(function(){
                    cluster.refreshIconOptions({
                        //shadowUrl: 'leaf-shadow.png',
                        iconSize:     [45, 45],
                    }, true); 
                }, 400);                        
            }

            return iicoon;
        },
        polygonOptions: {color: 'transparent'},
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
        //console.log('click icon ', a.layer.options.icon);
        //selectSlideById(a.layer.options.icon.options.iconUrl, true);
        selectSlideById(a.layer.options.icon.options.data, true);
        //let visibleOne = g_markers.getVisibleParent(a.layer);
        // console.log('click visibleOne', visibleOne);
    });

    g_markers.on('clusterclick', function (a) {
        //console.log('clusterclick ', a);
        /* a.layer is actually a cluster
        console.log('L ', L); */
        let visibleOne = g_markers.getVisibleParent(a.layer);
        visibleOne.refreshIconOptions({
                    iconSize:     [70, 70],
                }, true);
        g_markers.refreshClusters();
        /*g_markers.refreshClusters(visibleOne);*/
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

    window.findSlideSrc = function(slide) {
        // use JQuery
        var elem = $(slide);
    
        // Trouver l'élément img unique parmi tous les descendants
        var imgElements = elem.find('img');
    
        // Vérifier s'il y a un seul élément img
        if (imgElements.length === 1) {
            // console.log("findSlideSrc return", imgElements.first().attr('src'));
            return imgElements.first().attr('src'); // Retourner le seul élément img
        } else {
            return null; // Retourner null s'il n'y a pas exactement un seul élément img
        }
    }

    window.hideSlideDescription = function() {
        // hide all descriptions
        let alldescs = document.querySelectorAll('.desc-display');
        for (let i = 0; i < alldescs.length; i++) {
            if (alldescs[i].style.display != "none") {
                $(alldescs[i]).fadeOut(100);
            }
            //$(alldescs[i]).fadeOut();
        }
    
    };

    window.displaySlideDescription = function() {
        //console.log("displaySlideDescription g_slick", g_slick);
        const elem = g_slick.getCurrentElem().get(0);
        //console.log("displaySlideDescription elem", elem);
        const img = elem.getElementsByTagName("img")[0];
        //console.log("afterChange img", img);
        if (img){
            let postid = img.id.substring(7);    
    
            // find description
            if (postid != null) {
                const descid = "desc-"+postid;
                var descr = document.getElementById(descid);
                // console.log('descid ', descid);
                if (descr) {
                    $(descr).fadeIn();
                    //descr.style.display='block';
                }
            }
        }
    };

    // update planet slider from given data structure response
    window.updatePlanetSlider = function(datas) {
        //console.log("updatePlanetSlider IN", datas);
        //console.log("updatePlanetSlider close g_lightbox", g_lightbox);
        // close the lightbox if opened
        //g_lightbox.close();

        if (datas.action == "nothing") {
            // do nothing !!
            return;
        }

        let ban = document.getElementById('pg_ban').value;
        
        const slider = document.getElementById('imageSlider');
        const descr = document.getElementById('imageDescr');
        slider.innerHTML="";
        descr.innerHTML="";

        if (g_slick !== undefined) {
            //console.log("updatePlanetSlider destroy", g_slick);
            g_slick.destroy();
            g_slick = undefined;
            $('.slider').empty();
            //slider.innerHTML='';
        }
        //console.log("updatePlanetSlider descr avant", descr);
        //g_lightbox.destroy();
        //let newHtml="";
        if (datas?.images) {

            g_listImageIds = datas.ids; // save ids for optimisations

            let num=0;
            datas.images.forEach(function(image) {
                //console.log("updatePlanetSlider image", image);                
                let sliderHtml = "<div class='slide slider-item'>";
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
                    sliderHtml +=           "<div class='desc-lightbox-user'>";
                    if (image.user_url != "") {
                        let domain= new URL(image.user_url).origin
                        sliderHtml +=           "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, <a style='color: white;' href='"+image.user_url+"'>"+domain+"</a></div>";
                    }
                    else {
                        sliderHtml +=           "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b></div>";
                    }
                    sliderHtml +=               "<div class='desc-lightbox-address'>"+image.date+"</div>";
                    sliderHtml +=           "</div>";
            }
                else {
                    sliderHtml +=           "<div class='desc-lightbox-address'>"+image.date+"</div>";
                }
                sliderHtml +=           "</div>";

                // Add overlay buttons
                sliderHtml +=       "</div>";
                sliderHtml +=       "<div class='slider-overlay-circle'>";
                sliderHtml +=           "<i class='far fa-dot-circle slider-icon' data-num='"+num+"'></i>";
                sliderHtml +=       "</div>";
                sliderHtml +=       "<div class='slider-overlay-expand'>";
                sliderHtml +=           "<i class='fas fa-expand slider-icon' data-num='"+num+"'></i>";
                sliderHtml +=       "</div>";
                if (ban == 1) {
                    sliderHtml +=   "<div class='slider-overlay-ban'>";
                    sliderHtml +=       "<i class='fas fa-ban slider-icon' data-num='"+num+"'></i>";
                    sliderHtml +=   "</div>";
                }
    
                sliderHtml +=    "</div>";
                
                num = num + 1;            
    
                slider.innerHTML += sliderHtml;
    
                // description of the photo below the slider
                let descrHtml = "<div id='desc-"+image.id+"' class='desc-slider desc-display'>";
                if (image.content != "") {
                    descrHtml +="<div class='desc-slider-title'>"+image.content+"</div>";
                }
                if (image.address != "") {
                    descrHtml +="<div class='desc-slider-address'><i class='fas fa-map-marker-alt pg-tab'></i>"+image.address+"</div>";
                }
                if (image.user != "") {
                    descrHtml +=       "<div class='desc-slider-user'>";
                    if (image.user_url != "") {
                        let domain= new URL(image.user_url).origin
                        descrHtml +=         "<div class='desc-slider-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, <a href='"+image.user_url+"'>"+domain+"</a></div>";
                    }
                    else {
                        descrHtml +=         "<div class='desc-slider-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b></div>";
                    }
                    descrHtml +=             "<div class='desc-slider-address'>"+image.date+"</div>";
                    //sliderHtml +=           "<div class='desc-slider-address'><i class='fas fa-globe pg-tab'></i><a style='color: white;' href='"+image.user_url+"'>"+domain+"</a></div>";
                    descrHtml +=       "</div>";
                }
                else {
                    descrHtml +=       "<div class='desc-slider-address'>"+image.date+"</div>";
                }

                descrHtml +="</div>";            
    
                descr.innerHTML += descrHtml;
            
            }); // end foreach
 
            if (num > 0) {

                // create slider
                $('.slider').slick({
                    infinite: false,
                    centerMode: true,
                    // for slide-active class
                    // value muste be less than nb of slides
                    //slidesToShow: num-1, 
                    slidesToScroll: 1,
                    variableWidth: true,
                    //centerPadding: '40px',
                    swipeToSlide: true,
                });
                g_slick = $('.slider').slick('getSlick');
                let size = g_slick.getSlideSize();
                //console.log("SLIDE COUNT", size);
                g_slick.slickGoTo( size/2, true);
                if (size > 10) {
                    g_slick.setOption( "infinite", true, true);
                }

                // display description of first slide
                window.displaySlideDescription();
                // $('.slider').on('swipe', function(event, slick, direction){
                //     console.log("swipe");
                // });
                
                $('.slider').on('afterChange', function(event, slick, direction){
                    //console.log("afterChange target", event.target);
                    //console.log("afterChange currentSlide", currentSlide);

                    window.displaySlideDescription();
                                    
                });
                $('.slider').on('beforeChange', function(event, slick, currentSlide, nextSlide){
                    //console.log("beforeChange", {currentSlide, nextSlide});
                    //console.log("afterChange currentSlide", currentSlide);

                    if (currentSlide != nextSlide) {
                        window.hideSlideDescription();
                    }
                });
            }
    
            //console.log("updatePlanetSlider descr après", descr);
            const div_targets = slider.querySelectorAll('.slider-overlay-circle');
            div_targets.forEach(el => el.addEventListener('click', event => {
                processClickOnTarget(event.target);
                event.preventDefault();
            }));
    
            const div_expand = slider.querySelectorAll('.slider-overlay-expand');
            div_expand.forEach(el => el.addEventListener('click', event => {

                const slide = event.target.parentElement.parentElement;

                //console.log("expand slide", slide);
                const src = window.findSlideSrc(slide);
                if (src) {
                    g_lightbox.openSrc(src);
                }
            }));
    
            if (ban == 1) {
                const div_ban = slider.querySelectorAll('.slider-overlay-ban');
                div_ban.forEach(el => el.addEventListener('click', event => {
                    processClickOnBan(event.target);
                    event.preventDefault();
                }));
            }
    
            g_lightbox.refresh();
        }
        else {
            // no data --> empty area
            slider.innerHTML = "<div style='height:250px;'>";
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
    formData.append('current_ids', g_listImageIds);
    jQuery.ajax({
        method: 'POST',
        url: admin_url,
        data: formData,
        contentType: false,
        processData: false,
        success: function(response){
            // console.log("success", response);
            window.updatePlanetSlider(response.data);

        },
        error: function(response) {
            console.log("error", response);
        }
    });
}

