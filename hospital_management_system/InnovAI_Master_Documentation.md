# InnovAI Master Documentation

## 1. Executive Summary & Project Identity

**Project Name:** InnovAI Medical Center
**University Context:** Faculty of Computers and Information Technology – Innovation University, Egypt
**Core Objective:** InnovAI Medical Center is a fully functional, next-generation Hospital Management System (HMS) built on a foundation of artificial intelligence and automated workflows. The platform is designed to eliminate operational friction through five distinct, role-based user portals, enabling seamless patient communication, real-time clinical tracking, and autonomous AI-driven triage.

## 2. Technology Stack & External Integrations

The system architecture leverages a robust, lightweight stack ensuring high performance and rapid deployment across clinical environments.

*   **Backend & Server:** PHP running on a standard XAMPP environment, backed by a MySQL/MariaDB relational database.
*   **Frontend UI/UX:** HTML5, CSS3, JavaScript, and Bootstrap 5. The interface utilizes premium, tailored color palettes indicating role separation:
    *   **Admin:** Dark Charcoal/Indigo/Gold (Command & Control)
    *   **Receptionist:** Sky Blue/White (Productivity & Clarity)
    *   **Doctor:** Clinical Blue/White (Professionalism)
    *   **Nurse:** Mint Green/Soft Gray (Attentive Care)
    *   **Patient:** Lavender/Teal (Wellness & Calm)
*   **AI Engine Integration:** Powered by the Groq API utilizing the `llama-3.1-8b-instant` and `llama-3.3-70b-versatile` models. The integration employs a strict, zero-fault Chain-of-Thought (CoT) implementation via cURL, enforcing structured JSON outputs for symptom triage, risk scoring, and predictive clinical mapping.
*   **WhatsApp Gateway Microservice:** A custom-built, headless Node.js API bridge. Utilizing `express`, `puppeteer`, and `whatsapp-web.js`, it enables automated, background asynchronous appointment confirmations and notifications without the limitations of official API restrictions.
*   **Core Libraries:**
    *   **FPDF:** For generating dynamic, secure PDF documents including Patient Arrival Slips, Billing Invoices, and AI-Generated Health Passports.
    *   **HTML5-QRCode:** Deployed within the Receptionist portal for instantaneous, browser-based camera scanning of patient QR codes.

## 3. Database Architecture & "Constitutional Rules"

The system enforces strict data integrity and relational constraints, treating its core schema documents as absolute law.

*   **The Source of Truth:** All development and feature implementation strictly adhere to the defined constraints within `Project_ERD.pdf`, `Project_Schema.pdf`, and the executed `phpMyAdmin_(Hospital.sql).sql` file. No structural deviations exist without explicit architectural waivers.
*   **Naming Quirks & Constraints:** The database employs specific, asymmetric naming conventions to reflect varying levels of required detail:
    *   `Admin` and `Receptionist` tables utilize a single `Full_Name` column.
    *   `Doctor`, `Nurse`, and `Patient` tables separate data into `Fname` and `Lname`. Across all UI views, standalone last names are forbidden; logic ensures either the First Name or Full Name is always rendered.
*   **Multi-Valued Attributes:** Phone numbers are strictly normalized. The `Patient` and `Doctor` tables do not contain direct phone columns. All contact data is managed relationally via the `Patient_phones` and `Doctor_phones` multi-valued tables.
*   **Database Triggers (Age Calculation):** The `Age` column within the `Patient` table is a physical, derived attribute. It is dynamically calculated and synchronized via MySQL `BEFORE INSERT` and `BEFORE UPDATE` triggers based on the `Date_of_Birth`, ensuring data integrity without application-layer interference.
*   **Status ENUMs:** The `Appointment` table utilizes a carefully managed `Status` ENUM (`Scheduled`, `Checked-In`, `Ready`, `Completed`, `Cancelled`, `Pending`) driving the logic across all operational portals.

## 4. Comprehensive Portal Breakdown

