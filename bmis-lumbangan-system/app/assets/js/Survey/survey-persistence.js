/**
 * Survey Form Persistence with localStorage
 * Automatically saves form data as user types and restores it when they return
 * Clears all survey data on logout
 */

(function() {
    'use strict';

    const STORAGE_PREFIX = 'survey_';
    const STORAGE_KEY_LIST = 'survey_form_keys';

    /**
     * Get the current form identifier based on page
     */
    function getFormIdentifier() {
        const path = window.location.pathname;
        if (path.includes('wizard_personal')) return 'personal';
        if (path.includes('wizard_vitals')) return 'vitals';
        if (path.includes('wizard_family_history')) return 'family_history';
        if (path.includes('wizard_family')) return 'family';
        if (path.includes('wizard_lifestyle')) return 'lifestyle';
        if (path.includes('wizard_angina')) return 'angina';
        if (path.includes('wizard_diabetes')) return 'diabetes';
        if (path.includes('wizard_household')) return 'household';
        return null;
    }

    /**
     * Get storage key for current form
     */
    function getStorageKey(formId) {
        return STORAGE_PREFIX + formId;
    }

    /**
     * Track which forms have data in localStorage
     */
    function trackFormKey(formId) {
        const keys = JSON.parse(localStorage.getItem(STORAGE_KEY_LIST) || '[]');
        if (!keys.includes(formId)) {
            keys.push(formId);
            localStorage.setItem(STORAGE_KEY_LIST, JSON.stringify(keys));
        }
    }

    /**
     * Save form data to localStorage
     */
    function saveFormData(form) {
        const formId = getFormIdentifier();
        if (!formId) {
            console.warn('Survey Persistence: Cannot save - form identifier not found');
            return;
        }

        const formData = {};
        const elements = form.elements;
        let savedCount = 0;

        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            const name = element.name;
            if (!name) continue;

            // Special handling for Flatpickr-backed birthdate: if the hidden input has no value
            // try to read the selected date from the flatpickr instance or altInput and store it
            if (name === 'birthdate') {
                try {
                    let bdVal = '';
                    if (element._flatpickr && Array.isArray(element._flatpickr.selectedDates) && element._flatpickr.selectedDates.length) {
                        try {
                            bdVal = element._flatpickr.formatDate(element._flatpickr.selectedDates[0], 'Y-m-d');
                        } catch (e) {
                            bdVal = element.value || '';
                        }
                    } else if (element.value) {
                        bdVal = element.value;
                    } else if (element._flatpickr && element._flatpickr.altInput && element._flatpickr.altInput.value) {
                        // Try parsing altInput to canonical Y-m-d
                        try {
                            const altFmt = (element._flatpickr.config && element._flatpickr.config.altFormat) || 'F j, Y';
                            const parsed = window.flatpickr.parseDate(element._flatpickr.altInput.value, altFmt);
                            if (parsed) bdVal = window.flatpickr.formatDate(parsed, 'Y-m-d');
                        } catch (e) {
                            bdVal = element._flatpickr.altInput.value;
                        }
                    }

                    if (bdVal) {
                        formData[name] = bdVal;
                        savedCount++;
                        // Skip normal processing for this element
                        continue;
                    }
                } catch (e) {
                    // swallow and continue to regular handling
                }
            }
            if (element.hasAttribute('data-persist-skip')) continue; // allow opt-out

            // Persist all common types including range
            switch (element.type) {
                case 'checkbox':
                    formData[name] = element.checked;
                    savedCount++;
                    break;
                case 'radio':
                    if (element.checked) {
                        formData[name] = element.value;
                        savedCount++;
                    }
                    break;
                case 'range':
                case 'text':
                case 'number':
                case 'email':
                case 'tel':
                case 'date':
                    formData[name] = element.value;
                    savedCount++;
                    break;
                default:
                    if (element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') {
                        formData[name] = element.value;
                        savedCount++;
                    }
            }
        }

        const storageKey = getStorageKey(formId);
        localStorage.setItem(storageKey, JSON.stringify(formData));
        if ('birthdate' in formData) {
            console.log('Survey Persistence: birthdate saved as', formData.birthdate);
        }
        trackFormKey(formId);
        
        console.log('Survey Persistence: Saved', savedCount, 'fields to localStorage for:', formId);
    }

    /**
     * Restore form data from localStorage
     */
    function restoreFormData(form) {
        const formId = getFormIdentifier();
        if (!formId) {
            console.warn('Survey Persistence: Could not identify form');
            return;
        }

        const storageKey = getStorageKey(formId);
        const savedData = localStorage.getItem(storageKey);
        
        if (!savedData) {
            console.log('Survey Persistence: No saved data found for:', formId);
            return;
        }

        try {
            const formData = JSON.parse(savedData);
            console.log('Survey Persistence: Restoring data for:', formId, formData);
            if ('birthdate' in formData) {
                console.log('Survey Persistence: birthdate to restore =', formData.birthdate);
            }
            
            const elements = form.elements;
            let restoredCount = 0;

            for (let i = 0; i < elements.length; i++) {
                const element = elements[i];
                const name = element.name;
                if (!name || !(name in formData)) continue;
                if (element.hasAttribute('data-persist-skip')) continue; // opt-out

                // If the form control already has a value (for example rendered from DB),
                // prefer the existing value and do not overwrite it from localStorage.
                // This prevents localStorage from re-populating fields after logout when
                // the server already has authoritative data.
                const currentVal = (element.type === 'checkbox') ? element.checked : (element.value || '');
                const savedVal = formData[name];
                if (currentVal !== '' && currentVal !== false && currentVal !== null) {
                    // There's already a value present (likely from the DB render); skip restoring
                    continue;
                }

                switch (element.type) {
                    case 'checkbox':
                        element.checked = !!savedVal;
                        restoredCount++;
                        // fire change for any UI bindings
                        element.dispatchEvent(new Event('change', { bubbles: true }));
                        break;
                    case 'radio':
                        if (element.value === savedVal) {
                            element.checked = true;
                            restoredCount++;
                            element.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        break;
                    case 'range':
                    case 'text':
                    case 'number':
                    case 'email':
                    case 'tel':
                    case 'date':
                        if (element._flatpickr && savedVal) {
                            try {
                                element._flatpickr.setDate(savedVal, true);
                            } catch (err) {
                                element.value = savedVal;
                            }
                        } else {
                            element.value = savedVal;
                        }
                        // Special birthdate force restore
                        if (name === 'birthdate') {
                            try {
                                console.log('Survey Persistence: birthdate force restore attempt (direct value):', savedVal);
                                // Always set the underlying input value first
                                element.value = savedVal;

                                if (element._flatpickr) {
                                    try {
                                        element._flatpickr.setDate(savedVal, true);
                                        // Also update altInput if present
                                        if (element._flatpickr.altInput) {
                                            try {
                                                var parsed = element._flatpickr.parseDate(savedVal, element._flatpickr.config.dateFormat || 'Y-m-d');
                                                if (parsed) {
                                                    var altFmt = element._flatpickr.config.altFormat || 'F j, Y';
                                                    element._flatpickr.altInput.value = element._flatpickr.formatDate(parsed, altFmt);
                                                }
                                            } catch(e) { /* ignore formatting errors */ }
                                        }
                                        console.log('Survey Persistence: birthdate applied via flatpickr.setDate');
                                    } catch(e) {
                                        console.warn('Survey Persistence: flatpickr.setDate failed, falling back to raw value', e);
                                        element.value = savedVal;
                                    }
                                } else if (window.flatpickr && typeof window.flatpickr.parseDate === 'function') {
                                    // Try to parse and keep raw value
                                    try {
                                        var p = window.flatpickr.parseDate(savedVal, 'Y-m-d');
                                        if (p) {
                                            element.value = savedVal;
                                            console.log('Survey Persistence: birthdate parsed successfully (no instance)');
                                        }
                                    } catch(e) { element.value = savedVal; }
                                } else {
                                    // No picker available, raw value stays
                                    element.value = savedVal;
                                    console.log('Survey Persistence: birthdate applied raw (no picker instance)');
                                }
                            } catch(e){ console.warn('Survey Persistence: birthdate force restore error', e); }
                        }
                        restoredCount++;
                        element.dispatchEvent(new Event('input', { bubbles: true }));
                        element.dispatchEvent(new Event('change', { bubbles: true }));
                        break;
                    default:
                        if (element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') {
                            element.value = savedVal;
                            restoredCount++;
                            element.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                }
            }

            console.log('Survey Persistence: Restored', restoredCount, 'fields from localStorage');
        } catch (e) {
            console.error('Survey Persistence: Error restoring form data:', e);
        }
    }

    /**
     * Clear survey data for a specific form
     */
    function clearFormData(formId) {
        const storageKey = getStorageKey(formId);
        localStorage.removeItem(storageKey);
        console.log('Cleared survey data for:', formId);
    }

    /**
     * Clear all survey data from localStorage
     */
    function clearAllSurveyData() {
        const keys = JSON.parse(localStorage.getItem(STORAGE_KEY_LIST) || '[]');
        
        keys.forEach(formId => {
            clearFormData(formId);
        });
        
        localStorage.removeItem(STORAGE_KEY_LIST);
        console.log('All survey data cleared from localStorage');
    }

    /**
     * Initialize form persistence for survey forms
     */
    function initFormPersistence() {
        // Find all survey forms - try multiple selectors
        let surveyForms = document.querySelectorAll('form[id^="form-"]');
        
        // If no forms found with that pattern, try finding any form on survey pages
        if (surveyForms.length === 0) {
            surveyForms = document.querySelectorAll('form');
            console.log('Survey Persistence: Found forms using fallback selector:', surveyForms.length);
        } else {
            console.log('Survey Persistence: Found forms with id^="form-":', surveyForms.length);
        }
        
        if (surveyForms.length === 0) {
            console.warn('Survey Persistence: No forms found on page');
            return;
        }
        
        surveyForms.forEach(form => {
            console.log('Survey Persistence: Initializing form:', form.id);
            
            // Restore saved data on page load
            restoreFormData(form);

            // Special case: if flatpickr attaches after our restore, try a second restore soon after
            setTimeout(() => {
                try { restoreFormData(form); } catch(e) {}
                try { applyDeferredBirthdate(form); } catch(e) {}
            }, 300);

            // Save data on input change (debounced)
            let saveTimeout;
            form.addEventListener('input', function(e) {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    console.log('Survey Persistence: Auto-saving form data...');
                    saveFormData(form);
                }, 500); // Debounce 500ms
            });

            // Save data on form change (for selects and radios)
            form.addEventListener('change', function(e) {
                console.log('Survey Persistence: Form changed, saving...');
                saveFormData(form);
            });

            // Clear localStorage when form is successfully submitted
            form.addEventListener('submit', function(e) {
                // Wait a bit to ensure submission succeeded
                setTimeout(() => {
                    const formId = getFormIdentifier();
                    if (formId) {
                        console.log('Survey Persistence: Form submitted, clearing data for:', formId);
                        clearFormData(formId);
                    }
                }, 1000);
            });
        });
    }

    /**
     * Apply birthdate after date picker initialization if it was missed earlier.
     * Polls a few times because picker libraries can attach asynchronously.
     */
    function applyDeferredBirthdate(form) {
        const formId = getFormIdentifier();
        if (formId !== 'personal') return; // only applies on personal form
        const storageKey = getStorageKey('personal');
        const raw = localStorage.getItem(storageKey);
        if (!raw) return;
        let birthdate = null;
        try { birthdate = JSON.parse(raw).birthdate || null; } catch(e){ return; }
        if (!birthdate) return;

        const bd = form.querySelector('#birthdate');
        if (!bd) return;

        let attempts = 0;
        function trySet(){
            attempts++;
            const hasFlatpickr = !!bd._flatpickr;
            const hasLitepicker = !!bd.dataset.lpInitialized; // custom flag (can be set externally if needed)
            if (hasFlatpickr) {
                try {
                    bd._flatpickr.setDate(birthdate, true);
                    console.log('Survey Persistence: Deferred birthdate applied via Flatpickr:', birthdate);
                    return;
                } catch(e){ /* fallthrough */ }
            }
            if (hasLitepicker) {
                // Litepicker does not expose instance the same way; set raw value
                bd.value = birthdate;
                console.log('Survey Persistence: Deferred birthdate applied via Litepicker raw value:', birthdate);
                return;
            }
            // If neither picker attached yet, retry until limit
            if (attempts < 5) {
                setTimeout(trySet, 250);
            } else {
                // Last resort: set underlying input
                bd.value = birthdate;
                console.warn('Survey Persistence: Birthdate applied without picker after retries:', birthdate);
            }
        }
        trySet();
    }

    /**
     * Listen for successful AJAX submissions and clear localStorage
     */
    function monitorAjaxSubmissions() {
        // Hook into the global save-survey.js success callback if available
        window.addEventListener('surveyFormSaved', function(e) {
            const formId = e.detail?.formId || getFormIdentifier();
            if (formId) {
                clearFormData(formId);
            }
        });
    }

    /**
     * Clear all survey data on logout
     */
    function setupLogoutHandler() {
        // Find logout links/buttons
        const logoutLinks = document.querySelectorAll('a[href*="logout"], button[onclick*="logout"]');
        
        logoutLinks.forEach(link => {
            link.addEventListener('click', function() {
                clearAllSurveyData();
            });
        });

        // Also check for logout action in the URL
        if (window.location.href.includes('action=logout')) {
            clearAllSurveyData();
        }
    }

    /**
     * Initialize on page load
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initFormPersistence();
            monitorAjaxSubmissions();
            setupLogoutHandler();
        });
    } else {
        initFormPersistence();
        monitorAjaxSubmissions();
        setupLogoutHandler();
    }

    // Expose functions globally for manual control if needed
    window.SurveyPersistence = {
        save: saveFormData,
        restore: restoreFormData,
        clear: clearFormData,
        clearAll: clearAllSurveyData
    };

})();
