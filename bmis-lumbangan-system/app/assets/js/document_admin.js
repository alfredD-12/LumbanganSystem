$(document).ready(function () {
  const table = $("#requestsTable").DataTable({
    ajax: "index.php?action=getAllRequests",
    columns: [
      { data: "request_id" },
      { data: "requester_name" },
      { data: "document_name" },
      { data: "requested_for" },
      { data: "relation_to_requestee" },
      { data: "purpose" },
      {
        data: "status",
        render: (data) =>
          `<span class="status-badge status-${data}">${data}</span>`,
      },
      {
        data: "proof_upload",
        render: function (data) {
          if (!data) return "No file";
          const files = data.split(",");
          return files
            .map(
              (f) => `<a href="${BASE_URL}${f}" target="_blank">View File</a>`
            )
            .join("<br>");
        },
      },
      { data: "approval_date" },
      { data: "remarks" },
      { data: "request_date" },
      {
        data: null,
        render: (data) => `
          <button class="btn btn-sm btn-success edit-btn" data-id="${data.request_id}">Update</button>
        `,
      },
    ],
    order: [[10, "desc"]],
    responsive: true,
    pageLength: 10,
    language: { search: "🔍 Search Requests:" },
  });

  // Handle Update button click
  $("#requestsTable").on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    $("#request_id").val(id);
    $("#statusModal").modal("show");
  });

  // Handle Form Submit
  $("#statusForm").on("submit", function (e) {
    e.preventDefault();
    $.post(
      "index.php?action=updateStatus",
      $(this).serialize(),
      function (res) {
        if (res.success) {
          $("#statusModal").modal("hide");
          table.ajax.reload();
        } else {
          alert("Failed to update status");
        }
      },
      "json"
    );
  });
});
