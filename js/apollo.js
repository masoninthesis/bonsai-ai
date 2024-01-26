console.log('apollo.js loaded');

// Record Button Click
document.addEventListener('DOMContentLoaded', function() {
    var recordButton = document.getElementById('recordButton');

    if (recordButton) {
        recordButton.addEventListener('click', function() {
            if (recordButton.textContent === 'Start Recording') {
                recordButton.textContent = 'Stop Recording';
                startRecording();
            } else {
                recordButton.textContent = 'Start Recording';
                stopRecording();
            }
        });
    }
});

var mediaRecorder;
var audioChunks = [];
var audioStream;

function startRecording() {
    console.log('Recording started');

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            audioStream = stream;
            mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
            mediaRecorder.ondataavailable = event => audioChunks.push(event.data);
            mediaRecorder.onstop = handleRecordingStop;
            mediaRecorder.start();
        })
        .catch(error => {
            console.error('Error accessing the microphone', error);
            alert('Microphone access denied. Please allow microphone access to record audio.');
            var recordButton = document.getElementById('recordButton');
            if (recordButton) {
                recordButton.textContent = 'Start Recording';
            }
        });
}

function handleRecordingStop() {
    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
    const audioFile = new File([audioBlob], "recording.webm", { type: 'audio/webm' });

    // Assuming your Gravity Form file input has an ID like 'input_4_3'
    const fileInput = document.getElementById('input_4_3');
    if (fileInput) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(audioFile);
        fileInput.files = dataTransfer.files;
    }
}

function displayAudioFile(file, url) {
    var recordButton = document.getElementById('recordButton');

    // Create an audio element
    const audioElement = document.createElement('audio');
    audioElement.controls = true;
    audioElement.src = url;

    // Create a download link
    const downloadLink = document.createElement('a');
    downloadLink.href = url;
    downloadLink.download = file.name;
    downloadLink.textContent = `Download ${file.name}`;
    downloadLink.style.marginRight = '10px'; // Add some space between the buttons

    // Create a save button
    const saveButton = document.createElement('button');
    saveButton.textContent = 'Save to Library';
    saveButton.onclick = function() {
        uploadAudioFile(file);
    };

    // Append the elements
    recordButton.insertAdjacentElement('afterend', saveButton);
    recordButton.insertAdjacentElement('afterend', downloadLink);
    recordButton.insertAdjacentElement('afterend', audioElement);
}

function stopRecording() {
    console.log('Recording stopped');
    mediaRecorder.stop();
    audioStream.getTracks().forEach(track => track.stop());
}

// Upload the File Using WP-API
function uploadAudioFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    const username = 'admin'; // Your WordPress username
    const appPassword = 'Mn6q ZgLL rPDq 6cfL yEpv HGjc'.replace(/\s/g, ''); // Your application password with spaces removed

    fetch('/wp-json/wp/v2/media', {
        method: 'POST',
        body: formData,
        headers: {
          'Authorization': 'Basic ' + btoa(username + ':' + appPassword),
          'Content-Disposition': 'attachment; filename=recording.webm'
        }
    })

    .then(response => {
      if (!response.ok) {
          throw new Error('Network response was not ok ' + response.statusText);
      }
      return response.json();
    })

    .then(data => {
        console.log('Success:', data);
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}
