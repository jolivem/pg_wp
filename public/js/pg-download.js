
var g_filesArray = null;
const maxFile=5;

//
// SINGLE UPLOAD
//

// check latitude input
jQuery(document).find('#latitude').on("input", (event) => {
    console.log("latitude input");
    const latitudeInput = document.getElementById('latitude');
    const latitudeValue = parseFloat(latitudeInput.value);
    console.log("latitude input", latitudeInput.value);
    error = false;
    
    // not a float -> DMS style
    // console.log("isNaN str", isNaN(latitudeInput.value));
    // console.log("isNaN float", isNaN(latitudeValue));
    //console.log("isNumber(latitudeValue)", isNumber(latitudeInput.value));
    if (latitudeInput.value != '' && !isNumber(latitudeInput.value)) {
        const regLat = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(N|S)");
        console.log("regLat", regLat.test(latitudeInput.value) );
        if (regLat.test(latitudeInput.value) === false) {
            // Display error message
            latitudeInput.classList.add('is-invalid');
            var errorFeedback = document.getElementById('latitude-feedback');
            errorFeedback.innerText = 'Veuillez saisir une latitude valide.';
            error = true;
        } else {
            var lat = convertDMSToDD(latitudeInput.value);
            console.log('lat', lat);
            if (lat < -90 || lat > 90) {
                // Display error message
                latitudeInput.classList.add('is-invalid');
                var errorFeedback = document.getElementById('latitude-feedback');
                errorFeedback.innerText = 'Latitude must be between -90 and 90.';
                error = true;
            }
        }
    }
    else if (latitudeValue < -90 || latitudeValue > 90) {
        // Display error message
        latitudeInput.classList.add('is-invalid');
        var errorFeedback = document.getElementById('latitude-feedback');
        errorFeedback.innerText = 'Latitude must be between -90 and 90.';
        error = true;
    }

    if (error == false) {
        latitudeInput.classList.remove('is-invalid');
    }
  
});

// Validation of longitude field during input
jQuery(document).find('#longitude').on("input", (event) => {
    console.log("longitude input");
    let error = false;
    const longitudeInput = document.getElementById('longitude');
    const longitudeValue = parseFloat(longitudeInput.value);
    
    // if not a float -> maybe DMS style
    if (longitudeInput.value != '' && !isNumber(longitudeInput.value)) {
        const regLon = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(E|W)");
        if (regLon.test(longitudeInput.value) === false) {
            // Display error message
            longitudeInput.classList.add('is-invalid');
            var errorFeedback = document.getElementById('longitude-feedback');
            errorFeedback.innerText = 'Veuillez saisir une longitude valide.';
            error = true;
        } else {
            var lon = convertDMSToDD(longitudeInput.value);
            console.log('lon', lon);
            if (lon < -180 || lon > 180) {
                // Display error message
                longitudeInput.classList.add('is-invalid');
                var errorFeedback = document.getElementById('longitude-feedback');
                errorFeedback.innerText = 'Longitude must be between -180 and 180.';
                error = true;
            }
        }
    }
    else if (longitudeValue < -180 || longitudeValue > 180) {
      // Display error message
      longitudeInput.classList.add('is-invalid');
      var errorFeedback = document.getElementById('longitude-feedback');
      errorFeedback.innerText = 'Longitude must be between -180 and +180.';
      error = true;
    }

    if (error == false) {
        longitudeInput.classList.remove('is-invalid');
    }
  
});

