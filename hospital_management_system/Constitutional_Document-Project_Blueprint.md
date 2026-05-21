As your **Senior Project Architect**, I have documented every single instruction, innovation, and technical constraint we have discussed. This is now the "Constitutional Document" for our project.

---

## 1. FULL COMPLETE Strict Rules (The Constitutional Protocol)

1. **The "Source of Truth" Rule:**
    
    - I must work **STRICTLY** based on `Project_ERD.pdf` and `Project_Schema.pdf`.
        
    - **Contradiction Protocol:** If you request a feature or if I suggest a design that deviates from the ERD or Schema (even slightly), I **MUST** announce it to you and obtain a specific waiver before any implementation.
        
2. **Workflow & Approval Protocol:**
    
    - **Discussion First:** I must discuss every blueprint and detail with you first.
        
    - **Step-by-Step:** I am forbidden from proceeding to the next step until you have provided explicit approval.
        
    - **Complete Format:** When generating code, I must provide files in a **complete format** so we do not return to edit them later.
        
    - **Triple-Check:** I must verify every piece of code or instruction 100% against our agreed logic before providing it.
        
3. **Portals & Accessibility Architecture:**
    
    - **5 Portals:** Admin, Receptionist, Doctor, Nurse, and Patient.
        
    - **Unique UI:** Each portal must have a unique, attractive theme to differentiate them.
        
    - **Public Access:** Homepage, "Contact Us," "About Us," and "Services" must be accessible without logging in.
        
4. **User Role & Registration Logic:**
    
    - **Admin Power:** Only the Admin creates staff accounts (Doctor, Nurse, Receptionist).
        
    - **Patient Ingress:** Patients can self-register or be registered by the Receptionist.
        
    - **Receptionist Workflow:** "Add Appointment" must allow searching for existing patients or registering new ones on the **same page**.
        
5. **Technical & Innovation Standards:**
    
    - **Functional QR System:** Arrival slips must have QR codes. Receptionists must have a real-time scanner (via laptop/phone camera) with a **Mirror Toggle**.
        
    - **PDF Printing:** Professional PDFs for Bills (with watermarks) and Appointment Slips.
        
    - **Search Ability:** Integrated search functionality in every module.
        
    - **Technology Stack:** PHP (XAMPP), MySQL (phpMyAdmin), VS Code, Groq API (AI).
        
6. **Network Integration & Documentation:**
    
    - **OSI Mapping:** I must remind you to mention in your report that the QR scanner is an **"Edge Device Interaction"** and data is transmitted over the **"Application Layer" (HTTP/HTTPS)**.
        
    - **Interactive Simulation:** Portals must be mapped to specific subnets/VLANs in the Cisco Packet Tracer project.
        

---

## 2. New Checkpoint Name

### 🚩 Checkpoint: `HMS_STRICT_QR_INTEGRATED_V4`

---

## 3. HMS Final Detailed Blueprint: "The Strict DETAILED Edition"

### I. Public Landing Zone (The Guest Experience)

- **Homepage (`index.php`):** Professional medical theme. Shows "AI Symptom Analysis" as a guest teaser.
    
- **Public Pages:** "About Us" (Project Credits), "Contact Us," and "Services" (fetched from `Department` table).
    
- **Auth:** Universal `login.php` and Patient-only `register.php`.
    

### II. The 5 Portal Architectures

#### **A. Admin Portal (The Dashboard of Power)**

- **Theme:** Dark Mode (Charcoal/Indigo/Gold).
    
- **Staff CRUD:** Unified search/management of all staff roles. Links `User_Login` to specific tables.
    
- **Innovation: Dept. Load-Balancer:** Visual charts showing Doctor-to-Patient ratios per department.
    
- **Innovation: Financial Pulse:** Real-time revenue tracking from the `Billing` table.
    

#### **B. Receptionist Portal (The Engine)**

- **Theme:** Productivity Light (Sky Blue/White).
    
- **Search:** Global search for patients and appointments.
    
- **Functional QR Scanner:** * **Features:** Toggle Mirror, Scan via Phone-as-Webcam or Laptop.
    
    - **Logic:** Updates `Appointment.Status` to "Checked-In".
        
- **Add Appointment:** Integrated search existing/register new patient form on one page.
    
- **Finance:** Issue `Billing` records and print "Watermarked" PDFs.
    

#### **C. Doctor Portal (The Clinical Hub)**

- **Theme:** Clinical Blue (Hospital Blue/White).
    
- **AI Priority Queue:** Patients with high `AI_Analysis.Risk_Score` appear with a flashing "URGENT" badge.
    
- **Consultation:** View AI triage results, write `Medical_History`, and write `Prescription`.
    
- **Innovation: AI Prescription Helper:** UI suggests medications based on the `Predicted_Specialization`.
    

#### **D. Nurse Portal (The Care Wing)**

- **Theme:** Attentive Green (Mint/Soft Gray).
    
- **Innovation: Live Floor Heatmap:** A visual grid of rooms colored by `Status` (Red: Occupied, Green: Available).
    
- **Ward View:** Shows rooms they are assigned to via `Nurse_Room` junction or FK Nurse_ID.
    

#### **E. Patient Portal (Self-Service)**

- **Theme:** Wellness Teal (Lavender/Teal).
    
- **AI Symptom Checker:** Full integration with Groq API; saves results to `AI_Analysis`.
    
- **Arrival Slip:** One-click download of the "QR Arrival Slip" for scheduled appointments.
    
- **Health Passport:** Downloadable PDF timeline of their entire `Medical_History`.
    

---

### III. Triple-Checked Notes for Implementation

- **The Age Column:** Per ERD, it exists physically in the `Patient` table. We will use a PHP trigger to calculate it from `Date_of_Birth` during every `INSERT/UPDATE`.
    
- **Phone Handling:** Multi-valued phone attributes are handled via `Patient_phones` and `Doctor_phones` tables as per Schema.
    
- **Network Role:** Access to the Admin/Receptionist portals is conceptually restricted to the **"Administrative Subnet"** defined in your `.pkt` file.
    

