/**
 * wizard_family.js
 * Interactive family relationship management with search and family tree visualization
 */

(function() {
  'use strict';

  // ========== Utility Functions ==========
  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => document.querySelectorAll(sel);

  // ========== State Management ==========
  let familyMembers = []; // Array of {id, name, relationship, sex}
  let selectedPerson = null;
  let searchTimeout = null;
  let treeZoom = 1;
  let treePanX = 0;
  let treePanY = 0;

  // ========== i18n Translation ==========
  function translate() {
    const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
    
    // Save language preference to localStorage
    localStorage.setItem('survey_language', lang);
    
    // Translate text elements
    $$('.i18n').forEach(el => {
      const key = lang === 'tl' ? 'data-tl' : 'data-en';
      const text = el.getAttribute(key);
      if (text) el.textContent = text;
    });

    // Translate placeholders
    $$('.i18n-ph').forEach(el => {
      const key = lang === 'tl' ? 'data-ph-tl' : 'data-ph-en';
      const text = el.getAttribute(key);
      if (text) el.placeholder = text;
    });
  }

  // Restore language preference from localStorage
  function restoreLanguage() {
    const savedLang = localStorage.getItem('survey_language');
    const langEN = $('#lang-en');
    const langTL = $('#lang-tl');
    
    if (savedLang === 'tl' && langTL) {
      langTL.checked = true;
      translate();
    } else if (savedLang === 'en' && langEN) {
      langEN.checked = true;
      translate();
    } else {
      // Default to English if no saved preference
      if (langEN) langEN.checked = true;
      translate();
    }
  }

  // ========== Person Search ==========
  function initSearch() {
    const searchInput = $('#search-person');
    const searchResults = $('#search-results');
    const relationshipSelect = $('#relationship-type');
    const addButton = $('#btn-add-member');

    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.trim();
      
      clearTimeout(searchTimeout);
      
      if (query.length < 2) {
        searchResults.classList.remove('show');
        return;
      }

      searchTimeout = setTimeout(() => {
        performSearch(query);
      }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
      if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.remove('show');
      }
    });

    // Enable add button when person and relationship are selected
    relationshipSelect.addEventListener('change', () => {
      addButton.disabled = !selectedPerson || !relationshipSelect.value;
    });
  }

  function performSearch(query) {
    const searchResults = $('#search-results');
    
    // Demo data - replace with actual API call
    const demoResults = [
      { id: 1, firstName: 'Juan', lastName: 'Dela Cruz', sex: 'M', age: 45 },
      { id: 2, firstName: 'Maria', lastName: 'Santos', sex: 'F', age: 42 },
      { id: 3, firstName: 'Jose', lastName: 'Reyes', sex: 'M', age: 20 },
      { id: 4, firstName: 'Ana', lastName: 'Garcia', sex: 'F', age: 18 },
      { id: 5, firstName: 'Pedro', lastName: 'Ramos', sex: 'M', age: 65 },
    ].filter(p => 
      `${p.firstName} ${p.lastName}`.toLowerCase().includes(query.toLowerCase())
    );

    if (demoResults.length === 0) {
      searchResults.innerHTML = `
        <div class="search-result-item text-center text-muted py-3">
          <i class="fa-solid fa-magnifying-glass mb-2"></i>
          <p class="mb-0 small i18n" data-en="No results found" data-tl="Walang resulta">No results found</p>
        </div>
      `;
    } else {
      searchResults.innerHTML = demoResults.map(person => `
        <div class="search-result-item" data-person-id="${person.id}" data-person-name="${person.firstName} ${person.lastName}" data-person-sex="${person.sex}">
          <div class="d-flex align-items-center gap-3">
            <div class="member-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
              ${person.firstName.charAt(0)}${person.lastName.charAt(0)}
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold">${person.firstName} ${person.lastName}</div>
              <small class="text-muted">
                <i class="fa-solid fa-${person.sex === 'M' ? 'person' : 'person-dress'} me-1"></i>
                ${person.sex === 'M' ? 'Male' : 'Female'}, ${person.age} years old
              </small>
            </div>
          </div>
        </div>
      `).join('');

      // Add click handlers to search results
      $$('.search-result-item').forEach(item => {
        item.addEventListener('click', () => selectPerson(item));
      });
    }

    searchResults.classList.add('show');
    translate();
  }

  function selectPerson(item) {
    const id = parseInt(item.dataset.personId);
    const name = item.dataset.personName;
    const sex = item.dataset.personSex;

    selectedPerson = { id, name, sex };
    
    $('#search-person').value = name;
    $('#search-results').classList.remove('show');
    
    // Enable add button if relationship is selected
    const relationshipSelect = $('#relationship-type');
    $('#btn-add-member').disabled = !relationshipSelect.value;
  }

  // ========== Family Member Management ==========
  function initFamilyManagement() {
    const addButton = $('#btn-add-member');
    
    if (!addButton) return;

    addButton.addEventListener('click', addFamilyMember);
  }

  function addFamilyMember() {
    if (!selectedPerson) return;

    const relationshipSelect = $('#relationship-type');
    const relationship = relationshipSelect.value;
    
    if (!relationship) {
      toast('Please select a relationship', 'warning');
      return;
    }

    // Check if already added
    const exists = familyMembers.find(m => m.id === selectedPerson.id);
    if (exists) {
      toast('This person is already in your family list', 'warning');
      return;
    }

    // Add to family members array
    familyMembers.push({
      id: selectedPerson.id,
      name: selectedPerson.name,
      sex: selectedPerson.sex,
      relationship: relationship
    });

    // Update UI
    renderFamilyMembers();
    
    // Reset form
    $('#search-person').value = '';
    relationshipSelect.value = '';
    $('#btn-add-member').disabled = true;
    selectedPerson = null;

    const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
    toast(
      lang === 'tl' ? 'Naidagdag ang miyembro ng pamilya!' : 'Family member added!',
      'success'
    );
  }

  function renderFamilyMembers() {
    const container = $('#family-members-list');
    const memberCount = $('#member-count');
    
    if (!container) return;

    memberCount.textContent = familyMembers.length;

    if (familyMembers.length === 0) {
      container.innerHTML = `
        <div class="col-12 text-center text-muted py-5">
          <i class="fa-solid fa-users fs-1 mb-3 d-block" style="opacity: 0.3;"></i>
          <p class="i18n" data-en="No family members added yet. Search and add members above."
             data-tl="Walang miyembro ng pamilya na naidagdag pa. Maghanap at magdagdag ng mga miyembro sa itaas.">
            No family members added yet. Search and add members above.
          </p>
        </div>
      `;
      translate();
      return;
    }

    container.innerHTML = familyMembers.map((member, index) => `
      <div class="col-12 col-md-6">
        <div class="family-member-card">
          <div class="d-flex align-items-center gap-3">
            <div class="member-avatar">
              ${member.name.split(' ').map(n => n.charAt(0)).join('').substring(0, 2)}
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold mb-1">${member.name}</div>
              <div class="relationship-badge">${formatRelationship(member.relationship)}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.removeFamilyMember(${index})">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  function formatRelationship(rel) {
    const map = {
      parent: 'Parent',
      child: 'Child',
      spouse: 'Spouse',
      sibling: 'Sibling',
      grandparent: 'Grandparent',
      grandchild: 'Grandchild',
      guardian: 'Guardian',
      ward: 'Ward',
      step_parent: 'Step Parent',
      step_child: 'Step Child',
      adoptive_parent: 'Adoptive Parent',
      adopted_child: 'Adopted Child',
      other: 'Other'
    };
    return map[rel] || rel;
  }

  window.removeFamilyMember = function(index) {
    const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
    const confirmMsg = lang === 'tl' 
      ? 'Sigurado ka bang gusto mong alisin ang miyembrong ito?'
      : 'Are you sure you want to remove this member?';
    
    if (confirm(confirmMsg)) {
      familyMembers.splice(index, 1);
      renderFamilyMembers();
      toast(
        lang === 'tl' ? 'Naalis ang miyembro' : 'Member removed',
        'info'
      );
    }
  };

  // ========== Family Tree Visualization ==========
  function initFamilyTree() {
    const viewTreeBtn = $('#btn-view-tree');
    const closeTreeBtn = $('#btn-close-tree');
    const modal = $('#family-tree-modal');
    
    if (!viewTreeBtn) return;

    viewTreeBtn.addEventListener('click', () => {
      if (familyMembers.length === 0) {
        const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
        toast(
          lang === 'tl' ? 'Magdagdag muna ng mga miyembro ng pamilya' : 'Please add family members first',
          'warning'
        );
        return;
      }
      openFamilyTree();
    });

    closeTreeBtn?.addEventListener('click', closeFamilyTree);
    
    modal?.addEventListener('click', (e) => {
      if (e.target === modal) closeFamilyTree();
    });

    // Tree controls
    $('#btn-tree-zoom-in')?.addEventListener('click', () => zoomTree(0.1));
    $('#btn-tree-zoom-out')?.addEventListener('click', () => zoomTree(-0.1));
    $('#btn-tree-reset')?.addEventListener('click', resetTreeView);
  }

  function openFamilyTree() {
    const modal = $('#family-tree-modal');
    modal?.classList.add('show');
    renderFamilyTree();
  }

  function closeFamilyTree() {
    const modal = $('#family-tree-modal');
    modal?.classList.remove('show');
  }

  function renderFamilyTree() {
    const canvas = $('#tree-canvas');
    const svg = $('#tree-svg');
    
    if (!canvas || !svg) return;

    // Clear existing nodes
    const existingNodes = $$('.tree-node');
    existingNodes.forEach(node => node.remove());

    // Set canvas size
    const canvasWidth = Math.max(1200, familyMembers.length * 200);
    const canvasHeight = 800;
    canvas.style.width = canvasWidth + 'px';
    canvas.style.height = canvasHeight + 'px';
    svg.setAttribute('width', canvasWidth);
    svg.setAttribute('height', canvasHeight);

    // Layout algorithm - simple horizontal layout by relationship type
    const layout = calculateTreeLayout();
    
    // Render connections first (so they appear behind nodes)
    renderConnections(layout);
    
    // Render nodes
    layout.forEach((nodeData, index) => {
      const node = createTreeNode(nodeData, index);
      canvas.appendChild(node);
    });

    // Reset view
    resetTreeView();
  }

  function calculateTreeLayout() {
    const layout = [];
    const centerX = 600;
    const centerY = 400;
    
    // Group by relationship type
    const groups = {
      current: [],
      parents: [],
      children: [],
      siblings: [],
      spouses: [],
      grandparents: [],
      grandchildren: [],
      others: []
    };

    // Add current user (self)
    groups.current.push({
      id: 0,
      name: 'You',
      relationship: 'self',
      isSelf: true,
      x: centerX,
      y: centerY
    });

    // Group family members
    familyMembers.forEach(member => {
      const nodeData = { ...member, isSelf: false };
      
      if (member.relationship === 'parent' || member.relationship === 'adoptive_parent' || member.relationship === 'step_parent') {
        groups.parents.push(nodeData);
      } else if (member.relationship === 'child' || member.relationship === 'adopted_child' || member.relationship === 'step_child') {
        groups.children.push(nodeData);
      } else if (member.relationship === 'sibling') {
        groups.siblings.push(nodeData);
      } else if (member.relationship === 'spouse') {
        groups.spouses.push(nodeData);
      } else if (member.relationship === 'grandparent') {
        groups.grandparents.push(nodeData);
      } else if (member.relationship === 'grandchild') {
        groups.grandchildren.push(nodeData);
      } else {
        groups.others.push(nodeData);
      }
    });

    // Position nodes
    layout.push(...groups.current);

    // Spouses - to the right
    groups.spouses.forEach((node, i) => {
      node.x = centerX + 200;
      node.y = centerY + (i - groups.spouses.length / 2) * 100;
      layout.push(node);
    });

    // Parents - above
    groups.parents.forEach((node, i) => {
      node.x = centerX + (i - groups.parents.length / 2) * 200;
      node.y = centerY - 200;
      layout.push(node);
    });

    // Grandparents - above parents
    groups.grandparents.forEach((node, i) => {
      node.x = centerX + (i - groups.grandparents.length / 2) * 200;
      node.y = centerY - 400;
      layout.push(node);
    });

    // Siblings - to the left
    groups.siblings.forEach((node, i) => {
      node.x = centerX - 250;
      node.y = centerY + (i - groups.siblings.length / 2) * 100;
      layout.push(node);
    });

    // Children - below
    groups.children.forEach((node, i) => {
      node.x = centerX + (i - groups.children.length / 2) * 200;
      node.y = centerY + 200;
      layout.push(node);
    });

    // Grandchildren - below children
    groups.grandchildren.forEach((node, i) => {
      node.x = centerX + (i - groups.grandchildren.length / 2) * 200;
      node.y = centerY + 400;
      layout.push(node);
    });

    // Others - scattered
    groups.others.forEach((node, i) => {
      node.x = centerX + 300 + (i % 3) * 150;
      node.y = centerY - 100 + Math.floor(i / 3) * 150;
      layout.push(node);
    });

    return layout;
  }

  function createTreeNode(nodeData, index) {
    const node = document.createElement('div');
    node.className = 'tree-node' + (nodeData.isSelf ? ' current-user' : '');
    node.style.left = nodeData.x + 'px';
    node.style.top = nodeData.y + 'px';
    node.dataset.nodeId = index;

    const initials = nodeData.name.split(' ').map(n => n.charAt(0)).join('').substring(0, 2);
    
    node.innerHTML = `
      <div class="tree-node-avatar">${initials}</div>
      <div class="tree-node-name">${nodeData.name}</div>
      <div class="tree-node-relation">${nodeData.isSelf ? 'You' : formatRelationship(nodeData.relationship)}</div>
    `;

    // Make draggable
    makeDraggable(node, nodeData);

    return node;
  }

  function renderConnections(layout) {
    const svg = $('#tree-svg');
    if (!svg) return;

    svg.innerHTML = ''; // Clear existing connections

    const selfNode = layout.find(n => n.isSelf);
    if (!selfNode) return;

    // Draw lines from self to all family members
    layout.forEach(node => {
      if (node.isSelf) return;

      const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      const path = `M ${selfNode.x + 70} ${selfNode.y + 40} Q ${(selfNode.x + node.x) / 2 + 70} ${(selfNode.y + node.y) / 2} ${node.x + 70} ${node.y + 40}`;
      
      line.setAttribute('d', path);
      line.setAttribute('class', 'tree-connection');
      svg.appendChild(line);
    });
  }

  function makeDraggable(element, nodeData) {
    let isDragging = false;
    let startX, startY, initialX, initialY;

    element.addEventListener('mousedown', startDrag);
    
    function startDrag(e) {
      isDragging = true;
      startX = e.clientX;
      startY = e.clientY;
      initialX = nodeData.x;
      initialY = nodeData.y;
      
      document.addEventListener('mousemove', drag);
      document.addEventListener('mouseup', stopDrag);
      
      element.style.cursor = 'grabbing';
    }

    function drag(e) {
      if (!isDragging) return;
      
      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
      
      nodeData.x = initialX + dx;
      nodeData.y = initialY + dy;
      
      element.style.left = nodeData.x + 'px';
      element.style.top = nodeData.y + 'px';
      
      // Redraw connections
      const layout = Array.from($$('.tree-node')).map(node => {
        const id = parseInt(node.dataset.nodeId);
        return {
          x: parseInt(node.style.left),
          y: parseInt(node.style.top),
          isSelf: node.classList.contains('current-user')
        };
      });
      
      // Update connections (simplified)
      const svg = $('#tree-svg');
      if (svg) {
        const selfNode = layout.find(n => n.isSelf);
        if (selfNode) {
          svg.innerHTML = '';
          layout.forEach(node => {
            if (node.isSelf) return;
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            const path = `M ${selfNode.x + 70} ${selfNode.y + 40} Q ${(selfNode.x + node.x) / 2 + 70} ${(selfNode.y + node.y) / 2} ${node.x + 70} ${node.y + 40}`;
            line.setAttribute('d', path);
            line.setAttribute('class', 'tree-connection');
            svg.appendChild(line);
          });
        }
      }
    }

    function stopDrag() {
      isDragging = false;
      document.removeEventListener('mousemove', drag);
      document.removeEventListener('mouseup', stopDrag);
      element.style.cursor = 'move';
    }
  }

  function zoomTree(delta) {
    treeZoom = Math.max(0.5, Math.min(2, treeZoom + delta));
    applyTreeTransform();
  }

  function resetTreeView() {
    treeZoom = 1;
    treePanX = 0;
    treePanY = 0;
    applyTreeTransform();
  }

  function applyTreeTransform() {
    const canvas = $('#tree-canvas');
    if (canvas) {
      canvas.style.transform = `scale(${treeZoom}) translate(${treePanX}px, ${treePanY}px)`;
    }
  }

  // ========== Form Validation & Submission ==========
  function initFormSubmission() {
    const saveBtn = $('#btn-save-continue');
    
    if (!saveBtn) return;

    saveBtn.addEventListener('click', (e) => {
      e.preventDefault();
      
      const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
      
      if (familyMembers.length === 0) {
        toast(
          lang === 'tl' ? 'Magdagdag ng kahit isang miyembro ng pamilya' : 'Please add at least one family member',
          'warning'
        );
        return;
      }

      // Show loading state
      const originalText = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${lang === 'tl' ? 'Sine-save...' : 'Saving...'}`;
      
      // Simulate save delay then navigate
      setTimeout(() => {
        toast(lang === 'tl' ? 'Nai-save ang impormasyon ng pamilya!' : 'Family information saved!');
        setTimeout(() => {
          // Navigate to next step (lifestyle) when created
          window.location.href = 'wizard_personal.php'; // Placeholder
        }, 800);
      }, 1000);
    });
  }

  // ========== Toast Notification ==========
  function toast(message, type = 'success') {
    const toastEl = document.createElement('div');
    toastEl.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} position-fixed top-0 start-50 translate-middle-x mt-3`;
    toastEl.style.zIndex = '99999';
    toastEl.style.minWidth = '300px';
    toastEl.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        <span>${message}</span>
      </div>
    `;
    document.body.appendChild(toastEl);

    setTimeout(() => {
      toastEl.style.transition = 'opacity 0.3s';
      toastEl.style.opacity = '0';
      setTimeout(() => toastEl.remove(), 300);
    }, 3000);
  }

  // ========== Language Toggle ==========
  function initLanguageToggle() {
    const langEN = $('#lang-en');
    const langTL = $('#lang-tl');
    
    if (langEN) langEN.addEventListener('change', translate);
    if (langTL) langTL.addEventListener('change', translate);
  }

  // ========== Scroll Animations ==========
  function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });

    $$('.section-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'opacity 0.5s, transform 0.5s';
      observer.observe(card);
    });
  }

  // ========== Initialization ==========
  function init() {
    restoreLanguage(); // Restore saved language preference first
    initLanguageToggle();
    initSearch();
    initFamilyManagement();
    initFamilyTree();
    initFormSubmission();
    initScrollAnimations();
  }

  // Start when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