// When clicked on Single Download select button
jQuery(document).find('#single-upload').on('click', function(event){
    console.log("single-upload IN");
    event.preventDefault();
    let error = false;
    const longitudeInput = document.getElementById('longitude');
    const longitudeValue = parseFloat(longitudeInput.value);
    
    if (isNaN(longitudeValue)) {
        // Display error message
        longitudeInput.classList.add('is-invalid');
        var errorFeedback = document.getElementById('longitude-feedback');
        errorFeedback.innerText = 'Longitude is required.';
        error = true;
    }
    else if (longitudeValue < -180 || longitudeValue > 180) {
      // Display error message
      longitudeInput.classList.add('is-invalid');
      var errorFeedback = document.getElementById('longitude-feedback');
      errorFeedback.innerText = 'Longitude must between -180 and +180.';
      error = true;
    }

    const latitudeInput = document.getElementById('latitude');
    const latitudeValue = parseFloat(latitudeInput.value);
    
    if (isNaN(latitudeValue)) {
        // Display error message
        latitudeInput.classList.add('is-invalid');
        var errorFeedback = document.getElementById('latitude-feedback');
        errorFeedback.innerText = 'Latitude is required.';
        error = true;
    }
    else if (latitudeValue < -90 || latitudeValue > 90) {
      // Display error message
      latitudeInput.classList.add('is-invalid');
      var errorFeedback = document.getElementById('latitude-feedback');
      errorFeedback.innerText = 'Latitude must between -90 and +90.';
      error = true;
    }
 
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;
    if (files.length < 1) {
        error = true;
    }

    if (error == true) {
        event.preventDefault();
        event.stopPropagation();
        return;
    }

    const progressContainer = document.getElementById('progressContainer');

    progressContainer.innerHTML = '';
    progressContainer.style.display = 'block';

    
    file = files[0];
    console.log("uploadPhoto file=", file);
    const progressBarContainer = document.createElement('div');
    progressBarContainer.className = 'progress-bar-container';
    const fileName = document.createElement('span');
    fileName.textContent = file.name;
    const progressBar = document.createElement('div');
    progressBar.className = 'progress-bar';
    const progress = document.createElement('div');
    progress.className = 'progress';

    progressBarContainer.appendChild(fileName);
    progressBarContainer.appendChild(progressBar);
    progressBar.appendChild(progress);
    progressContainer.appendChild(progressBarContainer);

    let admin_url = document.getElementById('pg_admin_ajax_url').value;
    let nonce = document.getElementById('download_nonce').value;
    //console.log("uploadPhotos admin_url=", admin_url);

    const formData = new FormData();
    formData.append('action', 'download_single_photo');
    formData.append('nonce', nonce);
    formData.append('lat', latitudeValue);
    formData.append('lon', longitudeValue);
    formData.append('is_exif', false);
    formData.append('file', file);
    jQuery.ajax({
        method: 'POST',
        url: admin_url,
        data: formData,
        contentType: false,
        processData: false,
        success: function(response){
            console.log("upload done");
            const button = document.getElementById('single-upload');
            button.disabled = true;
        }
        // TODO handle error
    });
});

