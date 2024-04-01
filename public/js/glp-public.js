(function ($) {
    $(function () {
        var flag = true;
        var tapped = false;
        var touchStartCoords;
        $(document).on('touchstart', function (e){
            touchStartCoords = e.originalEvent.touches[0].clientY;
         });
        $(document).on("touchend" , ".lg-image",function(e){
            var touchEndCoords = e.originalEvent.changedTouches[0].clientY;
            if(touchStartCoords == touchEndCoords){
                if(!tapped){
                    tapped=setTimeout(function(){
                        if(flag){
                            $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut("fast");
                            flag = false;
                        }
                        else{
                            $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut().toggle("fast");
                            flag = true;
                        }
                        tapped = null;
                        
                    },200);  
                }
                else{
                    clearTimeout(tapped); 
                    tapped = null;
                }                    
                e.preventDefault();

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

        $(document).find('.user-gallery-option').on('click', function(e){
            console.log("user-gallery-option click", e)
            if (e.target.classList.contains("fa-edit")) {
                const galid = e.target.dataset.galid;
                console.log("user-gallery-option edit galid=", galid)
                let edit_gallery_url = document.getElementById('pg_edit_gallery_url').value;
                edit_gallery_url += "&gid=";
                edit_gallery_url += galid;
                window.location = edit_gallery_url;
            }
            if (e.target.classList.contains("fa-eye")) {
                const galid = e.target.dataset.galid;
                console.log("user-gallery-option view galid=", galid)
                let edit_gallery_url = document.getElementById('pg_show_gallery_url').value;
                edit_gallery_url += "&gid=";
                edit_gallery_url += galid;
                window.location = edit_gallery_url;
            }
            if (e.target.classList.contains("fa-trash")) {
                const galid = e.target.dataset.galid;
                console.log("user-gallery-option trash galid=", galid)
                let edit_gallery_url = document.getElementById('pg_show_gallery_url').value;
                edit_gallery_url += "&gid=";
                edit_gallery_url += galid;
                window.location = edit_gallery_url;
            }
            e.preventDefault();
        });

        $(document).find('.gallery-item-opgallery-photo-optiontion').on('click', function(e){
            console.log("gallery-photo-option click", e)
            if (e.target.classList.contains("fa-edit")) {
                const postid = e.target.parentElement.dataset.id;
                console.log("gallery-photo-option postid=", postid);
                let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                edit_photo_url += "&pid=";
                edit_photo_url += postid;
                window.location = edit_photo_url;
            }
            if (e.target.classList.contains("fa-trash")) {
                const postid = e.target.parentElement.dataset.id;
                console.log("gallery-photo-option postid=", postid);
                let edit_photo_url = document.getElementById('pg_edit_photo_url').value;
                edit_photo_url += "&pid=";
                edit_photo_url += postid;
                window.location = edit_photo_url;
            }
            e.preventDefault();
        });

        $(document).on('click',".lg-image",function(e){
            if(!tapped){
                tapped=setTimeout(function(){
                    if(flag){
                        $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut("fast");
                        flag = false;
                    }
                    else{
                        $(document).find(".lg-icon,.lg-sub-html,.lg-toolbar").fadeOut().toggle("fast");
                        flag = true;
                    }
                    tapped = null;
                    
                },200);  
            }
            else{
                clearTimeout(tapped); 
                tapped = null;
            }                    
            e.preventDefault();
        })

    })
})(jQuery)

function ays_closestEdge(x,y,w,h) {
    let ays_topEdgeDist = ays_distMetric(x,y,w/2,0);
    let ays_bottomEdgeDist = ays_distMetric(x,y,w/2,h);
    let ays_leftEdgeDist = ays_distMetric(x,y,0,h/2);
    let ays_rightEdgeDist = ays_distMetric(x,y,w,h/2);
    let ays_min = Math.min(ays_topEdgeDist,ays_bottomEdgeDist,ays_leftEdgeDist,ays_rightEdgeDist);

    switch (ays_min) {
        case ays_leftEdgeDist:
            return 'left';
        case ays_rightEdgeDist:
            return 'right';
        case ays_topEdgeDist:
            return 'top';
        case ays_bottomEdgeDist:
            return 'bottom';
    }
}

function lazyload_single () {
    console.log('lazyload_single in');
    
    var scrollTop = window.pageYOffset;
    console.log('lazyload_single in1');
    var lazyloadImages = document.querySelectorAll('img.lazy');
    if(lazyloadImages.length != 0) { 
        let img = lazyloadImages[0];
        if (img.src == '') {
            let parent = img.parentNode;
            console.log('lazyload_single offsetTop', parent.offsetTop);
            console.log('lazyload_single innerHeight', window.innerHeight);
            console.log('lazyload_single scrollTop', scrollTop);
            console.log('lazyload_single sum', window.innerHeight + scrollTop);
            if(parent.offsetTop < (window.innerHeight + scrollTop)) {
                //img.classList.remove('lazy');
                img.classList.add('lazyloaded');
                img.src = img.dataset.src;
                console.log('lazyload_single img', img);
            }
        }
    };
}

// for grid 
function lazyload_max () {
    console.log('lazyload_max in');
    var lazyloadImages = document.querySelectorAll('img.lazy');    
    console.log('lazyload_max lenght', lazyloadImages.length);
    
    var scrollTop = window.pageYOffset;
    console.log('lazyload_max in1');
    lazyloadImages.forEach(function(img) {
        if (img.src == '') {
            let parent = img.parentNode;
            if(parent.offsetTop < (window.innerHeight + scrollTop)) {
                img.classList.add('lazyloaded');
                img.src = img.dataset.src;
                //
                console.log('lazyload_max img', img);
            }
        }
    });
}


var geojson={};
//TODO do not load geojson if already loaded

async function ays_add_vignette_to_image( lmapId,jfile,lat,lon,zoom) {

    console.log("ays_add_vignette_to_image IN lmapId=", lmapId);
    // get country
    //console.log("country", country);
    //let zoom = country.zoom;
    let file = ays_vars.base_url + "assets/geojson/" + jfile;

    //let select = document.getElementsByClassName("compat-field-vignette")[0];
    //console.log("select", select);
    //console.log("BABAauRHUM", parent);
    // select.appendChild(p);
    // var elemDiv = document.createElement('td');
    // elemDiv.id = lmapId;
    var elemDiv = document.getElementById(lmapId);
    //console.log("ays_add_vignette_to_image", elemDiv);
    
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
        fillColor: 'lightgoldenrodyellow',
        //color: 'yellow',
        fillOpacity: 2,
        weight: 1
    }
    var myIconClass = L.Icon.extend({
        options: {
            iconSize:     [4, 4],
            iconAnchor:   [2, 2]
        }
    });
    
    // console.log("css:", css);
    //elemDiv.style.height = country.height;
    //elemDiv.style.width = country.width;
    //elemDiv.style.backgroundColor = 'white';
    elemDiv.style.background = 'transparent';
    // elemDiv.style.borderStyle = 'solid';
    // elemDiv.style.borderWidth = 'thin';
    // elemDiv.style.borderColor = 'lightgray';
    //select.appendChild(elemDiv);
    
    var mark = new myIconClass ({iconUrl: ays_vars.base_url + 'assets/markpoint.png'});
    
    let lmap = L.map(lmapId, props);
    //console.log("ays_add_vignette_to_image AA created lmap", {lmapId, lmap});
    //console.log("css:", css);
    // Charger le fichier GeoJSON et l'ajouter à la carte
    const response = await fetch(file);  // Use the 'await' keyword with 'fetch'

    //console.log("ays_add_vignette_to_image BB lmapId=", lmapId);
    const data = await response.json();

    L.geoJSON(data, {
        clickable: false,
        style: geostyle
    }).addTo(lmap);
    let clon = data.features[0].properties.geo_point_2d.lon;
    let clat = data.features[0].properties.geo_point_2d.lat;
    let center = [clat, clon];
    //console.log("coord:", coord);
    //console.log("lmap:", lmap);
    //console.log("ays_add_vignette_to_image DD setView", {lmapId, lmap});
    lmap.setView(center, zoom);
    let marker = [lat, lon];
    L.marker(marker, {icon: mark}).addTo(lmap);
 }


//Distance Formula
function ays_distMetric(x,y,x2,y2) {
    let ays_xDiff = x - x2;
    let ays_yDiff = y - y2;
    return (Math.abs(ays_xDiff) * Math.abs(ays_yDiff))/2;
}

function ays_getDirectionKey(ev, obj) {
    let ays_w = obj.offsetWidth,
        ays_h = obj.offsetHeight,
        ays_x = (ev.pageX - obj.offsetLeft - (ays_w / 2) * (ays_w > ays_h ? (ays_h / ays_w) : 1)),
        ays_y = (ev.pageY - obj.offsetTop - (ays_h / 2) * (ays_h > ays_w ? (ays_w / ays_h) : 1)),
        ays_d = Math.round( Math.atan2(ays_y, ays_x) / 1.57079633 + 5 ) % 4;
    return ays_d;
}


/** dragable and sortable list **/


const sortableList = document.querySelector(".sortable-list");
if (sortableList) {
    const items = sortableList.querySelectorAll(".item");

    items.forEach(item => {
        item.addEventListener("dragstart", () => {
            // Adding dragging class to item after a delay
            setTimeout(() => item.classList.add("dragging"), 0);
        });
        // Removing dragging class from item on dragend event
        item.addEventListener("dragend", () => item.classList.remove("dragging"));
    });

    const initSortableList = (e) => {
        e.preventDefault();
        const draggingItem = document.querySelector(".dragging");
        // Getting all items except currently dragging and making array of them
        let siblings = [...sortableList.querySelectorAll(".item:not(.dragging)")];

        // Finding the sibling after which the dragging item should be placed
        let nextSibling = siblings.find(sibling => {
            return e.clientY <= sibling.offsetTop + sibling.offsetHeight / 2;
        });

        // Inserting the dragging item before the found sibling
        sortableList.insertBefore(draggingItem, nextSibling);
    }

    sortableList.addEventListener("dragover", initSortableList);
    sortableList.addEventListener("dragenter", e => e.preventDefault());
}


