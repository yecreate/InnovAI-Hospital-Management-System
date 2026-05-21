<?php
require_once 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold" style="color: var(--primary-blue-dark);"><i class="fas fa-qrcode me-2 text-primary"></i> QR Fast-Track Check-In</h2>
        <p class="text-muted fs-6 mb-0">Scan digital arrival slips to instantly update patient status and notify clinical staff.</p>
    </div>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-lg border-0 overflow-hidden" style="border-radius: 20px;">
            <div class="card-header text-white text-center py-3 border-0" style="background-color: var(--primary-blue-dark);">
                <h5 class="fw-bold mb-0"><i class="fas fa-camera-retro me-2"></i> Optical Scanner Feed</h5>
            </div>
            <div class="card-body text-center bg-light p-md-5">
                
                <div class="position-relative mx-auto" style="width: 100%; max-width: 450px;">
                    <!-- Decorative scanning frame -->
                    <div class="position-absolute top-0 start-0 border-top border-start border-4 border-primary" style="width: 40px; height: 40px; border-radius: 12px 0 0 0; z-index: 10;"></div>
                    <div class="position-absolute top-0 end-0 border-top border-end border-4 border-primary" style="width: 40px; height: 40px; border-radius: 0 12px 0 0; z-index: 10;"></div>
                    <div class="position-absolute bottom-0 start-0 border-bottom border-start border-4 border-primary" style="width: 40px; height: 40px; border-radius: 0 0 0 12px; z-index: 10;"></div>
                    <div class="position-absolute bottom-0 end-0 border-bottom border-end border-4 border-primary" style="width: 40px; height: 40px; border-radius: 0 0 12px 0; z-index: 10;"></div>
                    
                    <div id="reader" class="rounded-4 shadow-sm bg-white overflow-hidden p-1 w-100 h-100"></div>
                </div>
                
                <div class="mt-5 mb-2 d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm" id="toggleMirrorBtn" onclick="toggleMirror()"><i class="fas fa-exchange-alt me-2"></i> Flip Camera Axis</button>
                    <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" id="stopBtn" onclick="stopScanner()" style="display:none;"><i class="fas fa-power-off me-2"></i> Terminate Feed</button>
                </div>
                
                <div id="scanResult" class="alert mt-4 mx-auto border-0 shadow-sm" style="display:none; max-width: 500px; font-size: 1.1rem; font-weight: 600; border-radius: 12px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="../assets/js/qr_scanner.js"></script>

<?php require_once 'footer.php'; ?>