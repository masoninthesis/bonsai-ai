console.log('apollo.js loaded');

// Record Button Click and Animation
document.addEventListener('DOMContentLoaded', function() {
    var recordButton = document.getElementById('recordButton');
    var formContainer = document.getElementById('gravityFormContainer');
    var hasBeenRecorded = false;

    // Ensure mediaRecorder is accessible in the broader scope
    var mediaRecorder;
    var audioChunks = [];

    if (recordButton && formContainer) {
        recordButton.addEventListener('click', function() {
            if (recordButton.textContent === 'Start Recording') {
                recordButton.textContent = 'Stop Recording';
                recordButton.classList.add('recording');
                startRecording();
            } else {
                recordButton.textContent = 'Start Recording';
                recordButton.classList.remove('recording');
                stopRecording();
                hasBeenRecorded = true;
                // Toggle form visibility using CSS classes
                formContainer.classList.remove('hidden');
                formContainer.classList.add('visible');
            }
        });
    }

    function startRecording() {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                try {
                    mediaRecorder = new MediaRecorder(stream);

                    mediaRecorder.ondataavailable = handleDataAvailable;
                    mediaRecorder.onstop = handleRecordingStop;

                    mediaRecorder.start();
                    console.log('Recording started without specifying MIME type');
                } catch (error) {
                    console.error('Error initializing MediaRecorder:', error);
                }
            })
            .catch(error => {
                console.error('Error accessing the microphone:', error);
            });
    }


    function handleDataAvailable(event) {
        if (event.data.size > 0) {
            audioChunks.push(event.data);
        }
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop(); // Stop the media recorder
            console.log('Stop recording called');

            // Stop each track on the stream to release the microphone
            mediaRecorder.stream.getTracks().forEach(track => track.stop());

            // This flag 'hasBeenRecorded' might need to be set true here instead,
            // if its purpose is to track whether recording has occurred at all for session
            hasBeenRecorded = true;

            // Show the form container after stopping the recording
            // This adjustment ensures the form is displayed after the first recording session completes
            formContainer.classList.remove('hidden');
            formContainer.classList.add('visible');
        } else {
            console.log('MediaRecorder not in recording state or undefined');
        }
    }


    function handleRecordingStop() {
        // Use the MIME type from mediaRecorder or a default
        const mimeType = mediaRecorder.mimeType || 'audio/mp4';
        const audioBlob = new Blob(audioChunks, { type: mimeType });
        const audioUrl = URL.createObjectURL(audioBlob);
        const fileName = `recording_${new Date().toISOString()}.${mimeType.split('/')[1]}`; // Dynamic extension based on MIME
        const file = new File([audioBlob], fileName, { type: mimeType });

        attachFileToInput(file);
        createDownloadLink(audioUrl, fileName);
    }

    function attachFileToInput(file) {
        const fileInput = document.getElementById('input_4_3');
        if (fileInput) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            console.log('File successfully attached to input');
        } else {
            console.error('File input not found');
            // Fallback or additional handling as needed
        }
    }

    function createDownloadLink(audioUrl, fileName) {
        let downloadLink = document.getElementById('downloadLink');
        if (!downloadLink) {
            downloadLink = document.createElement('a');
            downloadLink.id = 'downloadLink';
            downloadLink.href = audioUrl;
            downloadLink.download = fileName; // Use the dynamically generated file name
            // Set inner HTML to include "Download" text with icon and styling
            downloadLink.innerHTML = '<small class="text-secondary pl-3"><i class="fas fa-download mr-2"></i> Download Recording</small>';
            document.getElementById('recordButton').insertAdjacentElement('afterend', downloadLink);
        } else {
            // Update the link if it already exists
            downloadLink.href = audioUrl;
            downloadLink.download = fileName; // Update the filename as well
        }

        // Ensure the link is always visible after recording stops
        downloadLink.style.display = 'inline'; // Adjust as necessary for your layout
    }

});

// Safari not yet supported
document.addEventListener('DOMContentLoaded', function() {
    var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

    if (isSafari) {
        var warningContainer = document.getElementById('safari-warning-container');
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning';
        alertDiv.textContent = 'If using Safari, download the note after recording stops, and select Choose File to attach it– or use a different browser to remove this step';
        warningContainer.appendChild(alertDiv);
    }
});

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