### Admin Portal
The command center for hospital operations.
*   **Interactive Analytics:** Dashboard features clickable metric cards (Total Patients, Revenue, etc.) routing directly to detailed management views.
*   **Department Load-Balancer:** Visualizations (via Chart.js) mapping patient distribution and revenue trends across clinical departments.
*   **Financial Pulse:** Advanced financial tracking with dynamic, sortable tables and date-range filtered PDF revenue exports.
*   **CRUD Management:** Comprehensive creation, updating, and deletion capabilities for Staff (Doctors, Nurses, Receptionists), Departments, and Patients, adhering to strict foreign-key cascade rules.

### Receptionist Portal
The front-line operational hub handling patient flow.
*   **Edge Device Check-in:** Features an integrated HTML5-QRCode scanner with a mirror-toggle, allowing receptionists to scan patient arrival slips for instantaneous `Checked-In` status updates.
*   **Dynamic Appointment Booking:** A unified interface featuring real-time AJAX patient searching. If a patient is not found, an inline registration form seamlessly captures all ERD-mandated data (City, Blood Group, Emergency Contact, etc.) and books the appointment in a single transaction.
*   **Dashboard Triage:** Multi-tab appointment management tracking `Today`, `Upcoming`, and `Past` encounters.
*   **Automated Communication:** Booking an appointment silently triggers the Node.js WhatsApp microservice, instantly messaging the patient their QR arrival slip.
*   **Billing & PDFs:** Dynamic bill generation and professional PDF slip printing for patient routing.

### Doctor Portal
The clinical workspace optimized for urgent care delivery.
*   **AI-Driven Priority Queue:** The dashboard autonomously sorts incoming patients based on their AI-calculated `Risk_Score` and `Ready` status, utilizing flashing "URGENT" CSS badges for critical cases.
*   **Comprehensive Medical History:** Doctors can view a patient's complete timeline, including past diagnoses, treatments, and prescriptions, with the ability to export the full medical history to PDF.
*   **AI Clinical Assistant:** During consultations, doctors can trigger the AI Helper. The system asynchronously sends the patient's history and current clinical exam notes to the Groq API, dynamically returning and populating a structured, multi-medication prescription form.

### Nurse Portal
The floor management and patient preparation view.
*   **Live Floor Heatmap:** A visual, interactive grid representing all hospital rooms. Rooms are color-coded by status (Green = Available, Red = Occupied, Yellow = Maintenance) and display current patient assignments. Nurses can dynamically filter the view by status.
*   **Active Patient Queue:** Tracks `Checked-In` patients. Nurses can easily append vital signs or medication administration notes directly to the patient's `Medical_History` record before passing the patient to the Doctor.

### Patient Portal
The secure, self-service wellness interface.
*   **Editable Profile:** Patients can manage their personal data, contact information, and address fields.
*   **AI Symptom Checker:** A highly advanced triage tool. Patients describe their symptoms in natural language. The Groq AI parses the text, confirms it is a valid medical inquiry, assigns a 1-10 `Risk_Score`, and predicts the required specialization based *only* on the hospital's dynamically queried active departments.
*   **Health Passport:** Generates a professional PDF summarizing the patient's entire medical history, synthesized by a secondary Llama-3-70b model into a concise executive summary.
*   **Self-Booking:** Patients can schedule, review, and cancel their own `Pending` appointments.

## 5. Core System Innovations

*   **Omnipresent Dynamic Live Search:** Every table and data list across the system features a real-time, JavaScript-driven search bar (`onkeyup`). This allows users to filter vast amounts of data instantly without executing new database queries or refreshing the page.
*   **Real-Time UI Elements:** The system utilizes asynchronous JavaScript to provide live, ticking clocks and auto-refreshing UI components (like the Nurse Heatmap), ensuring staff operate on synchronized, up-to-the-second data.

## 6. Security & Authentication

*   **Universal Login System:** A centralized `login.php` gateway authenticates users. Upon success, it reads the `User_Login.Role` ENUM and securely routes the session to the corresponding portal (Admin, Doctor, Nurse, Receptionist, Patient).
*   **Cryptographic Security:** All passwords, regardless of role, are securely salted and hashed utilizing PHP's native `password_hash()` (bcrypt) algorithms before database insertion.