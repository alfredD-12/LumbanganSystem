// Load data ONLY when modal is opened
document
  .getElementById("documentTypeModal")
  .addEventListener("shown.bs.modal", function () {
    const modalBody = document.getElementById("documentTypeModalBody");

    // Load the view (HTML template)
    fetch("index.php?action=loadDocumentTypesView")
      .then((res) => res.text())
      .then((html) => {
        modalBody.innerHTML = html;

        // ⬅ IMPORTANT: attach event listener AFTER HTML is inserted
        initAddDocumentTypeButton();

        // Now load JSON data to populate categories and types
        loadDocumentTypes();

        // Attach search and filter functionality AFTER HTML is loaded
        const searchInput = document.getElementById("docSearchInput");
        const categoryFilter = document.getElementById("categoryFilter");

        // Debounce timer for search
        let debounceTimer;

        // Search input listener
        searchInput.addEventListener("input", () => {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(filterDocumentCards, 300);
        });

        // Category filter listener
        categoryFilter.addEventListener("change", filterDocumentCards);
      })
      .catch((err) => console.error("Error loading view:", err));
  });

function initAddDocumentTypeButton() {
  const addBtn = document.getElementById("addDocumentTypeBtn");
  if (!addBtn) return; // safety

  addBtn.addEventListener("click", function () {
    const modal = new bootstrap.Modal(
      document.getElementById("addDocumentModal")
    );
    modal.show();

    // Load categories
    fetch("index.php?action=getDocumentCategories")
      .then((res) => res.json())
      .then((categories) => {
        const catSelect = document.getElementById("addCategory");
        catSelect.innerHTML = `<option value="">Select Category</option>`;

        categories.forEach((c) => {
          const opt = document.createElement("option");
          opt.value = c.category_id;
          opt.textContent = c.category_name;
          catSelect.appendChild(opt);
        });
      });
  });
}

// Load JSON data
function loadDocumentTypes() {
  fetch("index.php?action=getDocumentTypesData")
    .then((res) => res.json())
    .then((data) => {
      populateCategories(data.categories);
      populateDocumentTypes(data.types);
    })
    .catch((err) => console.error(err));
}

// Populate category dropdown
function populateCategories(categories) {
  const filter = document.getElementById("categoryFilter");
  filter.innerHTML = `<option value="">All Categories</option>`;
  categories.forEach((cat) => {
    const option = document.createElement("option");
    option.value = cat.category_id;
    option.textContent = cat.category_name;
    filter.appendChild(option);
  });
}

// Populate document cards
function populateDocumentTypes(types) {
  const container = document.getElementById("docTypeContainer");
  container.innerHTML = "";

  types.forEach((t) => {
    container.innerHTML += `
      <div class="col doc-card-item" data-category="${
        t.category_id
      }" data-name="${t.document_name.toLowerCase()}">
        <div class="doc-card">
          <div class="card-top-accent"></div>
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary bg-opacity-10 text-primary mb-2">${
                t.category_name
              }</span>
              <div class="price-badge">${
                t.fee > 0 ? "₱ " + parseFloat(t.fee).toFixed(2) : "Free"
              }</div>
            </div>
            <h5 class="doc-title">${t.document_name}</h5>
            <p class="doc-desc">${t.description ?? ""}</p>
            <div class="req-box"><i class="fas fa-list-check me-1 text-primary"></i><strong>Reqs:</strong> ${
              t.requirements ?? ""
            }</div>
          </div>
          <div class="card-footer">
            <small class="text-muted">ID: #${t.document_type_id}</small>
            <div class="d-flex gap-2">
              <button class="btn-icon btn-edit" data-id="${
                t.document_type_id
              }"><i class="fas fa-pen"></i></button>
              <button class="btn-icon btn-delete" data-id="${
                t.document_type_id
              }"><i class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>
      </div>`;
  });
}

// Client-side search & filter
function filterDocumentCards() {
  const searchInput = document.getElementById("docSearchInput");
  const categoryFilter = document.getElementById("categoryFilter");
  const cards = document.querySelectorAll(".doc-card-item");

  const searchText = searchInput.value.toLowerCase();
  const categoryValue = categoryFilter.value;

  cards.forEach((card) => {
    const name = card.dataset.name.toLowerCase();
    const category = card.dataset.category;

    const matchesSearch = name.includes(searchText);
    const matchesCategory = categoryValue === "" || category === categoryValue;

    card.style.display = matchesSearch && matchesCategory ? "" : "none";
  });
}

