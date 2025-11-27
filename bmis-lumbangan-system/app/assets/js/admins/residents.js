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
        // sectionDef: array of {key,label}
        // build a nicer section card with label icons and two-column responsive layout
        let html = '<div class="section-card p-3 mb-3">';
        html += '<h6 class="mb-2">' + (sectionDef.title || '') + '</h6>';

        // Font Awesome icon map for fields (uses FA class names). Adjust classes if your project uses a different FA prefix (e.g. 'fa' / 'fas' / 'fa-solid').
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
            contact_no: '<i class="fas fa-phone fa-fw"></i>',
            highest_educ_attainment: '<i class="fas fa-graduation-cap fa-fw"></i>',
            religion: '<i class="fas fa-praying-hands fa-fw"></i>',
            occupation: '<i class="fas fa-briefcase fa-fw"></i>',
            blood_type: '<i class="fas fa-tint fa-fw"></i>',
            bp_systolic: '<i class="fas fa-heartbeat fa-fw"></i>',
            bp_diastolic: '<i class="fas fa-heartbeat fa-fw"></i>',
            pulse: '<i class="fas fa-heart fa-fw"></i>',
            respiratory_rate: '<i class="fas fa-lungs fa-fw"></i>',
            temperature_c: '<i class="fas fa-thermometer-half fa-fw"></i>',
            disability: '<i class="fas fa-wheelchair fa-fw"></i>',
            height_cm: '<i class="fas fa-arrows-v fa-fw"></i>',
            weight_kg: '<i class="fas fa-weight fa-fw"></i>',
            waist_circumference_cm: '<i class="fas fa-ruler-horizontal fa-fw"></i>'
        };

        // helper to render an array of fields into the html buffer
        function renderFieldsArray(fields){
            let out = '';
            (fields || []).forEach(function(f){
                const val = (data && (data[f.key] !== undefined && data[f.key] !== null)) ? String(data[f.key]) : '';
                // prefer Font Awesome mapping, fallback to a simple FA circle
                const icon = ICON_FA[f.key] || '<i class="fas fa-circle fa-fw"></i>';
                out += '<div class="col-12 col-md-6">';
                out += '<div class="p-2">';
                out += '<div class="field-label">' + '<span class="field-icon" aria-hidden="true">' + icon + '</span>' + '<span>' + (f.label || f.key) + '</span>' + '</div>';
                out += '<div class="field-value">' + (val === '' ? '—' : escapeHtml(val)) + '</div>';
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
                html += renderFieldsArray(sub.fields || []);
                html += '</div>';
                html += '</div>';
            });
        } else {
            // fallback to old single fields array
            html += '<div class="row g-2">';
            html += renderFieldsArray(sectionDef.fields || []);
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
                    {key:'current_smoking_status', label:'Current Smoking Status'},
                    {key:'additional_smoking_comments', label:'Additional Smoking Comments'}
                ]},
                { title: 'Alcohol Consumption', fields: [
                    {key:'alcohol_use_status', label:'Alcohol Use Status'},
                    {key:'excessive_alcohol_consumption', label:'Excessive Alcohol Consumption?'},
                    {key:'additional_alcohol_comments', label:'Additional Alcohol Comments'}
                ]},
                { title: 'Dietary Habits', fields: [
                    {key:'eat_processed_foods', label:'Eat Processed Foods?'},
                    {key:'eat_fruits_daily', label:'Eat Fruits Daily?'},
                    {key:'eat_vegetables_daily', label:'Eat Vegetables Daily?'}
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
        const list = (window.assessmentsByPerson && window.assessmentsByPerson[pid]) ? window.assessmentsByPerson[pid] : [];
        const assessment = list.find(function(x){ return String(x.id) === String(aid) || String(x.cvd_id) === String(aid); }) || list[0] || null;
        if (!assessment) {
            if (bsFullModal) {
                document.getElementById('fullAssessmentModalBody').innerHTML = '<div class="alert alert-info">No assessment data found.</div>';
                bsFullModal.show();
            }
            return;
        }

        // Build paginated content: one section per page
        const pages = SURVEY_SECTIONS.map(function(sec){
            return { title: sec.title, html: renderSectionFields(sec, assessment) };
        });

        // Render modal shell
        const container = document.getElementById('fullAssessmentModalBody');
        if (!container) return;
        let html = '<div id="full-assessment-pages">';
        pages.forEach(function(p, idx){
            // Render all pages visible but mark non-active as hidden for CSS animation
            html += '<div class="fa-page'+(idx===0?'':' hidden')+'" data-idx="'+idx+'">';
            html += p.html;
            html += '</div>';
        });
        html += '</div>';
        // pagination controls
        html += '<div class="d-flex justify-content-between align-items-center mt-3">';
        html += '<button type="button" class="btn btn-outline-secondary btn-sm" id="fa-prev">&larr; Prev</button>';
        html += '<div class="fa-page-indicator small text-muted">Page <span id="fa-cur">1</span> of <span id="fa-total">'+pages.length+'</span></div>';
        html += '<button type="button" class="btn btn-primary btn-sm" id="fa-next">Next &rarr;</button>';
        html += '</div>';

        container.innerHTML = html;
        // attach nav handlers
        let cur = 0;
        function showPage(i){
            const pagesEls = container.querySelectorAll('.fa-page');
            if (!pagesEls || pagesEls.length === 0) return;
            if (i < 0) i = 0; if (i >= pagesEls.length) i = pagesEls.length -1;
            pagesEls.forEach(function(pe, idx){
                if (idx === i) {
                    pe.classList.remove('hidden');
                } else {
                    pe.classList.add('hidden');
                }
            });
            cur = i;
            const curEl = container.querySelector('#fa-cur'); if (curEl) curEl.textContent = (cur+1);
            const totalEl2 = container.querySelector('#fa-total'); if (totalEl2) totalEl2.textContent = pagesEls.length;
            // update button disabled states
            const prevBtn = container.querySelector('#fa-prev'); const nextBtn = container.querySelector('#fa-next');
            if (prevBtn) prevBtn.disabled = (cur === 0);
            if (nextBtn) nextBtn.disabled = (cur === pagesEls.length - 1);
        }
        container.querySelector('#fa-prev').addEventListener('click', function(){ showPage(cur-1); });
        container.querySelector('#fa-next').addEventListener('click', function(){ showPage(cur+1); });

        // initialize buttons disabled state
        showPage(0);

        if (bsFullModal) bsFullModal.show();
    });

});
