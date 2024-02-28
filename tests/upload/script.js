function uploadPhotos() {
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;
    const progressContainer = document.getElementById('progressContainer');
  
    progressContainer.innerHTML = '';
    progressContainer.style.display = 'block';
  
    Array.from(files).forEach(file => {
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
  
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'your-upload-endpoint', true);
  
      xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
          const percentUploaded = (e.loaded / e.total) * 100;
          progress.style.width = percentUploaded + '%';
        }
      };
  
      xhr.onload = function() {
        if (xhr.status === 200) {
          fileName.textContent = file.name + ' - Uploaded';
        } else {
          fileName.textContent = file.name + ' - Failed';
        }
      };
  
      const formData = new FormData();
      formData.append('file', file);
      xhr.send(formData);
    });
  }
  
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
  