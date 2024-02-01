console.log('apollo.js loaded');

// Record Button Click and Animation
document.addEventListener('DOMContentLoaded', function() {
    var recordButton = document.getElementById('recordButton');
    var formContainer = document.getElementById('gravityFormContainer');
    var hasBeenRecorded = false;

    if (recordButton) {
        recordButton.addEventListener('click', function() {
            if (recordButton.textContent === 'Start Recording') {
                recordButton.textContent = 'Stop Recording';
                recordButton.classList.add('recording'); // Add the class to start blinking
                startRecording();
                hasBeenRecorded = true;
            } else if (hasBeenRecorded) {
                recordButton.textContent = 'Start Recording';
                recordButton.classList.remove('recording'); // Remove the class to stop blinking
                stopRecording();
                formContainer.style.display = 'block';
            }
        });
    }
});

// Recording audio
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

// Note filtering
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('keyup', function() {
        var searchTerm = searchInput.value.toLowerCase();
        var notes = document.querySelectorAll('.note-item');

        notes.forEach(function(note) {
            var title = note.getAttribute('data-title');
            if (title.indexOf(searchTerm) > -1) {
                note.style.display = '';
            } else {
                note.style.display = 'none';
            }
        });
    });
});



// This section should stop a user from exiting without saving their recording
// // Save Recording Before Leaving Page Reminder
// let isFormChanged = false;
//
// // Function to mark the form as changed
// function markFormChanged() {
//     isFormChanged = true;
// }
// // Add event listeners to each input field in your form
// document.querySelectorAll('#gform_4 input, #gform_4 textarea, #gform_4 select').forEach(input => {
//     input.addEventListener('change', markFormChanged);
// });
//
// // Listen for the beforeunload event
// window.addEventListener('beforeunload', function (e) {
//     if (isFormChanged) {
//         // Customize this message as needed
//         var confirmationMessage = 'It looks like you have been editing something. If you leave before saving, your changes will be lost.';
//         (e || window.event).returnValue = confirmationMessage; // Gecko and Trident
//         return confirmationMessage; // Gecko and WebKit
//     }
// });
