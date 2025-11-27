<?php
// Officials management view
if (!defined('BASE_URL')) require_once dirname(__DIR__, 2) . '/config/config.php';
$pageTitle = 'Officials Management';
$pageSubtitle = 'Create, view, edit and delete official accounts';
require_once dirname(__DIR__, 2) . '/components/admin_components/header-admin.php';
?>

<!-- Scoped CSS for Officials admin -->
<main class="main-content" id="officials-admin-scope">
    <div class="content-section" style="padding: 2rem;">
        <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px;">
            <div class="page-header-left">
                <!-- header icon -->
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="background:#eaf6ff;border-radius:10px;padding:8px;box-shadow:0 2px 8px rgba(30,58,95,0.04)">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" fill="#1e3a5f" opacity="0.95"/>
                    <path d="M4 20c0-2.21 3.58-4 8-4s8 1.79 8 4v1H4v-1z" fill="#93a4b4" opacity="0.35"/>
                </svg>
                <div>
                    <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
                    <div class="page-subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></div>                
                </div>
            </div>
            <div>
                <button id="btnCreateOfficial" class="create-btn">+ Create Official</button>
            </div>
        </div>

        <div id="rolesContainer" style="display: flex; flex-direction: column; gap: 1rem; padding-bottom: 0.5rem;">
        </div>
    </div>
<!-- View Official Modal -->
<div class="modal fade" id="viewOfficialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Official Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewOfficialContent">Loading...</div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button id="btnEditFromView" class="btn btn-sm btn-primary">Edit</button>
                <button id="btnDeleteFromView" class="btn btn-sm btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Official Modal -->
<div class="modal fade" id="editOfficialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Official</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editOfficialForm">
                    <input type="hidden" name="id" id="editOfficialId">
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input class="form-control" name="full_name" id="editFullName">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Username</label>
                        <input class="form-control" name="username" id="editUsername">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <input class="form-control" name="role" id="editRole">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" id="editEmail">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact Number</label>
                        <input class="form-control" name="contact_no" id="editContact" placeholder="09XX-XXXX-XXX">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password (leave blank to keep)</label>
                        <input class="form-control" type="password" name="password" id="editPassword">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="saveEditOfficialBtn" class="btn btn-sm btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Official Modal -->
<div class="modal fade" id="createOfficialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Official</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOfficialForm">
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input class="form-control" name="full_name" id="createFullName">
                    </div>
                    <div class="mb-2 input-with-icon">
                        <label class="form-label">Username</label>
                        <input class="form-control" name="username" id="createUsername" autocomplete="off">
                        <span id="createUsernameIcon" class="avail-icon">✓</span>
                        <div id="createUsernameFeedback" style="font-size:0.85rem;color:#d0342c;margin-top:6px;display:none"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select class="form-control" name="role" id="createRole">
                            <option value="">-- Select role --</option>
                            <option>Barangay Captain</option>
                            <option>Barangay Secretary</option>
                            <option>Barangay Health Worker President</option>
                            <option>Barangay Conciliation Panel</option>
                            <option>Barangay Tanod</option>
                            <option>Barangay Health Worker</option>
                            <option value="Other">Other</option>
                        </select>
                        <input class="form-control mt-2" name="other_role" id="createOtherRole" placeholder="If Other, specify role" style="display:none">
                    </div>
                    <div class="mb-2 input-with-icon">
                        <label class="form-label">Email</label>
                        <input class="form-control" name="email" id="createEmail" autocomplete="off">
                        <span id="createEmailIcon" class="avail-icon">✓</span>
                        <div id="createEmailFeedback" style="font-size:0.85rem;color:#d0342c;margin-top:6px;display:none"></div>
                    </div>
                    <div class="mb-2 input-with-icon">
                        <label class="form-label">Contact Number</label>
                        <input class="form-control" name="contact_no" id="createContact" placeholder="09XX-XXXX-XXX" autocomplete="off">
                        <span id="createContactIcon" class="avail-icon">✓</span>
                        <div id="createContactFeedback" style="font-size:0.85rem;color:#d0342c;margin-top:6px;display:none"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="saveCreateOfficialBtn" class="btn btn-sm btn-primary">Create</button>
            </div>
        </div>
    </div>
