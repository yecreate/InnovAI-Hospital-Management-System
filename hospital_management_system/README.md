# HMS V4 (Strict QR Integrated)

## Senior Project Architect Release
This repository contains the complete, production-ready codebase for the AI-integrated Hospital Management System, strictly built according to the `Project_ERD.pdf`, `Project_Schema.pdf`, and the Constitutional Document.

## Features
- **5 Secure Portals:** Admin, Receptionist, Doctor, Nurse, and Patient.
- **AI Triage Integration:** Real-time symptom analysis via Groq API.
- **Edge Device Scanner:** HTML5-based QR Code scanner for arrival tracking.
- **Dynamic PDF Generation:** Bills and Health Passports via FPDF.
- **Strict ERD Compliance:** All data models, multi-valued attributes, and junction tables are perfectly mapped.

## Setup Instructions

1. **Database:**
   - Import `HMS_DB_Foundation_1.sql` into phpMyAdmin.
   - Import `HMS_DB_Functional_Mock_Data_Script.sql` to populate mock data.

2. **API Configuration:**
   - Open `includes/constants.php`.
   - Replace the `GROQ_API_KEY` placeholder with your actual Groq API key.

3. **External Libraries:**
   - **FPDF:** Place the FPDF library inside `includes/fpdf/fpdf.php`.
   - **PHPQRCode:** Place the PHPQRCode library inside `includes/phpqrcode/qrlib.php`.
   - *(Note: HTML5-QRCode and Chart.js are loaded dynamically via CDN).*

4. **Web Server:**
   - Place this entire folder into `C:\xampp\htdocs\hospital_management_system`.
   - Start Apache and MySQL from the XAMPP Control Panel.
   - Navigate to `http://localhost/hospital_management_system/`.

## Login Credentials (Mock Data)
- **Admin:** ahmed.ramadan.24030005@iu.edu.eg / Password123
- **Doctor:** youssef.ahmed.24030126@iu.edu.eg / Password123
- **Receptionist:** marwan.mohammed.24030006@iu.edu.eg / Password123
- **Nurse:** nurse1@hms.com / Password123
- **Patient:** patient1@hms.com / Password123
