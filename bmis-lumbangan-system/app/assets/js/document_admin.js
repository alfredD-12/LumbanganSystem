$(document).ready(function () {
  /* ----------------------------
     🔹 INITIALIZE DATATABLE
  ----------------------------- */
  const table = $("#requestsTable").DataTable({
    ajax: "index.php?action=getAllRequests",
    responsive: false,
    autoWidth: true,
    pageLength: 10,
    order: [[10, "asc"]],
    language: {
      search: "🔍",
      searchPlaceholder: "Search requests, names, or purposes...",
    },
    /* 🔹 Enable Buttons (ColVis + Reset Order) */
    colReorder: true,
    dom:
      "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row mt-3 text-end'<'col-sm-12'i><'col-sm-12 d-flex justify-content-end' p>>",
    buttons: [
      {
        extend: "pageLength",
        className:
          "dt-btn-modern btn btn-light border rounded-pill px-3 py-1 shadow-sm me-2",
      },
      {
        extend: "colvis",
        text: "🧭 Toggle Columns",
        className:
          "dt-btn-modern btn btn-primary text-white rounded-pill px-3 py-1 shadow-sm me-2",
      },
      {
        text: "🔄 Reset Columns",
        className:
          "dt-btn-modern btn btn-secondary text-white rounded-pill px-3 py-1 shadow-sm",
        action: function (e, dt, node, config) {
          dt.colReorder.reset();
        },
      },
    ],
    columns: [
      { data: "request_id" },
      { data: "requester_name" },
      { data: "document_name" },
      { data: "request_date" },
      { data: "requested_for" },
      { data: "purpose" },
      { data: "relation_to_requestee" },
      {
        data: "requirements",
        render: function (data) {
          if (!data) return "—"; // if no data, show dash
          const items = data.split(",").map((item) => item.trim());
          return `
      <ul class="mb-0 ps-3">
        ${items.map((item) => `<li>${item}</li>`).join("")}
      </ul>
    `;
        },
      },
      {
        data: "proof_upload",
        render: function (data) {
          if (!data) return "No file";
          const files = data.split(",");
          return files
            .map(
              (f) => `
                <button class="btn btn-sm btn-outline-secondary mb-2 view-proof"
                  data-file="${BASE_URL}${f.trim()}">
                  View
                </button>`
            )
            .join("<br>");
        },
      },
      { data: "approval_date" },
      { data: "release_date" },
      { data: "remarks" },
      {
        data: "status",
        render: (data) =>
          `<span class="status-badge status-${data}">${data}</span>`,
      },
      {
        data: null,
        render: (data) => `
          <button class="btn btn-sm btn-warning edit-btn"
            data-id="${data.request_id}">
            Update
          </button>
        `,
      },
    ],
  });

  /* ----------------------------
     🔹 STATUS FILTER DROPDOWN
  ----------------------------- */
  $("#statusFilter").on("change", function () {
    table.column(12).search(this.value).draw();
  });

  /* ----------------------------
     🔹 UPDATE SUMMARY CARDS
  ----------------------------- */
  function updateSummaryCards() {
    $.getJSON("index.php?action=getStatusSummary", function (data) {
      $("#pendingCount").text(data.Pending || 0);
      $("#approvedCount").text(data.Approved || 0);
      $("#rejectedCount").text(data.Rejected || 0);
      $("#releasedCount").text(data.Released || 0);
    });
  }
  //Runs after the page loads
  updateSummaryCards();

  /* ----------------------------
     🔹 VIEW PROOF MODAL
  ----------------------------- */
  $("#requestsTable").on("click", ".view-proof", function () {
    const fileUrl = $(this).data("file");
    const ext = fileUrl.split(".").pop().toLowerCase();

    let content = "";
    if (["png", "jpg", "jpeg", "gif"].includes(ext)) {
      content = `<img src="${fileUrl}" class="img-fluid rounded shadow">`;
    } else if (ext === "pdf") {
      content = `<embed src="${fileUrl}" type="application/pdf" width="100%" height="500px">`;
    } else {
      content = `<p>Unsupported file format. <a href="${fileUrl}" target="_blank">Open manually</a></p>`;
    }

    $("#proofModal .modal-body").html(content);
    new bootstrap.Modal("#proofModal").show();
  });

  /* ----------------------------
     🔹 OPEN UPDATE STATUS MODAL
  ----------------------------- */
  $("#requestsTable").on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    $("#request_id").val(id);
    $("#statusModal").modal("show");
  });

  let oldStatus = null;
  let newStatus = null;

  // Capture current status when modal opens
  $("#statusModal").on("show.bs.modal", function () {
    oldStatus = $("#status").val();
  });

  // When Save Changes button is clicked
  $("#submitUpdate").on("click", function (e) {
    e.preventDefault();

    newStatus = $("#status").val();

    if (!newStatus) {
      alert("Please select a status before saving.");
      return;
    }

    if (newStatus === oldStatus) {
      alert("No changes detected.");
      return;
    }

    // Show confirmation modal
    $("#statusModal").modal("hide");
    $("#confirmMessage").html(
      `Are you sure you want to update this request from 
     <strong>${oldStatus}</strong> to <strong>${newStatus}</strong>?`
    );
    $("#confirmUpdateModal").modal("show");
  });

  // When user confirms update
  $("#confirmUpdateBtn").on("click", function () {
    // Submit the form via AJAX
    $.post(
      "index.php?action=updateStatus",
      $("#statusForm").serialize(),
      function (res) {
        if (res.success) {
          $("#confirmUpdateModal").modal("hide");
          $("#statusModal").modal("hide");

          // Reload DataTable without resetting pagination
          $("#requestsTable").DataTable().ajax.reload(null, false);
          updateSummaryCards(); // 🔁 refresh summary numbers
        } else {
          alert("Failed to update status");
        }
      },
      "json"
    );
  });

  /* ----------------------------
     🔹 HANDLE STATUS UPDATE FORM
  ----------------------------- */
  // Optional: run this again whenever you update a request status
  $("#statusForm").on("submit", function (e) {
    e.preventDefault();
    $.post(
      "index.php?action=updateStatus",
      $(this).serialize(),
      function (res) {
        if (res.success) {
          $("#statusModal").modal("hide");
          $("#requestsTable").DataTable().ajax.reload();
          updateSummaryCards(); // 🔁 refresh summary numbers
        } else {
          alert("Failed to update status");
        }
      },
      "json"
    );
  });

  /* ----------------------------
     🔹 TOAST NOTIFICATION HELPER
  ----------------------------- */
  function showToast(message, type = "success") {
    const toast = $(`
      <div class="toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
        <div class="d-flex">
          <div class="toast-body">${message}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    `);
    $("body").append(toast);
    new bootstrap.Toast(toast[0], { delay: 2500 }).show();
  }
});