</div>

            <!-- Review Create Modal -->
            <div class="modal fade" id="reviewCreateModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Official Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="reviewCreateContent">Preparing details...</div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button id="confirmCreateBtn" class="btn btn-sm btn-primary">Confirm & Create</button>
                        </div>
                    </div>
                </div>
            </div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const rolesContainer = document.getElementById('rolesContainer');
    const viewModalEl = document.getElementById('viewOfficialModal');
    const viewModal = new bootstrap.Modal(viewModalEl);
    const editModal = new bootstrap.Modal(document.getElementById('editOfficialModal'));
    const createModal = new bootstrap.Modal(document.getElementById('createOfficialModal'));
    const reviewModal = new bootstrap.Modal(document.getElementById('reviewCreateModal'));

    // Robust fetch helper: try to parse JSON, but return raw text on failure for debugging
    async function fetchJson(url, opts){
        const res = await fetch(url, opts);
        const txt = await res.text();
        try {
            return JSON.parse(txt);
        } catch(e) {
            console.error('Non-JSON response from', url, txt);
            return { success: false, __raw: txt, message: 'Non-JSON response' };
        }
    }

    async function loadOfficials(){
        rolesContainer.innerHTML = '<div>Loading...</div>';
        try{
            const res = await fetchJson('<?php echo rtrim(BASE_PUBLIC, "/"); ?>/index.php?action=list_officials');
            console.debug('list_officials response:', res);
            if (!res || !res.success) {
                // show raw server output if available to help debugging
                const raw = (res && res.__raw) ? res.__raw : (res && res.message ? res.message : 'Unknown error');
                rolesContainer.innerHTML = '<div class="text-danger">Error loading officials: ' + String(raw).substring(0,200) + '</div>';
                return;
            }
            // If data is empty object, show friendly message
            if (!res.data || Object.keys(res.data).length === 0) {
                rolesContainer.innerHTML = '<div class="text-muted">No officials found.</div>';
                return;
            }
            renderRoles(res.data);
                // After rendering, disable create-role options for exclusive roles already present
                try {
                    const existingRoles = Object.keys(res.data || {}).map(k => (k||'').toLowerCase().trim());
                    updateRoleOptions(existingRoles);
                } catch(e) { console.debug('updateRoleOptions failed', e); }
        }catch(e){
            console.error('Exception while loading officials', e);
            rolesContainer.innerHTML = '<div class="text-danger">Error loading officials (see console)</div>';
        }
    }

    // disable create-role select options when exclusive roles already exist
    function updateRoleOptions(existingRoles) {
        const exclusive = [
            'barangay captain',
            'barangay secretary',
            'barangay health worker president'
        ];
        if (!createRoleEl) return;
        // iterate options
        Array.from(createRoleEl.options).forEach(opt => {
            const val = (opt.value || opt.text || '').toLowerCase().trim();
            if (exclusive.indexOf(val) !== -1) {
                if (existingRoles.indexOf(val) !== -1) {
                    opt.disabled = true;
                    opt.title = 'Role already assigned';
                    // add visual marker if not present
                    if (!opt.dataset.mark) { opt.text = opt.text + ' (assigned)'; opt.dataset.mark = '1'; }
                } else {
                    opt.disabled = false;
                    opt.title = '';
                    if (opt.dataset.mark) { opt.text = opt.text.replace(/\s*\(assigned\)\s*$/, ''); delete opt.dataset.mark; }
                }
            }
        });
    }

    function renderRoles(grouped){
        rolesContainer.innerHTML = '';

        // Preferred order requested by the user. We'll attempt to match role keys loosely
        const preferredOrder = [
            'Captain',
            'Secretary',
            'Health Worker President',
            'Concialiation',
            'Tanod',
            'Normal Health Work'
        ];

        // Make a mutable copy of grouped
        const remaining = {};
        Object.keys(grouped).forEach(k => { remaining[k] = grouped[k]; });

        // Helper to find a key in remaining that matches a preferred name (case-insensitive, loose match)
        function findAndRemoveMatch(preferredName){
            const normPref = (preferredName || '').toLowerCase().replace(/[^a-z0-9]/g,'');
            for (const k in remaining){
                const normKey = (k || '').toLowerCase().replace(/[^a-z0-9]/g,'');
                if (!normKey) continue;
                if (normKey === normPref || normKey.indexOf(normPref) !== -1 || normPref.indexOf(normKey) !== -1) {
                    const val = remaining[k];
                    delete remaining[k];
                    return { key: k, val: val };
                }
            }
            return null;
        }

        // Render function for a single role block
        // Render function for a single role block — returns the DOM element so caller can place it
        function renderRoleBlock(roleName, officials){
            const roleCard = document.createElement('div');
            roleCard.className = 'role-card';
            roleCard.style.border = '1px solid #e6edf3';
            roleCard.style.borderRadius = '8px';
            roleCard.style.padding = '12px';
            roleCard.style.width = '100%';
            roleCard.style.boxSizing = 'border-box';
            roleCard.style.background = 'transparent';
            roleCard.innerHTML = `<h5 style="margin-top:0;">${roleName} <small style='color:#6b7280'>(${officials.length})</small></h5>`;

            const inner = document.createElement('div');
            // Display officials horizontally with an internal horizontal scroller
            inner.style.display = 'flex';
            inner.style.flexDirection = 'row';
            inner.style.gap = '10px';
            inner.style.overflowX = 'auto';
            inner.style.paddingBottom = '6px';
            inner.style.alignItems = 'flex-start';
            // Center officials inside the role card when there are few members, otherwise left-align for natural scrolling
            inner.style.justifyContent = (Array.isArray(officials) && officials.length <= 2) ? 'center' : 'flex-start';

            officials.forEach(off => {
                const card = document.createElement('div');
                card.className = 'official-card';
                card.style.border = '1px solid #eef2f6';
                card.style.borderRadius = '8px';
                card.style.padding = '12px';
                card.style.cursor = 'pointer';
                card.style.background = '#fff';
                // Keep each official card at a fixed width so they lay out horizontally
                card.style.flex = '0 0 220px';
                card.style.minWidth = '180px';
                // Vertical stacked layout: avatar on top, then full name, then username — use classes and icons
                const initials = (off.full_name||'').split(' ').map(s=>s[0]).slice(0,2).join('');
                card.innerHTML = `
                    <div class="official-avatar">${initials}</div>
                    <div class="official-name">${off.full_name}</div>
                    <div class="official-username">@${off.username}</div>
                    <div class="official-meta">
                        <!-- mail icon -->
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6.5C3 5.119 4.119 4 5.5 4h13c1.381 0 2.5 1.119 2.5 2.5v11c0 1.381-1.119 2.5-2.5 2.5h-13C4.119 20 3 18.881 3 17.5v-11z" stroke="#93a4b4" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 7.2l-9 6-9-6" stroke="#93a4b4" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <!-- phone icon (placeholder) -->
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.08 4.18 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.72c.12.94.38 1.85.76 2.7a2 2 0 0 1-.45 2.11L8.9 10.9a16 16 0 0 0 6 6l1.36-1.36a2 2 0 0 1 2.11-.45c.85.38 1.76.64 2.7.76A2 2 0 0 1 22 16.92z" stroke="#93a4b4" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                `;
                card.addEventListener('click', function(){ showOfficial(off.id); });
                inner.appendChild(card);
            });

            roleCard.appendChild(inner);
            return roleCard;
        }

        // Create a top-row container for the three primary roles we want side-by-side
        const topGroup = ['Captain', 'Secretary', 'Health Worker President'];
        const topRow = document.createElement('div');
        topRow.style.display = 'flex';
        topRow.style.gap = '1rem';
        topRow.style.alignItems = 'flex-start';
        topRow.style.marginBottom = '1rem';
        topRow.style.overflowX = 'auto';

        // First render preferred roles; collect top-row items and below-row items so we can control placement
        const belowBlocks = [];
        preferredOrder.forEach(pref => {
            const found = findAndRemoveMatch(pref);
            if (found) {
                const isTop = topGroup.some(g => g.toLowerCase().replace(/[^a-z0-9]/g,'').indexOf(pref.toLowerCase().replace(/[^a-z0-9]/g,'')) !== -1);
                const el = renderRoleBlock(found.key, found.val);
                if (isTop) {
                    el.style.flex = '0 0 32%';
                    el.style.minWidth = '280px';
                    topRow.appendChild(el);
                } else {
                    belowBlocks.push(el);
                }
            }
        });

        // Insert topRow first (if any)
        if (topRow.children.length > 0) {
            rolesContainer.appendChild(topRow);
        }

        // Then append any preferred-role blocks that were not part of topRow (this ensures Tanod/Concialiation appear below the top row)
        belowBlocks.forEach(b => rolesContainer.appendChild(b));

        // Then render any remaining roles (alphabetically)
        const remainingKeys = Object.keys(remaining).sort();
        remainingKeys.forEach(k => { rolesContainer.appendChild(renderRoleBlock(k, remaining[k])); });
    }

    async function showOfficial(id){
        const container = document.getElementById('viewOfficialContent');
        container.innerHTML = 'Loading...';
        try{
            const res = await fetchJson('<?php echo rtrim(BASE_PUBLIC, "/"); ?>/index.php?action=get_official&id='+encodeURIComponent(id));
            if (!res.success) { container.innerHTML = '<div class="text-danger">Not found</div>'; return; }
            const o = res.data;
            container.innerHTML = `
                <div style="display:flex;gap:12px;align-items:center;"><div style="width:88px;height:88px;border-radius:8px;background:#e6eef8;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1e3a5f;font-size:1.25rem">${(o.full_name||'').split(' ').map(s=>s[0]).slice(0,2).join('')}</div>
                <div style="flex:1;"><h5 style="margin:0">${o.full_name}</h5><div style="color:#6b7280">${o.role}</div></div></div>
                <hr>
                <div><strong>Username:</strong> ${o.username || ''}</div>
                <div><strong>Email:</strong> ${o.email || ''}</div>
                <div><strong>Contact:</strong> ${o.contact_no || ''}</div>
            `;
            // attach actions
            document.getElementById('btnEditFromView').onclick = function(){ openEditModal(o); };
            document.getElementById('btnDeleteFromView').onclick = function(){ confirmDelete(o.id); };
            viewModal.show();
        }catch(e){ container.innerHTML = '<div class="text-danger">Error</div>'; }
    }

    function openEditModal(o){
        document.getElementById('editOfficialId').value = o.id;
        document.getElementById('editFullName').value = o.full_name || '';
        document.getElementById('editUsername').value = o.username || '';
        document.getElementById('editRole').value = o.role || '';
        document.getElementById('editEmail').value = o.email || '';
        // set edit contact formatted and store raw digits
        const editContactElLocal = document.getElementById('editContact');
        const rawContact = (o.contact_no || '').toString().replace(/\D/g,'');
        editContactElLocal.dataset.raw = rawContact;
        editContactElLocal.value = rawContact ? ( (rawContact.slice(0,4)) + (rawContact.length>4?'-'+rawContact.slice(4,8):'') + (rawContact.length>8?'-'+rawContact.slice(8,11):'') ) : '';
        document.getElementById('editContact').value = editContactElLocal.value;
        // store originals so live checks ignore unchanged values
        originalEditUsername = o.username || '';
        originalEditEmail = o.email || '';
        originalEditContact = rawContact || '';
        viewModal.hide();
        editModal.show();
    }

    async function saveEdit(){
        const btn = document.getElementById('saveEditOfficialBtn');
        btn.disabled = true; var id = document.getElementById('editOfficialId').value;
        var fd = new FormData(document.getElementById('editOfficialForm'));
        // ensure contact_no is unformatted raw digits
        try {
            const raw = document.getElementById('editContact').dataset.raw || document.getElementById('editContact').value.replace(/\D/g,'');
            fd.set('contact_no', raw);
        } catch(e){}
        fd.append('id', id);
        try{
            const res = await fetchJson('<?php echo rtrim(BASE_PUBLIC, "/"); ?>/index.php?action=admin_update_official',{ method:'POST', body: fd });
            if (res.success) {
                // show inline message above button if helper exists
                if (typeof showInlineSavedMessage === 'function') showInlineSavedMessage(btn, 'Saved Successfully');
                editModal.hide();
                loadOfficials();
            } else {
                if (typeof showInlineSavedMessage === 'function') showInlineSavedMessage(btn, res.message || 'Error', true);
                else alert(res.message || 'Error');
            }
        }catch(e){ if (typeof showInlineSavedMessage === 'function') showInlineSavedMessage(btn, 'Server error', true); else alert('Server error'); }
        finally { btn.disabled = false; }
    }

    async function confirmDelete(id){
        if (!confirm('Delete this official? This will deactivate the account. Continue?')) return;
        try{
            const res = await fetchJson('<?php echo rtrim(BASE_PUBLIC, "/"); ?>/index.php?action=delete_official', { method:'POST', body: new URLSearchParams({ id: id }) });
            if (res.success) { loadOfficials(); viewModal.hide(); }
            else alert(res.message || 'Delete failed');
        }catch(e){ alert('Server error'); }
    }
    // Helper: check availability via server
    async function checkAvailability(field, value){
        if (!value) return { success: true, available: true };
        try{
            const url = '<?php echo rtrim(BASE_PUBLIC, "/"); ?>/index.php?action=check_availability&field='+encodeURIComponent(field)+'&value='+encodeURIComponent(value);
            const res = await fetchJson(url);
            return res;
        } catch(e){ return { success:false, available:false }; }
    }

    // Wire up role 'Other' toggle
    const createRoleEl = document.getElementById('createRole');
    const createOtherEl = document.getElementById('createOtherRole');
    createRoleEl.addEventListener('change', function(){
        if (this.value === 'Other') createOtherEl.style.display = 'block'; else createOtherEl.style.display = 'none';
    });

    // Availability checks for inputs (live while typing, debounced)
    const createUsernameEl = document.getElementById('createUsername');
    const createEmailEl = document.getElementById('createEmail');
    const createContactEl = document.getElementById('createContact');
    const usernameFb = document.getElementById('createUsernameFeedback');
    const emailFb = document.getElementById('createEmailFeedback');
    const contactFb = document.getElementById('createContactFeedback');
    const createUsernameIcon = document.getElementById('createUsernameIcon');
    const createEmailIcon = document.getElementById('createEmailIcon');
    const createContactIcon = document.getElementById('createContactIcon');

    // Edit form elements (skip checks when value equals original)
    const editUsernameEl = document.getElementById('editUsername');
    const editEmailEl = document.getElementById('editEmail');
    const editContactEl = document.getElementById('editContact');
    // Add icons for edit inputs (create them if not present)
    function ensureEditIcons(){
        if (!document.getElementById('editUsernameIcon')){
            const wrap = editUsernameEl.parentElement; const sp = document.createElement('span'); sp.id='editUsernameIcon'; sp.className='avail-icon'; sp.textContent='✓'; wrap.classList.add('input-with-icon'); wrap.appendChild(sp);
        }
        if (!document.getElementById('editEmailIcon')){
            const wrap = editEmailEl.parentElement; const sp = document.createElement('span'); sp.id='editEmailIcon'; sp.className='avail-icon'; sp.textContent='✓'; wrap.classList.add('input-with-icon'); wrap.appendChild(sp);
        }
        if (!document.getElementById('editContactIcon')){
            const wrap = editContactEl.parentElement; const sp = document.createElement('span'); sp.id='editContactIcon'; sp.className='avail-icon'; sp.textContent='✓'; wrap.classList.add('input-with-icon'); wrap.appendChild(sp);
        }
    }
    ensureEditIcons();
    const editUsernameIcon = document.getElementById('editUsernameIcon');
    const editEmailIcon = document.getElementById('editEmailIcon');
    const editContactIcon = document.getElementById('editContactIcon');

    let originalEditUsername = '';
    let originalEditEmail = '';
    let originalEditContact = '';

    // debounce helper
    function debounce(fn, wait){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }

    // contact formatting: group 4-4-3 (max 11 digits)
    function formatContactDigits(digits){ if (!digits) return ''; digits = digits.replace(/\D/g,'').slice(0,11); const p1 = digits.slice(0,4); const p2 = digits.slice(4,8); const p3 = digits.slice(8,11); return [p1,p2,p3].filter(Boolean).join('-'); }

    // update icon helper
    function showIcon(iconEl, ok){ if (!iconEl) return; iconEl.style.display = 'inline'; if (ok){ iconEl.classList.remove('avail-bad'); iconEl.classList.add('avail-ok'); iconEl.textContent='✓'; } else { iconEl.classList.remove('avail-ok'); iconEl.classList.add('avail-bad'); iconEl.textContent='✖'; } }
    function hideIcon(iconEl){ if (!iconEl) return; iconEl.style.display='none'; }

    // Live checks (debounced)
    const liveCheckUsernameCreate = debounce(async function(){ const v = createUsernameEl.value.trim(); if (!v){ usernameFb.style.display='none'; hideIcon(createUsernameIcon); return; } const res = await checkAvailability('username', v); if (res && res.success && res.available){ usernameFb.style.display='none'; showIcon(createUsernameIcon, true); } else { usernameFb.textContent='Username already taken'; usernameFb.style.display='block'; showIcon(createUsernameIcon, false); } }, 500);
    const liveCheckEmailCreate = debounce(async function(){ const v = createEmailEl.value.trim(); if (!v){ emailFb.style.display='none'; hideIcon(createEmailIcon); return; } const res = await checkAvailability('email', v); if (res && res.success && res.available){ emailFb.style.display='none'; showIcon(createEmailIcon, true); } else { emailFb.textContent='Email already used by another official'; emailFb.style.display='block'; showIcon(createEmailIcon, false); } }, 500);
    const liveCheckContactCreate = debounce(async function(){ const raw = (createContactEl.dataset.raw || createContactEl.value.replace(/\D/g,'')); if (!raw){ contactFb.style.display='none'; hideIcon(createContactIcon); return; } const res = await checkAvailability('contact_no', raw); if (res && res.success && res.available){ contactFb.style.display='none'; showIcon(createContactIcon, true); } else { contactFb.textContent='Contact number already used by another official'; contactFb.style.display='block'; showIcon(createContactIcon, false); } }, 500);

    // For edit form: skip checking when value === original
    const liveCheckUsernameEdit = debounce(async function(){ const v = editUsernameEl.value.trim(); if (!v){ hideIcon(editUsernameIcon); return; } if (v === originalEditUsername){ hideIcon(editUsernameIcon); return; } const res = await checkAvailability('username', v); if (res && res.success && res.available){ showIcon(editUsernameIcon, true); } else { showIcon(editUsernameIcon, false); } }, 500);
    const liveCheckEmailEdit = debounce(async function(){ const v = editEmailEl.value.trim(); if (!v){ hideIcon(editEmailIcon); return; } if (v === originalEditEmail){ hideIcon(editEmailIcon); return; } const res = await checkAvailability('email', v); if (res && res.success && res.available){ showIcon(editEmailIcon, true); } else { showIcon(editEmailIcon, false); } }, 500);
    const liveCheckContactEdit = debounce(async function(){ const raw = (editContactEl.dataset.raw || editContactEl.value.replace(/\D/g,'')); if (!raw){ hideIcon(editContactIcon); return; } if (raw === (originalEditContact||'')) { hideIcon(editContactIcon); return; } const res = await checkAvailability('contact_no', raw); if (res && res.success && res.available){ showIcon(editContactIcon, true); } else { showIcon(editContactIcon, false); } }, 500);

    // Attach input listeners
    createUsernameEl.addEventListener('input', liveCheckUsernameCreate);
    createEmailEl.addEventListener('input', liveCheckEmailCreate);
    // contact formatting and live check
    createContactEl.addEventListener('input', function(){ const raw = (this.value||'').replace(/\D/g,'').slice(0,11); this.dataset.raw = raw; this.value = formatContactDigits(raw); contactFb.style.display='none'; hideIcon(createContactIcon); liveCheckContactCreate(); });

    // Edit listeners
    editUsernameEl.addEventListener('input', function(){ liveCheckUsernameEdit(); });
    editEmailEl.addEventListener('input', function(){ liveCheckEmailEdit(); });
    editContactEl.addEventListener('input', function(){ const raw = (this.value||'').replace(/\D/g,'').slice(0,11); this.dataset.raw = raw; this.value = formatContactDigits(raw); hideIcon(editContactIcon); liveCheckContactEdit(); });

    // Prepare review modal when user clicks Create in the create modal
    async function saveCreate(){
        const btn = document.getElementById('saveCreateOfficialBtn'); btn.disabled = true;
        // Pre-validate availability
        const username = createUsernameEl.value.trim();
        const email = createEmailEl.value.trim();
        const contact = createContactEl.dataset.raw || createContactEl.value.replace(/\D/g,'');

        // check username
        const ures = await checkAvailability('username', username);
        if (!(ures && ures.success && ures.available)) { usernameFb.textContent = 'Username already taken'; usernameFb.style.display='block'; btn.disabled = false; return; }

        // check email (if provided)
        if (email) {
            const eres = await checkAvailability('email', email);
            if (!(eres && eres.success && eres.available)) { emailFb.textContent = 'Email already used'; emailFb.style.display='block'; btn.disabled = false; return; }
        }

        // check contact (if provided)
        if (contact) {
            const cres = await checkAvailability('contact_no', contact);
            if (!(cres && cres.success && cres.available)) { contactFb.textContent = 'Contact already used'; contactFb.style.display='block'; btn.disabled = false; return; }
        }

        // All checks passed — populate review modal
        const roleVal = (createRoleEl.value === 'Other' ? (createOtherEl.value.trim() || '[Other]') : (createRoleEl.value || '[Not set]'));
        const passwordPreview = username; // per requirement: password is same as username
        const detailsHtml = `
            <dl style="margin:0">
                <dt><strong>Full Name</strong></dt><dd>${escapeHtml(document.getElementById('createFullName').value || '')}</dd>
                <dt><strong>Username</strong></dt><dd>${escapeHtml(username)}</dd>
                <dt><strong>Password</strong></dt><dd><code>${escapeHtml(passwordPreview)}</code></dd>
                <dt><strong>Role</strong></dt><dd>${escapeHtml(roleVal)}</dd>
                <dt><strong>Email</strong></dt><dd>${escapeHtml(email || '')}</dd>
                <dt><strong>Contact</strong></dt><dd>${escapeHtml(contact || '')}</dd>
            </dl>
        `;
        document.getElementById('reviewCreateContent').innerHTML = detailsHtml;
        // hide create modal and show review
        createModal.hide();
        reviewModal.show();
        btn.disabled = false;
    }

    // Helper to escape HTML for the review content
    function escapeHtml(str){
        if (!str) return '';
        return String(str).replace(/[&"'<>]/g, function(m){ return ({'&':'&amp;','"':'&quot;',"'":'&#39;','<':'&lt;','>':'&gt;'})[m]; });
    }

    // Called when the user confirms in the review modal
    async function doCreateConfirmed(){
        const btn = document.getElementById('confirmCreateBtn'); btn.disabled = true;
        var fd = new FormData(document.getElementById('createOfficialForm'));
        // ensure contact_no is unformatted raw digits
        try {
            const raw = document.getElementById('createContact').dataset.raw || document.getElementById('createContact').value.replace(/\D/g,'');
            fd.set('contact_no', raw);
        } catch(e){}
        // include other_role when visible
        if (createOtherEl.style.display !== 'none') fd.append('other_role', createOtherEl.value.trim());
        try{
            const res = await fetchJson('<?php echo rtrim(BASE_PUBLIC, "/"); ?>/index.php?action=create_official', { method:'POST', body: fd });
            if (res.success) {
                if (typeof showInlineSavedMessage === 'function') showInlineSavedMessage(btn, 'Created Successfully');
                reviewModal.hide();
                // clear form
                document.getElementById('createOfficialForm').reset();
                createOtherEl.style.display = 'none';
                loadOfficials();
            } else {
                // show field-level feedback if controller returned field
                if (res.field === 'username') { usernameFb.textContent = res.message || 'Username error'; usernameFb.style.display='block'; }
                else if (res.field === 'email') { emailFb.textContent = res.message || 'Email error'; emailFb.style.display='block'; }
                else if (res.field === 'contact_no') { contactFb.textContent = res.message || 'Contact error'; contactFb.style.display='block'; }
                else {
                    if (typeof showInlineSavedMessage === 'function') showInlineSavedMessage(btn, res.message || 'Error', true);
                    else alert(res.message || 'Error');
                }
                // if server rejected, return to create modal so user can edit
                reviewModal.hide();
                createModal.show();
            }
        }catch(e){ if (typeof showInlineSavedMessage === 'function') showInlineSavedMessage(btn, 'Server error', true); else alert('Server error'); }
        finally { btn.disabled = false; }
    }

    document.getElementById('saveEditOfficialBtn').addEventListener('click', saveEdit);
    document.getElementById('saveCreateOfficialBtn').addEventListener('click', saveCreate);
    document.getElementById('confirmCreateBtn').addEventListener('click', doCreateConfirmed);
    document.getElementById('btnCreateOfficial').addEventListener('click', function(){ createModal.show(); });

    // Trigger CSS entry animation / ensure scoped CSS applies by adding `.animate-in`.
    // Short timeout gives the browser a moment to parse the stylesheet and ensures animations run.
    setTimeout(function(){
        try{
            var wrap = document.getElementById('officials-admin-scope');
            if (wrap) wrap.classList.add('animate-in');
        } catch(e){ console.debug('animate-in trigger failed', e); }
    }, 80);

    loadOfficials();
});
</script>
</main>

<?php require_once dirname(__DIR__, 2) . '/components/admin_components/footer-admin.php'; ?>
