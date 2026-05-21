let html5QrCode;
let isMirrored = false;

function initScanner() {
    html5QrCode = new Html5Qrcode("reader");
    startScanner();
}

function startScanner() {
    const config = { fps: 10, qrbox: { width: 250, height: 250 }, disableFlip: isMirrored };
    
    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
    .then(() => {
        document.getElementById('stopBtn').style.display = 'inline-block';
    })
    .catch((err) => {
        console.error("Error starting scanner:", err);
        document.getElementById('scanResult').style.display = 'block';
        document.getElementById('scanResult').className = 'alert alert-danger';
        document.getElementById('scanResult').innerText = "Camera access denied or device not found.";
    });
}

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('stopBtn').style.display = 'none';
        }).catch(err => console.error("Error stopping scanner.", err));
    }
}

function toggleMirror() {
    isMirrored = !isMirrored;
    stopScanner();
    setTimeout(startScanner, 500); // Restart with new config
}

function onScanSuccess(decodedText, decodedResult) {
    // Assuming decodedText contains just the Appointment_ID or a JSON string.
    // For this blueprint, we assume the QR code stores just the raw integer Appointment_ID.
    const appId = parseInt(decodedText);
    
    if(isNaN(appId)) {
        document.getElementById('scanResult').style.display = 'block';
        document.getElementById('scanResult').className = 'alert alert-warning';
        document.getElementById('scanResult').innerText = "Invalid QR Code format.";
        return;
    }

    // Temporarily pause scanning
    html5QrCode.pause();
    
    document.getElementById('scanResult').style.display = 'block';
    document.getElementById('scanResult').className = 'alert alert-info';
    document.getElementById('scanResult').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Check-In...';

    fetch('qr_checkin_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appId })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById('scanResult').className = 'alert alert-success fw-bold text-center border-2 border-success shadow-lg';
            document.getElementById('scanResult').innerHTML = `<h4 class="alert-heading mb-2"><i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>Success!</h4><p class="mb-0 fs-5">${data.message}</p>`;
            // Play a success beep if possible
        } else {
            document.getElementById('scanResult').className = 'alert alert-danger fw-bold text-center border-2 border-danger shadow-lg';
            document.getElementById('scanResult').innerHTML = `<h4 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i><br>Error</h4><p class="mb-0 fs-5">${data.message}</p>`;
        }
        
        // Resume scanning after 3 seconds
        setTimeout(() => {
            document.getElementById('scanResult').style.display = 'none';
            html5QrCode.resume();
        }, 3000);
    })
    .catch(err => {
        console.error("AJAX Error:", err);
        html5QrCode.resume();
    });
}

document.addEventListener("DOMContentLoaded", initScanner);