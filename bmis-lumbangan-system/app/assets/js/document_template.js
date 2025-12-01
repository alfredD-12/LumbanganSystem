document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("createTemplateModal");
  const dropdown = document.getElementById("documentTypeSelect");
  const searchInput = document.getElementById("searchDocumentType");
  const documentNameHeader = document.getElementById("documentName");
  const createTemplateBtn = document.getElementById("createTemplate");
  const placeholderDropdown = document.getElementById("placeholderDropdown");
  const saveTemplateBtn = document.getElementById("saveBtn");
  const confirnmSaveTemplateBtn = document.getElementById(
    "confirmSaveTemplateBtn"
  );

  let selectedDocumentTypeId = null;
  let selectedDocumentName = null;
  let isEditing = false;

  fetch("index.php?action=getTemplatePlaceholders")
    .then((res) => res.json())
    .then((data) => {
      Object.keys(data).forEach((tableName) => {
        // Create optgroup for clean organization
        let group = document.createElement("optgroup");
        group.label = tableName.toUpperCase();

        data[tableName].forEach((column) => {
          let option = document.createElement("option");

          // Placeholder format: {{table.column}}
          option.value = `{{${tableName}.${column}}}`;
          option.textContent = `${tableName}.${column}`;

          group.appendChild(option);
        });

        placeholderDropdown.appendChild(group);
      });
    });

  // When modal opens → load document types
  modal.addEventListener("shown.bs.modal", () => {
    loadDocumentTypes();
  });

  // Fetch document types from backend
  function loadDocumentTypes() {
    dropdown.innerHTML = ""; // Clear previous options`;

    fetch("index.php?action=getDocumentTypes")
      .then((res) => res.json())
      .then((data) => {
        data.forEach((doc) => {
          let option = document.createElement("option");
          option.value = doc.document_type_id;

          //Store document name so we can use it later
          option.dataset.docName = doc.document_name;
          option.textContent = `${doc.document_type_id} — ${doc.document_name}`;
          dropdown.appendChild(option);
        });
      })
      .catch((err) => {
        dropdown.innerHTML = `<option>Error loading data</option>`;
        console.error(err);
      });
  }

  // Live search filter
  searchInput.addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    const options = dropdown.querySelectorAll("option");

    options.forEach((option) => {
      const text = option.textContent.toLowerCase();
      option.style.display = text.includes(filter) ? "block" : "none";
    });
  });

  // Confirm Create → redirect
  document
    .getElementById("confirmCreateTemplate")
    .addEventListener("click", function () {
      const selectedOption = dropdown.options[dropdown.selectedIndex];

      if (!selectedOption) {
        showError("Please select a document type.");
        return;
      }

      selectedDocumentTypeId = selectedOption.value;
      selectedDocumentName = selectedOption.dataset.docName;

      //set header
      if (selectedDocumentName !== undefined) {
        documentNameHeader.innerHTML = `Creating Template for: <strong>${selectedDocumentName}</strong>`;
      }

      //close modal
      bootstrap.Modal.getInstance(modal).hide();
    });

  createTemplateBtn.addEventListener("click", function () {
    // Reset previous selection
    dropdown.selectedIndex = 0;
    searchInput.value = "";
    documentNameHeader.innerHTML = "";
    //Empty editor
    tinymce.activeEditor.setContent("");
    selectedDocumentTypeId = null;
    selectedDocumentName = null;
    isEditing = false;
  });

  document
    .getElementById("placeholderDropdown")
    .addEventListener("change", function () {
      if (this.value) {
        tinymce.activeEditor.execCommand("mceInsertContent", false, this.value);
        this.value = "";
      }
    });

  function clearEditor() {
    tinymce.activeEditor.setContent("");
    selectedDocumentTypeId = null;
    selectedDocumentName = null;
    documentNameHeader.innerHTML = "";
  }

  // Save Template
  saveTemplateBtn.addEventListener("click", () => {
        if (!selectedDocumentTypeId) {
            showError("Please select a document template first.");
            return;
        }

        let currentHTML = tinymce.activeEditor.getContent();

        // fallback to empty string if undefined
        let originalHTML = window.originalTemplateHTML || "";

        // If no change → block update
        if (currentHTML.trim() === originalHTML.trim()) {
            showError("No changes detected — template is already up to date.");
            return;
        }

        // If changed → show confirm modal
        const modal = new bootstrap.Modal(
            document.getElementById("confirmSaveTemplateModal")
        );
        modal.show();
  });

  confirnmSaveTemplateBtn.addEventListener("click", () => {
    if (!selectedDocumentTypeId && !isEditing) {
      showError("Please select a document type first.");
      return;
    }

    let htmlContent = tinymce.activeEditor.getContent();

    fetch("index.php?action=saveTemplate", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        document_type_id: selectedDocumentTypeId,
        template_html: htmlContent,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          showSuccess("Template saved successfully!");
          // VERY IMPORTANT — update stored original HTML
          window.originalTemplateHTML = htmlContent;
          clearEditor();
          // Close modal after saving
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("confirmSaveTemplateModal")
          );
          modal.hide();
        } else {
          showError("Error saving template: " + data.error);
        }
      })
      .catch((err) => {
        showError("An error occurred while saving the template.");
        console.error(err);
      });
  });

  //Load templates into modal
  document.getElementById("openTemplatesBtn").addEventListener("click", () => {
    const modal = new bootstrap.Modal(
      document.getElementById("templatesModal")
    );

    fetch("index.php?action=getTemplates")
      .then((res) => res.json())
      .then((data) => {
        let html = "";

        data.forEach((tpl) => {
          html += `
                        <div class="template-item" 
                            data-id="${tpl.document_type_id}"
                            data-name="${tpl.document_name}"
                            data-html="${encodeURIComponent(
                              tpl.template_html
                            )}">

                            <div class="template-icon">📄</div>
                            <strong>${tpl.document_name}</strong>
                        </div>
                    `;
        });

        document.getElementById("templatesList").innerHTML = html;

        modal.show();
      });
  });

  document.addEventListener("dblclick", function (e) {
    const item = e.target.closest(".template-item");
    if (!item) return;

    isEditing = true;
    selectedDocumentTypeId = item.dataset.id;

    let docName = item.dataset.name;
    let templateHtml = decodeURIComponent(item.dataset.html);

    // Insert document name
    document.getElementById("documentName").innerHTML = `Editing: <strong>${docName}</strong>`;

        // Insert into TinyMCE
        if (tinymce.get("template_editor")) {
            tinymce.get("template_editor").setContent(templateHtml);
        } else {
            document.getElementById("template_editor").value = templateHtml;
        }

        // Store original HTML for change detection
        window.originalTemplateHTML = templateHtml;

        // Store document_type_id
        window.selectedDocumentTypeId = item.dataset.id;

        // Close modal
        bootstrap.Modal.getInstance(
            document.getElementById("templatesModal")
        ).hide();
    });



});
