
//
// SINGLE UPLOAD
//

// check laitude input
jQuery(document).find('#latitude').on("input", (event) => {
    console.log("latitude input");
    const latitudeInput = document.getElementById('latitude');
    const latitudeValue = parseFloat(latitudeInput.value);
    console.log("latitude input", latitudeInput.value);
    error = false;
    
    if (latitudeInput.value != '' && isNaN(latitudeValue)) {

        const regLat = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(N|S)");
        if (regLat.test(latitudeInput.value) === false) {
            // Display error message
            latitudeInput.classList.add('is-invalid');
            var errorFeedback = document.getElementById('latitude-feedback');
            errorFeedback.innerText = 'Veuillez saisir une latitude valide.';
            error = true;
        } else {
            var partlat = sp[latitudeInput.value].split(/[^\d\w]+/);
            var lat = convertDMSToDD(parseInt(partlat[0]), parseInt(partlat[1]), parseInt(partlat[2]), partlat[3]);
            console.log('lat', lat);
            if (lat < -90 || lat > 90) {
                // Display error message
                latitudeInput.classList.add('is-invalid');
                var errorFeedback = document.getElementById('latitude-feedback');
                errorFeedback.innerText = 'Latitude must between -90 and 90.';
                error = true;
            }
        }
    }
    else if (latitudeValue < -90 || latitudeValue > 90) {
        // Display error message
        latitudeInput.classList.add('is-invalid');
        var errorFeedback = document.getElementById('latitude-feedback');
        errorFeedback.innerText = 'Latitude must between -90 and 90.';
        error = true;
    }

    if (error == false) {
        latitudeInput.classList.remove('is-invalid');
    }
  
});


jQuery(document).find('#longitude').on("input", (event) => {
    console.log("latitude input");
    let error = false;
    const longitudeInput = document.getElementById('longitude');
    const longitudeValue = parseFloat(longitudeInput.value);
    
    if (longitudeInput.value != '' && isNaN(longitudeValue)) {
        const regLon = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(E|W)");
        if (regLon.test(longitudeInput.value) === false) {
            // Display error message
            longitudeInput.classList.add('is-invalid');
            var errorFeedback = document.getElementById('longitude-feedback');
            errorFeedback.innerText = 'Veuillez saisir une longitude valide.';
            error = true;
        } else {
            var partlon = sp[longitudeInput.value].split(/[^\d\w]+/);
            var lon = convertDMSToDD(parseInt(partlon[0]), parseInt(partlon[1]), parseInt(partlon[2]), partlon[3]);
            console.log('lon', lon);
            if (lon < -180 || lon > 180) {
                // Display error message
                latitudeInput.classList.add('is-invalid');
                var errorFeedback = document.getElementById('longitude-feedback');
                errorFeedback.innerText = 'Longitude must between -180 and 180.';
                error = true;
            }
        }
    }
    else if (longitudeValue < -180 || longitudeValue > 180) {
      // Display error message
      longitudeInput.classList.add('is-invalid');
      var errorFeedback = document.getElementById('longitude-feedback');
      errorFeedback.innerText = 'Longitude must between -180 and +180.';
      error = true;
    }

    if (error == false) {
        longitudeInput.classList.remove('is-invalid');
    }
  
});

// When clicked on Single Download button
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
 
    if (error == true) {
        event.preventDefault();
        event.stopPropagation();
        return;
    }


    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;
    const progressContainer = document.getElementById('progressContainer');

    progressContainer.innerHTML = '';
    progressContainer.style.display = 'block';

    Array.from(files).forEach(file => {
        console.log("uploadPhotos file=", file);
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
        let nonce = document.getElementById('pg_nonce').value;
        //console.log("uploadPhotos admin_url=", admin_url);
        //const xhr = new XMLHttpRequest();
        // admin_url = admin_url;// + "?action=download_multiple_photos";
    
        const formData = new FormData();
        formData.append('action', 'download_multiple_photos');
        formData.append('nonce', nonce);
        formData.append('title', 'my Title');
        formData.append('lat', file.pgpg.lat);
        formData.append('lon', file.pgpg.lon);
        formData.append('origin', file.pgpg.origin);
        formData.append('date', file.pgpg.date);
        formData.append('file', file);
        console.log("uploadPhotos gps=", file.pgpg);
        jQuery.ajax({
            method: 'POST',
            url: admin_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                console.log("titi");
            }
        });
    });
});

//
// MULTIPLE UPLOAD
//