/*
function downloadASinglePhoto(files) {

    console.log('downloadASinglePhoto IN');

    // clean everything from previous photo
    const button = document.getElementById('single-upload');
    button.disabled = false;
    const latitudeInput = document.getElementById('latitude');
    latitudeInput.value = '';
    latitudeInput.classList.remove('is-invalid');

    const longitudeInput = document.getElementById('longitude');
    longitudeInput.value = '';
    longitudeInput.classList.remove('is-invalid');

    const title = document.getElementById('title-latlon');
    title.innerHTML = "Saisir les données GPS"

    document.getElementById("latitudeHelp").style.display = "block";
    document.getElementById("longitudeHelp").style.display = "block";

     g_filesArray = Array.from(files);
    if (g_filesArray.length == 1) {
        const file = g_filesArray[0];
        const reader = new FileReader();

        console.log('g_filesArray.length', g_filesArray.length);

        function renderItemSingle(src, name, latitude, longitude) {
            const photo = document.getElementById('photo-to-download');
            //photo.className = 'list-item';
            photo.innerHTML = `<img src="${src}" alt="Item Image" style="height:200px; width:auto; border: 1px solid #BBB; padding:3px; border-radius: 4px; margin: 10px 0 10px 0">`;

            const gmapInput = document.getElementById('gmap-position');

            // display the form for latitude and longitude
            const downloadBlock = document.getElementById('download-single-block');
            downloadBlock.style.display='block';

            if (latitude !== undefined && longitude != undefined) {
                const latitudeInput = document.getElementById('latitude');
                latitudeInput.value = latitude;
                latitudeInput.classList.remove('is-invalid');

                const longitudeInput = document.getElementById('longitude');
                longitudeInput.value = longitude;
                longitudeInput.classList.remove('is-invalid');

                // hide gmap area
                gmapInput.style.display='none';
            }
            else {
                // show gmap area
                gmapInput.style.display='block';
            }
            
        }

        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'thumbnail';
            //thumbnailContainer.appendChild(img);

            const inputGooglePosition = document.getElementById('input-google-position');
            console.log( "onload ", inputGooglePosition);

            // process Gmap input
            inputGooglePosition.addEventListener("input", (event) => {
                let position = inputGooglePosition.value;
                console.log( "handleInputGooglePosition", position);
                // Example 51°20'20.1"N 18°42'08.8"E
                // "([-|\\+]?\\d{1,3}[d|D|\u00B0|\\s](\\s*\\d{1,2}['|\u2019|\\s])?(\\s*\\d{1,2}[\"|\u201d|\\s])?\\s*([N|n|S|s|E|e|W|w])?\\s?)"
                const sp = position.split(" ");
                if (sp.length == 2) {
                    const regLat = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(N|S)");
                    const regLon = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(E|W)");
                    console.log('sp[0]', sp[0]);
                    console.log('sp[1]', sp[1]);
                    console.log(regLat.test(sp[0]))
                    console.log(regLon.test(sp[1]))
                    if (regLat.test(sp[0]) === true && regLon.test(sp[1]) === true) {
                        var lat = convertDMSToDD(sp[0]);
                        var lon = convertDMSToDD(sp[1]);
                        console.log('lat, lon', {lat,lon});
                        if (lat != NaN && lon != NaN) {
                            const latitudeInput = document.getElementById('latitude');
                            const longitudeInput = document.getElementById('longitude');
                            latitudeInput.value = lat;
                            longitudeInput.value = lon;
                            latitudeInput.classList.remove('is-invalid');
                            longitudeInput.classList.remove('is-invalid');
                        }
                    }
                }
            });
    
            // Extract EXIF data
            EXIF.getData(file, function() {
                console.log( "file: ", file);
                const exifData = EXIF.getAllTags(this);
                const lat = EXIF.getTag(this, 'GPSLatitude');
                const lon = EXIF.getTag(this, 'GPSLongitude');

                if (lat == undefined || lon == undefined) {
                    renderItemSingle(event.target.result, file.name);
                }
                else {
                    console.log('EXIF Data:', exifData);

                    const altitude = EXIF.getTag(this, 'GPSAltitude');
            
                    if (lat && lon) {
                        const latRef = EXIF.getTag(this, 'GPSLatitudeRef') || 'N';
                        const lonRef = EXIF.getTag(this, 'GPSLongitudeRef') || 'E';
            
                        const latitude = convertDMSToDDExif(lat[0], lat[1], lat[2], latRef);
                        const longitude = convertDMSToDDExif(lon[0], lon[1], lon[2], lonRef);

                        const date = EXIF.getTag(this, 'DateTimeOriginal');

                        renderItemSingle(event.target.result, file.name, latitude, longitude);

                        // change title
                        const title = document.getElementById('title-latlon');
                        title.innerHTML = "La position GPS a été extraite de la photo :";

                        // hide help
                        document.getElementById("latitudeHelp").style.display = "none";
                        document.getElementById("longitudeHelp").style.display = "none";

                        file.pgpg = {};
                        file.pgpg.lat = latitude;
                        file.pgpg.lon = longitude;
                        file.pgpg.altitude = altitude; // atltide to calculate with denominator
                        file.pgpg.is_exif = true;
                        //TODO calculate and fill zoom value
                        file.pgpg.zoom = 1;
                        file.pgpg.date = date;
            
                        // const info = document.createElement('div');
                        // info.textContent = `Latitude: ${latitude}, Longitude: ${longitude}`;
                    }
                }
            });
        };
    
        reader.readAsDataURL(file);
    };
}
*/
//
// MULTIPLE UPLOAD
//

