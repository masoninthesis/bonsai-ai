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

    uploadAudioFile(audioFile);
}

function uploadAudioFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    // Additional code for uploading goes here
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

    fetch('/wp-json/wp/v2/media', {
        method: 'POST',
        body: formData,
        headers: {
            'Authorization': 'Basic ' + btoa('admin:Mn6q ZgLL rPDq 6cfL yEpv HGjc')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}
