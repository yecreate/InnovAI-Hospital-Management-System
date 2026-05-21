sequenceDiagram
    autonumber
    actor Patient
    participant UI as Patient Portal<br/>(ai_symptom_checker.php)
    participant PHP as Backend Engine<br/>(ai_engine.php)
    participant Groq as Groq API<br/>(llama-3.3-70b-versatile)
    participant DB as MySQL Database<br/>(AI_Analysis & Depts)
    participant WA_Ajax as WA Handler<br/>(send_whatsapp_ajax.php)
    participant Node as Node.js Gateway<br/>(Port 3000)
    participant WhatsApp as Patient's Device

    Patient->>UI: Submits Symptoms & Clicks Book
    UI->>PHP: POST $_POST['symptoms']
    PHP->>DB: Query Active Departments
    DB-->>PHP: Returns $available_depts Array
    PHP->>Groq: cURL POST (Symptoms + Dynamic Depts)
    Groq-->>PHP: Strict JSON (Risk, Spec, Confidence)
    PHP->>DB: INSERT INTO AI_Analysis (Persistence)
    DB-->>PHP: Confirm Save
    PHP-->>UI: Display AI Triage Result
    
    Patient->>UI: Request Health Passport PDF
    UI->>WA_Ajax: AJAX POST (app_id, type)
    WA_Ajax->>WA_Ajax: FPDF Generates Local Document
    WA_Ajax->>Node: POST http://localhost:3000/send-msg<br/>(payload: number, fileUrl)
    Node->>WhatsApp: WebSockets / Baileys Push
    WhatsApp-->>Patient: PDF Successfully Delivered