jQuery(document).find('#close-multiple-modal').on('click', function(e){
    console.log("show-modal IN");
    e.preventDefault();
    location.reload();
});

// When clicking on "Download"
jQuery(document).find('#multiple-upload').on('click', function(e){
    console.log("uploadPhotos IN", e);
    e.preventDefault();

    const fileInput = document.getElementById('fileInput');
    console.log("uploadPhotos fileInput", fileInput);
    //const files = fileInput.files;
    //console.log("uploadPhotos files", files);
    // const progressContainer = document.getElementById('progressContainer');
    const galleryId = document.getElementById('gallery-id')?.value;
    // progressContainer.innerHTML = '';
    // progressContainer.style.display = 'block';
    console.log("uploadPhotos g_filesArray.length=", g_filesArray.length);
    //const files = Array.from(fileInput.files)
    for (let i = 0 ; i < g_filesArray.length ; i ++) {
    //Array.from(files).forEach(file => {
        const file = g_filesArray[i];
        console.log("uploadPhotos file=", file);
        //const progressBarContainer = document.createElement('div');

        // find the div element associated to the file name
        let file_div = find_div_from_file_name(file.name);
        if (file_div) {

            // check if file is valid (geoloc ok, data-valid="ok")
            if (!file_div.dataset.valid) {
                console.log("uploadPhotos ignore file=", file);
                continue;
            }
            //console.log("uploadPhotos file_div=", file_div);
            let spinner = file_div.parentNode.getElementsByClassName('download-spinner');
            //console.log("uploadPhotos spinner=", spinner);
            spinner[0].style.display='block';

            //file_div.classList.add( 'opaque');
            file_div.style.opacity=0.4;
        }
    
        let admin_url = document.getElementById('pg_admin_ajax_url').value;
        let nonce = document.getElementById('download_nonce').value;
        //console.log("uploadPhotos admin_url=", admin_url);
    
        const formData = new FormData();
        formData.append('action', 'download_multiple_photos');
        formData.append('nonce', nonce);
        formData.append('title', 'my Title');
        formData.append('lat', file.pgpg.lat);
        formData.append('lon', file.pgpg.lon);
        formData.append('is_exif', file.pgpg.is_exif);
        formData.append('altitude', file.pgpg.altitude);
        formData.append('address', file.pgpg.address);
        formData.append('address_json', file.pgpg.address_json);
        formData.append('country_code', file.pgpg.country_code);
        formData.append('date', file.pgpg.date);
        formData.append('file', file);
        formData.append('galleryId', galleryId);
        console.log("uploadPhotos gps=", file.pgpg);
        jQuery.ajax({
            method: 'POST',
            url: admin_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                console.log("success", response);
                console.log("file_div", file_div);
                const button = document.getElementById('multiple-upload');
                let spinner = file_div.parentNode.getElementsByClassName('download-spinner');
                spinner[0].style.display='none';
                let check = file_div.parentNode.getElementsByClassName('download-success');
                check[0].style.display='block';
                button.disabled = true;
            },
            error: function(response) {
                console.log("error", response);
                const button = document.getElementById('multiple-upload');
                let spinner = file_div.parentNode.getElementsByClassName('download-spinner');
                spinner[0].style.display='none';
                let check = file_div.parentNode.getElementsByClassName('download-error');
                check[0].style.display='block';
                button.disabled = true;
            }
        });
    };
});

function find_div_from_file_name(filename) {
    parent = document.getElementById("modal-item-list");
    if (parent) {
        children = parent.children;
        for (let i = 0; i < children.length; i++) {
            let texts = children[i].getElementsByClassName( "full-photo-text-container");
            if (texts.length > 0) {
                //console.log("find_div_from_file_name", texts[0].innerHTML);
                if (texts[0].innerHTML.indexOf(filename) != -1) {
                    console.log("find_div_from_file_name out", texts[0].parentNode);
                    // get the flex container
                    //let cont = children[i].
                    return texts[0].parentNode;
                }
            }
        }
    }
    console.log("find_div_from_file_name NOT found", filename);
    return null;
}

