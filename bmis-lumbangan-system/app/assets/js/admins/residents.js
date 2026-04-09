document.addEventListener('DOMContentLoaded', function(){
    const nameInput = document.getElementById('residentNameSearch');
    const purokSelect = document.getElementById('residentPurokFilter');
    const items = Array.from(document.querySelectorAll('#residentsAccordion .accordion-item'));
    const countElPage = document.getElementById('residentsCountPage');
    const totalEl = document.getElementById('residentsTotal');

    function normalize(s){ return (s||'').toString().toLowerCase(); }

    function applyFilters(){
        const q = normalize(nameInput && nameInput.value);
        const purok = normalize(purokSelect && purokSelect.value);
        let visible = 0;
        items.forEach(function(item){
            const headerText = normalize(item.querySelector('.accordion-header').textContent);
            const detailsText = normalize(item.querySelector('.resident-details').textContent);
            const matchesName = !q || headerText.indexOf(q) !== -1 || detailsText.indexOf(q) !== -1;
            const matchesPurok = !purok || headerText.indexOf(purok) !== -1 || detailsText.indexOf(purok) !== -1;
            if (matchesName && matchesPurok) { item.style.display = ''; visible++; }
            else { item.style.display = 'none'; }
        });
        if (countElPage) countElPage.textContent = visible;
        // total stays static; ensure it's populated if present
        if (totalEl && totalEl.textContent.trim() === '') totalEl.textContent = items.length;
    }

    if (nameInput) nameInput.addEventListener('input', applyFilters);
    if (purokSelect) purokSelect.addEventListener('change', applyFilters);
    applyFilters();

    // Modal behavior: expects `window.assessmentsByPerson` to be set in the page
    const modalEl = document.getElementById('assessmentsModal');
    const modalBody = document.getElementById('assessmentsModalBody');
    const modalTitle = document.getElementById('assessmentsModalLabel');
    const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    function formatDate(val){
        if (!val) return 'N/A';
        try {
            const d = new Date(val);
            return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: '2-digit' });
        } catch(e){ return val; }
    }

    function formatDateTime(val){
        if (!val) return 'N/A';
        try {
            const d = new Date(val);
            return d.toLocaleString(undefined, { year: 'numeric', month: 'long', day: '2-digit', hour: 'numeric', minute: '2-digit', second: '2-digit' });
        } catch(e){ return val; }
    }

    document.body.addEventListener('click', function(e){
        const btn = e.target.closest('.btn-show-assessments');
        if (!btn) return;
        const pid = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name') || 'Person';
        const list = (window.assessmentsByPerson && window.assessmentsByPerson[pid]) ? window.assessmentsByPerson[pid] : [];
        modalTitle.textContent = name + ' — CVD/NCD Assessments (' + list.length + ')';
        if (!list.length) {
            modalBody.innerHTML = '<div class="alert alert-info">No assessments found for this person.</div>';
        } else {
            // build improved card-like list for modal using a grid of fields with icons
            let html = '<div class="assessments-list">';
            list.forEach(function(a){
                const headerDate = a.survey_date || a.answered_at || '';
                html += '<div class="assessment-card p-3 mb-3">';
                html += '<div class="d-flex justify-content-between align-items-start mb-2">';
                html += '<div class="assessment-title"><strong>' + (headerDate ? formatDate(headerDate) : 'Assessment') + '</strong></div>';
                // determine approval badge and whether to show View Assessment button
                const approvedBadge = a.is_approved ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-secondary">Not approved</span>';
                // check if assessment is in current month and not approved
                let showViewBtn = false;
                if (!a.is_approved) {
                    const dateStr = a.answered_at || a.survey_date || a.answered_at || '';
                    try {
                        if (dateStr) {
                            const d = new Date(dateStr);
                            const now = new Date();
                            if (!isNaN(d.getTime()) && d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth()) {
                                showViewBtn = true;
                            }
                        }
                    } catch (e) { /* ignore parse errors */ }
                }
                html += '<div class="assessment-header d-flex align-items-start gap-2">' + approvedBadge + '</div>';
                html += '</div>';

                html += '<div class="assessment-grid">';
                // Answered at (calendar icon)
                html += '<div class="assessment-field"><div class="field-label"><span class="field-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 10h5v5H7z" opacity=".0001"/><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 14H5V9h14v9z" fill="#1e3a5f"/></svg></span>Answered at</div><div class="field-value">' + (a.answered_at ? formatDateTime(a.answered_at) : 'N/A') + '</div></div>';
                // Approved at (check icon)
                html += '<div class="assessment-field"><div class="field-label"><span class="field-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></span>Approved at</div><div class="field-value">' + (a.approved_at ? formatDateTime(a.approved_at) : 'Not approved') + '</div></div>';
                // Surveyed by (user icon)
                html += '<div class="assessment-field"><div class="field-label"><span class="field-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5z"/></svg></span>Surveyed by</div><div class="field-value">' + (a.surveyed_by_official_name || 'N/A') + '</div></div>';
                // Review notes only (span full width)
                html += '<div class="assessment-field" style="grid-column:1 / -1"><div class="field-label"><span class="field-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M2 6v12h20V6H2zm2 2l8 5 8-5v8H4V8z"/></svg></span>Review notes</div><div class="field-value">' + (a.review_notes ? a.review_notes : '—') + '</div></div>';
                html += '</div>';

                // inject bottom-right action (View Assessment) inside the card when needed
                if (showViewBtn) {
                    const aid = (a.id || a.cvd_id || '');
                    html += '<div class="assessment-card-action"><button type="button" class="btn btn-sm btn-view-full-assessment" data-pid="' + (pid || '') + '" data-aid="' + aid + '">View Assessment</button></div>';
                }

                html += '</div>';
            });
            html += '</div>';
            modalBody.innerHTML = html;
        }
        if (bsModal) bsModal.show();
    });

    // Full assessment (all sections) viewer
    const fullModalEl = document.getElementById('fullAssessmentModal');
    const bsFullModal = fullModalEl ? new bootstrap.Modal(fullModalEl) : null;

    // Inject small CSS for icons, pagination and animations
    (function injectFAStyles(){
        // Use the theme variables defined in secDash.css where possible (e.g. --primary-blue)
        const css = `
        .field-icon { display:inline-block; width:26px; text-align:center; margin-right:.6rem; vertical-align:middle; }
        .field-icon i { color: var(--primary-blue, #1e3a5f) !important; font-size:20px; }
        .section-card { background:#fff; border:1px solid rgba(30,58,95,0.06); border-radius:8px; }
        .section-card h6 { font-size:1.08rem; font-weight:700; color:var(--primary-blue); margin-bottom:.6rem; }
        .subsection-title { color:var(--dark-text, #495057); font-weight:700; font-size:1rem; margin-bottom:.5rem; }
        .field-label { display:flex; align-items:center; gap:.35rem; font-size:1.05rem; font-weight:700; color:var(--primary-blue); }
        .field-value { font-size:1rem; color: #2d3748; margin-top:.25rem; }
        .fa-page { display:block; opacity:1; transform:none; transition:opacity .28s ease, transform .28s ease; }
        .fa-page.hidden { opacity:0; transform:translateX(12px) scale(.99); pointer-events:none; height:0; overflow:hidden; }
        #fa-prev, #fa-next { min-width:96px; border-radius:8px; padding:8px 12px; font-weight:600; font-size:0.95rem; }
        #fa-prev { border:1px solid rgba(30,58,95,0.08); background:transparent; color:var(--dark-text); }
        #fa-next { background:linear-gradient(180deg,var(--primary-blue),var(--secondary-blue)); color:#fff; border:1px solid rgba(0,0,0,0.04); }
        #fa-prev:disabled, #fa-next:disabled { opacity:.5; cursor:not-allowed; }
        .fa-page-indicator { font-weight:700; color:var(--dark-text); background:transparent; padding:6px 12px; border-radius:8px; border:1px solid rgba(0,0,0,0.04); font-size:0.95rem; }
        @media (prefers-reduced-motion: reduce){ .fa-page, .fa-page * { transition:none !important; } }
        `;
        const s = document.createElement('style'); s.type = 'text/css'; s.appendChild(document.createTextNode(css));
        document.head.appendChild(s);
    })();

    function renderSectionFields(sectionDef, data){
        // sectionDef: { key, title, subsections }
        // data: object returned from API (nested: assessment, person, vitals, lifestyle...)
        let html = '<div class="section-card p-3 mb-3">';
        html += '<h6 class="mb-2">' + (sectionDef.title || '') + '</h6>';

        // Font Awesome icon map for fields
        const ICON_FA = {
            first_name: '<i class="fas fa-user fa-fw"></i>',
            middle_name: '<i class="fas fa-user fa-fw"></i>',
            last_name: '<i class="fas fa-user fa-fw"></i>',
            suffix: '<i class="fas fa-certificate fa-fw"></i>',
            sex: '<i class="fas fa-venus-mars fa-fw"></i>',
            birthdate: '<i class="fas fa-calendar-alt fa-fw"></i>',
            marital_status: '<i class="fas fa-ring fa-fw"></i>',
            is_head: '<i class="fas fa-home fa-fw"></i>',
            age: '<i class="fas fa-birthday-cake fa-fw"></i>',
            contact_no: '<i class="fas fa-phone-alt fa-fw"></i>',
            highest_educ_attainment: '<i class="fas fa-graduation-cap fa-fw"></i>',
            religion: '<i class="fas fa-praying-hands fa-fw"></i>',
            occupation: '<i class="fas fa-briefcase fa-fw"></i>',
            blood_type: '<i class="fas fa-tint fa-fw"></i>',
            bp_systolic: '<i class="fas fa-tachometer-alt fa-fw"></i>',
            bp_diastolic: '<i class="fas fa-tachometer-alt fa-fw"></i>',
            pulse: '<i class="fas fa-heartbeat fa-fw"></i>',
            respiratory_rate: '<i class="fas fa-lungs fa-fw"></i>',
            temperature_c: '<i class="fas fa-thermometer-half fa-fw"></i>',
            disability: '<i class="fas fa-wheelchair fa-fw"></i>',
            height_cm: '<i class="fas fa-ruler-vertical fa-fw"></i>',
            weight_kg: '<i class="fas fa-weight-scale fa-fw"></i>',
            waist_circumference_cm: '<i class="fas fa-tape fa-fw"></i>',
            // family history icons
            hypertension: '<i class="fas fa-heart fa-fw"></i>',
            heart_attack: '<i class="fas fa-heart-crack fa-fw"></i>',
            diabetes: '<i class="fas fa-syringe fa-fw"></i>',
            kidney_disease: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="fa-fw" style="vertical-align:middle"><path d="M12 2c2 0 4 1 5 3 1 2 1 4 0 6-1 2-3 4-5 6s-4 3-6 2c-2-1-3-3-3-5 0-2 1-4 2-6C6 8 8 6 12 2z" fill="#1e3a5f"/></svg>',
            stroke: '<i class="fas fa-brain fa-fw"></i>',
            cancer: '<i class="fas fa-ribbon fa-fw"></i>',
            asthma: '<i class="fas fa-lungs fa-fw"></i>'
        };

        ICON_FA.is_approved = '<i class="fas fa-check-circle fa-fw"></i>';
        ICON_FA.review_notes = '<i class="fas fa-sticky-note fa-fw"></i>';

        // add lifestyle-specific icons
        ICON_FA.smoking_status = '<i class="fas fa-smoking fa-fw"></i>';
        ICON_FA.alcohol_use = '<i class="fas fa-beer fa-fw"></i>';
        ICON_FA.excessive_alcohol = '<i class="fas fa-exclamation-triangle fa-fw"></i>';
        ICON_FA.eats_processed_weekly = '<i class="fas fa-hamburger fa-fw"></i>';
        ICON_FA.fruits_3_servings_daily = '<i class="fas fa-apple-alt fa-fw"></i>';
        ICON_FA.vegetables_3_servings_daily = '<i class="fas fa-carrot fa-fw"></i>';
        ICON_FA.exercise_days_per_week = '<i class="fas fa-calendar-day fa-fw"></i>';
        ICON_FA.exercise_minutes_per_day = '<i class="fas fa-stopwatch fa-fw"></i>';
        ICON_FA.exercise_intensity = '<i class="fas fa-running fa-fw"></i>';

        // Determine the data source object for this section
        function sourceForSection(sectionKey){
            if (!data) return {};
            switch (sectionKey) {
                case 'personal': return data.person || data;
                case 'assessment': return data.assessment || data;
                case 'vitals': return data.vitals || data;
                case 'lifestyle': return data.lifestyle || data;
                case 'angina': return data.angina || data;
                case 'diabetes': return data.diabetes || data;
                case 'family_history': return data.family_history || data;
                case 'household': return data.household || data;
                case 'family': {
                    const fam = data.family || [];
                    let text = '';
                    try {
                        if (Array.isArray(fam)) {
                            text = fam.filter(Boolean).map(function(f){ return f.full_name || ((f.first_name||'') + ' ' + (f.last_name||'')).trim(); }).join(', ');
                        } else if (fam && typeof fam === 'object') {
                            text = fam.full_name || ((fam.first_name||'') + ' ' + (fam.last_name||'')).trim();
                        }
                    } catch(e){ text = ''; }
                    return {'family_list': text};
                }
                default: return data || {};
            }
        }

        // helper to render an array of fields into the html buffer (editable inputs)
        function renderFieldsArray(fields, sectionKey){
            const src = sourceForSection(sectionKey);
            let out = '';
            (fields || []).forEach(function(f){
                // read value from nested source first, then fallback to root-level key
                const rawVal = (src && src[f.key] !== undefined && src[f.key] !== null) ? src[f.key] : (data && data[f.key] !== undefined ? data[f.key] : '');

                const val = (rawVal === null || rawVal === undefined) ? '' : String(rawVal);
                const icon = ICON_FA[f.key] || '<i class="fas fa-circle fa-fw"></i>';

                // pick input type with special cases for birthdate, age, radios, and family history
                let inputHtml = '';
                const textareaKeys = ['review_notes','notes','smoking_comments','alcohol_notes','family_list'];
                const numericPattern = /(_cm|_kg|bp_|bp|systolic|diastolic|pulse|temperature|respiratory|rbs|fbs|hba1c|length_of_residency|days|minutes|percent|_mg_dl|_percent|waist|bmi)/i;
                if (f.key === 'birthdate') {
                    // visible formatted text input plus hidden ISO date input
                    const isoVal = (val && !isNaN(Date.parse(val))) ? (new Date(val)).toISOString().slice(0,10) : '';
                    inputHtml = '<div class="d-flex gap-2 align-items-center">'
                        + '<input class="form-control fa-input birthdate-visible" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="text" value="'+escapeHtml(val)+'" placeholder="e.g. January 01, 2000">'
                        + '<input class="form-control birthdate-iso visually-hidden" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="date" value="'+escapeHtml(isoVal)+'">'
                        + '</div>';
                } else if (f.key === 'age') {
                    inputHtml = '<input class="form-control fa-input age-input" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="number" value="'+escapeHtml(val)+'" readonly>';
                } else if (f.key === 'sex') {
                    // radio inputs for sex (Male / Female only)
                    const opts = [{v:'M',l:'Male'},{v:'F',l:'Female'}];
                    inputHtml = '<div class="d-flex gap-2">';
                    opts.forEach(function(o){
                        const checked = (String(val) === String(o.v)) ? ' checked' : '';
                        inputHtml += '<div class="form-check form-check-inline"><input class="form-check-input fa-input" name="sex_radio_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="radio" value="'+escapeHtml(o.v)+'"'+checked+'> <label class="form-check-label">'+escapeHtml(o.l)+'</label></div>';
                    });
                    inputHtml += '</div>';
                } else if (f.key === 'is_head') {
                    // is_head: yes/no radios
                    const checkedYes = (String(val) === '1' || String(val).toLowerCase() === 'yes' || String(val).toLowerCase() === 'true') ? ' checked' : '';
                    const checkedNo = (!checkedYes) ? ' checked' : '';
                    inputHtml = '<div class="d-flex gap-2">'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="is_head_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="is_head" value="1"'+checkedYes+'> <label class="form-check-label">Yes</label></div>'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="is_head_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="is_head" value="0"'+checkedNo+'> <label class="form-check-label">No</label></div>'
                        + '</div>';
                } else if (f.key === 'is_approved') {
                    // is_approved: yes/no radios for officials
                    const checkedYesA = (String(val) === '1' || String(val).toLowerCase() === 'yes' || String(val).toLowerCase() === 'true') ? ' checked' : '';
                    const checkedNoA = (!checkedYesA) ? ' checked' : '';
                    inputHtml = '<div class="d-flex gap-2">'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="is_approved_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="is_approved" value="1"'+checkedYesA+'> <label class="form-check-label">Yes</label></div>'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="is_approved_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="is_approved" value="0"'+checkedNoA+'> <label class="form-check-label">No</label></div>'
                        + '</div>';
                } else if (['hypertension','heart_attack','diabetes','kidney_disease','stroke','asthma','cancer','family_diabetes'].indexOf(f.key) !== -1) {
                    // family history yes/no radios
                    const checkedY = (String(val) === '1' || String(val).toLowerCase() === 'yes' || String(val).toLowerCase() === 'true') ? ' checked' : '';
                    const checkedN = (!checkedY) ? ' checked' : '';
                    inputHtml = '<div class="d-flex gap-2">'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="fh_'+escapeHtml(f.key)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" value="1"'+checkedY+'> <label class="form-check-label">Yes</label></div>'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="fh_'+escapeHtml(f.key)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" value="0"'+checkedN+'> <label class="form-check-label">No</label></div>'
                        + '</div>';
                } else if (textareaKeys.indexOf(f.key) !== -1) {
                    inputHtml = '<textarea class="form-control fa-input" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" rows="3">'+escapeHtml(val)+'</textarea>';
                } else if (f.key === 'smoking_status') {
                    // explicit smoking status options rendered as radio buttons with icons
                    const opts = [
                        {v:'never', l:'Never Smoked', i: ICON_FA.smoking_status},
                        {v:'stopped_gt1', l:'Stopped (>1 year)', i: ICON_FA.smoking_status},
                        {v:'current', l:'Current Smoker', i: ICON_FA.smoking_status},
                        {v:'stopped_lt1', l:'Stopped (<1 year)', i: ICON_FA.smoking_status},
                        {v:'passive', l:'Passive Smoker', i: ICON_FA.smoking_status}
                    ];

                // Add Review & Approval as a final section for officials
                SURVEY_SECTIONS.push({
                    key: 'assessment',
                    title: 'Review & Approval',
                    subsections: [
                        { title: 'Approval', fields: [
                            { key: 'is_approved', label: 'Approve assessment?' }
                        ]},
                        { title: 'Review Notes', fields: [
                            { key: 'review_notes', label: 'Review notes' }
                        ]}
                    ]
                });
                    inputHtml = '<div class="d-flex flex-column gap-2">';
                    opts.forEach(function(o){
                        const checked = (String(val) === String(o.v)) ? ' checked' : '';
                        // make the radio visible and clickable; input precedes label text for accessibility
                        inputHtml += '<label class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 p-2">'
                            + '<input class="form-check-input fa-input me-2" type="radio" name="smoke_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" value="'+escapeHtml(o.v)+'"'+checked+'> '
                            + o.i + '<span>'+escapeHtml(o.l)+'</span></label>';
                    });
                    inputHtml += '</div>';
                } else if (f.key === 'alcohol_use') {
                    const opts = [ {v:'never',l:'Never'},{v:'current',l:'Current'},{v:'former',l:'Former'} ];
                    inputHtml = '<div class="d-flex gap-2">';
                    opts.forEach(function(o){
                        const checked = (String(val) === String(o.v)) ? ' checked' : '';
                        inputHtml += '<div class="form-check form-check-inline"><input class="form-check-input fa-input" name="alcohol_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="radio" value="'+escapeHtml(o.v)+'"'+checked+'> <label class="form-check-label">'+escapeHtml(o.l)+'</label></div>';
                    });
                    inputHtml += '</div>';
                } else if (['excessive_alcohol','eats_processed_weekly','fruits_3_servings_daily','vegetables_3_servings_daily'].indexOf(f.key) !== -1) {
                    // yes/no radios for dietary and excessive alcohol consumption
                    const checkedY = (String(val) === '1' || String(val).toLowerCase() === 'yes' || String(val).toLowerCase() === 'true') ? ' checked' : '';
                    const checkedN = (!checkedY) ? ' checked' : '';
                    inputHtml = '<div class="d-flex gap-2">'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="yn_'+escapeHtml(f.key)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" value="1"'+checkedY+'> <label class="form-check-label">Yes</label></div>'
                        + '<div class="form-check form-check-inline"><input class="form-check-input fa-input" type="radio" name="yn_'+escapeHtml(f.key)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" value="0"'+checkedN+'> <label class="form-check-label">No</label></div>'
                        + '</div>';
                } else if (f.key === 'exercise_days_per_week') {
                    // range slider 0-7 days
                    const vnum = val !== '' ? Number(val) : 0;
                    inputHtml = '<div class="d-flex align-items-center gap-2">'
                        + '<input class="form-range fa-input" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="range" min="0" max="7" step="1" value="'+escapeHtml(String(vnum))+'">'
                        + '<div class="ms-2"><span class="range-value">'+escapeHtml(String(vnum))+'</span> days</div>'
                        + '</div>';
                } else if (f.key === 'exercise_minutes_per_day') {
                    // range slider 0-300 minutes
                    const vnum2 = val !== '' ? Number(val) : 0;
                    inputHtml = '<div class="d-flex align-items-center gap-2">'
                        + '<input class="form-range fa-input" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="range" min="0" max="300" step="5" value="'+escapeHtml(String(vnum2))+'">'
                        + '<div class="ms-2"><span class="range-value">'+escapeHtml(String(vnum2))+'</span> minutes</div>'
                        + '</div>';
                } else if (f.key === 'exercise_intensity') {
                    const opts = [{v:'light',l:'Light'},{v:'moderate',l:'Moderate'},{v:'vigorous',l:'Vigorous'}];
                    inputHtml = '<div class="d-flex gap-2">';
                    opts.forEach(function(o){
                        const checked = (String(val) === String(o.v)) ? ' checked' : '';
                        inputHtml += '<div class="form-check form-check-inline"><input class="form-check-input fa-input" name="intensity_'+escapeHtml(sectionKey)+'" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="radio" value="'+escapeHtml(o.v)+'"'+checked+'> <label class="form-check-label">'+escapeHtml(o.l)+'</label></div>';
                    });
                    inputHtml += '</div>';
                } else if (numericPattern.test(f.key)) {
                    inputHtml = '<input class="form-control fa-input" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="number" step="any" value="'+escapeHtml(val)+'">';
                } else {
                    inputHtml = '<input class="form-control fa-input" data-section="'+escapeHtml(sectionKey)+'" data-key="'+escapeHtml(f.key)+'" type="text" value="'+escapeHtml(val)+'">';
                }

                out += '<div class="col-12 col-md-6">';
                out += '<div class="p-2">';
                out += '<div class="field-label">' + '<span class="field-icon" aria-hidden="true">' + icon + '</span>' + '<span>' + (f.label || f.key) + '</span>' + '</div>';
                out += '<div class="field-value mt-2">' + inputHtml + '</div>';
                out += '</div>';
                out += '</div>';
            });
            return out;
        }

        // if subsections exist (preferred), render them with a small subtitle
        if (sectionDef.subsections && Array.isArray(sectionDef.subsections) && sectionDef.subsections.length){
            sectionDef.subsections.forEach(function(sub){
                html += '<div class="subsection mb-3">';
                html += '<h6 class="subsection-title small text-muted mb-2">' + (sub.title ? escapeHtml(sub.title) : '') + '</h6>';
                html += '<div class="row g-2">';
                html += renderFieldsArray(sub.fields || [], sectionDef.key || '');
                html += '</div>';
                html += '</div>';
            });
        } else {
            // fallback to old single fields array
            html += '<div class="row g-2">';
            html += renderFieldsArray(sectionDef.fields || [], sectionDef.key || '');
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function escapeHtml(s){
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Sections definition: reorganized into subsections per user request.
    const SURVEY_SECTIONS = [
        {
            key: 'personal',
            title: 'Personal Information',
            subsections: [
                { title: 'Identity', fields: [
                    {key:'first_name', label:'First Name'},
                    {key:'middle_name', label:'Middle Name'},
                    {key:'last_name', label:'Last Name'},
                    {key:'suffix', label:'Suffix'}
                ]},
                { title: 'Demographics', fields: [
                    {key:'sex', label:'Sex'},
                    {key:'birthdate', label:'Birthdate'},
                    {key:'marital_status', label:'Civil Status'},
                    {key:'is_head', label:'Head of the Family?'},
                    {key:'age', label:'Age'}
                ]},
                { title: 'Contact & Socio', fields: [
                    {key:'contact_no', label:'Contact No.'},
                    {key:'highest_educ_attainment', label:'Educational Attainment'},
                    {key:'religion', label:'Religion'},
                    {key:'occupation', label:'Occupation'}
                ]},
                { title: 'Health & Biometrics', fields: [
                    {key:'blood_type', label:'Blood Type'},
                    {key:'disability', label:'Disability (if any)'},
                    {key:'height_cm', label:'Height'},
                    {key:'weight_kg', label:'Weight'},
                    {key:'waist_circumference_cm', label:'Waist Circumference'}
                ]}
            ]
        },
        {
            key: 'vitals',
            title: 'Vitals Information',
            subsections: [
                { title: 'Blood Pressure', fields: [
                    {key:'bp_systolic', label:'Systolic (mmHg)'},
                    {key:'bp_diastolic', label:'Diastolic (mmHg)'}
                ]},
                { title: 'Pulse Rate', fields: [
                    {key:'pulse', label:'Beats per Minute (bpm)'}
                ]},
                { title: 'Respiratory Rate', fields: [
                    {key:'respiratory_rate', label:'Respiratory Rate'}
                ]},
                { title: 'Body Temperature', fields: [
                    {key:'temperature_c', label:'Temperature (°C)'}
                ]}
            ]
        },
        { key: 'family_history', title: 'Family History Information', fields: [
            {key:'hypertension', label:'Hypertension?'},
            {key:'heart_attack', label:'Heart Attack?'},
            {key:'diabetes', label:'Diabetes?'},
            {key:'kidney_disease', label:'Kidney Disease?'},
            {key:'stroke', label:'Stroke?'},
            {key:'asthma', label:'Asthma?'},
            {key:'cancer', label:'Cancer?'}
        ]},
        { key: 'family', title: 'Family Members List', fields: [
            {key:'family_list', label:'Family Members'}
        ]},
        {
            key: 'lifestyle',
            title: 'Lifestyle Information',
            subsections: [
                { title: 'Smoking Status', fields: [
                    {key:'smoking_status', label:'Current Smoking Status'},
                    {key:'smoking_comments', label:'Smoking Comments'}
                ]},
                { title: 'Alcohol Consumption', fields: [
                    {key:'alcohol_use', label:'Alcohol Use Status'},
                    {key:'excessive_alcohol', label:'Excessive Alcohol Consumption?'},
                    {key:'alcohol_notes', label:'Alcohol Notes'}
                ]},
                { title: 'Dietary Habits', fields: [
                    {key:'eats_processed_weekly', label:'Do you eat processed foods weekly?'},
                    {key:'fruits_3_servings_daily', label:'Do you eat 3+ servings of fruits daily?'},
                    {key:'vegetables_3_servings_daily', label:'Do you eat 3+ servings of vegetables daily?'}
                ]},
                { title: 'Physical Activity', fields: [
                    {key:'exercise_days_per_week', label:'Exercise Days per Week'},
                    {key:'exercise_minutes_per_day', label:'Exercise Minutes per Day'},
                    {key:'exercise_intensity', label:'Exercise Intensity'}
                ]}
            ]
        },
        { key: 'angina', title: 'Angina Information', fields: [
            {key:'chest_tightness', label:'Chest Tightness'},
            {key:'chest_pain', label:'Chest Pain'},
            {key:'pain_during_exertion', label:'Pain During Exertion'},
            {key:'no_pain__when_taking_nitroglycerin', label:'Nitroglycerin Relief'},
            {key:'pain_last_minutes', label:'Pain Last for More than 10 Minutes'},
            {key:'chest_pain', label:'Front Chest Pain Lasting for Half an Hour'}
        ]},
        {
            key: 'diabetes',
            title: 'Diabetes Information',
            subsections: [
                { title: 'Medical History', fields: [
                    {key:'has_diabetes', label:'Has diabetes'},
                    {key:'family_diabetes', label:'Family has History of Diabetes'},
                    {key:'diabetes_med', label:'Diabetes medication'}
                ]},
                { title: 'Symptoms (3 P\'s)', fields: [
                    {key:'frequent_urination', label:'Frequent Urination'},
                    {key:'extreme_hunger', label:'Extreme Hunger'},
                    {key:'extreme_thirst', label:'Extreme Thirst'},
                    {key:'unexplained_weight_loss', label:'Unexplained Weight Loss'}
                ]},
                { title: 'Laboratory Results', fields: [
                    {key:'random_blood_sugar', label:'Random Blood Sugar (mg/dL)'},
                    {key:'fasting_blood_sugar', label:'Fasting Blood Sugar (mg/dL)'},
                    {key:'hba1c', label:'HbA1c (%)'}
                ]},
                { title: 'Urine Test', fields: [
                    {key:'ketones_in_urine', label:'Ketones in Urine'},
                    {key:'protein_in_urine', label:'Protein in Urine'}
                ]}
            ]
        },
        {
            key: 'household',
            title: 'Household Information',
            subsections: [
                { title: 'Address Information', fields: [
                    {key:'purok_sitio', label:'Purok/Sitio'},
                    {key:'household_no', label:'Household No.'},
                    {key:'house_block', label:'House No./Block & Lot'},
                    {key:'street', label:'Street Name'},
                    {key:'subdivision_compound', label:'Subdivision/Compound'},
                    {key:'building_apartment', label:'Building/Apartment Name'}
                ]},
                { title: 'Home Ownership & Construction', fields: [
                    {key:'home_ownership', label:'Home Owenership'},
                    {key:'construction_material', label:'Construction Material'}
                ]},
                { title: 'Facilities & Utilities', fields: [
                    {key:'lighting_facility', label:'Lighting Facility'},
                    {key:'toilet_type', label:'Toilet Type'}
                ]},
                { title: 'Water Source & Storage', fields: [
                    {key:'water_level', label:'Water Level'},
                    {key:'water_source', label:'Water Source'},
                    {key:'water_storage', label:'Water Storage'},
                    {key:'other_drinking_water_source', label:'Other Drinking Water Source'}
                ]},
                { title: 'Garbage Disposal', fields: [
                    {key:'garbage_container', label:'Garbage Container'},
                    {key:'garbage_segregated', label:'Garbage Segregated?'},
                    {key:'disposal_method', label:'Disposal Method'}
                ]},
                { title: 'Family Information', fields: [
                    {key:'family_number', label:'Family Number'},
                    {key:'residency_status', label:'Residency Status'},
                    {key:'length_of_residency_months', label:'Length of Residency (Months)'},
                    {key:'email_address', label:'Email Address'}
                ]}
            ]
        }
    ];

    document.body.addEventListener('click', function(e){
        const btnFull = e.target.closest('.btn-view-full-assessment');
        if (!btnFull) return;
        const pid = btnFull.getAttribute('data-pid');
        const aid = btnFull.getAttribute('data-aid');

        // helper to derive base url for API calls (tries to detect BASE_URL from script tag)
        function getBaseUrl(){
            if (window.BASE_URL) return window.BASE_URL;
            const scripts = Array.from(document.getElementsByTagName('script'));
            const me = scripts.find(s => (s.src || '').indexOf('assets/js/admins/residents.js') !== -1);
            if (me && me.src) {
                const idx = me.src.indexOf('assets/');
                if (idx !== -1) return me.src.substring(0, idx);
            }
            return '/';
        }

        // Prefer absolute BASE_URL when available (injected by PHP). Fallback to relative path.
        const baseUrlClient = (window.BASE_URL ? String(window.BASE_URL).replace(/\/$/, '') : '');
        const apiUrl = (baseUrlClient ? (baseUrlClient + '/api/get_full_assessment.php?cvd_id=' + encodeURIComponent(aid)) : ('app/api/get_full_assessment.php?cvd_id=' + encodeURIComponent(aid)));

        const container = document.getElementById('fullAssessmentModalBody');
        if (!container) return;
        container.innerHTML = '<div class="text-center text-muted">Loading assessment...</div>';

        fetch(apiUrl, { credentials: 'same-origin' })
            .then(function(res){
                if (!res.ok) {
                    return res.text().then(function(text){ throw new Error('Server returned ' + res.status + ': ' + text); });
                }
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    return res.text().then(function(txt){ throw new Error('Invalid JSON response: ' + txt.slice(0,300)); });
                }
                return res.json();
            })
            .then(function(json){
                if (!json || !json.success) {
                    container.innerHTML = '<div class="alert alert-danger">Unable to load assessment.</div>';
                    if (json && json.error) console.warn(json.error);
                    if (bsFullModal) bsFullModal.show();
                    return;
                }

            const fullData = Object.assign({}, json.assessment || {}, {
                person: json.person || {},
                vitals: json.vitals || {},
                lifestyle: json.lifestyle || {},
                angina: json.angina || {},
                diabetes: json.diabetes || {},
                household: json.household || {},
                family_history: json.family_history || {}
            });

            // Build paginated content: one section per page
            const pages = SURVEY_SECTIONS.map(function(sec){
                return { title: sec.title, html: renderSectionFields(sec, fullData) };
            });

            let html = '<div id="full-assessment-pages">';
            pages.forEach(function(p, idx){
                html += '<div class="fa-page'+(idx===0?'':' hidden')+'" data-idx="'+idx+'">';
                html += p.html;
                html += '</div>';
            });
            html += '</div>';

            // pagination + save controls
            html += '<div class="d-flex justify-content-between align-items-center mt-3">';
            html += '<div><button type="button" class="btn btn-outline-secondary btn-sm" id="fa-prev">&larr; Prev</button></div>';
            html += '<div class="fa-page-indicator small text-muted">Page <span id="fa-cur">1</span> of <span id="fa-total">'+pages.length+'</span></div>';
            html += '<div class="d-flex gap-2">';
            // removed Cancel button (Close exists separately)
            html += '<button type="button" class="btn btn-success btn-sm" id="fa-save">Save Changes</button>';
            html += '<button type="button" class="btn btn-primary btn-sm" id="fa-next">Next &rarr;</button>';
            html += '</div>';
            html += '</div>';

            container.innerHTML = html;

            // attach nav handlers and save
            let cur = 0;
            function showPage(i){
                const pagesEls = container.querySelectorAll('.fa-page');
                if (!pagesEls || pagesEls.length === 0) return;
                if (i < 0) i = 0; if (i >= pagesEls.length) i = pagesEls.length -1;
                pagesEls.forEach(function(pe, idx){
                    if (idx === i) { pe.classList.remove('hidden'); } else { pe.classList.add('hidden'); }
                });
                        cur = i;
                const curEl = container.querySelector('#fa-cur'); if (curEl) curEl.textContent = (cur+1);
                const totalEl2 = container.querySelector('#fa-total'); if (totalEl2) totalEl2.textContent = pagesEls.length;
                const prevBtn = container.querySelector('#fa-prev'); const nextBtn = container.querySelector('#fa-next');
                if (prevBtn) prevBtn.disabled = (cur === 0);
                if (nextBtn) nextBtn.disabled = (cur === pagesEls.length - 1);
                        // Toggle Save button visibility for specific sections (hide on Family members page)
                        try {
                            const saveBtn = container.querySelector('#fa-save');
                            const curSectionKey = (SURVEY_SECTIONS && SURVEY_SECTIONS[cur] && SURVEY_SECTIONS[cur].key) ? SURVEY_SECTIONS[cur].key : null;
                            if (saveBtn) {
                                if (curSectionKey === 'family') {
                                    saveBtn.style.display = 'none';
                                    saveBtn.disabled = true;
                                } else {
                                    saveBtn.style.display = '';
                                    saveBtn.disabled = false;
                                }
                            }
                        } catch(e) { /* safe-guard: do nothing on error */ }
            }

            container.querySelector('#fa-prev').addEventListener('click', function(){ showPage(cur-1); });
            container.querySelector('#fa-next').addEventListener('click', function(){ showPage(cur+1); });

            // fa-cancel removed — Close btn is available on modal footer

            // post-render initialization: wire birthdate/age sync, replace family_list textarea with read-only list
            (function postRenderInit(){
                // normalize data.family to array and render members list
                try {
                    const familySection = container.querySelector('[data-key="family_list"]');
                    if (familySection) {
                        // find underlying textarea (if any)
                        const ta = familySection.closest('.field-value') ? familySection.closest('.field-value').querySelector('textarea') : null;
                        const fam = json.family || [];
                        const currentPid = (json.assessment && json.assessment.person_id) ? Number(json.assessment.person_id) : (json.person && json.person.id ? Number(json.person.id) : null);
                        let members = [];
                        if (Array.isArray(fam)) members = fam.filter(Boolean).map(function(m){ return m; });
                        else if (fam && typeof fam === 'object') members = [fam];

                        // exclude the current person from the members list and only show distinct names
                        const seen = new Set();
                        const filtered = members.filter(function(m){
                            const id = m && (m.id || m.person_id) ? Number(m.id || m.person_id) : null;
                            if (currentPid && id && id === currentPid) return false;
                            const name = (m && (m.full_name || ((m.first_name||'') + ' ' + (m.last_name||'')).trim())) || '';
                            const key = (name || '').trim().toLowerCase();
                            if (!key) return false;
                            if (seen.has(key)) return false;
                            seen.add(key);
                            return true;
                        });

                        const listHtml = filtered.length ? ('<ul class="list-unstyled mb-0">' + filtered.map(function(m){
                            const fn = m.full_name || ((m.first_name||'') + ' ' + (m.last_name||'')).trim() || '(Unnamed)';
                            return '<li class="mb-1"><strong>'+escapeHtml(fn)+'</strong></li>';
                        }).join('') + '</ul>') : '<div class="text-muted">No family members recorded.</div>';

                        if (ta) ta.outerHTML = listHtml; else if (familySection) familySection.innerHTML = listHtml;
                    }
                } catch(e){ console.warn('family render error', e); }

                // birthdate sync and age calc
                function computeAgeFromISO(iso){
                    if (!iso) return '';
                    const d = new Date(iso);
                    if (isNaN(d.getTime())) return '';
                    const now = new Date();
                    let age = now.getFullYear() - d.getFullYear();
                    const m = now.getMonth() - d.getMonth();
                    if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
                    return age;
                }

                const isoInputs = container.querySelectorAll('.birthdate-iso');
                isoInputs.forEach(function(iso){
                    const visible = iso.closest('.d-flex') ? iso.closest('.d-flex').querySelector('.birthdate-visible') : null;
                    // when iso changes, update visible formatted and age
                    iso.addEventListener('change', function(){
                        const v = iso.value;
                        if (visible) {
                            try { visible.value = v ? (new Date(v)).toLocaleDateString(undefined,{year:'numeric',month:'long',day:'2-digit'}) : ''; } catch(e){ visible.value = v; }
                        }
                        const ageEl = container.querySelector('.age-input');
                        if (ageEl) ageEl.value = computeAgeFromISO(v);
                    });
                    // initialize
                    if (iso.value) iso.dispatchEvent(new Event('change'));
                });

                // clicking the visible birthdate should open the hidden date picker if present
                const visibleBirths = container.querySelectorAll('.birthdate-visible');
                visibleBirths.forEach(function(vis){
                    vis.addEventListener('focus', function(){
                        const iso = vis.closest('.d-flex') ? vis.closest('.d-flex').querySelector('.birthdate-iso') : null;
                        if (iso) iso.showPicker && iso.showPicker();
                    });
                });

                // Contact number formatting: enforce digits-only and format as 09XX-XXXX-XXX
                function formatPHMobile(digits) {
                    if (!digits) return '';
                    digits = String(digits).replace(/\D/g, '').slice(0,11);
                    // if user typed 9XXXXXXXXX (10 digits starting with 9), prepend 0
                    if (digits.length === 10 && digits.charAt(0) === '9') digits = '0' + digits;
                    const a = digits.slice(0,4);
                    const b = digits.slice(4,8);
                    const c = digits.slice(8,11);
                    const parts = [];
                    if (a) parts.push(a);
                    if (b) parts.push(b);
                    if (c) parts.push(c);
                    return parts.join('-');
                }

                const contactInputs = container.querySelectorAll('.fa-input[data-key="contact_no"]');
                contactInputs.forEach(function(ci){
                    // hint to mobile keyboards
                    try { ci.setAttribute('inputmode', 'numeric'); } catch(e){}
                    // initial format
                    ci.value = formatPHMobile(ci.value);
                    ci.addEventListener('input', function(){
                        const raw = String(ci.value || '');
                        const digits = raw.replace(/\D/g,'').slice(0,11);
                        ci.value = formatPHMobile(digits);
                    });
                    // prevent pasting non-digit characters
                    ci.addEventListener('paste', function(ev){
                        ev.preventDefault();
                        const txt = (ev.clipboardData || window.clipboardData).getData('text') || '';
                        const digits = txt.replace(/\D/g,'').slice(0,11);
                        ci.value = formatPHMobile(digits);
                    });
                });

                // Range slider live value updates (exercise sliders)
                try {
                    const ranges = container.querySelectorAll('input.form-range.fa-input');
                    ranges.forEach(function(r){
                        const display = r.parentElement ? r.parentElement.querySelector('.range-value') : null;
                        const update = function(){ if (display) display.textContent = String(r.value || '0'); };
                        r.addEventListener('input', update);
                        // initialize
                        update();
                    });
                } catch(e){ /* ignore */ }
            })();

            // Save handler: collect inputs and post to update API. If current page is Vitals, send vitals-only payload.
            container.querySelector('#fa-save').addEventListener('click', function(){
                const curSectionKey = (SURVEY_SECTIONS && SURVEY_SECTIONS[cur] && SURVEY_SECTIONS[cur].key) ? SURVEY_SECTIONS[cur].key : null;

                // limit inputs to current page when saving vitals-only, otherwise include all inputs
                let inputs = [];
                if (curSectionKey === 'vitals') {
                    const pageEls = container.querySelectorAll('.fa-page');
                    const pageEl = pageEls && pageEls[cur] ? pageEls[cur] : null;
                    inputs = pageEl ? Array.from(pageEl.querySelectorAll('.fa-input')) : [];
                } else {
                    inputs = Array.from(container.querySelectorAll('.fa-input'));
                }

                // construct payload
                const payload = { cvd_id: json.assessment.id || json.assessment.cvd_id };
                if (json.assessment.person_id) payload.person_id = json.assessment.person_id;

                const vitalsOnly = (curSectionKey === 'vitals');
                if (vitalsOnly && !payload.vitals) payload.vitals = {};

                inputs.forEach(function(inp){
                    const section = inp.getAttribute('data-section') || 'other';
                    const key = inp.getAttribute('data-key');
                    if (!key) return;
                    // radios: only take checked
                    if (inp.type === 'radio' && !inp.checked) return;
                    // skip visible birthdate (we use iso)
                    if (inp.classList && inp.classList.contains('birthdate-visible')) return;

                    let value = inp.value;
                    if (key === 'contact_no' && typeof value === 'string') {
                        value = value.replace(/\D/g,'').slice(0,11);
                    }

                    if (vitalsOnly) {
                        // only capture vitals keys
                        const vkeys = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
                        if (vkeys.indexOf(key) !== -1) payload.vitals[key] = value;
                    } else {
                        if (!payload[section]) payload[section] = {};
                        payload[section][key] = value;
                    }
                });

                // if there's a birthdate iso input, ensure it wins when doing full save
                const iso = container.querySelector('.birthdate-iso');
                if (iso && !vitalsOnly) {
                    if (!payload.personal) payload.personal = {};
                    payload.personal.birthdate = iso.value || payload.personal.birthdate || '';
                }

                // if full save, copy biometric fields from personal into vitals to be safe
                if (!vitalsOnly) {
                    const bioKeys = ['height_cm','weight_kg','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','respiratory_rate','temperature_c'];
                    if (!payload.vitals) payload.vitals = {};
                    bioKeys.forEach(function(k){
                        if ((!payload.vitals[k] || payload.vitals[k] === '') && payload.personal && payload.personal[k]) {
                            payload.vitals[k] = payload.personal[k];
                        }
                    });
                }

                // Ensure key lifestyle fields are always included in the payload (defensive collect)
                if (!vitalsOnly) {
                    if (!payload.lifestyle) payload.lifestyle = {};
                    // explicit keys that sometimes were missed by the generic collection
                    const lifestyleKeys = ['smoking_status','smoking_comments','alcohol_use','alcohol_notes','excessive_alcohol','eats_processed_weekly','fruits_3_servings_daily','vegetables_3_servings_daily','exercise_days_per_week','exercise_minutes_per_day','exercise_intensity'];
                    lifestyleKeys.forEach(function(k){
                        try {
                            // radio/checkbox checked selector
                            const checked = container.querySelector('.fa-input[data-key="'+k+'"]:checked');
                            if (checked) {
                                payload.lifestyle[k] = checked.value;
                                return;
                            }
                            // textareas/inputs
                            const el = container.querySelector('.fa-input[data-key="'+k+'"]');
                            if (el) {
                                payload.lifestyle[k] = el.value;
                                return;
                            }
                            // Some radio groups render inputs without data-key on the options (use name fallback)
                            const nameFallback = container.querySelector('[name^="smoke_"]') || container.querySelector('[name^="alcohol_"]') || container.querySelector('[name^="intensity_"]');
                            if (nameFallback && nameFallback.name) {
                                const c = container.querySelector('[name="'+nameFallback.name+'"]:checked');
                                if (c && c.getAttribute('data-key') === k) payload.lifestyle[k] = c.value;
                            }
                        } catch(e) { /* ignore per-key errors */ }
                    });
                }

                const updBaseClient = (window.BASE_URL ? String(window.BASE_URL).replace(/\/$/, '') : '');
                const updUrl = (updBaseClient ? (updBaseClient + '/api/update_assessment.php') : 'app/api/update_assessment.php');

                const saveBtn = container.querySelector('#fa-save');
                saveBtn.disabled = true; saveBtn.textContent = 'Saving...';

                fetch(updUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(function(res){
                    if (!res.ok) return res.text().then(function(t){ throw new Error('Server returned ' + res.status + ': ' + t); });
                    const ct = res.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) return res.text().then(function(t){ throw new Error('Invalid JSON response: ' + t); });
                    return res.json();
                })
                .then(function(resJson){
                    if (resJson && resJson.success) {
                        saveBtn.textContent = 'Saved';
                        // advance to next page after short delay
                        setTimeout(function(){
                            saveBtn.textContent = 'Save Changes'; saveBtn.disabled = false;
                            if (cur < (SURVEY_SECTIONS.length - 1)) showPage(cur + 1);
                        }, 800);
                    } else {
                        saveBtn.textContent = 'Save Changes'; saveBtn.disabled = false;
                        alert('Save failed: ' + (resJson && resJson.error ? resJson.error : 'unknown error'));
                    }
                })
                .catch(function(err){
                    saveBtn.textContent = 'Save Changes'; saveBtn.disabled = false;
                    console.error('Fetch/update_assessment error:', err);
                    alert('Save error: ' + err.message);
                });
            });

            // initialize buttons disabled state
            showPage(0);
            if (bsFullModal) bsFullModal.show();
            }).catch(function(err){
                container.innerHTML = '<div class="alert alert-danger">Error loading assessment.</div>';
                console.error('Fetch/get_full_assessment error:', err);
                if (bsFullModal) bsFullModal.show();
            });
    });

});
