/**
 * Admin Complaint Page JavaScript
 * For: app/views/complaint/admin.php
 */

// Only run if we're on the complaints page
if (document.getElementById('complaintForm')) {
    const baseUrl = '/Lumbangan_BMIS/bmis-lumbangan-system/public';
    
    // Format time to 12-hour format with AM/PM
    function formatTime(timeString) {
        if (!timeString) return 'N/A';
        
        const timeParts = timeString.split(':');
        let hours = parseInt(timeParts[0]);
        const minutes = timeParts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        
        return `${hours}:${minutes} ${ampm}`;
    }
    
    // Handle form submission (Create/Update)
    document.getElementById('complaintForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const complaintId = document.getElementById('complaintId').value;
        
        let url = `${baseUrl}/index.php?route=api/complaint/save`;
        if (complaintId) {
            url = `${baseUrl}/index.php?route=api/complaint/save&action=edit&id=${complaintId}`;
        }
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save complaint'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error saving complaint');
        });
    });
    
    // Edit complaint
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            
            fetch(`${baseUrl}/index.php?route=api/complaint/details&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('complaintId').value = data.id;
                    document.querySelector('[name="incident_title"]').value = data.incident_title;
                    document.querySelector('[name="blotter_type"]').value = data.blotter_type;
                    document.querySelector('[name="complainant_type"]').value = data.complainant_type || '';
                    document.querySelector('[name="complainant_name"]').value = data.complainant_name;
                    document.querySelector('[name="complainant_contact"]').value = data.complainant_contact || '';
                    document.querySelector('[name="complainant_gender"]').value = data.complainant_gender?.toLowerCase() || '';
                    document.querySelector('[name="complainant_birthday"]').value = data.complainant_birthday?.split(' ')[0] || '';
                    document.querySelector('[name="complainant_address"]').value = data.complainant_address || '';
                    document.querySelector('[name="offender_type"]').value = data.offender_type?.toLowerCase() || '';
                    document.querySelector('[name="offender_gender"]').value = data.offender_gender?.toLowerCase() || '';
                    document.querySelector('[name="offender_name"]').value = data.offender_name || '';
                    document.querySelector('[name="offender_address"]').value = data.offender_address || '';
                    document.querySelector('[name="offender_description"]').value = data.offender_description || '';
                    document.querySelector('[name="date_of_incident"]').value = data.date_of_incident?.split(' ')[0] || '';
                    document.querySelector('[name="time_of_incident"]').value = data.time_of_incident?.substring(0, 5) || '';
                    document.querySelector('[name="location"]').value = data.location;
                    document.querySelector('[name="narrative"]').value = data.narrative;
                    
                    if (data.case_type_id) {
                        document.querySelector(`[name="case_type_id"][value="${data.case_type_id}"]`).checked = true;
                    }
                    
                    document.getElementById('modalTitle').textContent = 'Edit Complaint';
                    document.getElementById('submitBtn').textContent = 'Save Changes';
                    new bootstrap.Modal(document.getElementById('newIncidentModal')).show();
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Error loading complaint details');
                });
        });
    });
    
    // Reset form when modal is closed
    document.getElementById('newIncidentModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('complaintForm').reset();
        document.getElementById('complaintId').value = '';
        document.getElementById('modalTitle').textContent = 'Add New Complaint';
        document.getElementById('submitBtn').textContent = 'Submit';
    });
    
    // View details
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const statusesData = document.getElementById('statusesData')?.textContent;
            const statuses = statusesData ? JSON.parse(statusesData) : [];
            
            fetch(`${baseUrl}/index.php?route=api/complaint/details&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    const isResolved = data.status_id == 3;
                    
                    let statusUpdateHtml = '';
                    if (!isResolved) {
                        statusUpdateHtml = `
                            <div class="card border-info mb-3">
                                <div class="card-body">
                                    <h6 class="text-info mb-3 d-flex align-items-center">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        Update Status
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-md-8">
                                            <label class="form-label">Change Status</label>
                                            <select class="form-select" id="statusSelect">
                                                ${statuses.map(s => `
                                                    <option value="${s.id}" ${s.id == data.status_id ? 'selected' : ''}>
                                                        ${s.label}
                                                    </option>
                                                `).join('')}
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button class="btn btn-info w-100" onclick="updateComplaintStatus(${data.id})">
                                                <i class="fas fa-save"></i> Update
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i> Note: Once marked as "Resolved", the status cannot be changed.
                                    </small>
                                </div>
                            </div>
                        `;
                    } else {
                        statusUpdateHtml = `
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-lock"></i> This complaint has been marked as <strong>Resolved</strong> 
                                ${data.resolved_at ? 'on ' + new Date(data.resolved_at).toLocaleString() : ''} 
                                and can no longer be updated.
                            </div>
                        `;
                    }
                    
                    const html = `
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex gap-2 mb-3">
                                <span class="status-badge status-${data.status_label.toLowerCase()}">${data.status_label}</span>
                                <span class="status-badge case-${data.case_type.toLowerCase()}">${data.case_type}</span>
                            </div>
                            <h5 class="text-primary mb-0">${data.incident_title || 'Complaint Details'}</h5>
                        </div>
                        
                        ${statusUpdateHtml}
                        
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="text-primary mb-3 d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2"></i>
                                    Complainant Information
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Name</small>
                                        <strong>${data.complainant_name}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Type</small>
                                        <strong>${data.complainant_type || 'N/A'}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Gender</small>
                                        <strong>${data.complainant_gender || 'N/A'}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Contact</small>
                                        <strong>${data.complainant_contact || 'N/A'}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Birthday</small>
                                        <strong>${data.complainant_birthday || 'N/A'}</strong>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Address</small>
                                        <strong>${data.complainant_address || 'N/A'}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="text-danger mb-3 d-flex align-items-center">
                                    <i class="fas fa-user-secret me-2"></i>
                                    Offender Information
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Type</small>
                                        <strong>${data.offender_type || 'N/A'}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Name</small>
                                        <strong>${data.offender_name || 'N/A'}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Gender</small>
                                        <strong>${data.offender_gender || 'N/A'}</strong>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Address</small>
                                        <strong>${data.offender_address || 'N/A'}</strong>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Description</small>
                                        <strong>${data.offender_description || 'N/A'}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="text-warning mb-3 d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Incident Details
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Date</small>
                                        <strong>${data.date_of_incident}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Time</small>
                                        <strong>${formatTime(data.time_of_incident)}</strong>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Location</small>
                                        <strong>${data.location}</strong>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Narrative</small>
                                        <p class="mb-0 mt-1">${data.narrative}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('detailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('detailsModal')).show();
                })
                .catch(err => alert('Error loading details'));
        });
    });

    // Delete complaint
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this complaint?')) return;
            
            const id = this.dataset.id;
            fetch(`${baseUrl}/index.php?route=api/complaint/delete&id=${id}`, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting complaint');
                    }
                });
        });
    });
    
    // Update complaint status function
    window.updateComplaintStatus = function(complaintId) {
        const statusSelect = document.getElementById('statusSelect');
        const statusId = statusSelect.value;
        const statusLabel = statusSelect.options[statusSelect.selectedIndex].text;
        
        if (statusId == 3) {
            if (!confirm('Are you sure you want to mark this complaint as RESOLVED? This action cannot be undone and the status will be locked.')) {
                return;
            }
        }
        
        const formData = new FormData();
        formData.append('incident_id', complaintId);
        formData.append('status_id', statusId);
        
        fetch(`${baseUrl}/index.php?route=api/complaint/update-status`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully to: ' + statusLabel);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update status'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error updating status');
        });
    };
    
    // Filtering functionality
    if (document.getElementById('statusFilter')) {
        document.getElementById('statusFilter').addEventListener('change', filterAllCards);
        document.getElementById('caseTypeFilter').addEventListener('change', filterAllCards);
        document.getElementById('applyFilter').addEventListener('click', filterAllCards);
        
        const cards = document.querySelectorAll('.incident-card');
        
        function filterAllCards() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusValue = document.getElementById('statusFilter').value.toLowerCase().trim();
            const caseValue = document.getElementById('caseTypeFilter').value.toLowerCase().trim();
            let visibleCount = 0;
            
            cards.forEach(card => {
                const searchText = (card.dataset.search || '').toLowerCase();
                const status = (card.dataset.status || '').toLowerCase();
                const caseType = (card.dataset.case || '').toLowerCase();
                
                const matchesSearch = !searchTerm || searchText.includes(searchTerm);
                const matchesStatus = !statusValue || status === statusValue;
                const matchesCase = !caseValue || caseType === caseValue;
                
                if (matchesSearch && matchesStatus && matchesCase) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            const noResultsMsg = document.getElementById('noResultsMessage');
            if (noResultsMsg) {
                noResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }
        
        document.getElementById('searchInput').addEventListener('input', filterAllCards);
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterAllCards();
            }
        });
        
        filterAllCards();
    }
}
