   
 jQuery(document).find('#myupload').on('click', function(e){
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
        formData.append('file', file);
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
        //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        // fetch(admin_url, {
        //     method: 'POST',
        //     body: formData
        // })
        // .then(function(response) {
        //     return response.text();
        // })
        // .then(function(body) {
        //     console.log(body);
        // });
    });
});


// Display thumbnails after selecting files
// Display thumbnails after selecting files and extract EXIF data
document.getElementById('fileInput').addEventListener('change', function(event) {
    const files = event.target.files;
    const thumbnailContainer = document.getElementById('thumbnails');
    thumbnailContainer.innerHTML = '';
  
    Array.from(files).forEach(file => {
      const reader = new FileReader();
  
      reader.onload = function(event) {
        const img = document.createElement('img');
        img.src = event.target.result;
        img.className = 'thumbnail';
        thumbnailContainer.appendChild(img);
  
        // Extract EXIF data
        EXIF.getData(file, function() {
          const lat = EXIF.getTag(this, 'GPSLatitude');
          const lon = EXIF.getTag(this, 'GPSLongitude');
  
          if (lat && lon) {
            const latRef = EXIF.getTag(this, 'GPSLatitudeRef') || 'N';
            const lonRef = EXIF.getTag(this, 'GPSLongitudeRef') || 'E';
  
            const latitude = convertDMSToDD(lat[0], lat[1], lat[2], latRef);
            const longitude = convertDMSToDD(lon[0], lon[1], lon[2], lonRef);
  
            const info = document.createElement('div');
            info.textContent = `Latitude: ${latitude}, Longitude: ${longitude}`;
            thumbnailContainer.appendChild(info);
          }
        });
      };
  
      reader.readAsDataURL(file);
    });
});
  
// Function to convert degrees, minutes, and seconds to decimal degrees
function convertDMSToDD(degrees, minutes, seconds, direction) {
    let dd = degrees + minutes / 60 + seconds / (60 * 60);
    if (direction === 'S' || direction === 'W') {
        dd = -dd;
    }
    return dd;
}
  

/*
function uploadMultiple() {
    console.log("uploadPhotos IN");
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
        const xhr = new XMLHttpRequest();
        admin_url = admin_url + "?action=download_multiple_photos";
        xhr.open('POST', admin_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
        xhr.upload.onprogress = function(e) {
            console.log("uploadPhotos xhr.upload.onprogress", e);
            if (e.lengthComputable) {
                const percentUploaded = (e.loaded / e.total) * 100;
                progress.style.width = percentUploaded + '%';
            }
        };
    
        xhr.onload = function() {
            console.log("uploadPhotos xhr.onload");
            if (xhr.status === 200) {
            fileName.textContent = file.name + ' - Uploaded';
            } else {
            fileName.textContent = file.name + ' - Failed';
            }
        };
    
        xhr.onerror = function() {
            console.log("uploadPhotos xhr.onerror", xhr);
            alert("[XHR] Fatal Error.");
        };

        xhr.onreadystatechange = function() {
            try {
                console.log("uploadPhotos xhr.onreadystatechange", xhr);
                if (xhr.readyState == 4) {
                    alert('[XHR] Done')
                } else if (xhr.readyState > 2) {
                    // var new_response = xhr.responseText.substring(xhr.previous_text.length);
                    // var result = JSON.parse(new_response);

                    // document.getElementById("divProgress").innerHTML += result.message + '<br />';
                    // document.getElementById('progressor').style.width = result.progress + "%";

                    // xhr.previous_text = xhr.responseText;
                }
            } catch (e) {
                alert("[XHR STATECHANGE] Exception: " + e);
            }
        };        
        const formData = new FormData();
        //formData.append('action', 'download_multiple_photos');
        formData.append('nonce', nonce);
        formData.append('title', 'my Title');
        formData.append('file', file);
        //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(formData);
    });
}
*/