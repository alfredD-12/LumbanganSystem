/**
 * Admin Complaint Page JavaScript
 * For: app/views/complaint/admin.php
 */

// Define baseUrl globally for the confirmDeleteComplaint function
const baseUrl = '/Lumbangan_BMIS/bmis-lumbangan-system/public';

// Only run if we're on the complaints page
if (document.getElementById('complaintForm')) {
    
    // Helper: safely parse JSON responses (throws if content-type isn't JSON)
    function parseJsonResponse(res) {
        const ct = res.headers.get('content-type') || '';
        if (!res.ok) throw new Error('Network response was not ok');
        if (!ct.includes('application/json')) {
            return res.text().then(t => { throw new Error('Expected JSON, got: ' + ct + '\n' + t); });
        }
        return res.json();
    }
    
    // Make parseJsonResponse global for confirmDeleteComplaint
    window.parseJsonResponse = parseJsonResponse;

    // Helper: render a complaint card HTML (matches server-side markup)
    function renderComplaintCard(c) {
        const searchData = (c.incident_title || '') + ' ' + (c.complainant_name || '') + ' ' + (c.offender_name || '') + ' ' + (c.location || '') + ' ' + (c.narrative || '');
        const statusClass = (c.status_label || '').toLowerCase();
        const caseClass = (c.case_type || '').toLowerCase();

        return `
        <div class="card incident-card mb-3" data-search="${(searchData||'').toLowerCase()}" data-status="${(c.status_label||'').toLowerCase()}" data-case="${(c.case_type||'').toLowerCase()}">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">${c.incident_title || 'Untitled Complaint'}</h5>
                    <div>
                        <span class="status-badge status-${statusClass} me-2">${c.status_label || ''}</span>
                        <span class="status-badge case-${caseClass}">${c.case_type || ''}</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong class="text-muted">Complainant:</strong>
                        ${c.complainant_name || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Offender:</strong>
                        ${c.offender_name || 'Unknown'}
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Date:</strong>
                        ${c.date_of_incident ? new Date(c.date_of_incident).toLocaleDateString() : ''}
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Location:</strong>
                        ${c.location || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Blotter Type:</strong>
                        ${c.blotter_type || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Offender Type:</strong>
                        ${c.offender_type || 'N/A'}
                    </div>
                    <div class="col-12">
                        <strong class="text-muted">Description:</strong>
                        <p class="mb-0">${(c.narrative||'').substring(0,150)}...</p>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-primary btn-sm view-details-btn" data-id="${c.id}">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="${c.id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${c.id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }

    function prependComplaintCard(c) {
        const container = document.querySelector('.complaints-list');
        if (!container) return;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = renderComplaintCard(c);
        container.prepend(wrapper.firstElementChild);
        // Re-bind event handlers for newly added buttons
        bindCardButtons();
    }

    function replaceComplaintCard(c) {
        const existing = document.querySelector(`.incident-card[data-search*="${(c.incident_title||'').toLowerCase()}"]`) || document.querySelector(`.incident-card .edit-btn[data-id="${c.id}"]`)?.closest('.incident-card');
        const container = document.querySelector('.complaints-list');
        if (!container) return;
        // Try to find by data-id using edit-btn selector
        let el = document.querySelector(`.edit-btn[data-id="${c.id}"]`);
        if (el) {
            const card = el.closest('.incident-card');
            if (card) {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = renderComplaintCard(c);
                card.replaceWith(wrapper.firstElementChild);
                bindCardButtons();
                return;
            }
        }
        // Fallback: prepend if not found
        prependComplaintCard(c);
    }

    function removeComplaintCardById(id) {
        const btn = document.querySelector(`.delete-btn[data-id="${id}"]`);
        const card = btn ? btn.closest('.incident-card') : document.querySelector(`.incident-card .edit-btn[data-id="${id}"]`)?.closest('.incident-card');
        if (card && card.parentNode) card.parentNode.removeChild(card);
    }
    
    // Make removeComplaintCardById global for confirmDeleteComplaint
    window.removeComplaintCardById = removeComplaintCardById;

    function bindCardButtons() {
        // Bind view details
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            if (!btn._bound) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const statusesData = document.getElementById('statusesData')?.textContent;
                    const statuses = statusesData ? JSON.parse(statusesData) : [];
                    fetch(`${baseUrl}/index.php?action=getComplaint&id=${id}`).then(parseJsonResponse).then(data => {
                        // reuse existing details rendering
                        // trigger the existing handler by simulating click? we'll call existing code fragment instead
                        const isResolved = data.status_id == 3;
                        // build details HTML (same as earlier code)
                        // ... (the existing view-details code below handles rendering)
                        // For simplicity, call the same logic by triggering a custom event
                        document.dispatchEvent(new CustomEvent('complaint:detailsLoaded', { detail: data }));
                    }).catch(err => showError('Error loading details: ' + (err.message || err)));
                });
                btn._bound = true;
            }
        });

        // Bind edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            if (!btn._bound) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    fetch(`${baseUrl}/index.php?action=getComplaint&id=${id}`)
                        .then(parseJsonResponse)
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
                                const el = document.querySelector(`[name="case_type_id"][value="${data.case_type_id}"]`);
                                if (el) el.checked = true;
                            }
                            document.getElementById('modalTitle').textContent = 'Edit Complaint';
                            document.getElementById('submitBtn').textContent = 'Save Changes';
                            new bootstrap.Modal(document.getElementById('newIncidentModal')).show();
                        })
                        .catch(err => {
                            console.error('Error loading complaint details:', err);
                            showError('Error loading complaint details: ' + (err.message || err));
                        });
                });
                btn._bound = true;
            }
        });

        // Bind delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            if (!btn._bound) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    window.complaintToDelete = id;
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteComplaintModal'));
                    deleteModal.show();
                });
                btn._bound = true;
            }
        });
    }

    // Initialize bindings for elements already present
    bindCardButtons();

    // Listen for programmatic details load events (used by bindCardButtons)
    document.addEventListener('complaint:detailsLoaded', function(e) {
        const data = e.detail;
        const statusesData = document.getElementById('statusesData')?.textContent;
        const statuses = statusesData ? JSON.parse(statusesData) : [];
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
                        Complaint Information
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
    });

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
        
        // Use front-controller action endpoints that return JSON
        let url = `${baseUrl}/index.php?action=createComplaint`;
        if (complaintId) {
            url = `${baseUrl}/index.php?action=updateComplaint&id=${complaintId}`;
        }
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(parseJsonResponse)
        .then(data => {
            if (data.success) {
                showSuccess(data.message || 'Complaint saved successfully');
                // If server returned the record data, update the DOM accordingly
                if (data.data) {
                    if (complaintId) {
                        // Updated
                        replaceComplaintCard(data.data);
                    } else {
                        // Created
                        prependComplaintCard(data.data);
                        // increment total and pending counters if present
                        const totalEl = document.querySelector('.gradient-card-blue h2');
                        if (totalEl) totalEl.textContent = (parseInt(totalEl.textContent || '0') + 1).toString();
                        const pendingEl = document.querySelector('.gradient-card-yellow h2');
                        if (pendingEl) pendingEl.textContent = (parseInt(pendingEl.textContent || '0') + 1).toString();
                    }
                }
                // Close and reset modal
                const modalEl = document.getElementById('newIncidentModal');
                const modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInst.hide();
                document.getElementById('complaintForm').reset();
                document.getElementById('complaintId').value = '';
                document.getElementById('modalTitle').textContent = 'Add New Complaint';
                document.getElementById('submitBtn').textContent = 'Submit';
            } else {
                showError(data.message || 'Failed to save complaint');
            }
        })
        .catch(err => {
            console.error('Error saving complaint:', err);
            showError('Error saving complaint: ' + (err.message || err));
        });
    });
    
    
    
    // Reset form when modal is closed
    document.getElementById('newIncidentModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('complaintForm').reset();
        document.getElementById('complaintId').value = '';
        document.getElementById('modalTitle').textContent = 'Add New Complaint';
        document.getElementById('submitBtn').textContent = 'Submit';
    });
    
    

    // (Delete handlers are bound via bindCardButtons for dynamic content)
    
    // Update complaint status function
    window.updateComplaintStatus = function(complaintId) {
        const statusSelect = document.getElementById('statusSelect');
        const statusId = statusSelect.value;
        const statusLabel = statusSelect.options[statusSelect.selectedIndex].text;

        if (statusId == 3) {
            // Store data for confirmation
            window.pendingStatusUpdate = { complaintId, statusId, statusLabel };
            const resolveModal = new bootstrap.Modal(document.getElementById('resolveConfirmModal'));
            resolveModal.show();
            return;
        }

        // If not resolving, proceed directly
        performStatusUpdate(complaintId, statusId, statusLabel);
    };

    // Confirm resolve status function
    window.confirmResolveStatus = function() {
        const data = window.pendingStatusUpdate;
        if (!data) return;
        
        bootstrap.Modal.getInstance(document.getElementById('resolveConfirmModal')).hide();
        performStatusUpdate(data.complaintId, data.statusId, data.statusLabel);
        window.pendingStatusUpdate = null;
    };

    // Perform the actual status update
    function performStatusUpdate(complaintId, statusId, statusLabel) {

        const formData = new FormData();
        formData.append('id', complaintId);
        formData.append('status_id', statusId);

        fetch(`${baseUrl}/index.php?action=updateComplaintStatus`, {
            method: 'POST',
            body: formData
        })
        .then(parseJsonResponse)
        .then(data => {
            if (data.success) {
                showSuccess('Status updated to: ' + statusLabel);
                // Update counts if stats returned
                if (data.stats) {
                    const totalEl = document.querySelector('.gradient-card-blue h2');
                    const pendingEl = document.querySelector('.gradient-card-yellow h2');
                    const investigatingEl = document.querySelector('.gradient-card-purple h2');
                    const resolvedEl = document.querySelector('.gradient-card-green h2');
                    if (totalEl && typeof data.stats.total !== 'undefined') totalEl.textContent = data.stats.total;
                    if (pendingEl && typeof data.stats.pending !== 'undefined') pendingEl.textContent = data.stats.pending;
                    if (investigatingEl && typeof data.stats.investigating !== 'undefined') investigatingEl.textContent = data.stats.investigating;
                    if (resolvedEl && typeof data.stats.resolved !== 'undefined') resolvedEl.textContent = data.stats.resolved;
                }

                // Update status badge on the card if present
                const editBtn = document.querySelector(`.edit-btn[data-id="${complaintId}"]`);
                if (editBtn) {
                    const card = editBtn.closest('.incident-card');
                    const badge = card ? card.querySelector('.status-badge') : null;
                    if (badge) badge.textContent = statusLabel;
                    if (badge) badge.className = 'status-badge status-' + statusLabel.toLowerCase();
                }
            } else {
                showError(data.error || data.message || 'Failed to update status');
            }
        })
        .catch(err => {
            console.error('Error updating status:', err);
            showError('Error updating status: ' + (err.message || err));
        });
    }
    
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

// Show success notification
function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    setTimeout(() => modal.hide(), 2000);
}

// Show error notification
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
    setTimeout(() => modal.hide(), 3000);
}

// Confirm delete complaint function
function confirmDeleteComplaint() {
    const id = window.complaintToDelete;
    if (!id) return;

    const delForm = new FormData();
    delForm.append('id', id);
    
    fetch(`${baseUrl}/index.php?action=deleteComplaint`, { method: 'POST', body: delForm })
        .then(window.parseJsonResponse)
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('deleteComplaintModal')).hide();
                window.complaintToDelete = null;
                window.removeComplaintCardById(id);
                const totalEl = document.querySelector('.gradient-card-blue h2');
                if (totalEl) totalEl.textContent = Math.max(0, parseInt(totalEl.textContent || '0') - 1);
                showSuccess(data.message || 'Complaint deleted successfully');
            } else {
                showError(data.message || 'Failed to delete complaint');
            }
        })
        .catch(err => { 
            console.error('Delete error', err); 
            showError('Error deleting complaint');
        });
}