jQuery(document).find('#multiple-upload').on('click', function(e){
    console.log("uploadPhotos IN");
    e.preventDefault();

    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;
    const progressContainer = document.getElementById('progressContainer');

    progressContainer.innerHTML = '';
    progressContainer.style.display = 'block';

    Array.from(files).forEach(file => {
        console.log("uploadPhotos file=", file);
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
        let nonce = document.getElementById('pg_nonce').value;
        //console.log("uploadPhotos admin_url=", admin_url);
        //const xhr = new XMLHttpRequest();
        // admin_url = admin_url;// + "?action=download_multiple_photos";
    
        const formData = new FormData();
        formData.append('action', 'download_multiple_photos');
        formData.append('nonce', nonce);
        formData.append('title', 'my Title');
        formData.append('lat', file.pgpg.lat);
        formData.append('lon', file.pgpg.lon);
        formData.append('origin', file.pgpg.origin);
        formData.append('date', file.pgpg.date);
        formData.append('file', file);
        console.log("uploadPhotos gps=", file.pgpg);
        jQuery.ajax({
            method: 'POST',
            url: admin_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                console.log("titi");
            }
        });
    });
});

// Function to remove the list item
function removeListItem(item) {
    //item.remove(); // Remove the corresponding list item
    item.style.animationDuration = '.35s';
    item.style.animationName = 'slideOutLeft';

    setTimeout(() => {
        item.remove(); // Remove the corresponding list item after animation
    }, 300); // Duration of the animation    
}     

const maxFile=5;

function downloadMultiplePhotos(files) {

    const filesArray = Array.from(files);

    for (let i = 0; i < filesArray.length ; i ++) {
        const file = filesArray[i];
        const reader = new FileReader();

        const list = document.getElementById('item-list');
        console.log('list.length', list.childElementCount);
        console.log('filesArray.length', filesArray.length);
        if ( list.childElementCount == maxFile) {
            const fileInput = document.getElementById("fileInput");
            fileInput.disabled = true;
            break;
        }
  
        function renderItem(src, lat, lon, date, name) {
            const list = document.getElementById('item-list');
            const listItem = document.createElement('div');
            listItem.className = 'list-item row';
            listItem.innerHTML = `
                <div class="col">
                    <img src="${src}" alt="Item Image" class="square-thumbnail">
                </div>
                <div class="col">${name}</div>
                <div class="col hidden-xs">lat=${lat}<br>lon=${lon}<br>date=${date}</div>
                <div class="col trash-icon fas fa-trash"  aria-hidden='true' onclick='removeListItem(this.parentNode)'></div>`;
            list.appendChild(listItem);
        }

        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'thumbnail';
            //thumbnailContainer.appendChild(img);
    
            // Extract EXIF data
            EXIF.getData(file, function() {
                console.log( "file: ", file);
                const exifData = EXIF.getAllTags(this);
                console.log('EXIF Data:', exifData);

                const lat = EXIF.getTag(this, 'GPSLatitude');
                const lon = EXIF.getTag(this, 'GPSLongitude');
                const altitude = EXIF.getTag(this, 'GPSAltitude');
        
                if (lat && lon) {
                    const latRef = EXIF.getTag(this, 'GPSLatitudeRef') || 'N';
                    const lonRef = EXIF.getTag(this, 'GPSLongitudeRef') || 'E';
        
                    const latitude = convertDMSToDD(lat[0], lat[1], lat[2], latRef);
                    const longitude = convertDMSToDD(lon[0], lon[1], lon[2], lonRef);

                    const date = EXIF.getTag(this, 'DateTimeOriginal');

                    renderItem(event.target.result, latitude, longitude, date, file.name)

                    file.pgpg = {};
                    file.pgpg.lat = latitude;
                    file.pgpg.lon = longitude;
                    file.pgpg.altitude = altitude; // atltide to calculate with denominator
                    file.pgpg.origin = "exif";
                    //TODO calculate and fill zoom value
                    file.pgpg.zoom = 1;
                    file.pgpg.date = date;
        
                    const info = document.createElement('div');
                    info.textContent = `Latitude: ${latitude}, Longitude: ${longitude}`;
                    //thumbnailContainer.appendChild(info);
                }
            });
        };
    
        reader.readAsDataURL(file);
    };
}