// Function to remove the list item
function removeDownloadPhoto(item) {
    //item.remove(); // Remove the corresponding list item
    //console.log("removeDownloadPhoto item", item);
    let ancestor = item.parentNode.parentNode.parentNode;
    let title = ancestor.getElementsByClassName('download-photo-title')[0].innerHTML;
    //console.log("removeDownloadPhoto title=", title);
    // remove the assocaited file in g_filesArray
    for (let i = 0 ; i < g_filesArray.length ; i ++) {
        //Array.from(files).forEach(file => {
        //const file = g_filesArray[i];
        //console.log("removeDownloadPhoto file.name=", g_filesArray[i].name);
        if (g_filesArray[i].name == title) {
            //console.log("removeDownloadPhoto FOUND");
            g_filesArray.splice(i, 1);
            break;
        }
    }
    console.log('removeDownloadPhoto IN');
    
    
    ancestor.style.animationDuration = '.35s';
    ancestor.style.animationName = 'slideOutLeft';

    setTimeout(() => {
        ancestor.remove(); // Remove the corresponding list item after animation
    }, 300); // Duration of the animation    

    // remove from g_filesArray

}     


function updateDownloadMultipleModal(files) {
    console.log('updateDownloadMultipleModal IN');
    
    const newFiles= Array.from(files);

    const list = document.getElementById('modal-item-list');
    const button = document.getElementById('multiple-upload');
    const spinner = document.getElementById('selection-spinner');
    console.log('updateDownloadMultipleModal button.disabled', button.disabled);
    console.log('updateDownloadMultipleModal spinner', spinner);
    const fileInput = document.getElementById("fileInput");
    console.log('updateDownloadMultipleModal current files', fileInput.files);
    
    // delete previous list
    if (button.disabled == true) {
        // the previous list has been uploaded, remove it
        list.innerHTML = "";
        g_filesArray = null;
    }
    spinner.style.display = "block";
    button.disabled = false;

    if (newFiles.length > 0) {
        button.style.display = "block";
    }
    else {
        button.style.display = "none";
        
    }
    console.log('html-list.length', list.childElementCount);
    console.log('newFiles.length', newFiles.length);

    function renderItemMultiple_gps(src, name, date) {
        console.log('renderItemMultiple_gps IN', name);
        const list = document.getElementById('modal-item-list');
        const listItem = document.createElement('div');
        listItem.className = 'full-item';

        listItem.innerHTML = `
        <div style="position: relative;">
            <div class="spinner-border text-primary download-spinner" style="display:none;"></div>
            <div class="download-success"><i class="fas fa-check" style="color: green;"></i></div>
            <div class="download-error"><i class="fas fa-times" style="color: red;"></i></div>
            <div class="flex-container" style="margin-top:0px" data-valid="ok">
                <img src="${src}" class="full-miniature" style="max-width: 135px;"></img>
                <div class="full-photo-text-container" style="flex: 10 10 150px;">
                    <div class="download-photo-title footer-desc-font">${name}</div>
                    <div class="download-photo-text footer-desc-font">Localisation OK<br/>${date}</div>
                </div>
                <div class="flex-options-3" style="background-color: lightblue">
                    <div data-id="'.$id.'">
                        <div class="download-photo-option pointer-icon fas fa-trash" aria-hidden="true" onclick='removeDownloadPhoto(this.parentNode.parentNode)'></div>
                    </div>
                </div>
            </div>
        </div>
        </div>`;

        //list.appendChild(spinner);
        list.appendChild(listItem);
        console.log('renderItemMultiple_gps OUT');
    }

    function renderItemMultiple_error(src, name, error) {
        console.log('renderItemMultiple_error IN', name);
        const list = document.getElementById('modal-item-list');
        const listItem = document.createElement('div');
        listItem.className = 'full-item';
            // Localisation not found
        listItem.innerHTML = `
        <div style="position: relative;">
            <div class="download-error"><i class="fas fa-times" style="color: red;"></i></div>
            <div class="flex-container" style="margin-top:0px">
                <img src="${src}" class="full-miniature"></img>
                    <div class="full-photo-text-container" style="flex: 10 0 200px;">
                        <div class="download-photo-title footer-desc-font">Fichier : ${name}</div>
                        <div class="download-photo-text footer-desc-font" style="color:red;">
                            ${error}
                        </div>
                    </div>
                    <div class="flex-options-3" style="background-color: lightblue">
                        <div data-id="'.$id.'">
                            <div class="download-photo-option pointer-icon fas fa-trash" aria-hidden="true" onclick='removeDownloadPhoto(this.parentNode.parentNode)'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        //list.appendChild(spinner);
        list.appendChild(listItem);
        console.log('renderItemMultiple_error OUT');
    }

    for (let i = 0; i < newFiles.length ; i ++) {
        const file = newFiles[i];
        const reader = new FileReader();
        
        if ( list.childElementCount == maxFile) {
            fileInput.disabled = true;
            break;
        }
  
        reader.onload = function(event) {
            console.log( "onload IN");
        
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'thumbnail';
            //thumbnailContainer.appendChild(img);
    
            // Extract EXIF data
            EXIF.getData(file, async function() {

                //console.log( "EXIF.getData IN file", file);
                const exifData = EXIF.getAllTags(this);
                console.log('EXIF Data:', exifData);

                const gpsInfo = await EXIF.getTag(this, 'GPSInfoIFDPointer');
                if (gpsInfo) {
                    const lat = await EXIF.getTag(this, 'GPSLatitude');
                    const lon = await EXIF.getTag(this, 'GPSLongitude');
                    const altitude = await EXIF.getTag(this, 'GPSAltitude');
            
                    if (lat && lon) {
                        const latRef = await EXIF.getTag(this, 'GPSLatitudeRef') || 'N';
                        const lonRef = await EXIF.getTag(this, 'GPSLongitudeRef') || 'E';
            
                        const latitude = convertDMSToDDExif(lat[0], lat[1], lat[2], latRef);
                        const longitude = convertDMSToDDExif(lon[0], lon[1], lon[2], lonRef);

                        const date = await EXIF.getTag(this, 'DateTimeOriginal');
                        let zoomRatio = '';
                        let zoomExif = await EXIF.getTag(this, 'DigitalZoomRation');
                        if (zoomExif) {
                            zoomRatio = zoomExif.toString();
                        }

                        let geocod = await reverseGeocoding(latitude, longitude);

                        if (g_filesArray == null) {
                            // first files 
                            g_filesArray = [];
                        }
                        if (g_filesArray.length < maxFile) {
                            
                            //console.log( "onload geocod", geocod);
                            renderItemMultiple_gps(event.target.result, file.name, date);

                            file.pgpg = {};
                            file.pgpg.lat = latitude;
                            file.pgpg.lon = longitude;
                            file.pgpg.altitude = altitude; // atltide to calculate with denominator
                            file.pgpg.is_exif = true;
                            //TODO calculate and fill zoom value
                            file.pgpg.zoom = zoomRatio;
                            file.pgpg.date = date;
                            if (geocod) {
                                file.pgpg.address = geocod.address;
                                file.pgpg.address_json = geocod.address_json;
                                file.pgpg.country_code = geocod.country_code;
                            }
                        
                            console.log('updateDownloadMultipleModal onload push file', file);
                            g_filesArray.push(file);
                        }
                    }
                    else {
                        // render without lat,lon -> error because no EXIF data
                        renderItemMultiple_error(event.target.result, file.name, 
                            "Coordonnées GPS absentes.<br/>Consultez les conseils.");
                    }
    
                }
                else {
                    // render without lat,lon -> error because no EXIF data
                    renderItemMultiple_error(event.target.result, file.name, 
                        "Coordonnées GPS absentes.<br/>Consultez les conseils.");
                }
            });
            const spinner = document.getElementById('selection-spinner');
            spinner.style.display = "none";
        };
    
        reader.readAsDataURL(file);
    };
    console.log('updateDownloadMultipleModal OUT');
}

// Display thumbnails after selecting files
// Display thumbnails after selecting files and extract EXIF data
document.getElementById('fileInput').addEventListener('change', function(event) {
    //console.log('event.target', event.target);
    //const files = event.target.files;

    if (event.target.multiple == true) {
        // multiple downloads
        updateDownloadMultipleModal(event.target.files);
    }
    else {
        // single download
        downloadASinglePhoto(event.target.files);
    }
});
  
// Function to convert degrees, minutes, and seconds to decimal degrees
// Convert string dsm to decimal
function convertDMSToDD(dms) {
    let parts = dms.split(/[^\d+(\,\d+)\d+(\.\d+)?\w]+/);
    let degrees = parseFloat(parts[0]);
    let minutes = parseFloat(parts[1]);
    let seconds = parseFloat(parts[2].replace(',','.'));
    let direction = parts[3];

    // console.log('degrees: '+degrees)
    // console.log('minutes: '+minutes)
    // console.log('seconds: '+seconds)
    // console.log('direction: '+direction)

    let dd = degrees + minutes / 60 + seconds / (60 * 60);

    if (direction == 'S' || direction == 'W') {
      dd = dd * -1;
    } // Don't do anything for N or E
    return dd;
}
// Convert degrees, minute, second, direction to decimal
function convertDMSToDDExif(degrees, minutes, seconds, direction) {
    //console.log('convertDMSToDDExif', direction);
    let dd = degrees + minutes / 60 + seconds / (60 * 60);
    if (direction === 'S' || direction === 'W') {
        dd = -dd;
    }
    return dd;
}

// TODO improve, accept comma also, not dot only
function isNumber(st) {
    // console.log('isNumber IN -------------', st);
    if (typeof(st) != "string") {
        console.log('isNumber error shall be a string');
        return false;
    }
    // console.log('isNumber !isNaN(str)', !isNaN(st));
    // console.log('isNumber !isNaN(parseFloat(str))', !isNaN(parseFloat(st)));
    // could also coerce to string: str = ""+str
    const resu = !isNaN(st) && !isNaN(parseFloat(st));
    // console.log('isNumber', resu);
    return resu;
}

async function reverseGeocoding(lat, lon) {
    try {
        const geocoder = new google.maps.Geocoder();
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`;
        //console.log('reverseGeocoding url=', url);

        // Url for the request
        let address_json='';
        let address='';
        let country_code = '';

        const latlng = {
            lat: lat,
            lng: lon,
        };
        console.log('before geocoder');
        await geocoder
            .geocode({ location: latlng })
            .then((response) => {
                if (response.results[0]) {
                    console.log('reverseGeocoding BINGO', response.results);

                    //infowindow.setContent(response.results[0].formatted_address);
                } else {
                    window.alert("No results found");
                }
        })
        .catch((e) => window.alert("Geocoder failed due to: " + e));
        //console.log('reverseGeocoding out=', {country_code, address});
        return {country_code, address, address_json};
    }
    catch (e){
        console.log('Exception in reverseGeocoding');
    }
}

async function OsmReverseGeocoding(lat, lon) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`;
    //console.log('reverseGeocoding url=', url);

    // Url for the request
    let address_json='';
    let address='';
    let country_code = '';
    
    // Making our request 
    await fetch(url, { method: 'GET' })
        .then(Result => Result.json())
        .then(json => {

            console.log('reverseGeocoding json=', json);
            // Printing our response 
            if (json.address) {
                //console.log('reverseGeocoding BINGO');
                country_code = json.address.country_code
                address = json.display_name;
                address_json = JSON.stringify(json.address);
            }
            // Printing our field of our response
            //console.log(`Title of our response : ${string.title}`);
        })
        .catch(errorMsg => { console.log(errorMsg); });
    //console.log('reverseGeocoding out=', {country_code, address});
    return {country_code, address, address_json};
}