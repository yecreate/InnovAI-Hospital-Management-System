function searchPatients() {
    const input = document.getElementById('patientSearchInput').value;
    const resultsContainer = document.getElementById('searchResults');
    
    if(input.length < 2) {
        resultsContainer.innerHTML = '<div class="text-danger p-2">Please enter at least 2 characters.</div>';
        return;
    }
    
    resultsContainer.innerHTML = '<div class="text-info p-2"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
    
    fetch(`search_patients_ajax.php?q=${encodeURIComponent(input)}`)
        .then(response => response.json())
        .then(data => {
            resultsContainer.innerHTML = '';
            if(data.length === 0) {
                resultsContainer.innerHTML = '<div class="list-group-item text-muted">No matches found.</div>';
                return;
            }
            data.forEach(patient => {
                const a = document.createElement('a');
                a.href = "javascript:void(0)";
                a.className = "list-group-item list-group-item-action flex-column align-items-start";
                a.onclick = () => selectPatient(patient.Patient_ID, patient.Fname + ' ' + patient.Lname);
                a.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${patient.Fname} ${patient.Lname}</h6>
                        <small class="text-muted">ID: ${patient.Patient_ID}</small>
                    </div>
                    <p class="mb-1 text-muted small"><i class="fas fa-phone me-1"></i> ${patient.PhoneNumber || 'N/A'} | <i class="fas fa-envelope me-1"></i> ${patient.Email}</p>
                `;
                resultsContainer.appendChild(a);
            });
        })
        .catch(err => {
            resultsContainer.innerHTML = '<div class="text-danger p-2">Error during search.</div>';
        });
}

function selectPatient(id, name) {
    document.getElementById('selectedPatientId').value = id;
    document.getElementById('selectedPatientName').innerText = name;
    document.getElementById('selectedPatientAlert').style.display = 'block';
    
    // Hide new patient form if it was open
    document.getElementById('newPatientForm').style.display = 'none';
    removeNewPatientRequired();
    
    // Enable submit
    document.getElementById('submitBtn').disabled = false;
}

function toggleNewPatientForm() {
    const form = document.getElementById('newPatientForm');
    const isHidden = form.style.display === 'none';
    
    if(isHidden) {
        form.style.display = 'block';
        document.getElementById('selectedPatientId').value = '';
        document.getElementById('selectedPatientAlert').style.display = 'none';
        
        // Add required fields
        setNewPatientRequired(true);
        document.getElementById('submitBtn').disabled = false;
    } else {
        form.style.display = 'none';
        setNewPatientRequired(false);
        document.getElementById('submitBtn').disabled = true;
    }
}

function setNewPatientRequired(isRequired) {
    document.getElementById('new_fname').required = isRequired;
    document.getElementById('new_lname').required = isRequired;
    document.getElementById('new_email').required = isRequired;
    document.getElementById('new_dob').required = isRequired;
    document.getElementById('new_gender').required = isRequired;
}

function removeNewPatientRequired() {
    setNewPatientRequired(false);
}