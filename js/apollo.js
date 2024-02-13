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
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            hasBeenRecorded = true;
            formContainer.classList.remove('hidden');
            formContainer.classList.add('visible');
        }
    }

    function handleRecordingStop() {
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        const fileName = `recording_${new Date().toISOString().split('.')[0].replace(/:/g, '-')}.webm`;
        const file = new File([audioBlob], fileName, { type: 'audio/webm' });
        const audioUrl = URL.createObjectURL(audioBlob);
        attachFileToInput(file);
        createDownloadLink(audioUrl, fileName);
    }

    function attachFileToInput(file) {
        const fileInput = document.getElementById('input_4_3');
        if (fileInput) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
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
});

// JavaScript to Intercept GForm4 Submission
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form#gform_4');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission intercepted.');
            var audioFileInput = document.getElementById('input_4_3');
            var file = audioFileInput.files[0];

            if (file) {
                uploadAndTranscribeAudio(file, this);
            } else {
                console.error('No audio file found.');
                // If no file, you might decide to submit the form or handle differently
            }
        });
    }
});

// JavaScript Function to Upload and Transcribe Audio
function uploadAndTranscribeAudio(file, form) {
    var formData = new FormData();
    formData.append('audio_file', file);

    fetch(bonsaiAiParams.transcriptionHandlerUrl, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.transcription) {
            var transcriptionField = document.getElementById('input_4_5');
            transcriptionField.value = data.transcription;
            form.submit(); // Directly submit the form without removing listeners
        } else {
            console.error('Transcription failed:', data.error);
            // Handle failure: Show an error message or log it
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
