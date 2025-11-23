/**
 * Residents Complaint View JavaScript
 * For: app/views/residents/residents.php
 */

// Base URL
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

// View complaint details
document.querySelectorAll('.view-details-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        // Use front-controller AJAX action that returns JSON for resident views
        const url = `${baseUrl}/index.php?action=complaint_getDetails&id=${id}`;
        console.log('Fetching complaint details from:', url);
        
        fetch(url)
            .then(res => {
                console.log('Response status:', res.status);
                console.log('Response headers:', res.headers.get("content-type"));
                if (!res.ok) {
                    return res.text().then(text => {
                        console.error('Error response body:', text);
                        throw new Error(`HTTP error! status: ${res.status}`);
                    });
                }
                const contentType = res.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    return res.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error("Response is not JSON. Got: " + contentType);
                    });
                }
                return res.json();
            })
            .then(data => {
                const html = `
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="d-flex gap-2 mb-3">
                            <span class="status-badge status-${data.status_label.toLowerCase()}">
                                ${data.status_label.toLowerCase() === 'resolved' && data.updated_at ? 
                                    `<i class="fas fa-check-circle"></i> Resolved<br><small>${new Date(data.updated_at).toLocaleDateString()}</small>` : 
                                    data.status_label}
                            </span>
                            <span class="status-badge case-${data.case_type.toLowerCase()}">${data.case_type}</span>
                        </div>
                        <h5 class="text-primary mb-0">${data.incident_title || 'Complaint Details'}</h5>
                    </div>
                    
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
                                ${data.complainant_birthday ? `
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Birthday</small>
                                    <strong>${data.complainant_birthday}</strong>
                                </div>
                                ` : ''}
                                ${data.complainant_address ? `
                                <div class="col-12">
                                    <small class="text-muted d-block">Address</small>
                                    <strong>${data.complainant_address}</strong>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('detailsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            })
            .catch(err => {
                console.error('Full error details:', err);
                alert('Error loading complaint details. Check console for details.');
            });
    });
});

// Filtering functionality
if (document.getElementById('statusFilter')) {
    document.getElementById('statusFilter').addEventListener('change', filterAllCards);
    document.getElementById('caseFilter').addEventListener('change', filterAllCards);
    
    const cards = document.querySelectorAll('.incident-card');
    
    function filterAllCards() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const statusValue = document.getElementById('statusFilter').value.toLowerCase().trim();
        const caseValue = document.getElementById('caseFilter').value.toLowerCase().trim();
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
    
    // Initial filter
    filterAllCards();
}
