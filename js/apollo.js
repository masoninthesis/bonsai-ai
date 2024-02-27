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
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = handleDataAvailable;
                mediaRecorder.onstop = handleRecordingStop;
                mediaRecorder.start();
                console.log('Recording started');
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
            mediaRecorder.stop();
            console.log('Stop recording called');
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            hasBeenRecorded = true;
            formContainer.classList.remove('hidden');
            formContainer.classList.add('visible');
        } else {
            console.log('MediaRecorder not in recording state or undefined');
        }
    }

    function handleRecordingStop() {
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        const fileName = `recording_${new Date().toISOString().split('.')[0].replace(/:/g, '-')}.webm`;
        const file = new File([audioBlob], fileName, { type: 'audio/webm' });

        attachFileToInput(file);
        createDownloadLink(URL.createObjectURL(file), fileName);

        // Attempt to fire the Bootstrap modal specifically here when recording stops.
        console.log('Attempting to show modal...');
        showBootstrapModal('addNoteModal');
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
        }
    }

    function createDownloadLink(audioUrl, fileName) {
        let downloadLink = document.getElementById('downloadLink');
        if (!downloadLink) {
            downloadLink = document.createElement('a');
            downloadLink.id = 'downloadLink';
            downloadLink.href = audioUrl;
            downloadLink.download = fileName;
            downloadLink.innerHTML = '<small class="text-secondary pl-3"><i class="fas fa-download mr-2"></i> Download Recording</small>';
            document.getElementById('recordButton').insertAdjacentElement('afterend', downloadLink);
        } else {
            downloadLink.href = audioUrl;
            downloadLink.download = fileName;
        }
        downloadLink.style.display = 'inline';
    }

    // Show addNoteModal when recording stops, adjusted for Bootstrap 4
    function showBootstrapModal(modalId) {
        console.log(`Showing modal with ID: ${modalId}`);
        jQuery('#' + modalId).modal('show');
    }

});

// Transcribe upon redirect to new note
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOMContentLoaded event fired. Checking for 'transcribe' parameter...");

    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('transcribe') === 'true') {
        console.log("'transcribe=true' found in URL parameters. Looking for the button...");

        var transcribeButton = document.getElementById('transcribeAudio');
        if (transcribeButton) {
            console.log("Transcribe button found. Attempting to click...");

            // Introduce a delay before clicking the button
            setTimeout(function() {
                transcribeButton.click();
                console.log("Button click attempted after delay.");
            }, 500); // Delay in milliseconds (500ms = 0.5 second)
        } else {
            console.log("Transcribe button not found.");
        }
        // Show the loading modal using jQuery in no-conflict mode
        jQuery('#loadingModal').modal('show');
    } else {
        console.log("'transcribe' parameter not set to 'true'. No action taken.");
    }
});

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
