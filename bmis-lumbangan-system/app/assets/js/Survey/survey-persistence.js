/**
 * Survey Form Persistence with localStorage (per-person)
 * Automatically saves form data as user types and restores it when they return.
 * Per-person keys prevent leakage across accounts.
 *
 * To use: ensure your page injects window.CURRENT_PERSON_ID (person id from server)
 * and window.BASE_URL / window.SURVEY_API (see footer-resident.php).
 */
(function() {
    'use strict';

    const STORAGE_PREFIX = 'survey_';
    const STORAGE_KEY_LIST = 'survey_form_keys'; // list of form identifiers stored (global)

    // Use server-provided person id to namespace keys, fallback to 'anon'
    const CURRENT_PERSON_ID = (typeof window.CURRENT_PERSON_ID !== 'undefined' && window.CURRENT_PERSON_ID !== null) ? String(window.CURRENT_PERSON_ID) : 'anon';
    const PERSON_PREFIX = STORAGE_PREFIX + 'person_' + CURRENT_PERSON_ID + '_';

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

    function getStorageKey(formId) {
        return PERSON_PREFIX + (formId || 'unknown');
    }

    function trackFormKey(formId) {
        // Track by person to avoid mixing keys across users
        const ksKey = STORAGE_KEY_LIST + ':' + CURRENT_PERSON_ID;
        const keys = JSON.parse(localStorage.getItem(ksKey) || '[]');
        if (!keys.includes(formId)) {
            keys.push(formId);
            localStorage.setItem(ksKey, JSON.stringify(keys));
        }
    }

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
            if (element.hasAttribute('data-persist-skip')) continue;

            if (name === 'birthdate') {
                try {
                    let bdVal = '';
                    if (element._flatpickr && Array.isArray(element._flatpickr.selectedDates) && element._flatpickr.selectedDates.length) {
                        bdVal = element._flatpickr.formatDate(element._flatpickr.selectedDates[0], 'Y-m-d');
                    } else if (element.value) {
                        bdVal = element.value;
                    } else if (element._flatpickr && element._flatpickr.altInput && element._flatpickr.altInput.value) {
                        try {
                            const parsed = window.flatpickr.parseDate(element._flatpickr.altInput.value, element._flatpickr.config.altFormat || 'F j, Y');
                            if (parsed) bdVal = window.flatpickr.formatDate(parsed, 'Y-m-d');
                        } catch(e){ bdVal = element._flatpickr.altInput.value || ''; }
                    }
                    if (bdVal !== '') {
                        formData[name] = bdVal;
                        savedCount++;
                        continue;
                    }
                } catch(e){}
            }

            switch (element.type) {
                case 'checkbox':
                    formData[name] = element.checked;
                    savedCount++;
                    break;
                case 'radio':
                    if (element.checked) { formData[name] = element.value; savedCount++; }
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
        trackFormKey(formId);
        console.log('Survey Persistence: Saved', savedCount, 'fields for:', formId, 'key=', storageKey);
    }

    function restoreFormData(form) {
        const formId = getFormIdentifier();
        if (!formId) {
            console.warn('Survey Persistence: Could not identify form');
            return;
        }
        const storageKey = getStorageKey(formId);
        const savedData = localStorage.getItem(storageKey);
        if (!savedData) {
            console.log('Survey Persistence: No saved data for:', formId);
            return;
        }
        try {
            const formData = JSON.parse(savedData);
            console.log('Survey Persistence: Restoring', Object.keys(formData).length, 'fields for', formId);
            const elements = form.elements;
            let restoredCount = 0;
            for (let i = 0; i < elements.length; i++) {
                const element = elements[i];
                const name = element.name;
                if (!name || !(name in formData)) continue;
                if (element.hasAttribute('data-persist-skip')) continue;
                const currentVal = (element.type === 'checkbox') ? element.checked : (element.value || '');
                const savedVal = formData[name];
                // If page already rendered DB value, prefer it (don't overwrite)
                if (currentVal !== '' && currentVal !== false && currentVal !== null) {
                    continue;
                }
                switch (element.type) {
                    case 'checkbox':
                        element.checked = !!savedVal;
                        restoredCount++;
                        element.dispatchEvent(new Event('change', { bubbles: true }));
                        break;
                    case 'radio':
                        if (element.value === savedVal) { element.checked = true; restoredCount++; element.dispatchEvent(new Event('change', { bubbles: true })); }
                        break;
                    case 'range':
                    case 'text':
                    case 'number':
                    case 'email':
                    case 'tel':
                    case 'date':
                        if (element._flatpickr && savedVal) {
                            try { element._flatpickr.setDate(savedVal, true); } catch(e){ element.value = savedVal; }
                        } else {
                            element.value = savedVal;
                        }
                        if (name === 'birthdate') {
                            try { element.value = savedVal; if (element._flatpickr) { element._flatpickr.setDate(savedVal, true); } } catch(e){}
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
            console.log('Survey Persistence: Restored', restoredCount, 'fields for:', formId);
        } catch (e) {
            console.error('Survey Persistence: Error parsing saved data', e);
        }
    }

    function clearFormData(formId) {
        const storageKey = getStorageKey(formId);
        localStorage.removeItem(storageKey);
        // Remove from the tracked list
        const ksKey = STORAGE_KEY_LIST + ':' + CURRENT_PERSON_ID;
        const keys = JSON.parse(localStorage.getItem(ksKey) || '[]').filter(k => k !== formId);
        localStorage.setItem(ksKey, JSON.stringify(keys));
        console.log('Survey Persistence: Cleared data for:', formId);
    }

    function clearAllSurveyDataForPerson() {
        const ksKey = STORAGE_KEY_LIST + ':' + CURRENT_PERSON_ID;
        const keys = JSON.parse(localStorage.getItem(ksKey) || '[]');
        keys.forEach(fid => {
            const sk = getStorageKey(fid);
            localStorage.removeItem(sk);
        });
        localStorage.removeItem(ksKey);
        console.log('Survey Persistence: Cleared all survey data for person:', CURRENT_PERSON_ID);
    }

    function initFormPersistence() {
        let surveyForms = document.querySelectorAll('form[id^="form-"]');
        if (surveyForms.length === 0) {
            surveyForms = document.querySelectorAll('form');
        }
        if (surveyForms.length === 0) {
            console.warn('Survey Persistence: No forms found on page');
            return;
        }
        surveyForms.forEach(form => {
            restoreFormData(form);
            setTimeout(() => { try { restoreFormData(form); } catch(e){} }, 300);
            let saveTimeout;
            form.addEventListener('input', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => saveFormData(form), 500);
            });
            form.addEventListener('change', function() { saveFormData(form); });
            form.addEventListener('submit', function() {
                setTimeout(() => {
                    const formId = getFormIdentifier();
                    if (formId) clearFormData(formId);
                }, 1000);
            });
        });
    }

    function monitorAjaxSubmissions() {
        window.addEventListener('surveyFormSaved', function(e) {
            const formId = e.detail?.formId || getFormIdentifier();
            if (formId) clearFormData(formId);
        });
    }

    function setupLogoutHandler() {
        const logoutLinks = document.querySelectorAll('a[href*="logout"], button[onclick*="logout"]');
        logoutLinks.forEach(link => {
            link.addEventListener('click', function() {
                clearAllSurveyDataForPerson();
            });
        });
        if (window.location.href.includes('action=logout')) {
            clearAllSurveyDataForPerson();
        }
    }

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

    window.SurveyPersistence = {
        save: saveFormData,
        restore: restoreFormData,
        clear: clearFormData,
        clearAllForPerson: clearAllSurveyDataForPerson
    };

})();