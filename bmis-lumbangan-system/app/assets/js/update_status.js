document.addEventListener('DOMContentLoaded', function() {
    // Global notification function
    function showNotification(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show fixed-top mx-auto mt-3`;
        alert.style.maxWidth = '500px';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    // Format date-time
    function formatDateTime(date) {
        return new Date(date).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
        });
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes countUpdate {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        @keyframes resolvedPulse {
            0% { transform: scale(1); box-shadow: none; }
            50% { transform: scale(1.02); box-shadow: 0 0 15px rgba(40, 167, 69, 0.3); }
            100% { transform: scale(1); box-shadow: none; }
        }
        
        .status-badge {
            transition: all 0.3s ease-in-out;
        }
        
        .incident-card {
            transition: all 0.3s ease-in-out;
        }
    `;
    document.head.appendChild(style);

    // Handle status updates
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', function() {
            const modal = document.getElementById('incidentDetailsModal');
            const incidentId = modal.getAttribute('data-incident-id');
            const statusSelect = document.getElementById('statusSelect');
            const statusId = statusSelect ? statusSelect.value : '';

            // Validation
            if (!incidentId || !statusId) {
                showNotification('Error: Missing incident ID or status', 'danger');
                return;
            }

            const currentStatusText = document.getElementById('modalStatus').textContent.trim();
            const isCurrentlyResolved = currentStatusText.toLowerCase().includes('resolved');
            const isChangingToResolved = statusId === '3';

            // Prevent modifying resolved incidents
            if (isCurrentlyResolved) {
                showNotification('This incident is already resolved and cannot be modified', 'danger');
                statusSelect.value = '3'; // Reset to resolved
                return;
            }

            // Confirm if changing to resolved
            if (isChangingToResolved && !isCurrentlyResolved) {
                if (!confirm('Are you sure you want to mark this incident as Resolved? This action cannot be undone.')) {
                    // Reset to previous status
                    statusSelect.value = currentStatusText.toLowerCase().includes('investigating') ? '2' : '1';
                    return;
                }
            }

            // Show loading state
            this.disabled = true;
            const originalText = this.textContent;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            // Prepare form data
            const formData = new FormData();
            formData.append('incident_id', incidentId);
            formData.append('status_id', statusId);

            // Send the update request
            fetch('update_complaint_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Get status text
                    const statusLabels = ['Pending', 'Investigating', 'Resolved'];
                    const statusLabel = statusLabels[parseInt(statusId) - 1];

                    // Update modal badge
                    const statusBadge = document.getElementById('modalStatus');
                    statusBadge.textContent = statusLabel;
                    statusBadge.className = `status-badge status-${statusLabel.toLowerCase()}`;

                    // Update card badge if it exists
                    const card = document.querySelector(`.incident-card[data-incident-id="${incidentId}"]`);
                    if (card) {
                        const cardBadge = card.querySelector('.status-badge');
                        if (cardBadge) {
                            // If the incident is now resolved, add timestamp
                            if (statusId === '3' && data.updated_at) {
                                const timestamp = formatDateTime(new Date(data.updated_at));
                                cardBadge.innerHTML = `${statusLabel} <small class="d-block mt-1" style="font-size: 0.8em;">
                                    <i class="fas fa-check-circle me-1"></i>Resolved on ${timestamp}</small>`;
                                statusBadge.innerHTML = cardBadge.innerHTML;
                                
                                // Add resolved animation
                                card.style.animation = 'resolvedPulse 1s ease-out';
                                
                                // Disable status updates
                                statusSelect.disabled = true;
                                this.disabled = true;
                                this.innerHTML = '<i class="fas fa-check me-2"></i>Resolved';
                            } else {
                                cardBadge.textContent = statusLabel;
                                cardBadge.className = `status-badge status-${statusLabel.toLowerCase()}`;
                                this.disabled = false;
                                this.innerHTML = originalText;
                            }
                        }
                    }

                    // Update statistics if provided
                    if (data.statistics) {
                        const stats = data.statistics;
                        // Update the main statistics cards
                        const elements = {
                            'gradient-card-yellow': stats.pending,
                            'gradient-card-purple': stats.investigating,
                            'gradient-card-green': stats.resolved
                        };

                        for (const [cardClass, value] of Object.entries(elements)) {
                            const element = document.querySelector(`.${cardClass} h2`);
                            if (element) {
                                element.textContent = value;
                                element.style.animation = 'countUpdate 0.5s ease-out';
                                setTimeout(() => element.style.animation = '', 500);
                            }
                        }

                        // Update total count
                        const total = parseInt(stats.pending || 0) + 
                                    parseInt(stats.investigating || 0) + 
                                    parseInt(stats.resolved || 0);
                        const totalElement = document.querySelector('.gradient-card-blue h2');
                        if (totalElement) {
                            totalElement.textContent = total;
                            totalElement.style.animation = 'countUpdate 0.5s ease-out';
                            setTimeout(() => totalElement.style.animation = '', 500);
                        }
                    }

                    showNotification('Status updated successfully');

                    // If status is now resolved, close the modal after a short delay
                    if (statusId === '3') {
                        setTimeout(() => {
                            const bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) bsModal.hide();
                        }, 1500);
                    }
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating status: ' + error.message, 'danger');
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }
});