/**
 * assets/js/Survey/wizard_family.js
 *
 * Robust, tolerant search + multi-select family UI.
 * - tolerant selectors
 * - uses window.SURVEY_API or window.BASE_URL
 * - logs helpful debug information
 * - falls back to demo results if the server call fails
 */

(function () {
  'use strict';

  const $ = (s, p = document) => (p || document).querySelector(s);
  const $$ = (s, p = document) => Array.from((p || document).querySelectorAll(s));
  const debounce = (fn, ms = 250) => { let t; return function() { clearTimeout(t); const args=arguments, ctx=this; t=setTimeout(()=>fn.apply(ctx,args), ms); }; };
  const escapeHtml = s => s ? String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]) : '';

  const API = (window.SURVEY_API || ((window.BASE_URL||'').replace(/\/$/,'') + '/controllers/SurveyController.php')) || '/controllers/SurveyController.php';

  // Relationship options (used by selected UI elsewhere)
  const REL_OPTIONS = [
    { value: 'child', label: 'Child' },
    { value: 'spouse', label: 'Spouse' },
    { value: 'other', label: 'Other' }
  ];

  // state
  let selectedMap = new Map(); // id -> { id, full_name, meta, relationship }
  let selectedPerson = null;
  let familyMembers = [];
  let removedSet = new Set(); // ids to remove on save

  // Minimal renderer for the main family-members list on the page
  function renderFamilyMembers() {
    const container = document.getElementById('family-members-list');
    if (!container) return;
    container.innerHTML = '';
    if (!Array.isArray(familyMembers) || familyMembers.length === 0) {
      container.innerHTML = '<div class="text-muted">No members added</div>';
      return;
    }

    // Categorize members: editable (spouse, child, other), parents (read-only), grandchildren (read-only)
    const editable = [];
    const parents = [];
    const grandchildren = [];
    const others = [];

    familyMembers.forEach(m => {
      const rel = String(m.relationship || '').toLowerCase();
      if (rel === 'spouse' || rel === 'child' || rel === 'other' || rel === '') editable.push(m);
      else if (rel === 'parent') parents.push(m);
      else if (rel.includes('grand')) grandchildren.push(m);
      else others.push(m);
    });

    // Editable section: spouse & children (and other editable entries)
    const headerEditable = document.createElement('div');
    headerEditable.className = 'mb-2';
    headerEditable.innerHTML = '<strong>Spouse & Children</strong>';
    container.appendChild(headerEditable);

    const fragEditable = document.createDocumentFragment();
    function makeEditableRow(m) {
      const el = document.createElement('div');
      el.className = 'family-member d-flex align-items-center justify-content-between p-2 border-bottom gap-2 wf-editable-member';
      el.dataset.id = String(m.id);
      const full = escapeHtml(m.full_name || m.name || m.fullname || 'Unknown');
      const rel = escapeHtml(m.relationship || 'other');
      el.innerHTML = `
        <div style="flex:1">
          <div class="fw-semibold mb-1">${full}</div>
          <div class="small text-muted">${escapeHtml((m.relationship || '').toString())}</div>
        </div>
        <div style="flex:0 0 260px; display:flex; gap:8px; align-items:center;">
          <select class="form-select form-select-sm wf-main-relationship" data-id="${escapeHtml(String(m.id))}" style="min-width:160px">
            ${REL_OPTIONS.map(o => `<option value="${o.value}" ${o.value === (m.relationship || '') ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('')}
          </select>
          <button class="btn btn-sm btn-outline-danger wf-main-remove" data-id="${escapeHtml(String(m.id))}" title="Remove">Remove</button>
        </div>`;
      return el;
    }

    editable.forEach(m => fragEditable.appendChild(makeEditableRow(m)));
    others.forEach(m => fragEditable.appendChild(makeEditableRow(m)));
    container.appendChild(fragEditable);

    // Parents (read-only)
    if (parents.length > 0) {
      const hp = document.createElement('div'); hp.className = 'mt-3 mb-1'; hp.innerHTML = '<strong>Parents (read only)</strong>'; container.appendChild(hp);
      parents.forEach(m => {
        const el = document.createElement('div');
        el.className = 'family-parent d-flex align-items-center justify-content-between p-2 border-bottom gap-2 wf-readonly-member';
        const full = escapeHtml(m.full_name || m.name || m.fullname || 'Unknown');
        el.innerHTML = `<div style="flex:1"><div class="fw-semibold mb-1">${full}</div><div class="small text-muted">Parent</div></div><div style="flex:0 0 160px; text-align:right"></div>`;
        container.appendChild(el);
      });
    }

    // Grandchildren (read-only)
    if (grandchildren.length > 0) {
      const hg = document.createElement('div'); hg.className = 'mt-3 mb-1'; hg.innerHTML = '<strong>Grandchildren (read only)</strong>'; container.appendChild(hg);
      grandchildren.forEach(m => {
        const el = document.createElement('div');
        el.className = 'family-grandchild d-flex align-items-center justify-content-between p-2 border-bottom gap-2 wf-readonly-member';
        const full = escapeHtml(m.full_name || m.name || m.fullname || 'Unknown');
        el.innerHTML = `<div style="flex:1"><div class="fw-semibold mb-1">${full}</div><div class="small text-muted">Grandchild</div></div><div style="flex:0 0 160px; text-align:right"></div>`;
        container.appendChild(el);
      });
    }

    // Attach handlers only for editable controls
    $$('.wf-main-relationship', container).forEach(sel => {
      if (!sel.__wf_bound) {
        sel.__wf_bound = true;
        sel.addEventListener('change', async (e) => {
          const id = String(e.target.dataset.id);
          const newRel = String(e.target.value || 'other');
          // update local model immediately
          const idx = familyMembers.findIndex(x => String(x.id) === String(id));
          if (idx > -1) familyMembers[idx].relationship = newRel;
          try {
            const fd = new FormData(); fd.append('member_person_id', id); fd.append('relationship', newRel);
            const res = await fetch(API + '?action=add_family_member', { method: 'POST', body: fd, credentials: 'same-origin' });
            const txt = await res.text(); let json = null; try { json = txt ? JSON.parse(txt) : null; } catch(e) { json = null; }
            if (!res.ok || !json || !json.success) throw new Error(json?.message || txt || res.status);
          } catch (err) {
            console.error('Failed to update relationship', err);
            toast('Failed to update relationship', 'warning');
          }
          // persist and refresh
          await saveFamilyMembersToServer();
          await loadFamilyMembersFromServer();
        });
      }
    });

    $$('.wf-main-remove', container).forEach(btn => {
      if (!btn.__wf_bound) {
        btn.__wf_bound = true;
        btn.addEventListener('click', async (e) => {
          const id = String(btn.dataset.id);
          try {
            const fd = new FormData(); fd.append('member_person_id', id);
            const res = await fetch(API + '?action=remove_family_member', { method: 'POST', body: fd, credentials: 'same-origin' });
            const txt = await res.text(); let json = null; try { json = txt ? JSON.parse(txt) : null; } catch(e) { json = null; }
            if (!res.ok || !json || !json.success) throw new Error(json?.message || txt || res.status);
          } catch (err) {
            console.error('Failed to remove member', err);
            toast('Failed to remove member', 'warning');
          }
          await loadFamilyMembersFromServer();
        });
      }
    });
  }

  function saveFamilyToStorage() {
    try {
      const pid = window.CURRENT_PERSON_ID ? String(window.CURRENT_PERSON_ID) : 'anon';
      const toSave = (familyMembers || []).map(m => ({ id: m.id, full_name: m.full_name || m.name || m.fullname || '', relationship: m.relationship || '', sex: m.sex || '' }));
      localStorage.setItem(`survey_family_members:${pid}`, JSON.stringify(toSave));
    } catch (e) { dbg('saveFamilyToStorage failed', e); }
  }

  // Initialize search input wiring (debounced)
  function initSearch() {
    const input = findSearchInput();
    const results = findResults();
    if (!input || !results) { dbg('initSearch: missing input/results'); return; }
    const doSearch = debounce(() => {
      const q = String(input.value || '').trim();
      if (q.length < 2) {
        results.innerHTML = '';
        findEmpty()?.classList.add('d-none');
        results.classList.remove('show');
        return;
      }
      performSearch(q);
    }, 200);
    input.addEventListener('input', doSearch);
    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });
  }

  // Wire modal buttons (clear, add) and ensure single-binding
  function initFamilyManagement() {
    const btnClear = document.getElementById('btn-clear-selection');
    const btnAddConfirm = findAddConfirm();
    if (btnClear && !btnClear.__wf_bound) {
      btnClear.addEventListener('click', () => {
        selectedMap.clear();
        const results = findResults();
        if (results) Array.from(results.querySelectorAll('.wf-result-check')).forEach(c => c.checked = false);
        renderSelectedList();
      });
      btnClear.__wf_bound = true;
    }
    if (btnAddConfirm && !btnAddConfirm.__wf_bound) {
      btnAddConfirm.addEventListener('click', async () => { await addSelectedConfirmAction(); await loadFamilyMembersFromServer(); });
      btnAddConfirm.__wf_bound = true;
    }
  }

  // tolerant element getters
  function findSearchInput() {
    return $('#family-search') || $('#search-person') || $('#family_search') || $('#search_person') || null;
  }
  function findResults() {
    return $('#search-results') || $('#search-results-list') || $('#results') || null;
  }
  function findEmpty() {
    return $('#search-empty') || $('#search-empty-list') || null;
  }
  function findSelectedList() {
    return $('#selected-people-list') || $('#selected-people') || null;
  }
  function findAddConfirm() {
    return $('#btn-add-selected-confirm') || $('#btn-add-selected') || $('#btn-add-member') || null;
  }

  // Debug helper - prints contextual info
  function dbg(...args) { try { console.debug('[wf]', ...args); } catch(e){} }

  // performSearch -> calls server and renders results
  async function performSearch(q) {
    const resultsBox = findResults();
    const emptyBox = findEmpty();
    if (!resultsBox) {
      console.warn('wizard_family: results container not found (expected id="search-results")');
      return;
    }

    resultsBox.innerHTML = '<div class="list-group-item text-center text-muted py-3">Searching…</div>';
    emptyBox?.classList.add('d-none');

    // Build URL
    const url = API + '?action=search_persons&q=' + encodeURIComponent(q);
    dbg('searching', q, 'url=', url);

    let data = null;
    try {
      const resp = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' }});
      const text = await resp.text();
      dbg('search response status', resp.status, 'text:', text);
      try {
        const json = text ? JSON.parse(text) : null;
        if (json && Array.isArray(json.data)) data = json.data;
        else if (json && json.success && Array.isArray(json.data)) data = json.data;
        else {
          // server returned something unexpected
          dbg('search: unexpected JSON shape', json);
        }

        
      } catch (e) {
        dbg('search: failed to parse JSON, falling back to demo', e);
      }
    } catch (err) {
      dbg('search fetch failed', err);
    }

    // If no data from server, use demo fallback but still indicate offline behavior
    if (!data) {
      dbg('Using demo fallback results');
      data = [
        { id: 1, full_name: 'Juan Dela Cruz', meta: 'M - 45' },
        { id: 2, full_name: 'Maria Santos', meta: 'F - 42' },
        { id: 3, full_name: 'Jose Reyes', meta: 'M - 20' }
      ].filter(p => p.full_name.toLowerCase().includes(q.toLowerCase()));
    }

    if (!data || data.length === 0) {
      resultsBox.innerHTML = `
        <div class="list-group-item text-center text-muted py-3">
          <i class="fa-solid fa-magnifying-glass mb-2"></i>
          <div class="small i18n" data-en="No results found" data-tl="Walang resulta">No results found</div>
        </div>`;
      emptyBox?.classList.remove('d-none');
      resultsBox.classList.remove('show');
      return;
    }

    // render results as clickable rows (multi-select checkbox style)
    // filter out current person so you cannot add yourself
    if (typeof window.CURRENT_PERSON_ID !== 'undefined' && window.CURRENT_PERSON_ID !== null) {
      data = data.filter(p => String(p.id) !== String(window.CURRENT_PERSON_ID));
    }
    resultsBox.innerHTML = '';
    data.forEach(p => {
      const id = p.id;
      // tolerant name resolution: support multiple server shapes
      const first = p.first_name || p.firstName || p.given_name || null;
      const last = p.last_name || p.lastName || p.family_name || null;
      const full = p.full_name || p.fullname || p.name || p.display_name || (first && last ? `${first} ${last}` : (first || last) ) || 'Unknown';
      const meta = p.meta || p.birthdate || p.age || '';
      const already = selectedMap.has(String(id));
      const row = document.createElement('div');
      row.className = 'list-group-item d-flex gap-3 align-items-start wf-search-row';
      row.innerHTML = `
        <div style="flex:0 0 36px;">
          <input class="form-check-input wf-result-check" type="checkbox" ${already ? 'checked' : ''} data-id="${escapeHtml(String(id))}" />
        </div>
        <div class="flex-grow-1">
          <div class="fw-semibold">${escapeHtml(full)}</div>
          <div class="small text-muted">${escapeHtml(meta)}</div>
        </div>
      `;
      // checkbox handler toggles selectedMap and updates selected list
      const chk = row.querySelector('.wf-result-check');
      chk.addEventListener('change', (e) => {
        const iid = String(e.target.dataset.id);
        if (e.target.checked) {
          selectedMap.set(iid, { id: iid, full_name: full, meta: meta, relationship: '' });
        } else {
          selectedMap.delete(iid);
        }
        renderSelectedList();
      });

      // make the whole row clickable (except the checkbox or other interactive controls)
      row.addEventListener('click', (e) => {
        if (e.target.closest('.wf-result-check') || e.target.closest('.wf-relationship') || e.target.closest('button')) return;
        if (!chk) return;
        // toggle checkbox state and trigger change
        chk.checked = !chk.checked;
        chk.dispatchEvent(new Event('change', { bubbles: true }));
      });
      resultsBox.appendChild(row);
    });

    // show results
    resultsBox.classList.add('show');
  }

  // Selected list render (right pane in modal)
  function renderSelectedList() {
    const container = findSelectedList();
    if (!container) {
      dbg('selected list container not found');
      return;
    }
    container.innerHTML = '';
    if (selectedMap.size === 0) {
      container.innerHTML = '<div class="text-muted">No people selected</div>';
      toggleAddConfirm(false);
      return;
    }

    const frag = document.createDocumentFragment();
    selectedMap.forEach((p, key) => {
      const rel = String(p.relationship || '').toLowerCase();
      const isGrandchild = rel.includes('grand');
      const row = document.createElement('div');
      row.className = 'selected-person p-2 mb-2 border rounded d-flex align-items-center justify-content-between';
      row.innerHTML = `
        <div>
          <div class="fw-semibold">${escapeHtml(p.full_name)}</div>
          <div class="small text-muted">${escapeHtml(p.meta || '')}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm wf-relationship" data-id="${escapeHtml(key)}" style="min-width:150px" ${isGrandchild ? 'disabled' : ''}>
            <option value="">Select relationship</option>
            ${REL_OPTIONS.map(o => `<option value="${o.value}" ${o.value === (p.relationship || '') ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('')}
          </select>
          <button class="btn btn-sm btn-outline-danger wf-remove" data-id="${escapeHtml(key)}" title="Remove" style="${isGrandchild ? 'display:none' : ''}"><i class="fa-solid fa-trash"></i></button>
        </div>
      `;
      frag.appendChild(row);
    });
    container.appendChild(frag);

    // attach handlers
    $$('.wf-relationship', container).forEach(sel => {
      sel.addEventListener('change', (e) => {
        const id = String(e.target.dataset.id);
        const obj = selectedMap.get(id);
        if (obj) { obj.relationship = e.target.value; selectedMap.set(id, obj); }
        updateConfirmState();
      });
    });
    $$('.wf-remove', container).forEach(btn => {
      btn.addEventListener('click', () => {
        const id = String(btn.dataset.id);
        // If this id exists in persisted familyMembers, mark for removal so server is notified
        const existed = Array.isArray(familyMembers) && familyMembers.some(m => String(m.id) === String(id));
        if (existed) {
          removedSet.add(id);
        }
        selectedMap.delete(id);
        // uncheck in results if present
        const chk = findResults()?.querySelector(`.wf-result-check[data-id="${id}"]`);
        if (chk) chk.checked = false;
        renderSelectedList();
      });
    });

    updateConfirmState();
  }

  function updateConfirmState() {
    const addConfirm = findAddConfirm();
    if (!addConfirm) return;
    const allHaveRel = Array.from(selectedMap.values()).every(v => v.relationship && v.relationship.trim() !== '');
    // Allow confirm when there are selected items with relationships OR when there are removals pending
    addConfirm.disabled = !((selectedMap.size > 0 && allHaveRel) || removedSet.size > 0);
  }

  function toggleAddConfirm(enabled) {
    const btn = findAddConfirm();
    if (btn) btn.disabled = !enabled;
  }

  // Expose helper to collect selected into familyMembers array used elsewhere
  function collectSelectedToFamilyMembers() {
    const arr = [];
    selectedMap.forEach(v => {
      arr.push({
        id: v.id,
        full_name: v.full_name,
        meta: v.meta || '',
        relationship: v.relationship || ''
      });
    });
    return arr;
  }

  // on Add & Save footer button we want to persist selected to localStorage and then call controller
  async function addSelectedConfirmAction() {
    const btn = findAddConfirm();
    if (!btn) { dbg('no add confirm'); return; }
    if (btn.disabled) return;

    const members = collectSelectedToFamilyMembers();
    // persist locally (per-person key if available)
    try {
      const pid = window.CURRENT_PERSON_ID ? String(window.CURRENT_PERSON_ID) : 'anon';
      localStorage.setItem(`survey_family_members:${pid}`, JSON.stringify(members));
      dbg('saved members to localStorage', members);
    } catch (e) { dbg('localStorage save failed', e); }

    const results = [];

    // First, process removals (best-effort)
    if (removedSet.size > 0) {
      for (const rid of Array.from(removedSet)) {
        try {
          const fd = new FormData();
          fd.append('member_person_id', String(rid));
          dbg('removing member', rid);
          const res = await fetch(API + '?action=remove_family_member', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept':'application/json' }});
          const txt = await res.text();
          let json = null; try { json = txt ? JSON.parse(txt) : null; } catch(e){}
          dbg('remove response', res.status, json, txt);
          if (!res.ok || !json || !json.success) results.push({ id: rid, op: 'remove', ok: false, reason: json?.message || txt || res.status });
          else results.push({ id: rid, op: 'remove', ok: true });
        } catch (err) {
          dbg('remove error', err);
          results.push({ id: rid, op: 'remove', ok: false, reason: String(err) });
        }
      }
    }

    // Then, process additions/updates
    for (const m of members) {
      try {
        const fd = new FormData();
        fd.append('member_person_id', String(m.id));
        fd.append('relationship', String(m.relationship || 'other'));
        dbg('persisting member', m.id);
        const res = await fetch(API + '?action=add_family_member', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept':'application/json' }});
        const txt = await res.text();
        let json = null; try { json = txt ? JSON.parse(txt) : null; } catch(e){}
        dbg('persist response', res.status, json, txt);
        if (!res.ok || !json || !json.success) results.push({ id: m.id, op: 'add', ok: false, reason: json?.message || txt || res.status });
        else results.push({ id: m.id, op: 'add', ok: true });
      } catch (err) {
        dbg('persist error', err);
        results.push({ id: m.id, op: 'add', ok: false, reason: String(err) });
      }
    }

    const failed = results.filter(r => !r.ok);
    if (failed.length > 0) {
      toast(`${failed.length} operation(s) failed (kept locally)`, 'warning');
      console.warn('persist/remove failures', failed);
    } else {
      toast('Family members updated', 'success');
    }

    // clear removedSet on success for removed items
    if (removedSet.size > 0) {
      const removedFailed = results.filter(r => r.op === 'remove' && !r.ok);
      if (removedFailed.length === 0) removedSet.clear();
    }

    // close modal (if present)
    try { bootstrap.Modal.getOrCreateInstance($('#addFamilyModal'))?.hide(); } catch(e) { $('#addFamilyModal')?.classList.remove('show'); }
    // refresh the page UI list from server (preferred)
    await loadFamilyMembersFromServer();
  }

  // load saved family members from local storage and populate familyMembers + UI
  function loadFamilyMembersFromStorage() {
    try {
      const pid = window.CURRENT_PERSON_ID ? String(window.CURRENT_PERSON_ID) : 'anon';
      const raw = localStorage.getItem(`survey_family_members:${pid}`);
      if (!raw) {
        familyMembers = [];
        renderFamilyMembers();
        return;
      }
      const arr = JSON.parse(raw);
      if (Array.isArray(arr)) familyMembers = arr.map(a => ({ id: a.id, name: a.full_name || a.name || '', relationship: a.relationship || '', sex: a.sex || '' }));
      else familyMembers = [];
      renderFamilyMembers();
    } catch (e) {
      console.warn('loadFamilyMembersFromStorage failed', e);
      familyMembers = [];
      renderFamilyMembers();
    }
  }

  // Attempt to load canonical family members from server; falls back to local storage on failure
  async function loadFamilyMembersFromServer() {
    try {
      const pid = window.CURRENT_PERSON_ID ? String(window.CURRENT_PERSON_ID) : null;
      const relUrl = API + '?action=get_person_relationships' + (pid ? '&person_id=' + encodeURIComponent(pid) : '') + '&debug=1';
      const relResp = await fetch(relUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      const relText = await relResp.text();
      let relJson = null; try { relJson = relText ? JSON.parse(relText) : null; } catch(e) { relJson = null; }
      if (relJson && relJson.debug_sql) {
        console.groupCollapsed('get_person_relationships SQL debug');
        console.debug(relJson.debug_sql);
        console.groupEnd();
      }
      if (relJson && relJson.success && Array.isArray(relJson.data)) {
        familyMembers = relJson.data.map(it => ({ id: it.id, full_name: it.full_name || it.fullname || it.name || '', relationship: it.relationship_type || '', sex: it.sex || '' }));
        renderFamilyMembers();
        return true;
      }
    } catch (err) {
      dbg('loadFamilyMembersFromServer failed', err);
    }
    // fallback
    loadFamilyMembersFromStorage();
    return false;
  }

  // Save current familyMembers to server using save_family action; server will sync relationships
  async function saveFamilyMembersToServer() {
    // Ensure familyMembers is up-to-date from the UI before saving
    const currentMembers = [];
    $$('#family-members-list .wf-main-relationship').forEach(sel => {
      const id = sel.dataset.id;
      const member = familyMembers.find(m => String(m.id) === String(id));
      if (member) {
        currentMembers.push({ id: id, relationship: sel.value || 'other' });
      }
    });

    try {
      const form = new FormData();
      form.append('family_members', JSON.stringify(currentMembers));
      const res = await fetch(API + '?action=save_family', { method: 'POST', body: form, credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      const txt = await res.text(); let json = null; try { json = txt ? JSON.parse(txt) : null; } catch(e) { json = null; }
      if (!res.ok || !json || !json.success) {
        console.warn('saveFamilyMembersToServer failed', res.status, json, txt);
        return false;
      }
      return true;
    } catch (e) {
      console.error('saveFamilyMembersToServer error', e);
      toast('Network error while saving family', 'warning');
      return false;
    }
  }

  // Populate the modal's selectedMap from the server (preferred) or storage so user can edit relationships
  async function populateSelectedFromServer() {
    try {
      // Prefer server canonical data
      const ok = await loadFamilyMembersFromServer();
      if (!ok) {
        // fallback already loaded into familyMembers by loadFamilyMembersFromServer
        dbg('populateSelectedFromServer: using local fallback');
      }
      selectedMap.clear();
      (familyMembers || []).forEach(m => {
        const id = String(m.id || m.id === 0 ? m.id : '');
        if (!id) return;
        selectedMap.set(id, { id: id, full_name: m.name || m.full_name || m.fullname || '', meta: m.meta || '', relationship: m.relationship || '' });
      });
      renderSelectedList();

      // ensure checkboxes in current results reflect selectedMap
      const results = findResults();
      if (results) {
        Array.from(results.querySelectorAll('.wf-result-check[data-id]')).forEach(chk => {
          chk.checked = selectedMap.has(String(chk.dataset.id));
        });
      }
    } catch (e) {
      dbg('populateSelectedFromServer failed', e);
    }
  }

  // ========== Form wiring ==========
  function initFormSubmission() {
    const form = document.getElementById('form-family');
    if (!form) { dbg('form-family not found'); return; }

    // ensure a hidden input for family_members exists and will be filled on submit
    let hidden = form.querySelector('input[name="family_members"]');
    if (!hidden) {
      hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'family_members';
      form.appendChild(hidden);
    }

    form.addEventListener('submit', (e) => {
      try { hidden.value = JSON.stringify(familyMembers || []); } catch(err) { hidden.value = '[]'; }
      // allow central save-survey.js to handle the POST and navigation
    });

    // Make the footer Save & Continue button trigger the form submit (defensive)
    const footerBtn = document.getElementById('btn-save-continue');
    if (footerBtn && !footerBtn.__wf_bound) {
      footerBtn.addEventListener('click', (ev) => {
        ev.preventDefault();
        // ensure familyMembers up to date with selectedMap
        familyMembers = collectSelectedToFamilyMembers();
        saveFamilyToStorage();
        // dispatch submit so save-survey.js picks it up
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      });
      footerBtn.__wf_bound = true;
    }
  }

  // ========== Toast (same canonical API) ==========
  function toast(message, type = 'success') {
    if (window.surveyCreateAlert) { window.surveyCreateAlert(message, type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info')); return; }
    window._pendingSurveyAlerts = window._pendingSurveyAlerts || [];
    window._pendingSurveyAlerts.push({ message, type: type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info') });
  }

  // ========== Family Tree (simple renderer) ==========
  function initFamilyTree() {
    const modal = document.getElementById('family-tree-modal');
    const svg = document.getElementById('tree-svg');
    if (!svg) {
      dbg('initFamilyTree: #tree-svg not found');
      return;
    }

    const NS = 'http://www.w3.org/2000/svg';

    // Add defs for gradient
    const defs = document.createElementNS(NS, 'defs');
    defs.innerHTML = `
      <linearGradient id="nodeGradient" x1="0%" y1="0%" x2="0%" y2="100%">
        <stop offset="0%" style="stop-color:#f8fafc;stop-opacity:1" />
        <stop offset="100%" style="stop-color:#e2e8f0;stop-opacity:1" />
      </linearGradient>
    `;
    svg.appendChild(defs);

    // Zoom and Pan functionality
    let isPanning = false;
    let startPoint = { x: 0, y: 0 };
    svg.style.cursor = 'grab';

    svg.addEventListener('wheel', (event) => {
      event.preventDefault();
      const viewBox = svg.getAttribute('viewBox').split(' ').map(Number);
      if (viewBox.length !== 4) return;
      const [vx, vy, vw, vh] = viewBox;
      
      const zoomFactor = event.deltaY < 0 ? 0.9 : 1.1;
      const newWidth = vw * zoomFactor;
      const newHeight = vh * zoomFactor;

      const pt = new DOMPoint(event.clientX, event.clientY);
      const cursorpt = pt.matrixTransform(svg.getScreenCTM().inverse());

      const newX = cursorpt.x - (cursorpt.x - vx) * zoomFactor;
      const newY = cursorpt.y - (cursorpt.y - vy) * zoomFactor;

      svg.setAttribute('viewBox', `${newX} ${newY} ${newWidth} ${newHeight}`);
    });

    svg.addEventListener('mousedown', (event) => {
      if (event.button !== 0) return; // Only left click
      isPanning = true;
      startPoint = { x: event.clientX, y: event.clientY };
      svg.style.cursor = 'grabbing';
    });

    svg.addEventListener('mousemove', (event) => {
      if (!isPanning) return;
      event.preventDefault();
      
      const viewBox = svg.getAttribute('viewBox').split(' ').map(Number);
      if (viewBox.length !== 4) return;
      const [vx, vy, vw, vh] = viewBox;

      const dx = (startPoint.x - event.clientX) * (vw / svg.clientWidth);
      const dy = (startPoint.y - event.clientY) * (vh / svg.clientHeight);

      svg.setAttribute('viewBox', `${vx + dx} ${vy + dy} ${vw} ${vh}`);
      startPoint = { x: event.clientX, y: event.clientY };
    });

    svg.addEventListener('mouseup', () => {
      isPanning = false;
      svg.style.cursor = 'grab';
    });
    
    svg.addEventListener('mouseleave', () => {
      if (isPanning) {
        isPanning = false;
        svg.style.cursor = 'grab';
      }
    });


    function clearSvg() {
      // Keep defs and pan/zoom listeners, remove other elements
      const nodes = svg.getElementById('tree-nodes');
      const edges = svg.getElementById('tree-edges');
      if (nodes) nodes.remove();
      if (edges) edges.remove();
      const placeholder = svg.querySelector('.placeholder');
      if(placeholder) placeholder.remove();
    }

    async function drawTree() {
      try {
        // Try to fetch canonical relationships from server first
        let fetched = false;
        try {
          const pid = window.CURRENT_PERSON_ID ? String(window.CURRENT_PERSON_ID) : null;
          const relUrl = API + '?action=get_person_relationships' + (pid ? '&person_id=' + encodeURIComponent(pid) : '') + '&debug=1';
          const relResp = await fetch(relUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
          const relText = await relResp.text();
          let relJson = null; try { relJson = relText ? JSON.parse(relText) : null; } catch(e) { relJson = null; }
          if (relJson && relJson.debug_sql) {
            console.groupCollapsed('get_person_relationships SQL debug');
            console.debug(relJson.debug_sql);
            console.groupEnd();
          }
          if (relJson && relJson.success && Array.isArray(relJson.data)) {
            // Normalize server rows into familyMembers shape expected by renderer
            const edges = Array.isArray(relJson.edges) ? relJson.edges : [];

            const inverseMap = {
              // This map is for display purposes in the tree, converting from the perspective of the other person
              // to the perspective of the logged-in user.
              'parent': 'child', 'child': 'parent', 'spouse': 'spouse', 'sibling': 'sibling',
              'grandparent': 'grandchild', 'grandchild': 'grandparent',
              'guardian': 'ward', 'ward': 'guardian',
              'step_parent': 'step_child', 'step_child': 'step_parent',
              'adoptive_parent': 'adopted_child', 'adopted_child': 'adoptive_parent',
              'other': 'other'
            };

            familyMembers = relJson.data.map(it => {
              const rel = (it.relationship_type || '').toLowerCase();
              return { id: it.id, full_name: it.full_name || it.fullname || it.name || '', relationship: rel, sex: it.sex || '' };
            });

            window.__wf_last_tree_edges = edges;
            fetched = true;
          }
        } catch (err) {
          dbg('get_person_relationships fetch failed', err);
        }

        if (!fetched) {
          loadFamilyMembersFromStorage(); // fallback to local storage
        }

        clearSvg();
        if (!Array.isArray(familyMembers) || familyMembers.length === 0) {
          const t = document.createElementNS(NS, 'text');
          t.setAttribute('class', 'placeholder');
          t.setAttribute('x', '50%');
          t.setAttribute('y', '50%');
          t.setAttribute('text-anchor', 'middle');
          t.setAttribute('fill', '#94a3b8');
          t.textContent = 'Family tree will render here';
          svg.appendChild(t);
          return;
        }

        // compute sizes
        const bbox = svg.getBoundingClientRect();
        const width = Math.max(800, bbox.width || 800);
        const height = width; // Make it square
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

        const nodeW = 160;
        const nodeH = 44;
        const gap = 18;
        const total = familyMembers.length;
        const totalWidth = total * nodeW + Math.max(0, total - 1) * gap;
        const startX = Math.max(20, (width - totalWidth) / 2);
        const centerY = height / 2;

        // Relationship-aware layout
        const curName = (typeof window.CURRENT_PERSON_NAME !== 'undefined' && window.CURRENT_PERSON_NAME) ? window.CURRENT_PERSON_NAME : 'You';

        // Group members by relationship
        const parents = familyMembers.filter(m => String(m.relationship).toLowerCase() === 'parent' || String(m.relationship).toLowerCase() === 'grandparent');
        const spouses = familyMembers.filter(m => String(m.relationship).toLowerCase() === 'spouse');
        const children = familyMembers.filter(m => String(m.relationship).toLowerCase() === 'child' || String(m.relationship).toLowerCase() === 'grandchild');
        const grandchildren = familyMembers.filter(m => String(m.relationship).toLowerCase() === 'grandchild');
        let siblings = familyMembers.filter(m => String(m.relationship).toLowerCase() === 'sibling');
        let others = familyMembers.filter(m => {
          const r = String(m.relationship).toLowerCase();
          return !['parent','spouse','child','sibling'].includes(r) && !r.includes('grand');
        });

        // Enforce two-generation limit: do not render siblings or other distant relations in the SVG tree
        siblings = [];
        others = [];

        // vertical positions (compute from the svg viewBox so layout centers correctly)
        const grandparentRowY = 40;
        const parentRowY = grandparentRowY + nodeH + 60;

        // Compute center of the SVG (middle of the viewBox)
        const centerNodeY = parentRowY + nodeH + 60;

        // Place parents above the center and children below the center using fixed offsets
        // Tune these offsets to adjust vertical spacing.
        const childRowY  = centerNodeY + nodeH + 60;
        const grandchildRowY = childRowY + nodeH + 60;

        // helper to place a row of nodes centered (y param is the top position where nodes should be placed)
        function placeRow(arr, y) {
          if (!arr || arr.length === 0) return [];
          const count = arr.length;
          const rowTotalW = count * nodeW + Math.max(0, count - 1) * gap;
          const rowStartX = Math.max(20, Math.round((width - rowTotalW) / 2));
          const positions = [];
          arr.forEach((m, i) => {
            const gx = rowStartX + i * (nodeW + gap);
            // We want to translate the group so the rect's top-left is at (gx, y - nodeH/2) in previous usage.
            // Current drawing code expects pos.y to be the top (not center), so keep y as-is (consistent with usage)
            positions.push({ m, x: gx, y: y - nodeH / 2 });
          });
          return positions;
        }

        // compute a point on the border of a rectangle pointing towards a target point
        function rectEdgePoint(rectX, rectY, rectW, rectH, targetX, targetY) {
          const cx = rectX + rectW / 2;
          const cy = rectY + rectH / 2;
          const dx = targetX - cx;
          const dy = targetY - cy;
          if (dx === 0 && dy === 0) return { x: cx, y: cy };
          const hw = rectW / 2;
          const hh = rectH / 2;
          const tx = dx === 0 ? Infinity : hw / Math.abs(dx);
          const ty = dy === 0 ? Infinity : hh / Math.abs(dy);
          const t = Math.min(tx, ty);
          return { x: cx + dx * t, y: cy + dy * t };
        }

        // create groups: edges behind nodes so lines don't draw on top of nodes
        const edgesGroup = document.createElementNS(NS, 'g'); edgesGroup.setAttribute('id','tree-edges');
        const nodesGroup = document.createElementNS(NS, 'g'); nodesGroup.setAttribute('id','tree-nodes');
        svg.appendChild(edgesGroup);
        svg.appendChild(nodesGroup);

        // draw center node into nodesGroup
        const centerNodeX = Math.round(width / 2 - nodeW / 2);
        // Replace the existing drawCenter IIFE with this block
        (function drawCenter(){
          const g = document.createElementNS(NS,'g');
          // place the group's top-left at the rect top-left (center the rect at centerNodeY)
          g.setAttribute('transform', `translate(${centerNodeX}, ${centerNodeY - nodeH/2})`);

          // Node background rectangle
          const rect = document.createElementNS(NS,'rect');
          rect.setAttribute('x','0');
          rect.setAttribute('y','0');
          rect.setAttribute('rx','6');
          rect.setAttribute('ry','6');
          rect.setAttribute('width', String(nodeW));
          rect.setAttribute('height', String(nodeH));
          rect.setAttribute('fill','url(#nodeGradient)');
          rect.setAttribute('stroke','#cbd5e1');
          rect.setAttribute('stroke-width','1');
          g.appendChild(rect);

          // Create a group centered inside the rect to hold text so baseline quirks are avoided.
          // Translate to rect center (nodeW/2, nodeH/2)
          const tg = document.createElementNS(NS,'g');
          tg.setAttribute('transform', `translate(${nodeW/2}, ${nodeH/2})`);
          tg.setAttribute('aria-hidden', 'false');

          // Name text — x=0 (centered by transform), y negative to sit slightly above center
          const nameText = document.createElementNS(NS,'text');
          nameText.setAttribute('x', '0');
          nameText.setAttribute('y', String(-6)); // nudge up a bit
          nameText.setAttribute('text-anchor', 'middle');
          nameText.setAttribute('fill', '#0f172a');
          nameText.style.fontSize = '13px';
          nameText.style.fontFamily = 'inherit';
          nameText.textContent = (curName || '').toString();
          tg.appendChild(nameText);

          // Subtitle text — below the name
          const relText = document.createElementNS(NS,'text');
          relText.setAttribute('x', '0');
          relText.setAttribute('y', String(12)); // below center
          relText.setAttribute('text-anchor', 'middle');
          relText.setAttribute('fill', '#475569');
          relText.style.fontSize = '11px';
          relText.style.fontFamily = 'inherit';
          relText.textContent = '(You)';
          tg.appendChild(relText);

          g.appendChild(tg);
          nodesGroup.appendChild(g);
        })();
        // Draw parents (above center)
        const justParents = parents.filter(p => p.relationship === 'parent');
        const justGrandparents = parents.filter(p => p.relationship === 'grandparent');
        const parentPositions = placeRow(justParents, parentRowY + nodeH/2);
        parentPositions.forEach(pos => {
          pos.m.nodeType = 'parent';
          const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
          const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
          const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || pos.m.name || pos.m.fullname || 'Unknown'; g.appendChild(t);
          const relLabel = document.createElementNS(NS,'text'); relLabel.setAttribute('x',String(nodeW/2)); relLabel.setAttribute('y',String(nodeH/2 + 12)); relLabel.setAttribute('text-anchor','middle'); relLabel.setAttribute('dominant-baseline','middle'); relLabel.setAttribute('fill','#475569'); relLabel.style.fontSize='11px'; relLabel.textContent = pos.m.relationship || '';
          g.appendChild(relLabel);
          nodesGroup.appendChild(g);

          // connector line to center (draw into edgesGroup so it sits behind nodes)
          const parentRectX = pos.x; const parentRectY = pos.y;
          const parentCx = parentRectX + nodeW/2; const parentCy = parentRectY + nodeH/2;
          const centerRectX = centerNodeX; const centerRectY = centerNodeY - nodeH/2;
          const centerCx = centerRectX + nodeW/2; const centerCy = centerRectY + nodeH/2;
          const startP = rectEdgePoint(parentRectX, parentRectY, nodeW, nodeH, centerCx, centerCy);
          const endP = rectEdgePoint(centerRectX, centerRectY, nodeW, nodeH, parentCx, parentCy);
          const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(startP.x)); line.setAttribute('y1', String(startP.y)); line.setAttribute('x2', String(endP.x)); line.setAttribute('y2', String(endP.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.5'); edgesGroup.appendChild(line);
        });
        
        // Draw grandparents
        const grandparentPositions = placeRow(justGrandparents, grandparentRowY + nodeH/2);
        grandparentPositions.forEach(pos => {
            pos.m.nodeType = 'grandparent';
            const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
            const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
            const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || 'Unknown'; g.appendChild(t);
            const relLabel = document.createElementNS(NS,'text'); relLabel.setAttribute('x',String(nodeW/2)); relLabel.setAttribute('y',String(nodeH/2 + 12)); relLabel.setAttribute('text-anchor','middle'); relLabel.setAttribute('dominant-baseline','middle'); relLabel.setAttribute('fill','#475569'); relLabel.style.fontSize='11px'; relLabel.textContent = pos.m.relationship || '';
            g.appendChild(relLabel);
            nodesGroup.appendChild(g);

            // Find this grandparent's child who is the current person's parent
            const childNode = parentPositions.find(p => (window.__wf_last_tree_edges || []).some(edge => 
                (String(edge.person_id) === String(pos.m.id) && String(edge.related_person_id) === String(p.m.id) && edge.relationship_type === 'child') ||
                (String(edge.related_person_id) === String(pos.m.id) && String(edge.person_id) === String(p.m.id) && edge.relationship_type === 'parent')
            ));

            if (childNode) {
                const gpRectX = pos.x, gpRectY = pos.y;
                const gpCx = gpRectX + nodeW/2, gpCy = gpRectY + nodeH/2;
                const pRectX = childNode.x, pRectY = childNode.y;
                const pCx = pRectX + nodeW/2, pCy = pRectY + nodeH/2;
                const startP = rectEdgePoint(gpRectX, gpRectY, nodeW, nodeH, pCx, pCy);
                const endP = rectEdgePoint(pRectX, pRectY, nodeW, nodeH, gpCx, gpCy);
                const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(startP.x)); line.setAttribute('y1', String(startP.y)); line.setAttribute('x2', String(endP.x)); line.setAttribute('y2', String(endP.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.5'); edgesGroup.appendChild(line);
            }
        });

        // Draw spouses adjacent to center (left/right)
        const spousePositions = [];
        if (spouses.length > 0) {
          const sposCount = spouses.length;
          // spread spouses around center to the right then left with more horizontal gap
          const baseSpouseOffset = nodeW + 40;
          spouses.forEach((m, idx) => {
            const sideOffset = (idx % 2 === 0) ? 1 : -1; // alternate sides
            const ord = Math.floor(idx/2);
            const gx = centerNodeX + sideOffset * (baseSpouseOffset + ord * (nodeW + gap));
            const gy = centerNodeY - nodeH/2;
            spousePositions.push({ m, x: gx, y: gy });
            const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${gx}, ${gy})`); g.setAttribute('data-id', String(m.id));
            const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
            const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = m.full_name || m.name || m.fullname || 'Spouse'; g.appendChild(t);
              const rel = document.createElementNS(NS,'text'); rel.setAttribute('x',String(nodeW/2)); rel.setAttribute('y',String(nodeH/2 + 12)); rel.setAttribute('text-anchor','middle'); rel.setAttribute('dominant-baseline','middle'); rel.setAttribute('fill','#475569'); rel.style.fontSize='11px'; rel.textContent = m.relationship || 'spouse'; g.appendChild(rel);
            nodesGroup.appendChild(g);

            // connector line between center and spouse (computed edge-to-edge)
            const spouseRectX = gx; const spouseRectY = gy;
            const spouseCx = spouseRectX + nodeW/2; const spouseCy = spouseRectY + nodeH/2;
            const centerRectX2 = centerNodeX; const centerRectY2 = centerNodeY - nodeH/2;
            const centerCx2 = centerRectX2 + nodeW/2; const centerCy2 = centerRectY2 + nodeH/2;
            const sStart = rectEdgePoint(centerRectX2, centerRectY2, nodeW, nodeH, spouseCx, spouseCy);
            const sEnd = rectEdgePoint(spouseRectX, spouseRectY, nodeW, nodeH, centerCx2, centerCy2);
            const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(sStart.x)); line.setAttribute('y1', String(sStart.y)); line.setAttribute('x2', String(sEnd.x)); line.setAttribute('y2', String(sEnd.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.5'); edgesGroup.appendChild(line);
          });
        }

        // Draw children row
        const justChildren = children.filter(c => c.relationship === 'child');
        const childPositions = placeRow(justChildren, childRowY + nodeH/2);
        childPositions.forEach(pos => {
          pos.m.nodeType = 'child';
          const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
          const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
          const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || pos.m.name || pos.m.fullname || 'Unknown'; g.appendChild(t);
          const rel = document.createElementNS(NS,'text'); rel.setAttribute('x',String(nodeW/2)); rel.setAttribute('y',String(nodeH/2 + 12)); rel.setAttribute('text-anchor','middle'); rel.setAttribute('dominant-baseline','middle'); rel.setAttribute('fill','#475569'); rel.style.fontSize='11px'; rel.textContent = pos.m.relationship || 'child'; g.appendChild(rel);
          nodesGroup.appendChild(g);

          // connector center -> child (edge-to-edge)
          const childRectX = pos.x; const childRectY = pos.y;
          const childCx = childRectX + nodeW/2; const childCy = childRectY + nodeH/2;
          const centerRectX3 = centerNodeX; const centerRectY3 = centerNodeY - nodeH/2;
          const centerCx3 = centerRectX3 + nodeW/2; const centerCy3 = centerRectY3 + nodeH/2;
          const cStart = rectEdgePoint(centerRectX3, centerRectY3, nodeW, nodeH, childCx, childCy);
          const cEnd = rectEdgePoint(childRectX, childRectY, nodeW, nodeH, centerCx3, centerCy3);
          const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(cStart.x)); line.setAttribute('y1', String(cStart.y)); line.setAttribute('x2', String(cEnd.x)); line.setAttribute('y2', String(cEnd.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.5'); edgesGroup.appendChild(line);
        });
        
        // After children are placed, treat spouse(s) as parents for the children by drawing connectors
        if (spousePositions.length > 0 && childPositions.length > 0) {
          spousePositions.forEach(sp => {
            childPositions.forEach(cp => {
              // draw connector spouse -> child (edge-to-edge)
              const sRectX = sp.x; const sRectY = sp.y;
              const sCx = sRectX + nodeW/2; const sCy = sRectY + nodeH/2;
              const cRectX = cp.x; const cRectY = cp.y;
              const cCx = cRectX + nodeW/2; const cCy = cRectY + nodeH/2;
              const startP = rectEdgePoint(sRectX, sRectY, nodeW, nodeH, cCx, cCy);
              const endP = rectEdgePoint(cRectX, cRectY, nodeW, nodeH, sCx, sCy);
              const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(startP.x)); line.setAttribute('y1', String(startP.y)); line.setAttribute('x2', String(endP.x)); line.setAttribute('y2', String(endP.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.2'); edgesGroup.appendChild(line);
            });
          });
        }

        // Draw grandchildren row
        const justGrandchildren = children.filter(c => c.relationship === 'grandchild');
        const grandchildPositions = placeRow(justGrandchildren, grandchildRowY + nodeH/2);
        grandchildPositions.forEach(pos => {
            pos.m.nodeType = 'grandchild';
            const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
            const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
            const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || 'Unknown'; g.appendChild(t);
            const rel = document.createElementNS(NS,'text'); rel.setAttribute('x',String(nodeW/2)); rel.setAttribute('y',String(nodeH/2 + 12)); rel.setAttribute('text-anchor','middle'); rel.setAttribute('dominant-baseline','middle'); rel.setAttribute('fill','#475569'); rel.style.fontSize='11px'; rel.textContent = pos.m.relationship || 'grandchild'; g.appendChild(rel);
            nodesGroup.appendChild(g);

            const parentNode = childPositions.find(c => (window.__wf_last_tree_edges || []).some(edge =>
                (String(edge.person_id) === String(c.m.id) && String(edge.related_person_id) === String(pos.m.id) && edge.relationship_type === 'child') ||
                (String(edge.related_person_id) === String(c.m.id) && String(edge.person_id) === String(pos.m.id) && edge.relationship_type === 'parent')
            ));

            if (parentNode) {
                const gcRectX = pos.x, gcRectY = pos.y;
                const gcCx = gcRectX + nodeW/2, gcCy = gcRectY + nodeH/2;
                const cRectX = parentNode.x, cRectY = parentNode.y;
                const cCx = cRectX + nodeW/2, cCy = cRectY + nodeH/2;
                const startP = rectEdgePoint(gcRectX, gcRectY, nodeW, nodeH, cCx, cCy);
                const endP = rectEdgePoint(cRectX, cRectY, nodeW, nodeH, gcCx, gcCy);
                const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(startP.x)); line.setAttribute('y1', String(startP.y)); line.setAttribute('x2', String(endP.x)); line.setAttribute('y2', String(endP.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.5'); edgesGroup.appendChild(line);
            }
        });

        // Draw siblings: if parents exist, attach siblings to parents (spread across parent row), else attach to side of center
        if (siblings.length > 0) {
          if (justParents.length > 0) {
            // attach siblings grouped under/around the first parent
            const targetParent = parentPositions.length > 0 ? parentPositions[0] : null;
            if (targetParent) {
              const sibPositions = placeRow(siblings, parentRowY + nodeH/2);
              sibPositions.forEach(pos => {
                const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
                const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
                const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || pos.m.name || pos.m.fullname || 'Sibling'; g.appendChild(t);
                const rel = document.createElementNS(NS,'text'); rel.setAttribute('x',String(nodeW/2)); rel.setAttribute('y',String(nodeH/2 + 12)); rel.setAttribute('text-anchor','middle'); rel.setAttribute('dominant-baseline','middle'); rel.setAttribute('fill','#475569'); rel.style.fontSize='11px'; rel.textContent = pos.m.relationship || 'sibling'; g.appendChild(rel);
                nodesGroup.appendChild(g);

                  // Connect sibling to the center node (You)
                  const siblingRectX = pos.x, siblingRectY = pos.y;
                  const siblingCx = siblingRectX + nodeW/2, siblingCy = siblingRectY + nodeH/2;
                  const centerRectX = centerNodeX, centerRectY = centerNodeY - nodeH/2;
                  const centerCx = centerRectX + nodeW/2, centerCy = centerRectY + nodeH/2;
                  const startP = rectEdgePoint(siblingRectX, siblingRectY, nodeW, nodeH, centerCx, centerCy);
                  const endP = rectEdgePoint(centerRectX, centerRectY, nodeW, nodeH, siblingCx, siblingCy);
                  const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(startP.x)); line.setAttribute('y1', String(startP.y)); line.setAttribute('x2', String(endP.x)); line.setAttribute('y2', String(endP.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.5'); edgesGroup.appendChild(line);
              });
            }
          } else {
            // place siblings on the left side of center
            const sibPositions = placeRow(siblings, centerNodeY);
            // shift them to the left of center
            sibPositions.forEach((pos, idx) => {
              const gx = Math.max(20, centerNodeX - (idx + 1) * (nodeW + gap));
              pos.x = gx;
              const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
              const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
              const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || pos.m.name || pos.m.fullname || 'Sibling'; g.appendChild(t);
              const rel = document.createElementNS(NS,'text'); rel.setAttribute('x',String(nodeW/2)); rel.setAttribute('y',String(nodeH/2 + 12)); rel.setAttribute('text-anchor','middle'); rel.setAttribute('dominant-baseline','middle'); rel.setAttribute('fill','#475569'); rel.style.fontSize='11px'; rel.textContent = 'sibling'; g.appendChild(rel);
              nodesGroup.appendChild(g);

              // connector line to center (edgesGroup) - edge-to-edge
              const siblingRectX2 = pos.x; const siblingRectY2 = pos.y;
              const siblingCx2 = siblingRectX2 + nodeW/2; const siblingCy2 = siblingRectY2 + nodeH/2;
              const centerRectX4 = centerNodeX; const centerRectY4 = centerNodeY - nodeH/2;
              const centerCx4 = centerRectX4 + nodeW/2; const centerCy4 = centerRectY4 + nodeH/2;
              const s2Start = rectEdgePoint(siblingRectX2, siblingRectY2, nodeW, nodeH, centerCx4, centerCy4);
              const s2End = rectEdgePoint(centerRectX4, centerRectY4, nodeW, nodeH, siblingCx2, siblingCy2);
              const line = document.createElementNS(NS,'line'); line.setAttribute('x1', String(s2Start.x)); line.setAttribute('y1', String(s2Start.y)); line.setAttribute('x2', String(s2End.x)); line.setAttribute('y2', String(s2End.y)); line.setAttribute('stroke','#94a3b8'); line.setAttribute('stroke-width','1.2'); edgesGroup.appendChild(line);
            });
          }
        }

        // Draw any 'other' relations in a bottom row
        const otherPositions = placeRow(others, grandchildRowY + 80 + nodeH/2);
        otherPositions.forEach(pos => {
          const g = document.createElementNS(NS,'g'); g.setAttribute('transform', `translate(${pos.x}, ${pos.y})`); g.setAttribute('data-id', String(pos.m.id));
          const rect = document.createElementNS(NS,'rect'); rect.setAttribute('x','0'); rect.setAttribute('y','0'); rect.setAttribute('rx','6'); rect.setAttribute('ry','6'); rect.setAttribute('width',String(nodeW)); rect.setAttribute('height',String(nodeH)); rect.setAttribute('fill','url(#nodeGradient)'); rect.setAttribute('stroke','#cbd5e1'); rect.setAttribute('stroke-width','1'); g.appendChild(rect);
          const t = document.createElementNS(NS,'text'); t.setAttribute('x',String(nodeW/2)); t.setAttribute('y',String(nodeH/2 - 4)); t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle'); t.setAttribute('fill','#0f172a'); t.style.fontSize='13px'; t.textContent = pos.m.full_name || pos.m.name || pos.m.fullname || 'Relative'; g.appendChild(t);
          const rel = document.createElementNS(NS,'text'); rel.setAttribute('x',String(nodeW/2)); rel.setAttribute('y',String(nodeH/2 + 12)); rel.setAttribute('text-anchor','middle'); rel.setAttribute('dominant-baseline','middle'); rel.setAttribute('fill','#475569'); rel.style.fontSize='11px'; rel.textContent = pos.m.relationship || ''; g.appendChild(rel);
          nodesGroup.appendChild(g);
        });
      } catch (err) {
        console.error('drawTree failed', err);
      }
    }

    // Trigger draw when the modal is opened
    const opener = document.getElementById('btn-view-tree');
    if (opener && !opener.__wf_tree_bound) {
      opener.addEventListener('click', () => { setTimeout(drawTree, 80); });
      opener.__wf_tree_bound = true;
    }

    if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      modal.addEventListener('shown.bs.modal', drawTree);
    } else if (modal) {
      // fallback: observe class changes
      const mo = new MutationObserver((muts) => {
        muts.forEach((m) => {
          if (m.type === 'attributes' && m.attributeName === 'class') {
            if (modal.classList.contains('show')) setTimeout(drawTree, 40);
          }
        });
      });
      mo.observe(modal, { attributes: true });
    }
  }

  // ========== Init ==========
  async function init() {
    initSearch();
    initFamilyManagement();
    await loadFamilyMembersFromServer();
    // Ensure modal prepopulation so when user opens Add Family Member they see already-added people
    // Expose the save function globally for the inline script to use
    window.wizardFamilySave = saveFamilyMembersToServer;

    try {
      const btn = document.getElementById('btn-open-add-modal');
      const modal = document.getElementById('addFamilyModal');
      if (btn && !btn.__wf_prepop_bound) {
        btn.addEventListener('click', () => { populateSelectedFromServer(); });
        btn.__wf_prepop_bound = true;
      }
      // if bootstrap modal events are available, listen for show event too
      if (modal && modal.addEventListener && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        modal.addEventListener('show.bs.modal', () => { populateSelectedFromServer(); });
      }
    } catch (e) { dbg('modal prepopulate wiring failed', e); }
    initFamilyTree();
    initFormSubmission();
    // init scroll animations if available
    if (typeof IntersectionObserver !== 'undefined') {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, { threshold: 0.1 });
      Array.from(document.querySelectorAll('.section-card')).forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s, transform 0.5s';
        observer.observe(card);
      });
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();

})();