function downloadASinglePhoto(files) {

    console.log('downloadASinglePhoto IN');

    const filesArray = Array.from(files);
    if (filesArray.length == 1) {
        const file = filesArray[0];
        const reader = new FileReader();

        const downloadBlock = document.getElementById('download-single-block');
        downloadBlock.style.display='block';
        

        console.log('filesArray.length', filesArray.length);

        function renderItem(src, name) {
            const photo = document.getElementById('photo-to-download');
            //photo.className = 'list-item';
            photo.innerHTML = `<img src="${src}" alt="Item Image" class="square-thumbnail">`;
        }

        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'thumbnail';
            //thumbnailContainer.appendChild(img);

            const inputGooglePosition = document.getElementById('input-google-position');
            console.log( "onload ", inputGooglePosition);
            inputGooglePosition.addEventListener("input", (event) => {
                let position = inputGooglePosition.value;
                console.log( "handleInputGooglePosition", position);
                // Example 51°20'20.1"N 18°42'08.8"E
                // "([-|\\+]?\\d{1,3}[d|D|\u00B0|\\s](\\s*\\d{1,2}['|\u2019|\\s])?(\\s*\\d{1,2}[\"|\u201d|\\s])?\\s*([N|n|S|s|E|e|W|w])?\\s?)"
                const sp = position.split(" ");
                if (sp.length == 2) {
                    const regLat = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(N|S)");
                    const regLon = new RegExp("(\\d+)\\s?°\\s?(\\d+)\\s?'\\s?(\\d+\\.?\\,?\\d*?)\\\"\\s?(E|W)");
                    // console.log('sp[0]', sp[0]);
                    // console.log('sp[1]', sp[1]);
                    // console.log(regLat.test(sp[0]))
                    // console.log(regLon.test(sp[1]))
                    if (regLat.test(sp[0]) === true && regLon.test(sp[1]) === true) {
                        var partlat = sp[0].split(/[^\d\w]+/);
                        var partlon = sp[1].split(/[^\d\w]+/);
                        var lat = convertDMSToDD(parseInt(partlat[0]), parseInt(partlat[1]), parseInt(partlat[2]), partlat[3]);
                        var lon = convertDMSToDD(parseInt(partlon[0]), parseInt(partlon[1]), parseInt(partlon[2]), partlon[3]);
                        console.log('lat, lon', {lat,lon});
                        if (lat != NaN && lon != NaN) {
                            const latitude = document.getElementById('latitude');
                            const longitude = document.getElementById('longitude');
                            latitude.value = lat;
                            longitude.value = lon;
                        }
                    }
                }
            });
    
            // Extract EXIF data
            EXIF.getData(file, function() {
                console.log( "file: ", file);
                const exifData = EXIF.getAllTags(this);
                console.log('EXIF Data:', exifData);

                const lat = EXIF.getTag(this, 'GPSLatitude');
                const lon = EXIF.getTag(this, 'GPSLongitude');
                const altitude = EXIF.getTag(this, 'GPSAltitude');
        
                if (lat && lon) {
                    const latRef = EXIF.getTag(this, 'GPSLatitudeRef') || 'N';
                    const lonRef = EXIF.getTag(this, 'GPSLongitudeRef') || 'E';
        
                    const latitude = convertDMSToDD(lat[0], lat[1], lat[2], latRef);
                    const longitude = convertDMSToDD(lon[0], lon[1], lon[2], lonRef);

                    const date = EXIF.getTag(this, 'DateTimeOriginal');

                    renderItem(event.target.result, file.name)

                    file.pgpg = {};
                    file.pgpg.lat = latitude;
                    file.pgpg.lon = longitude;
                    file.pgpg.altitude = altitude; // atltide to calculate with denominator
                    file.pgpg.origin = "exif";
                    //TODO calculate and fill zoom value
                    file.pgpg.zoom = 1;
                    file.pgpg.date = date;
        
                    const info = document.createElement('div');
                    info.textContent = `Latitude: ${latitude}, Longitude: ${longitude}`;
                    //thumbnailContainer.appendChild(info);
                }
            });
        };
    
        reader.readAsDataURL(file);
    };
}
// Display thumbnails after selecting files
// Display thumbnails after selecting files and extract EXIF data
document.getElementById('fileInput').addEventListener('change', function(event) {
    console.log('event.target', event.target);
    const files = event.target.files;

    if (event.target.multiple == true) {
        // multiple downloads
        downloadMultiplePhotos(event.target.files);
    }
    else {
        // single download
        downloadASinglePhoto(event.target.files);
    }


});
  
// Function to convert degrees, minutes, and seconds to decimal degrees
function convertDMSToDD(degrees, minutes, seconds, direction) {
    let dd = degrees + minutes / 60 + seconds / (60 * 60);
    if (direction === 'S' || direction === 'W') {
        dd = -dd;
    }
    return dd;
}

