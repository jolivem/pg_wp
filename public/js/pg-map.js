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


/** 
 * @brief move to slide image when map image is clicked 
 * @param id example "slider-574" where 574 is the post id
 * */
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
            g_slick.slickGoTo(index, true);
        }
        else {
            console.log('selectSlideById index = -1 for', {id});
        }
    }
    else {
        console.log('selectSlideById imageElement not found for', {id});
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

// brief: show map image when click on slide target
var processClickOnTarget = function() {
    //console.log('processClickOnTarget IN');
    const elem = g_slick.getCurrentElem().get(0);
    //console.log("displaySlideDescription elem", elem);
    const img = elem.getElementsByTagName("img")[0];
    //console.log("afterChange img", img);
    if (img){
        g_selectedImageSrc = img.getAttribute('src'); //  used to focus on clustered image in the map
        //console.log('processClickOnTarget imageSrc = '+g_selectedImageSrc);
        //selectSliderImageByElem(img, false);
        animateMarkerById(img.getAttribute('id'));
    }
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
var animateMarkerById = function(id, size) {

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

                layer._icon.style.width="55px";
                layer._icon.style.height="55px";
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
                iicoon.options.iconSize = [55,55];
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
                    
                    g_map.setView([lat, lon], 12);

                    L.marker([lat, lon]).addTo(g_map)
                        .bindPopup(address)
                        .openPopup();
                } else {
                    alert(ays_vars.address_not_found);
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

    // return postid or -1
    function get_current_slick_pid() {
        
        const elem = g_slick.getCurrentElem().get(0);
        //console.log("displaySlideDescription elem", elem);
        const img = elem.getElementsByTagName("img")[0];
        //console.log("afterChange img", img);
        if (img){
            return img.id.substring(7);

        }
        return -1;
    }

    function get_current_slick_description() {
        
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
                    return descr;
                }
            }
        }
        return null;
    }

    window.displaySlideDescription = function() {
        let descr = get_current_slick_description();
        //console.log("displaySlideDescription descr", descr);
        if (descr) {
            
            // add vignette
            if (window.pg_add_vignette_to_slider) {
                // vignette only for planet map, not for user map
                window.pg_add_vignette_to_slider(descr);
            }

            $(descr).fadeIn();
            //descr.style.display='block';
        }
    };

    // window.focusOnLeafletPhoto = function() {

    //     let descr = get_current_slick_description();
    //     //console.log("focusOnLeafletPhoto descr", descr);
    //     if (descr) {

    //         //descr.id example = desc-708
    //         // change to slider-708 = id of the image
    //         const img_id = descr.id.replace("desc", "slider");

    //         const img = document.getElementById(img_id);
    //         // //console.log('processClickOnTarget img', img);
        
    //         //const imageSrc = img.getAttribute('id');
    //         g_selectedImageSrc = img.getAttribute('src'); //  used to focus on clustered image in the map
    //         // //console.log('processClickOnTarget imageSrc = '+g_selectedImageSrc);
    //         // //selectSliderImageByElem(img, false);
    //         animateMarkerById(img.getAttribute('id'));                        
    //     }
    // };

    // update planet slider from given data structure response
    window.updatePlanetSlider = function(datas) {
        //console.log("updatePlanetSlider IN", datas);
        //console.log("updatePlanetSlider ids", datas.ids);
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
                //console.log("updatePlanetSlider image", image.vignette);
                let sliderHtml = "<div class='slide slider-item'>";
                sliderHtml +=       "<div class='slider-lb' data-full='"+image.url_full+"'>";
                sliderHtml +=           "<img src='"+image.url_medium+"' id='slider-"+image.id+"' class='slider-img'>";
                sliderHtml +=           "<div class='lightbox-descr'>";
                //sliderHtml +=              "<div class='lightbox-descr-all'>";
                sliderHtml +=                 "<div class='lightbox-descr-text'>";
                
                // description of the photo INSIDE the lightbox
                if (image.content != "") {
                    sliderHtml +=                 "<div class='desc-lightbox-title'>"+image.content+"</div>";
                }
                if (image.address != "") {
                    sliderHtml +=                 "<div class='desc-lightbox-address'><i class='fas fa-map-marker-alt pg-tab'></i>"+image.address+"</div>";
                }
                if (image.user != "") {
                    sliderHtml +=                 "<div class='desc-lightbox-user'>";
                    if (image.user_url != "") {
                        let domain= new URL(image.user_url).origin
                        sliderHtml +=                 "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, <a style='color: white;' href='"+image.user_url+"'>"+domain+"</a>,&nbsp</div>";
                    }
                    else {
                        sliderHtml +=                 "<div class='desc-lightbox-address'><i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>,&nbsp</div>";
                    }
                    sliderHtml +=                     "<div class='desc-lightbox-address'>"+image.date+"</div>";
                    sliderHtml +=                 "</div>";
                }
                else {
                    sliderHtml +=                 "<div class='desc-lightbox-address'>"+image.date+"</div>";
                }
                sliderHtml +=                 "</div>"; // for lightbox-descr-text
                // if (image.vignette != "") {
                //     sliderHtml +=             "<div id='vignette-lb-"+image.id+"' class='desc-slider-address pg-lightbox-vignette' data-lon='"+image.longitude+"' data-lat='"+image.latitude+"' data-country='"+image.vignette+"'></div>";
                // }
                sliderHtml +=              "</div>";
                //sliderHtml +=           "</div>";

                // Add overlay buttons
                sliderHtml +=       "</div>";
                sliderHtml +=       "<div class='slider-overlay-expand'>";
                sliderHtml +=           "<i class='fas fa-expand slider-icon' data-num='"+num+"'></i>";
                sliderHtml +=       "</div>";
                if (ban == 1) {
                    sliderHtml +=   "<div class='slider-overlay-link'>";
                    sliderHtml +=       "<i class='fas fa-link slider-icon' data-num='"+num+"'></i>";
                    sliderHtml +=   "</div>";
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
                if (image.vignette != "") {
                    descrHtml +=       "<div id='vignette-"+image.id+"' class='desc-slider-address pg-descr-vignette' data-lon='"+image.longitude+"' data-lat='"+image.latitude+"' data-country='"+image.vignette+"'></div>";
                }
                if (image.user != "") {
                    descrHtml +=       "<div class='desc-slider-address'>";
                    if (image.user_url != "") {
                        let domain= new URL(image.user_url).origin
                        descrHtml +=         "<i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>, <a href='"+image.user_url+"'>"+domain+"</a>,&nbsp"+image.date;
                    }
                    else {
                        descrHtml +=         "<i class='fas fa-user pg-tab'></i><b>"+image.user+"</b>,&nbsp"+image.date;
                    }
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

                if (size > 10) {
                    g_slick.setOption( "infinite", true, true);
                }

                // is there a 'focus on pid' value ?
                if (datas.fpid) {
                    //console.log("fpid still equals to", datas.fpid);
                    selectSlideById( "slider-" + datas.fpid);

                }
                else {
                    // remove the property
                    //console.log("remove fpid");
                    delete document.getElementById('fpid').value;
                    g_slick.slickGoTo( size/2, true);
                }

                // display description of first slide
                window.displaySlideDescription();
                // $('.slider').on('swipe', function(event, slick, direction){
                //     console.log("swipe");
                // });
                //console.log("window.g_slick_has_callbacks", window.g_slick_has_callbacks);
                // bind callbacks once
                if (window.g_slick_has_callbacks === undefined) {
                
                    $('.slider').on('afterChange', function(event, slick, direction){
                        //console.log("afterChange IN", {event, slick, direction});
                        //console.log("afterChange target", event.target);
                        //console.log("afterChange currentSlide", currentSlide);

                        var current_pid = get_current_slick_pid();
                        if (current_pid != -1) {
                            //console.log("afterChange set fpid", current_pid);
                            document.getElementById('fpid').value = current_pid;
                        }
                        else {
                            //console.log("afterChange delete fpid");
                            delete document.getElementById('fpid').value;
                        }

                        window.displaySlideDescription();
                    });

                    $('.slider').on('beforeChange', function(event, slick, currentSlide, nextSlide){
                        //console.log("beforeChange", {currentSlide, nextSlide});
                        //console.log("beforeChange currentSlide", currentSlide);

                        if (currentSlide != nextSlide) {
                            window.hideSlideDescription();
                        }
                        else {
                            //console.log("beforeChange SAME SLIDE");
                            processClickOnTarget();
                        }
                    });
                    window.g_slick_has_callbacks = true;
                }
            }

            // handle click on overlay-expand to open lightbox
            const div_expand = slider.querySelectorAll('.slider-overlay-expand');
            div_expand.forEach(el => el.addEventListener('click', event => {

                const slide = event.target.parentElement.parentElement;

                //console.log("expand slide", slide);
                const src = window.findSlideSrc(slide);
                if (src) {
                    g_lightbox.openSrc(src);
                }
            }));
    
            // handle double click on images to open lightbox
            // const div_img_slider = slider.querySelectorAll('.slider-img');
            // div_img_slider.forEach(el => el.addEventListener('dblclick', event => {

            //     const slide = event.target.parentElement.parentElement;
            //     console.log("dblclick slide", slide.parentElement.parentElement);
            //     if (slide.closest('.slick-current')) {
            //         console.log("slick-current found");
            //         const src = window.findSlideSrc(slide);
            //         if (src) {
            //             g_lightbox.openSrc(src);
            //         }
            //     }
            // }));

            // handle long press on images to open lightbox
            // let longPressTimer;

            // div_img_slider.forEach(el => el.addEventListener('touchstart', (event) => {
            //     const slide = event.target.parentElement.parentElement;
            //     console.log("dblclick slide", slide);
            //     if (slide.closest('.slick-current')) {
            //         longPressTimer = setTimeout(() => {
            //             console.log('Long press detected');
                        
            //             // open lightbox
            //             const src = window.findSlideSrc(slide);
            //             if (src) {
            //                 g_lightbox.openSrc(src);
            //             }
            //         }, 500); // 500ms threshold for long press
            //     }
            // }));

            // div_img_slider.forEach(el => el.addEventListener('touchend', (event) => {
            //     clearTimeout(longPressTimer); // Cancel if the finger is lifted
            // }));

            // div_img_slider.forEach(el => el.addEventListener('touchmove', (event) => {
            //     clearTimeout(longPressTimer); // Cancel if the finger is moved
            // }));

            if (ban == 1) {
                const div_targets = slider.querySelectorAll('.slider-overlay-link');
                div_targets.forEach(el => el.addEventListener('click', event => {
                    //processClickOnTarget(event.target);
                    const img = event.target.parentElement?.parentElement?.getElementsByTagName("img")[0];
                    if (img){
                        //console.log('click on link img', img);
                        //  remove prefix "slider-"
                        let postid = img.id.substring(7);
                        console.log('click on link img postid', postid);
                        let site_url = document.getElementById('pg_site_url').value;
                        site_url += "/?fpid=";
                        site_url += postid;
        
                        navigator.clipboard.writeText(site_url).then(() => {
                            // display a toast
                            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
                            // console.log("user-gallery-option toastElList=", toastElList);
                            toastElList.map(function(toastEl) {
                                // console.log("user-gallery-option toastEl=", toastEl);
                                return new bootstrap.Toast(toastEl);
                            });
        
                            var myToastEl = document.getElementById('copy-to-clipboard');
                            var myToast = bootstrap.Toast.getInstance(myToastEl);
                            myToast.show();                
                        });
                    }
                    event.preventDefault();
                }));
        
                const div_ban = slider.querySelectorAll('.slider-overlay-ban');
                div_ban.forEach(el => el.addEventListener('click', event => {
                    processClickOnBan(event.target);
                    event.preventDefault();
                }));
            }
    
            g_lightbox.refresh();
            // bind callbacks once
            // if (window.g_lightbox_has_callbacks === undefined) {
            //     g_lightbox.on('shown.simplelightbox', function (e) {
            //         console.log("shown.simplelightbox", e.target);
            //         let caption = get_lightbox_caption();
            //         if (caption != null) {
            //             window.pg_add_vignette_to_lightbox(caption);
            //         }

            //     });
            //     g_lightbox.on('changed.simplelightbox', function (e) {
            //         console.log("changed.simplelightbox", e.target);
            //         let caption = get_lightbox_caption();
            //         if (caption != null) {
            //             window.pg_add_vignette_to_lightbox(caption);
            //         }
            //     });
            //     window.g_lightbox_has_callbacks = true;
            // }

            //window.pg_load_vignettes();
        }
        else {
            // no data --> empty area
            slider.innerHTML = "<div style='height:250px;'>";
        }
    }

})( jQuery );

function getImagesFromBB(ne_lat, ne_lng, sw_lat, sw_lng, zoom) {

    //console.log("getImagesFromBB IN", {ne_lat, ne_lng, sw_lat, sw_lng, zoom});
    
    let admin_url = document.getElementById('pg_admin_ajax_url').value;
    let nonce = document.getElementById('page_nonce').value;
    let fpid = document.getElementById('fpid').value;
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
    if (fpid != undefined) {
        //console.log("getImagesFromBB fpid", {fpid});
        formData.append('fpid', fpid);
    }
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

