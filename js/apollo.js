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
                audioChunks = []; // Initialize or clear existing chunks
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' }); // Adjust MIME type as needed
                mediaRecorder.ondataavailable = event => audioChunks.push(event.data);
                mediaRecorder.onstop = handleRecordingStop; // Correctly place inside startRecording
                mediaRecorder.start();
                console.log('Recording started');
            })
            .catch(error => console.error('Error accessing the microphone:', error));
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop(); // Stop the media recorder
            console.log('Stop recording called');

            // Assuming mediaRecorder was created with a stream
            // Stop each track on the stream
            mediaRecorder.stream.getTracks().forEach(track => track.stop());

            if (hasBeenRecorded) {
                // Toggle form visibility using CSS classes
                formContainer.classList.remove('hidden');
                formContainer.classList.add('visible');
            }
        }
    }

    function handleRecordingStop() {
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        const audioUrl = URL.createObjectURL(audioBlob);
        const fileName = `recording_${new Date().toISOString()}.webm`; // Generate a file name
        const file = new File([audioBlob], fileName, { type: 'audio/webm' });

        attachFileToInput(file);
        createDownloadLink(audioUrl, fileName); // Adjust to create a download hyperlink
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