// ==========================================
// 1. EDIT BUTTON FIX
// ==========================================
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btn-edit");
  if (!btn) return;

  const id = btn.dataset.id;
  const editModalEl = document.getElementById("editDocumentTypeModal");

  // --- THE FIX: Move modal to the body tag ---
  // This ensures it sits on top of everything else (z-index stacking)
  if (editModalEl.parentElement !== document.body) {
    document.body.appendChild(editModalEl);
  }
  // -------------------------------------------

  const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);

  // Fix backdrop stacking issue
  editModalEl.addEventListener(
    "shown.bs.modal",
    function () {
      const backdrops = document.querySelectorAll(".modal-backdrop");
      if (backdrops.length > 1) {
        backdrops[backdrops.length - 1].style.zIndex = "1056";
      }
    },
    { once: true }
  );

  editModal.show();

  // Fetch the document data (No changes here)
  fetch(`index.php?action=getDocumentType&id=${id}`)
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("editDocumentId").value = data.document_type_id;
      document.getElementById("editDocumentName").value = data.document_name;
      document.getElementById("editDescription").value = data.description;
      document.getElementById("editRequirements").value = data.requirements;
      document.getElementById("editFee").value = data.fee;

      fetch("index.php?action=getDocumentCategories")
        .then((res) => res.json())
        .then((categories) => {
          populateEditCategoryDropdown(categories, data.category_id);
        });
    })
    .catch((err) => console.error(err));
});

// ==========================================
// 2. UPDATE FORM SUBMISSION (No changes needed)
// ==========================================
document
  .getElementById("editDocumentTypeForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("index.php?action=updateDocumentType", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          alert("Document updated!");
          loadDocumentTypes();
          bootstrap.Modal.getInstance(
            document.getElementById("editDocumentTypeModal")
          ).hide();
        } else {
          alert("Update failed!");
        }
      });
  });

// ==========================================
// 3. DELETE BUTTON FIX
// ==========================================
let deleteId = null;

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btn-delete");
  if (!btn) return;

  deleteId = btn.dataset.id;
  const confirmModalEl = document.getElementById("deleteConfirmModal");

  // --- THE FIX: Move modal to the body tag ---
  if (confirmModalEl.parentElement !== document.body) {
    document.body.appendChild(confirmModalEl);
  }
  // -------------------------------------------

  const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);

  // Fix backdrop stacking issue
  confirmModalEl.addEventListener(
    "shown.bs.modal",
    function () {
      const backdrops = document.querySelectorAll(".modal-backdrop");
      if (backdrops.length > 1) {
        backdrops[backdrops.length - 1].style.zIndex = "1056";
      }
    },
    { once: true }
  );

  confirmModal.show();
});

// ==========================================
// 4. CONFIRM DELETE (No changes needed)
// ==========================================
document
  .getElementById("confirmDeleteBtn")
  .addEventListener("click", function () {
    if (!deleteId) return;

    fetch("index.php?action=deleteDocumentType", {
      method: "POST",
      body: new URLSearchParams({ id: deleteId }),
    })
      .then((res) => res.json())
      .then((res) => {
        const confirmModalEl = document.getElementById("deleteConfirmModal");
        const confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
        confirmModal.hide();

        if (res.success) {
          loadDocumentTypes();
          showSuccess("Document type deleted successfully!");
        } else {
          showError(res.message || "Delete failed!");
        }
      })
      .catch((err) => console.error(err))
      .finally(() => {
        deleteId = null;
      });
  });

//Additional functions for CRUD in cards
function populateEditCategoryDropdown(categories, selectedId) {
  const select = document.getElementById("editCategory");
  select.innerHTML = ""; // clear existing options

  categories.forEach((cat) => {
    const option = document.createElement("option");
    option.value = cat.category_id;
    option.textContent = cat.category_name;
    if (cat.category_id == selectedId) {
      option.selected = true; // select the current category
    }
    select.appendChild(option);
  });
}

// Clear form fields
function clearAddDocForm() {
  document.getElementById("addDocumentForm").reset();

  // If you have custom resets:
  const selects = document.querySelectorAll("#addDocumentForm select");
  selects.forEach((s) => (s.selectedIndex = 0));

  const textareas = document.querySelectorAll("#addDocumentForm textarea");
  textareas.forEach((t) => (t.value = ""));

  const inputs = document.querySelectorAll("#addDocumentForm input");
  inputs.forEach((i) => {
    if (i.type !== "submit" && i.type !== "button") i.value = "";
  });
}

// SAVE BUTTON (AJAX POST)
document
  .getElementById("saveDocumentBtn")
  .addEventListener("click", function () {
    const form = document.getElementById("addDocumentForm");
    const formData = new FormData(form);

    fetch("index.php?action=addDocumentType", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((result) => {
        if (result.success) {
          // Close modal
          bootstrap.Modal.getInstance(
            document.getElementById("addDocumentModal")
          ).hide();

          // Reload card list
          loadDocumentTypes();

          // Show success notification
          showSuccess("Document type added successfully!");
          // Clear form for next use
          clearAddDocForm();
        }
      });
  });
