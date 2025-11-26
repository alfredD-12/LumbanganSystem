document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("newRequestModal");
  if (!modal) return;

  // Restore last active tab using localStorage (keeps user's last-open tab)
  const TAB_KEY = "documentRequest_activeTab";
  function restoreActiveTab() {
    try {
      const saved = localStorage.getItem(TAB_KEY);
      if (saved) {
        const trigger = document.getElementById(saved);
        if (trigger && typeof bootstrap !== "undefined" && bootstrap.Tab) {
          const t = new bootstrap.Tab(trigger);
          t.show();
        }
      }
    } catch (e) {
      console.error("Failed to restore active tab", e);
    }
  }

  function saveActiveTab(id) {
    try {
      if (id) localStorage.setItem(TAB_KEY, id);
    } catch (e) {
      console.error("Failed to save active tab", e);
    }
  }

  // Attach listeners to nav tabs to persist the last active one
  [
    "home-tab",
    "approved-tab",
    "released-tab",
    "rejected-tab",
    "history-tab",
  ].forEach((tabId) => {
    const el = document.getElementById(tabId);
    if (!el) return;
    el.addEventListener("shown.bs.tab", function (e) {
      saveActiveTab(e.target.id || tabId);
    });
  });

  // Try to restore previously active tab ASAP
  restoreActiveTab();

  //for the form submission
  const form = modal.querySelector("form");
  const submitBtn = form.querySelector('button[name="submit_request"]');
  const fileInput = form.querySelector("#proof_upload");
  const fileList = form.querySelector("#fileList");
  let selectedFiles = []; // tracks files

  // 🔹 Checkbox logic for "Requesting for someone else"
  const chk = modal.querySelector("#forSomeone");
  const fields = modal.querySelector("#someoneFields");
  if (chk && fields) {
    function update() {
      const show = chk.checked;
      fields.classList.toggle("d-none", !show);
      fields
        .querySelectorAll("input")
        .forEach((el) => el.toggleAttribute("required", show));
    }
    chk.addEventListener("change", update);
    update(); // initial state
  }
  // 🔹 File input change handler

  if (fileInput && fileList) {
    renderFileList(); // initial render

    fileInput.addEventListener("change", function () {
      const newFiles = Array.from(fileInput.files);

      // Merge new files while avoiding duplicates
      newFiles.forEach((file) => {
        if (
          !selectedFiles.some(
            (f) => f.name === file.name && f.size === file.size
          )
        ) {
          selectedFiles.push(file);
        }
      });

      renderFileList();
      fileInput.value = ""; // reset so the same file can be selected again
    });
  }

  // Camera capture integration (capture a photo and append to selectedFiles)
  let cameraStream = null;
  const openCameraBtn = modal.querySelector("#openCameraBtn");
  const cameraContainer = modal.querySelector("#cameraContainer");
  const cameraPreview = modal.querySelector("#cameraPreview");
  const capturePhotoBtn = modal.querySelector("#capturePhotoBtn");
  const closeCameraBtn = modal.querySelector("#closeCameraBtn");

  async function openCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert("Camera not supported in this browser.");
      return;
    }
    try {
      cameraStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: "environment" },
        audio: false,
      });
      if (cameraPreview) {
        cameraPreview.srcObject = cameraStream;
        cameraPreview.play().catch(() => {});
      }
      if (cameraContainer) cameraContainer.classList.remove("d-none");
    } catch (e) {
      console.error("Failed to open camera", e);
      alert("Unable to access camera. Please allow camera permission.");
    }
  }

  function stopCamera() {
    try {
      if (cameraStream) {
        cameraStream.getTracks().forEach((t) => t.stop());
        cameraStream = null;
      }
      if (cameraPreview) cameraPreview.srcObject = null;
      if (cameraContainer) cameraContainer.classList.add("d-none");
    } catch (e) {
      console.error("Error stopping camera", e);
    }
  }

  async function capturePhoto() {
    if (!cameraPreview) return;
    const video = cameraPreview;
    const w = video.videoWidth || 640;
    const h = video.videoHeight || 480;
    const canvas = document.createElement("canvas");
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, w, h);
    canvas.toBlob(
      (blob) => {
        if (!blob) {
          alert("Failed to capture image");
          return;
        }
        const filename = `capture_${Date.now()}.jpg`;
        const file = new File([blob], filename, { type: "image/jpeg" });
        selectedFiles.push(file);
        renderFileList();
        stopCamera();
      },
      "image/jpeg",
      0.9
    );
  }

  if (openCameraBtn) openCameraBtn.addEventListener("click", openCamera);
  if (capturePhotoBtn) capturePhotoBtn.addEventListener("click", capturePhoto);
  if (closeCameraBtn) closeCameraBtn.addEventListener("click", stopCamera);

  // Ensure camera is stopped when modal hides
  if (modal) {
    modal.addEventListener("hidden.bs.modal", function () {
      stopCamera();
    });
  }

  // 🔹 Renders the file list dynamically
  function renderFileList() {
    fileList.innerHTML = ""; // clear list

    if (selectedFiles.length === 0) {
      const emptyItem = document.createElement("li");
      emptyItem.className = "list-group-item text-muted";
      emptyItem.textContent = "No files selected.";
      fileList.appendChild(emptyItem);
      return;
    }

    selectedFiles.forEach((file, index) => {
      const listItem = document.createElement("li");
      listItem.className =
        "list-group-item d-flex justify-content-between align-items-center";

      // 🔸 File name + size
      const left = document.createElement("span");
      left.innerHTML = `
      <i class="fa fa-file me-2"></i>${escapeHtml(file.name)}
      <small class="text-muted">(${(file.size / 1024).toFixed(1)} KB)</small>
    `;

      // 🔹 Optional image preview
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.style.height = "40px";
        img.style.marginLeft = "8px";
        img.style.borderRadius = "4px";
        img.onload = () => URL.revokeObjectURL(img.src);
        left.appendChild(img);
      }

      // 🔸 Right section (file type badge + delete button)
      const right = document.createElement("div");
      right.className = "d-flex align-items-center";

      const badge = document.createElement("span");
      badge.className = "badge bg-secondary me-2";
      badge.textContent = file.type.split("/")[1] || "N/A";
      right.appendChild(badge);

      const deleteBtn = document.createElement("button");
      deleteBtn.className = "btn btn-sm btn-danger";
      deleteBtn.type = "button";
      deleteBtn.innerHTML = '<i class="fa fa-trash"></i>';
      deleteBtn.addEventListener("click", () => {
        selectedFiles.splice(index, 1);
        renderFileList();
      });
      right.appendChild(deleteBtn);

      listItem.appendChild(left);
      listItem.appendChild(right);
      fileList.appendChild(listItem);
    });
  }
  //AJAX form submission
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    selectedFiles.forEach((file) => {
      formData.append("proof_upload[]", file);
    });

    submitBtn.disabled = true;
    submitBtn.textContent = "Submitting...";

    try {
      const response = await fetch("index.php?action=submitRequest", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        alert("Request submitted successfully!");
        form.reset();
        selectedFiles = [];
        renderFileList();

        // hide modal
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) modalInstance.hide();
        chk.checked = false;
        update();

        // refresh ongoing requests
        loadOngoingRequests();
      } else {
        alert("Error: " + data.message);
      }
    } catch (error) {
      console.error(error);
      alert("An error occurred while submitting the request.");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Submit Request";
    }
  });

  // 🔹 Fetch and display document requirements dynamically
  const docTypeSelect = document.getElementById("document_type_id");
  const reqList = document.getElementById("requirementList");

  docTypeSelect.addEventListener("change", async function () {
    const docTypeId = this.value;
    reqList.innerHTML = "";

    if (!docTypeId) return;

    try {
      const response = await fetch(
        `index.php?action=getRequirements&document_type_id=${docTypeId}`
      );
      const data = await response.json();

      if (data.requirements.length) {
        data.requirements.forEach((req) => {
          const li = document.createElement("li");
          li.textContent = req;
          reqList.appendChild(li);
        });
      } else {
        const li = document.createElement("li");
        li.textContent = "No requirements.";
        reqList.appendChild(li);
      }
    } catch (e) {
      console.error("Error fetching requirements", e);
      const li = document.createElement("li");
      li.textContent = "Error loading requirements.";
      reqList.appendChild(li);
    }
  });

  // -------------------------------
  // 🔹 Ongoing requests AJAX
  // -------------------------------
  // helper: render a styled status badge
  function renderStatusBadge(status) {
    const s = String(status ?? "N/A").trim();
    const key = s
      .toLowerCase()
      .replace(/\s+/g, "-")
      .replace(/[^a-z0-9\-]/g, "");
    const cls = "status-" + (key || "n-a");
    return `<span class="status-badge ${cls}">${escapeHtml(s)}</span>`;
  }

  // Stored history for front-end filtering
  let historyData = [];

  // Edit modal captured files (new files added in edit flow)
  let editSelectedFiles = [];
  let editCameraStream = null;

  // Render captured files for edit modal (appends after server-provided existing file items)
  function renderEditFileList() {
    const editFileList = document.getElementById("editFileList");
    if (!editFileList) return;

    // remove any existing captured items before re-adding
    Array.from(
      editFileList.querySelectorAll('li[data-captured="true"]')
    ).forEach((n) => n.remove());

    if (!editSelectedFiles.length) return;

    editSelectedFiles.forEach((file, idx) => {
      const li = document.createElement("li");
      li.className =
        "list-group-item d-flex justify-content-between align-items-center";
      li.setAttribute("data-captured", "true");

      const left = document.createElement("div");
      left.className = "d-flex align-items-center";
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.style.height = "48px";
        img.style.width = "48px";
        img.style.objectFit = "cover";
        img.style.borderRadius = "4px";
        img.style.marginRight = "8px";
        img.onload = () => URL.revokeObjectURL(img.src);
        left.appendChild(img);
      }
      const meta = document.createElement("div");
      meta.innerHTML = `<div>${escapeHtml(
        file.name
      )}</div><small class="text-muted">${(file.size / 1024).toFixed(
        1
      )} KB</small>`;
      left.appendChild(meta);

      const rm = document.createElement("button");
      rm.type = "button";
      rm.className = "btn btn-sm btn-danger";
      rm.innerHTML = '<i class="fa fa-trash"></i>';
      rm.addEventListener("click", () => {
        editSelectedFiles.splice(idx, 1);
        renderEditFileList();
      });

      li.appendChild(left);
      li.appendChild(rm);
      editFileList.appendChild(li);
    });
  }

  // Edit modal camera elements
  const editModal = document.getElementById("editRequestModal");
  const openEditCameraBtn = document.getElementById("openEditCameraBtn");
  const editCameraContainer = document.getElementById("editCameraContainer");
  const editCameraPreview = document.getElementById("editCameraPreview");
  const captureEditPhotoBtn = document.getElementById("captureEditPhotoBtn");
  const closeEditCameraBtn = document.getElementById("closeEditCameraBtn");

  async function openEditCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert("Camera not supported in this browser.");
      return;
    }
    try {
      editCameraStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: "environment" },
        audio: false,
      });
      if (editCameraPreview) {
        editCameraPreview.srcObject = editCameraStream;
        editCameraPreview.play().catch(() => {});
      }
      if (editCameraContainer) editCameraContainer.classList.remove("d-none");
    } catch (e) {
      console.error("Failed to open edit camera", e);
      alert("Unable to access camera. Please allow camera permission.");
    }
  }

  function stopEditCamera() {
    try {
      if (editCameraStream) {
        editCameraStream.getTracks().forEach((t) => t.stop());
        editCameraStream = null;
      }
      if (editCameraPreview) editCameraPreview.srcObject = null;
      if (editCameraContainer) editCameraContainer.classList.add("d-none");
    } catch (e) {
      console.error("Error stopping edit camera", e);
    }
  }

  function captureEditPhoto() {
    if (!editCameraPreview) return;
    const video = editCameraPreview;
    const w = video.videoWidth || 640;
    const h = video.videoHeight || 480;
    const canvas = document.createElement("canvas");
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, w, h);
    canvas.toBlob(
      (blob) => {
        if (!blob) {
          alert("Failed to capture image");
          return;
        }
        const filename = `edit_capture_${Date.now()}.jpg`;
        const file = new File([blob], filename, { type: "image/jpeg" });
        editSelectedFiles.push(file);
        renderEditFileList();
        stopEditCamera();
      },
      "image/jpeg",
      0.9
    );
  }

  if (openEditCameraBtn)
    openEditCameraBtn.addEventListener("click", openEditCamera);
  if (captureEditPhotoBtn)
    captureEditPhotoBtn.addEventListener("click", captureEditPhoto);
  if (closeEditCameraBtn)
    closeEditCameraBtn.addEventListener("click", stopEditCamera);

  if (editModal) {
    editModal.addEventListener("hidden.bs.modal", function () {
      stopEditCamera();
    });
    editModal.addEventListener("shown.bs.modal", function () {
      renderEditFileList();
    });
  }

  // Renders given history list into the history container (cards with View only)
  function renderHistory(list) {
    const container = document.getElementById("requestHistoryContainer");
    container.innerHTML = "";
    if (!list || !list.length) {
      container.innerHTML =
        '<p class="text-muted">No request history found.</p>';
      return;
    }

    list.forEach((row) => {
      const col = document.createElement("div");
      col.className = "col-sm-4 mb-sm-0 mt-2 position-relative";

      const card = document.createElement("div");
      card.className =
        "card resident-card shadow-md shadow-sm position-relative w-100 h-100";

      const cardBody = document.createElement("div");
      cardBody.className = "card-body";
      cardBody.innerHTML = `
        <h5 class="card-title fs-2 document-type">
          <i class="fa fa-file" style="font-size: 2rem;"></i> ${
            row.document_name
          }
        </h5>
        <p><i class="fa-solid fa-calendar"></i> Request Date: ${
          row.request_date
        }</p>
        <p><i class="fa-solid fa-check-circle"></i> Approved Date: ${
          row.approval_date ? row.approval_date : "N/A"
        }</p>
        <p><i class="fa-solid fa-calendar-check"></i> Released Date: ${
          row.release_date ? row.release_date : "N/A"
        }</p>
        <p><i class="fa-solid fa-info-circle"></i> Status: ${renderStatusBadge(
          row.status && row.release_date === null ? "Rejected" : row.status
        )}</p>
      `;

      if (row.requested_for && row.relation_to_requestee) {
        cardBody.innerHTML += `
          <hr>
          <p><i class="fa-solid fa-user"></i> Requested For: ${row.requested_for}</p>
          <p><i class="fa-solid fa-people-arrows"></i> Relation to Requestee: ${row.relation_to_requestee}</p>
        `;
      }

      if (row.remarks && row.status === "Rejected") {
        cardBody.innerHTML += `
          <hr>
          <p><i class="fa-solid fa-comment-dots"></i> Remarks: ${row.remarks}</p>
        `;
      }

      card.appendChild(cardBody);

      // Footer with View button only
      const cardFooter = document.createElement("div");
      cardFooter.className =
        "card-footer bg-transparent d-flex justify-content-end gap-2 border-0";

      const viewBtn = document.createElement("button");
      viewBtn.type = "button";
      viewBtn.className = "btn btn-sm btn-outline-primary";
      viewBtn.innerHTML = '<i class="fa fa-eye me-1"></i> View';
      viewBtn.addEventListener("click", async () => {
        try {
          const resp = await fetch(
            `index.php?action=getRequestById&request_id=${row.request_id}`
          );
          const data = await resp.json();
          const body = document.getElementById("viewRequestBody");
          let html = `
            <h5 class="mb-2 document-type"><i class="fa fa-file me-2"></i>${escapeHtml(
              data.document_type_name
            )}</h5>
            <p><strong>Request Date:</strong> ${escapeHtml(
              data.request_date
            )}</p>
            <p><strong>Status:</strong> ${escapeHtml(data.status)}</p>
            <hr>
            <p><strong>Purpose:</strong> ${escapeHtml(
              data.purpose || "N/A"
            )}</p>
          `;
          if (data.proof_upload) {
            const files = data.proof_upload
              .split(",")
              .map((s) => s.trim())
              .filter(Boolean);
            if (files.length) {
              html += "<hr><p><strong>Proof Files:</strong></p><ul>";
              files.forEach((f) => {
                const url = buildFileUrl(f);
                html += `<li><a href="${escapeHtml(
                  url
                )}" target="_blank">${escapeHtml(f.split("/").pop())}</a></li>`;
              });
              html += "</ul>";
            }
          }
          if (data.remarks)
            html += `<hr><p><strong>Remarks:</strong> ${escapeHtml(
              data.remarks
            )}</p>`;
          body.innerHTML = html;
          const vm = new bootstrap.Modal(
            document.getElementById("viewRequestModal")
          );
          vm.show();
        } catch (e) {
          console.error(e);
          alert("Failed to load details");
        }
      });

      cardFooter.appendChild(viewBtn);
      card.appendChild(cardFooter);
      col.appendChild(card);
      container.appendChild(col);
    });
  }

  // Apply filter using stored historyData
  function applyHistoryFilter() {
    const status =
      (document.getElementById("historyFilterStatus") || {}).value || "";
    const kw =
      (document.getElementById("historyFilterKeyword") || {}).value || "";
    const k = kw.trim().toLowerCase();

    let filtered = historyData.slice();
    if (status) {
      filtered = filtered.filter(
        (r) =>
          (r.status || "").toString().toLowerCase() === status.toLowerCase()
      );
    }
    if (k) {
      filtered = filtered.filter((r) => {
        const doc = (r.document_name || "").toString().toLowerCase();
        const purpose = (r.purpose || "").toString().toLowerCase();
        const remarks = (r.remarks || "").toString().toLowerCase();
        return doc.includes(k) || purpose.includes(k) || remarks.includes(k);
      });
    }

    renderHistory(filtered);
  }

  // Clear filter UI and show all
  function clearHistoryFilter() {
    const s = document.getElementById("historyFilterStatus");
    const k = document.getElementById("historyFilterKeyword");
    if (s) s.value = "";
    if (k) k.value = "";
    renderHistory(historyData);
  }

  async function loadOngoingRequests() {
    const container = document.getElementById("ongoingRequestsContainer");
    container.innerHTML =
      '<p class="text-muted">Loading ongoing requests...</p>';

    try {
      const response = await fetch("index.php?action=getOngoingRequests");
      const requests = await response.json();

      if (!requests.length) {
        container.innerHTML =
          '<p class="text-muted">No ongoing document requests found.</p>';
        return;
      }

      container.innerHTML = ""; // clear

      requests.forEach((row) => {
        const col = document.createElement("div");
        col.className = "col-sm-4 mb-sm-0 mt-2 position-relative";

        const card = document.createElement("div");
        card.className =
          "card resident-card shadow-md shadow-sm position-relative w-100 h-100";

        // 🔹 Delete badge button
        const deleteBtn = document.createElement("span");
        deleteBtn.className =
          "badge bg-danger position-absolute top-0 start-100 translate-middle p-2 rounded-pill";
        deleteBtn.style.cursor = "pointer";
        deleteBtn.innerHTML =
          '<i class="fa-solid fa-square-xmark" style="font-size: 1.2rem;"></i>';
        deleteBtn.title = "Delete Request";
        deleteBtn.addEventListener("click", async () => {
          if (!confirm("Are you sure you want to delete this request?")) return;

          try {
            const delResponse = await fetch("index.php?action=deleteRequest", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ request_id: row.request_id }),
            });

            const delData = await delResponse.json();
            if (delData.success) {
              alert("Request deleted successfully!");
              col.remove(); // remove card from DOM
              loadOngoingRequests(); // refresh list
            } else {
              alert("Error: " + delData.message);
            }
          } catch (e) {
            console.error("Error deleting request:", e);
            alert("Failed to delete request.");
          }
        });

        const cardBody = document.createElement("div");
        cardBody.className = "card-body";

        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${
              row.document_name
            }
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${
            row.request_date
          }</p>
          <p><i class="fa-solid fa-bars-progress"></i> Status: ${renderStatusBadge(
            row.status
          )}</p>
          <hr>
          <p><i class="fa-solid fa-user"></i> Requested For: ${
            row.requested_for ? row.requested_for : "N/A"
          }</p>
          <p><i class="fa-solid fa-people-arrows"></i> Relation to Requestee: ${
            row.relation_to_requestee ? row.relation_to_requestee : "N/A"
          }</p>
        `;

        card.appendChild(deleteBtn);
        card.appendChild(cardBody);
        // Card footer with View & Edit buttons
        const cardFooter = document.createElement("div");
        cardFooter.className =
          "card-footer bg-transparent d-flex justify-content-end gap-2 border-0";

        const viewBtn = document.createElement("button");
        viewBtn.type = "button";
        viewBtn.className = "btn btn-sm btn-outline-primary";
        viewBtn.innerHTML = '<i class="fa fa-eye me-1"></i> View';
        viewBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            const body = document.getElementById("viewRequestBody");
            if (!data) {
              body.innerHTML =
                '<p class="text-muted">No details available.</p>';
            } else {
              let html = `
                <h5 class="mb-2 document-type"><i class="fa fa-file me-2"></i>${escapeHtml(
                  data.document_type_name
                )}</h5>
                <p><strong>Request Date:</strong> ${escapeHtml(
                  data.request_date
                )}</p>
                <p><strong>Status:</strong> ${escapeHtml(data.status)}</p>
                <hr>
                <p><strong>Purpose:</strong> ${escapeHtml(
                  data.purpose || "N/A"
                )}</p>
                <p><strong>Requested For:</strong> ${escapeHtml(
                  data.requested_for || data.subject_full_name || "N/A"
                )}</p>
                <p><strong>Relation:</strong> ${escapeHtml(
                  data.relation_to_requestee || "N/A"
                )}</p>
              `;

              if (data.proof_upload) {
                const files = data.proof_upload
                  .split(",")
                  .map((s) => s.trim())
                  .filter(Boolean);
                if (files.length) {
                  html += "<hr><p><strong>Proof Files:</strong></p><ul>";
                  files.forEach((f) => {
                    const url = buildFileUrl(f);
                    html += `<li><a href="${escapeHtml(
                      url
                    )}" target="_blank">${escapeHtml(
                      f.split("/").pop()
                    )}</a></li>`;
                  });
                  html += "</ul>";
                }
              }

              body.innerHTML = html;
            }
            const vm = new bootstrap.Modal(
              document.getElementById("viewRequestModal")
            );
            vm.show();
          } catch (e) {
            console.error("Failed to load request details", e);
            alert("Failed to load request details.");
          }
        });

        const editBtn = document.createElement("button");
        editBtn.type = "button";
        editBtn.className = "btn btn-sm btn-primary";
        editBtn.innerHTML = '<i class="fa fa-pen me-1"></i> Edit';
        editBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            if (!data) {
              alert("Request not found");
              return;
            }

            // Populate edit form fields
            document.getElementById("edit_request_id").value = data.request_id;
            document.getElementById("edit_document_type_id").value =
              data.document_type_id;
            document.getElementById("edit_purpose").value = data.purpose || "";
            document.getElementById("edit_requested_for").value =
              data.requested_for || "";
            document.getElementById("edit_relation_to_requestee").value =
              data.relation_to_requestee || "";

            // Show/hide someone fields
            const editChk = document.getElementById("edit_forSomeone");
            const editSomeoneFields =
              document.getElementById("editSomeoneFields");
            if (data.requested_for && data.requested_for !== "") {
              editChk.checked = true;
              editSomeoneFields.classList.remove("d-none");
            } else {
              editChk.checked = false;
              editSomeoneFields.classList.add("d-none");
            }

            // Requirements for selected document type
            const reqResp = await fetch(
              `index.php?action=getRequirements&document_type_id=${data.document_type_id}`
            );
            const reqData = await reqResp.json();
            const editReqList = document.getElementById("editRequirementList");
            editReqList.innerHTML = "";
            if (
              reqData &&
              reqData.requirements &&
              reqData.requirements.length
            ) {
              reqData.requirements.forEach((r) => {
                const li = document.createElement("li");
                li.textContent = r;
                editReqList.appendChild(li);
              });
            } else {
              editReqList.innerHTML =
                '<li class="text-muted">No requirements.</li>';
            }

            // Existing proof files (with remove button)
            const editFileList = document.getElementById("editFileList");
            // reset captured files for this edit session
            editSelectedFiles = [];
            editFileList.innerHTML = "";
            if (data.proof_upload) {
              const files = data.proof_upload
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
              if (files.length) {
                files.forEach((f) => {
                  const li = document.createElement("li");
                  li.className =
                    "list-group-item d-flex justify-content-between align-items-center";

                  const left = document.createElement("div");
                  const link = document.createElement("a");
                  link.href = buildFileUrl(f);
                  link.target = "_blank";
                  link.textContent = f.split("/").pop();
                  left.appendChild(link);

                  const rmBtn = document.createElement("button");
                  rmBtn.type = "button";
                  rmBtn.className = "btn btn-sm btn-outline-danger ms-2";
                  rmBtn.innerHTML = '<i class="fa fa-trash"></i>';
                  rmBtn.addEventListener("click", async (evt) => {
                    evt.preventDefault();
                    if (!confirm("Remove this file?")) return;
                    try {
                      const resp = await fetch(
                        "index.php?action=removeProofFile",
                        {
                          method: "POST",
                          headers: { "Content-Type": "application/json" },
                          body: JSON.stringify({
                            request_id: data.request_id,
                            file_path: f,
                          }),
                        }
                      );
                      const j = await resp.json();
                      if (j.success) {
                        li.remove();
                      } else {
                        alert(
                          "Error: " + (j.message || "Failed to remove file")
                        );
                      }
                    } catch (e) {
                      console.error("Remove file error", e);
                      alert("Failed to remove file");
                    }
                  });

                  li.appendChild(left);
                  li.appendChild(rmBtn);
                  editFileList.appendChild(li);
                });
              } else {
                const li = document.createElement("li");
                li.className = "list-group-item text-muted";
                li.textContent = "No existing files.";
                editFileList.appendChild(li);
              }
            } else {
              const li = document.createElement("li");
              li.className = "list-group-item text-muted";
              li.textContent = "No existing files.";
              editFileList.appendChild(li);
            }

            // Show modal
            const em = new bootstrap.Modal(
              document.getElementById("editRequestModal")
            );
            em.show();
          } catch (e) {
            console.error("Failed to load request for editing", e);
            alert("Failed to load request for editing.");
          }
        });

        cardFooter.appendChild(viewBtn);
        cardFooter.appendChild(editBtn);
        card.appendChild(cardFooter);
        col.appendChild(card);
        container.appendChild(col);
      });
    } catch (e) {
      console.error("Error loading ongoing requests:", e);
      container.innerHTML =
        '<p class="text-danger">Failed to load ongoing requests.</p>';
    }
  }

  // 🔹 Load ongoing requests on page load
  loadOngoingRequests();

  //Load approved requests
  async function loadApprovedRequests() {
    const container = document.getElementById("approvedRequestsContainer");
    container.innerHTML =
      '<p class="text-muted">Loading approved requests...</p>';
    try {
      const response = await fetch(
        "index.php?action=getApprovedRequestsByUser"
      );
      const requests = await response.json();

      if (!requests.length) {
        container.innerHTML =
          '<p class="text-muted">No approved document requests found.</p>';
        return;
      }
      container.innerHTML = ""; // clear

      requests.forEach((row) => {
        const col = document.createElement("div");
        col.className = "col-sm-4 mb-sm-0 mt-2 position-relative";

        const card = document.createElement("div");
        card.className =
          "card resident-card shadow-md shadow-sm position-relative w-100 h-100";

        const cardBody = document.createElement("div");
        cardBody.className = "card-body";
        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${
              row.document_name
            }
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${
            row.request_date
          }</p>
          <p><i class="fa-solid fa-check-circle"></i> Status: ${renderStatusBadge(
            row.status
          )}</p>
          <p><i class="fa-solid fa-calendar-check"></i> Approved Date: ${
            row.approval_date ? row.approval_date : "N/A"
          }</p>
        `;

        if (row.requested_for && row.relation_to_requestee) {
          cardBody.innerHTML += `
            <hr>
            <p><i class="fa-solid fa-user"></i> Requested For: ${row.requested_for}</p>
            <p><i class="fa-solid fa-people-arrows"></i> Relation to Requestee: ${row.relation_to_requestee}</p>
          `;
        }

        card.appendChild(cardBody);
        // Footer (View/Edit) for history cards
        const cardFooter = document.createElement("div");
        cardFooter.className =
          "card-footer bg-transparent d-flex justify-content-end gap-2 border-0";

        const viewBtn = document.createElement("button");
        viewBtn.type = "button";
        viewBtn.className = "btn btn-sm btn-outline-primary";
        viewBtn.innerHTML = '<i class="fa fa-eye me-1"></i> View';
        viewBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            const body = document.getElementById("viewRequestBody");
            let html = `
              <h5 class="mb-2 document-type"><i class="fa fa-file me-2"></i>${escapeHtml(
                data.document_type_name
              )}</h5>
              <p><strong>Request Date:</strong> ${escapeHtml(
                data.request_date
              )}</p>
              <p><strong>Status:</strong> ${escapeHtml(data.status)}</p>
              <hr>
              <p><strong>Purpose:</strong> ${escapeHtml(
                data.purpose || "N/A"
              )}</p>
            `;
            if (data.proof_upload) {
              const files = data.proof_upload
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
              if (files.length) {
                html += "<hr><p><strong>Proof Files:</strong></p><ul>";
                files.forEach((f) => {
                  const url = buildFileUrl(f);
                  html += `<li><a href="${escapeHtml(
                    url
                  )}" target="_blank">${escapeHtml(
                    f.split("/").pop()
                  )}</a></li>`;
                });
                html += "</ul>";
              }
            }
            body.innerHTML = html;
            const vm = new bootstrap.Modal(
              document.getElementById("viewRequestModal")
            );
            vm.show();
          } catch (e) {
            console.error(e);
            alert("Failed to load details");
          }
        });

        // No edit on history perhaps, but add for parity
        const editBtn = document.createElement("button");
        editBtn.type = "button";
        editBtn.className = "btn btn-sm btn-primary";
        editBtn.innerHTML = '<i class="fa fa-pen me-1"></i> Edit';
        editBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            document.getElementById("edit_request_id").value = data.request_id;
            document.getElementById("edit_document_type_id").value =
              data.document_type_id;
            document.getElementById("edit_purpose").value = data.purpose || "";
            document.getElementById("edit_requested_for").value =
              data.requested_for || "";
            document.getElementById("edit_relation_to_requestee").value =
              data.relation_to_requestee || "";

            // Show/hide someone fields
            const editChk = document.getElementById("edit_forSomeone");
            const editSomeoneFields =
              document.getElementById("editSomeoneFields");
            if (data.requested_for && data.requested_for !== "") {
              editChk.checked = true;
              editSomeoneFields.classList.remove("d-none");
            } else {
              editChk.checked = false;
              editSomeoneFields.classList.add("d-none");
            }

            // Requirements for selected document type
            try {
              const reqResp = await fetch(
                `index.php?action=getRequirements&document_type_id=${data.document_type_id}`
              );
              const reqData = await reqResp.json();
              const editReqList = document.getElementById(
                "editRequirementList"
              );
              editReqList.innerHTML = "";
              if (
                reqData &&
                reqData.requirements &&
                reqData.requirements.length
              ) {
                reqData.requirements.forEach((r) => {
                  const li = document.createElement("li");
                  li.textContent = r;
                  editReqList.appendChild(li);
                });
              } else {
                editReqList.innerHTML =
                  '<li class="text-muted">No requirements.</li>';
              }
            } catch (e) {
              console.error(
                "Error loading requirements for edit (approved)",
                e
              );
            }

            // Existing proof files (same logic as ongoing)
            const editFileList = document.getElementById("editFileList");
            // reset captured files for this edit session
            editSelectedFiles = [];
            editFileList.innerHTML = "";
            if (data.proof_upload) {
              const files = data.proof_upload
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
              if (files.length) {
                files.forEach((f) => {
                  const li = document.createElement("li");
                  li.className =
                    "list-group-item d-flex justify-content-between align-items-center";
                  const left = document.createElement("div");
                  const link = document.createElement("a");
                  link.href = f;
                  link.target = "_blank";
                  link.textContent = f.split("/").pop();
                  left.appendChild(link);
                  const rmBtn = document.createElement("button");
                  rmBtn.type = "button";
                  rmBtn.className = "btn btn-sm btn-outline-danger ms-2";
                  rmBtn.innerHTML = '<i class="fa fa-trash"></i>';
                  rmBtn.addEventListener("click", async (evt) => {
                    evt.preventDefault();
                    if (!confirm("Remove this file?")) return;
                    try {
                      const resp2 = await fetch(
                        "index.php?action=removeProofFile",
                        {
                          method: "POST",
                          headers: { "Content-Type": "application/json" },
                          body: JSON.stringify({
                            request_id: data.request_id,
                            file_path: f,
                          }),
                        }
                      );
                      const j2 = await resp2.json();
                      if (j2.success) {
                        li.remove();
                      } else {
                        alert(
                          "Error: " + (j2.message || "Failed to remove file")
                        );
                      }
                    } catch (e) {
                      console.error("Remove file error", e);
                      alert("Failed to remove file");
                    }
                  });
                  li.appendChild(left);
                  li.appendChild(rmBtn);
                  editFileList.appendChild(li);
                });
              } else {
                const li = document.createElement("li");
                li.className = "list-group-item text-muted";
                li.textContent = "No existing files.";
                editFileList.appendChild(li);
              }
            } else {
              const li = document.createElement("li");
              li.className = "list-group-item text-muted";
              li.textContent = "No existing files.";
              editFileList.appendChild(li);
            }

            const em = new bootstrap.Modal(
              document.getElementById("editRequestModal")
            );
            em.show();
          } catch (e) {
            console.error(e);
            alert("Failed to load for edit");
          }
        });

        cardFooter.appendChild(viewBtn);
        cardFooter.appendChild(editBtn);
        card.appendChild(cardFooter);
        col.appendChild(card);
        container.appendChild(col);
      });
    } catch (e) {
      console.error("Error loading approved requests:", e);
      container.innerHTML =
        '<p class="text-danger">Failed to load approved requests.</p>';
    }
  }

  // 🔹 Load approved requests on page load
  loadApprovedRequests();

  // Load released requests with server endpoint
  async function loadReleasedRequests() {
    const container = document.getElementById("releasedRequestsContainer");
    if (!container) return;
    container.innerHTML =
      '<p class="text-muted">Loading released requests...</p>';
    try {
      const resp = await fetch("index.php?action=getReleasedRequestsByUser");
      const requests = await resp.json();
      if (!requests || !requests.length) {
        container.innerHTML =
          '<p class="text-muted">No released requests found.</p>';
        return;
      }
      container.innerHTML = "";
      requests.forEach((row) => {
        const col = document.createElement("div");
        col.className = "col-sm-4 mb-sm-0 mt-2 position-relative";
        const card = document.createElement("div");
        card.className =
          "card resident-card shadow-md shadow-sm position-relative w-100 h-100";

        const cardBody = document.createElement("div");
        cardBody.className = "card-body";
        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${
              row.document_name
            }
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${
            row.request_date
          }</p>
          <p><i class="fa-solid fa-calendar-check"></i> Released Date: ${
            row.release_date ? row.release_date : "N/A"
          }</p>
          <p><i class="fa-solid fa-info-circle"></i> Status: ${renderStatusBadge(
            row.status
          )}</p>
        `;

        card.appendChild(cardBody);

        // Footer with View button (editing after release may be disabled, but we keep View)
        const cardFooter = document.createElement("div");
        cardFooter.className =
          "card-footer bg-transparent d-flex justify-content-end gap-2 border-0";

        const viewBtn = document.createElement("button");
        viewBtn.type = "button";
        viewBtn.className = "btn btn-sm btn-outline-primary";
        viewBtn.innerHTML = '<i class="fa fa-eye me-1"></i> View';
        viewBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            const body = document.getElementById("viewRequestBody");
            if (!data) {
              body.innerHTML =
                '<p class="text-muted">No details available.</p>';
            } else {
              let html = `
                <h5 class="mb-2 document-type"><i class="fa fa-file me-2"></i>${escapeHtml(
                  data.document_type_name
                )}</h5>
                <p><strong>Request Date:</strong> ${escapeHtml(
                  data.request_date
                )}</p>
                <p><strong>Status:</strong> ${escapeHtml(data.status)}</p>
                <hr>
                <p><strong>Purpose:</strong> ${escapeHtml(
                  data.purpose || "N/A"
                )}</p>
              `;
              if (data.proof_upload) {
                const files = data.proof_upload
                  .split(",")
                  .map((s) => s.trim())
                  .filter(Boolean);
                if (files.length) {
                  html += "<hr><p><strong>Proof Files:</strong></p><ul>";
                  files.forEach((f) => {
                    const url = buildFileUrl(f);
                    html += `<li><a href="${escapeHtml(
                      url
                    )}" target="_blank">${escapeHtml(
                      f.split("/").pop()
                    )}</a></li>`;
                  });
                  html += "</ul>";
                }
              }
              body.innerHTML = html;
            }
            const vm = new bootstrap.Modal(
              document.getElementById("viewRequestModal")
            );
            vm.show();
          } catch (e) {
            console.error(e);
            alert("Failed to load details");
          }
        });

        // Append footer and card
        cardFooter.appendChild(viewBtn);
        card.appendChild(cardFooter);
        col.appendChild(card);
        container.appendChild(col);
      });
    } catch (e) {
      console.error("Error loading released requests", e);
      container.innerHTML =
        '<p class="text-danger">Failed to load released requests.</p>';
    }
  }

  // Load rejected requests with server endpoint
  async function loadRejectedRequests() {
    const container = document.getElementById("rejectedRequestsContainer");
    if (!container) return;
    container.innerHTML =
      '<p class="text-muted">Loading rejected requests...</p>';
    try {
      const resp = await fetch("index.php?action=getRejectedRequestsByUser");
      const requests = await resp.json();
      if (!requests || !requests.length) {
        container.innerHTML =
          '<p class="text-muted">No rejected requests found.</p>';
        return;
      }
      container.innerHTML = "";
      requests.forEach((row) => {
        const col = document.createElement("div");
        col.className = "col-sm-4 mb-sm-0 mt-2 position-relative";
        const card = document.createElement("div");
        card.className =
          "card resident-card shadow-md shadow-sm position-relative w-100 h-100";

        const cardBody = document.createElement("div");
        cardBody.className = "card-body";
        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${
              row.document_name
            }
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${
            row.request_date
          }</p>
          <p><i class="fa-solid fa-info-circle"></i> Status: ${renderStatusBadge(
            row.status
          )}</p>
          ${
            row.remarks
              ? `<hr><p><i class="fa-solid fa-comment-dots"></i> Remarks: ${row.remarks}</p>`
              : ""
          }
        `;

        card.appendChild(cardBody);

        const cardFooter = document.createElement("div");
        cardFooter.className =
          "card-footer bg-transparent d-flex justify-content-end gap-2 border-0";

        const viewBtn = document.createElement("button");
        viewBtn.type = "button";
        viewBtn.className = "btn btn-sm btn-outline-primary";
        viewBtn.innerHTML = '<i class="fa fa-eye me-1"></i> View';
        viewBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            const body = document.getElementById("viewRequestBody");
            let html = `
              <h5 class="mb-2 document-type"><i class="fa fa-file me-2"></i>${escapeHtml(
                data.document_type_name
              )}</h5>
              <p><strong>Request Date:</strong> ${escapeHtml(
                data.request_date
              )}</p>
              <p><strong>Status:</strong> ${escapeHtml(data.status)}</p>
              <hr>
              <p><strong>Purpose:</strong> ${escapeHtml(
                data.purpose || "N/A"
              )}</p>
            `;
            if (data.proof_upload) {
              const files = data.proof_upload
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
              if (files.length) {
                html += "<hr><p><strong>Proof Files:</strong></p><ul>";
                files.forEach((f) => {
                  html += `<li><a href="${escapeHtml(
                    f
                  )}" target="_blank">${escapeHtml(
                    f.split("/").pop()
                  )}</a></li>`;
                });
                html += "</ul>";
              }
            }
            if (data.remarks)
              html += `<hr><p><strong>Remarks:</strong> ${escapeHtml(
                data.remarks
              )}</p>`;
            body.innerHTML = html;
            const vm = new bootstrap.Modal(
              document.getElementById("viewRequestModal")
            );
            vm.show();
          } catch (e) {
            console.error(e);
            alert("Failed to load details");
          }
        });

        // Allow editing rejected requests so user can resubmit
        cardFooter.appendChild(viewBtn);

        // Edit button for rejected requests (resubmit / update documents)
        const editBtn = document.createElement("button");
        editBtn.type = "button";
        editBtn.className = "btn btn-sm btn-primary";
        editBtn.innerHTML = '<i class="fa fa-pen me-1"></i> Edit';
        editBtn.addEventListener("click", async () => {
          try {
            const resp = await fetch(
              `index.php?action=getRequestById&request_id=${row.request_id}`
            );
            const data = await resp.json();
            if (!data) {
              alert("Request not found");
              return;
            }

            // Populate edit form fields (same flow as other edit handlers)
            document.getElementById("edit_request_id").value = data.request_id;
            document.getElementById("edit_document_type_id").value =
              data.document_type_id;
            document.getElementById("edit_purpose").value = data.purpose || "";
            document.getElementById("edit_requested_for").value =
              data.requested_for || "";
            document.getElementById("edit_relation_to_requestee").value =
              data.relation_to_requestee || "";

            // Show/hide someone fields
            const editChk = document.getElementById("edit_forSomeone");
            const editSomeoneFields =
              document.getElementById("editSomeoneFields");
            if (data.requested_for && data.requested_for !== "") {
              editChk.checked = true;
              editSomeoneFields.classList.remove("d-none");
            } else {
              editChk.checked = false;
              editSomeoneFields.classList.add("d-none");
            }

            // Requirements for selected document type
            try {
              const reqResp = await fetch(
                `index.php?action=getRequirements&document_type_id=${data.document_type_id}`
              );
              const reqData = await reqResp.json();
              const editReqList = document.getElementById(
                "editRequirementList"
              );
              editReqList.innerHTML = "";
              if (
                reqData &&
                reqData.requirements &&
                reqData.requirements.length
              ) {
                reqData.requirements.forEach((r) => {
                  const li = document.createElement("li");
                  li.textContent = r;
                  editReqList.appendChild(li);
                });
              } else {
                editReqList.innerHTML =
                  '<li class="text-muted">No requirements.</li>';
              }
            } catch (e) {
              console.error(
                "Error loading requirements for edit (rejected)",
                e
              );
            }

            // Existing proof files (with remove button) - reuse editFileList logic
            const editFileList = document.getElementById("editFileList");
            // reset captured files for this edit session
            editSelectedFiles = [];
            editFileList.innerHTML = "";
            if (data.proof_upload) {
              const files = data.proof_upload
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
              if (files.length) {
                files.forEach((f) => {
                  const li = document.createElement("li");
                  li.className =
                    "list-group-item d-flex justify-content-between align-items-center";

                  const left = document.createElement("div");
                  const link = document.createElement("a");
                  link.href = buildFileUrl(f);
                  link.target = "_blank";
                  link.textContent = f.split("/").pop();
                  left.appendChild(link);

                  const rmBtn = document.createElement("button");
                  rmBtn.type = "button";
                  rmBtn.className = "btn btn-sm btn-outline-danger ms-2";
                  rmBtn.innerHTML = '<i class="fa fa-trash"></i>';
                  rmBtn.addEventListener("click", async (evt) => {
                    evt.preventDefault();
                    if (!confirm("Remove this file?")) return;
                    try {
                      const resp = await fetch(
                        "index.php?action=removeProofFile",
                        {
                          method: "POST",
                          headers: { "Content-Type": "application/json" },
                          body: JSON.stringify({
                            request_id: data.request_id,
                            file_path: f,
                          }),
                        }
                      );
                      const j = await resp.json();
                      if (j.success) {
                        li.remove();
                      } else {
                        alert(
                          "Error: " + (j.message || "Failed to remove file")
                        );
                      }
                    } catch (e) {
                      console.error("Remove file error", e);
                      alert("Failed to remove file");
                    }
                  });

                  li.appendChild(left);
                  li.appendChild(rmBtn);
                  editFileList.appendChild(li);
                });
              } else {
                const li = document.createElement("li");
                li.className = "list-group-item text-muted";
                li.textContent = "No existing files.";
                editFileList.appendChild(li);
              }
            } else {
              const li = document.createElement("li");
              li.className = "list-group-item text-muted";
              li.textContent = "No existing files.";
              editFileList.appendChild(li);
            }

            // Show modal
            const em = new bootstrap.Modal(
              document.getElementById("editRequestModal")
            );
            em.show();
          } catch (e) {
            console.error(e);
            alert("Failed to load for edit");
          }
        });

        cardFooter.appendChild(editBtn);
        card.appendChild(cardFooter);
        col.appendChild(card);
        container.appendChild(col);
      });
    } catch (e) {
      console.error("Error loading rejected requests", e);
      container.innerHTML =
        '<p class="text-danger">Failed to load rejected requests.</p>';
    }
  }

  // Load released/rejected on page load
  loadReleasedRequests();
  loadRejectedRequests();

  //Load rejected and released requests can be added similarly
  async function loadRequestHistory() {
    const container = document.getElementById("requestHistoryContainer");
    container.innerHTML =
      '<p class="text-muted">Loading request history...</p>';

    try {
      const response = await fetch("index.php?action=getRequestsHistoryByUser");
      const requests = await response.json();

      if (!requests || !requests.length) {
        container.innerHTML =
          '<p class="text-muted">No request history found.</p>';
        historyData = [];
        return;
      }

      // store for front-end filtering
      historyData = requests;
      renderHistory(historyData);
    } catch (e) {
      console.error("Error loading request history:", e);
      container.innerHTML =
        '<p class="text-danger">Failed to load request history.</p>';
    }
  }

  //Load request history on tab click
  loadRequestHistory();

  // Wire filter buttons (if present)
  const historyApplyBtn = document.getElementById("historyApplyFilter");
  const historyClearBtn = document.getElementById("historyClearFilter");
  const historyKeyword = document.getElementById("historyFilterKeyword");
  if (historyApplyBtn)
    historyApplyBtn.addEventListener("click", applyHistoryFilter);
  if (historyClearBtn)
    historyClearBtn.addEventListener("click", clearHistoryFilter);
  if (historyKeyword) {
    historyKeyword.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        applyHistoryFilter();
      }
    });
  }

  // -------------------------------
  // Edit modal small behaviors and submit
  // -------------------------------
  const editForm = document.getElementById("editRequestForm");
  if (editForm) {
    const editForSomeoneChk = document.getElementById("edit_forSomeone");
    const editSomeoneFields = document.getElementById("editSomeoneFields");
    if (editForSomeoneChk && editSomeoneFields) {
      function toggleEditSomeone() {
        const show = editForSomeoneChk.checked;
        editSomeoneFields.classList.toggle("d-none", !show);
        editSomeoneFields
          .querySelectorAll("input")
          .forEach((el) => el.toggleAttribute("required", show));
      }
      editForSomeoneChk.addEventListener("change", toggleEditSomeone);
      toggleEditSomeone();
    }

    // When document type change in edit form, load reqs
    const editDocTypeSelect = document.getElementById("edit_document_type_id");
    const editReqList = document.getElementById("editRequirementList");
    if (editDocTypeSelect && editReqList) {
      editDocTypeSelect.addEventListener("change", async function () {
        editReqList.innerHTML = "";
        if (!this.value) return;
        try {
          const resp = await fetch(
            `index.php?action=getRequirements&document_type_id=${this.value}`
          );
          const data = await resp.json();
          if (data.requirements && data.requirements.length) {
            data.requirements.forEach((r) => {
              const li = document.createElement("li");
              li.textContent = r;
              editReqList.appendChild(li);
            });
          } else {
            editReqList.innerHTML =
              '<li class="text-muted">No requirements.</li>';
          }
        } catch (e) {
          console.error("Error loading requirements for edit", e);
        }
      });
    }

    // Submit updated request
    editForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const submitBtn = editForm.querySelector('button[type="submit"]');
      const fd = new FormData(editForm);

      // Append any captured files made in edit modal
      if (editSelectedFiles && editSelectedFiles.length) {
        editSelectedFiles.forEach((f) => fd.append("proof_upload[]", f));
      }

      submitBtn.disabled = true;
      submitBtn.textContent = "Saving...";

      try {
        const res = await fetch("index.php?action=updateRequest", {
          method: "POST",
          body: fd,
        });
        const data = await res.json();
        if (data.success) {
          alert("Request updated successfully");
          const em = bootstrap.Modal.getInstance(
            document.getElementById("editRequestModal")
          );
          if (em) em.hide();
          loadOngoingRequests();
          loadApprovedRequests();
          loadRequestHistory();
        } else {
          alert("Error: " + (data.message || "Failed to update"));
        }
      } catch (err) {
        console.error("Update error", err);
        alert("Failed to update request");
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = "Save Changes";
      }
    });
  }

  // 🔹 Small helper to escape HTML
  function escapeHtml(str) {
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
        "/": "&#x2F;",
        "`": "&#x60;",
        "=": "&#x3D;",
      }[s];
    });
  }

  // Build absolute URL for a file path stored in DB. Uses `window.BASE_URL` when path is relative.
  function buildFileUrl(path) {
    if (!path) return "";
    const p = String(path).trim();
    // already absolute URL (http(s) or protocol-relative)
    if (/^(https?:)?\/\//i.test(p)) return p;
    // if already contains the BASE_URL, return as-is
    if (
      typeof window !== "undefined" &&
      window.BASE_URL &&
      p.indexOf(window.BASE_URL) === 0
    )
      return p;
    // if starts with slash, join to BASE_URL
    if (p.startsWith("/")) return (window.BASE_URL || "") + p;
    // otherwise treat as relative path under BASE_URL
    return (window.BASE_URL || "") + "/" + p;
  }
});
