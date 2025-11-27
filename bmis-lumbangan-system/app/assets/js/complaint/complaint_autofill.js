/**
 * Complaint Form Autofill Functionality
 * Allows searching for registered users and auto-filling complainant information
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        searchDelay: 300, // milliseconds
        minSearchLength: 2,
        apiEndpoint: '../app/api/search_users.php'
    };

    let searchTimeout = null;
    let selectedUser = null;

    /**
     * Initialize autofill functionality
     */
    function initComplaintAutofill() {
        const complainantNameInput = document.querySelector('input[name="complainant_name"]');
        
        if (!complainantNameInput) {
            console.warn('Complainant name input not found');
            return;
        }

        // Create search results container
        const searchContainer = createSearchContainer();
        complainantNameInput.parentNode.appendChild(searchContainer);

        // Attach event listeners
        complainantNameInput.addEventListener('input', handleNameInput);
        complainantNameInput.addEventListener('focus', handleNameFocus);
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!complainantNameInput.contains(e.target) && !searchContainer.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Clear selected user when manually editing fields
        attachClearListeners();
    }

    /**
     * Create search results container
     */
    function createSearchContainer() {
        const container = document.createElement('div');
        container.className = 'complaint-user-search-dropdown';
        container.style.cssText = `
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: none;
            margin-top: 2px;
        `;
        return container;
    }

    /**
     * Handle name input events
     */
    function handleNameInput(e) {
        const searchTerm = e.target.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (searchTerm.length < CONFIG.minSearchLength) {
            hideSearchResults();
            return;
        }

        searchTimeout = setTimeout(() => {
            searchUsers(searchTerm);
        }, CONFIG.searchDelay);
    }

    /**
     * Handle name input focus
     */
    function handleNameFocus(e) {
        const searchTerm = e.target.value.trim();
        if (searchTerm.length >= CONFIG.minSearchLength) {
            searchUsers(searchTerm);
        }
    }

    /**
     * Search for users via API
     */
    async function searchUsers(searchTerm) {
        try {
            const response = await fetch(`${CONFIG.apiEndpoint}?q=${encodeURIComponent(searchTerm)}`);
            
            if (!response.ok) {
                throw new Error('Search request failed');
            }

            const users = await response.json();
            displaySearchResults(users);
            
        } catch (error) {
            console.error('User search error:', error);
            hideSearchResults();
        }
    }

    /**
     * Display search results
     */
    function displaySearchResults(users) {
        const container = document.querySelector('.complaint-user-search-dropdown');
        
        if (!users || users.length === 0) {
            container.innerHTML = '<div style="padding: 10px; color: #999;">No registered users found</div>';
            container.style.display = 'block';
            return;
        }

        container.innerHTML = users.map(user => `
            <div class="user-search-item" 
                 data-user='${JSON.stringify(user).replace(/'/g, '&apos;')}'
                 style="
                     padding: 10px;
                     cursor: pointer;
                     border-bottom: 1px solid #f0f0f0;
                     transition: background 0.2s;
                 "
                 onmouseover="this.style.background='#f8f9fa'"
                 onmouseout="this.style.background='white'">
                <div style="font-weight: 500;">${escapeHtml(user.full_name)}</div>
                <div style="font-size: 0.85em; color: #666;">
                    ${user.mobile ? `📱 ${escapeHtml(user.mobile)}` : ''}
                    ${user.address ? `<br>📍 ${escapeHtml(user.address)}` : ''}
                </div>
            </div>
        `).join('');

        // Attach click handlers
        container.querySelectorAll('.user-search-item').forEach(item => {
            item.addEventListener('click', function() {
                const userData = JSON.parse(this.getAttribute('data-user'));
                fillComplaintForm(userData);
                hideSearchResults();
            });
        });

        container.style.display = 'block';
    }

    /**
     * Fill complaint form with user data
     */
    function fillComplaintForm(user) {
        selectedUser = user;

        // Store user_id in a hidden field (create if doesn't exist)
        let userIdInput = document.querySelector('input[name="user_id"]');
        if (!userIdInput) {
            userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'user_id';
            document.querySelector('form').appendChild(userIdInput);
        }
        userIdInput.value = user.user_id;

        // Fill form fields
        setInputValue('complainant_name', user.full_name);
        setInputValue('complainant_contact', user.mobile || '');
        setInputValue('complainant_address', user.address || '');
        
        // Set gender
        const genderSelect = document.querySelector('select[name="complainant_gender"]');
        if (genderSelect && user.gender) {
            genderSelect.value = user.gender;
        }

        // Set birthday
        if (user.birthdate) {
            setInputValue('complainant_birthday', user.birthdate);
        }

        // Set complainant type to Resident (since they're in the system)
        const typeSelect = document.querySelector('select[name="complainant_type"]');
        if (typeSelect) {
            typeSelect.value = 'Resident';
        }

        // Add visual feedback
        showAutofillNotification('Information auto-filled from user profile');
    }

    /**
     * Set input value helper
     */
    function setInputValue(name, value) {
        const input = document.querySelector(`input[name="${name}"], textarea[name="${name}"]`);
        if (input) {
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    /**
     * Hide search results
     */
    function hideSearchResults() {
        const container = document.querySelector('.complaint-user-search-dropdown');
        if (container) {
            container.style.display = 'none';
        }
    }

    /**
     * Show autofill notification
     */
    function showAutofillNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'autofill-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Attach listeners to clear selected user on manual edit
     */
    function attachClearListeners() {
        const fields = ['complainant_contact', 'complainant_gender', 'complainant_birthday', 'complainant_address'];
        
        fields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('input', function() {
                    // If user manually changes autofilled data, clear the user_id
                    if (selectedUser) {
                        const userIdInput = document.querySelector('input[name="user_id"]');
                        if (userIdInput) {
                            userIdInput.value = '';
                        }
                        selectedUser = null;
                    }
                });
            }
        });
    }

    /**
     * Escape HTML helper
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .complaint-user-search-dropdown::-webkit-scrollbar {
            width: 8px;
        }
        
        .complaint-user-search-dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .complaint-user-search-dropdown::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .complaint-user-search-dropdown::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    `;
    document.head.appendChild(style);

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initComplaintAutofill);
    } else {
        initComplaintAutofill();
    }

    // Export for external use
    window.ComplaintAutofill = {
        init: initComplaintAutofill,
        search: searchUsers
    };

})();
