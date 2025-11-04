document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('newRequestModal');
  if (!modal) return;

  //for the form submission
  const form = modal.querySelector('form');
  const submitBtn = form.querySelector('button[name="submit_request"]');
  const fileInput = form.querySelector('#proof_upload');
  const fileList = form.querySelector('#fileList');
  let selectedFiles = []; // tracks files

  // 🔹 Checkbox logic for "Requesting for someone else"
  const chk = modal.querySelector('#forSomeone');
  const fields = modal.querySelector('#someoneFields');
  if (chk && fields) {
    function update() {
      const show = chk.checked;
      fields.classList.toggle('d-none', !show);
      fields.querySelectorAll('input').forEach(el => el.toggleAttribute('required', show));
    }
    chk.addEventListener('change', update);
    update(); // initial state
  }
// 🔹 File input change handler

if (fileInput && fileList) {
  renderFileList(); // initial render

  fileInput.addEventListener('change', function () {
    const newFiles = Array.from(fileInput.files);

    // Merge new files while avoiding duplicates
    newFiles.forEach(file => {
      if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
        selectedFiles.push(file);
      }
    });

    renderFileList();
    fileInput.value = ''; // reset so the same file can be selected again
  });
}

// 🔹 Renders the file list dynamically
function renderFileList() {
  fileList.innerHTML = ''; // clear list

  if (selectedFiles.length === 0) {
    const emptyItem = document.createElement('li');
    emptyItem.className = 'list-group-item text-muted';
    emptyItem.textContent = 'No files selected.';
    fileList.appendChild(emptyItem);
    return;
  }

  selectedFiles.forEach((file, index) => {
    const listItem = document.createElement('li');
    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

    // 🔸 File name + size
    const left = document.createElement('span');
    left.innerHTML = `
      <i class="fa fa-file me-2"></i>${escapeHtml(file.name)}
      <small class="text-muted">(${(file.size / 1024).toFixed(1)} KB)</small>
    `;

    // 🔹 Optional image preview
    if (file.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.style.height = '40px';
      img.style.marginLeft = '8px';
      img.style.borderRadius = '4px';
      img.onload = () => URL.revokeObjectURL(img.src);
      left.appendChild(img);
    }

    // 🔸 Right section (file type badge + delete button)
    const right = document.createElement('div');
    right.className = 'd-flex align-items-center';

    const badge = document.createElement('span');
    badge.className = 'badge bg-secondary me-2';
    badge.textContent = file.type.split('/')[1] || 'N/A';
    right.appendChild(badge);

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn btn-sm btn-danger';
    deleteBtn.innerHTML = '<i class="fa fa-trash"></i>';
    deleteBtn.addEventListener('click', () => {
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
form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    selectedFiles.forEach(file => {
      formData.append('proof_upload[]', file);
    });

    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    try {
      const response = await fetch('index.php?action=submitRequest', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert('Request submitted successfully!');
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
        alert('Error: ' + data.message);
      }
    } catch (error) {
      console.error(error);
      alert('An error occurred while submitting the request.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit Request';
    }
  });

// 🔹 Fetch and display document requirements dynamically
const docTypeSelect = document.getElementById('document_type_id');
const reqList = document.getElementById('requirementList');

docTypeSelect.addEventListener('change', async function () {
    const docTypeId = this.value;
    reqList.innerHTML = '';

    if (!docTypeId) return;

    try {
        const response = await fetch(`index.php?action=getRequirements&document_type_id=${docTypeId}`);
        const data = await response.json();

        if (data.requirements.length) {
            data.requirements.forEach(req => {
                const li = document.createElement('li');
                li.textContent = req;
                reqList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = 'No requirements.';
            reqList.appendChild(li);
        }
    } catch (e) {
        console.error('Error fetching requirements', e);
        const li = document.createElement('li');
        li.textContent = 'Error loading requirements.';
        reqList.appendChild(li);
    }
});

// -------------------------------
  // 🔹 Ongoing requests AJAX
  // -------------------------------
  async function loadOngoingRequests() {
    const container = document.getElementById('ongoingRequestsContainer');
    container.innerHTML = '<p class="text-muted">Loading ongoing requests...</p>';

    try {
      const response = await fetch('index.php?action=getOngoingRequests');
      const requests = await response.json();

      if (!requests.length) {
        container.innerHTML = '<p class="text-muted">No ongoing document requests found.</p>';
        return;
      }

      container.innerHTML = ''; // clear

      requests.forEach(row => {
        const col = document.createElement('div');
        col.className = 'col-sm-4 mb-sm-0 mt-2 position-relative';

        const card = document.createElement('div');
        card.className = 'card shadow-md shadow-sm position-relative';

        // 🔹 Delete badge button
      const deleteBtn = document.createElement('span');
      deleteBtn.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle p-2 rounded-pill';
      deleteBtn.style.cursor = 'pointer';
      deleteBtn.innerHTML = '<i class="fa-solid fa-square-xmark" style="font-size: 1.2rem;"></i>';
      deleteBtn.title = 'Delete Request';
      deleteBtn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to delete this request?')) return;

        try {
          const delResponse = await fetch('index.php?action=deleteRequest', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: row.request_id })
          });

          const delData = await delResponse.json();
          if (delData.success) {
            alert('Request deleted successfully!');
            col.remove(); // remove card from DOM
            loadOngoingRequests(); // refresh list
          } else {
            alert('Error: ' + delData.message);
          }
        } catch (e) {
          console.error('Error deleting request:', e);
          alert('Failed to delete request.');
        }
      });





        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';

        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${row.document_name}
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${row.request_date}</p>
          <p><i class="fa-solid fa-bars-progress"></i> Status: ${row.status}</p>
        `;

        if (row.requested_for && row.relation_to_requestee) {
          cardBody.innerHTML += `
            <hr>
            <p><i class="fa-solid fa-user"></i> Requested For: ${row.requested_for}</p>
            <p><i class="fa-solid fa-people-arrows"></i> Relation to Requestee: ${row.relation_to_requestee}</p>
          `;
        }
        card.appendChild(deleteBtn);
        card.appendChild(cardBody);
        col.appendChild(card);
        container.appendChild(col);
      });
    } catch (e) {
      console.error('Error loading ongoing requests:', e);
      container.innerHTML = '<p class="text-danger">Failed to load ongoing requests.</p>';
    }
  }

  // 🔹 Load ongoing requests on page load
  loadOngoingRequests();

  //Load approved requests
  async function loadApprovedRequests() {
    const container = document.getElementById('approvedRequestsContainer');
    container.innerHTML = '<p class="text-muted">Loading approved requests...</p>';
    try {
      const response = await fetch('index.php?action=getApprovedRequestsByUser');
      const requests = await response.json();

      if(!requests.length) {
        container.innerHTML = '<p class="text-muted">No approved document requests found.</p>';
        return;
      }
      container.innerHTML = ''; // clear
      
      requests.forEach(row => {
        const col = document.createElement('div');
        col.className = 'col-sm-4 mb-sm-0 mt-2 position-relative';

        const card = document.createElement('div');
        card.className = 'card shadow-md shadow-sm position-relative';

        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${row.document_name}
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${row.request_date}</p>
          <p><i class="fa-solid fa-check-circle"></i> Status: ${row.status}</p>
          <p><i class="fa-solid fa-calendar-check"></i> Approved Date: ${row.approval_date ? row.approval_date : 'N/A'}</p>
        `;
      

        if (row.requested_for && row.relation_to_requestee) {
          cardBody.innerHTML += `
            <hr>
            <p><i class="fa-solid fa-user"></i> Requested For: ${row.requested_for}</p>
            <p><i class="fa-solid fa-people-arrows"></i> Relation to Requestee: ${row.relation_to_requestee}</p>
          `;
        }

        card.appendChild(cardBody);
        col.appendChild(card);
        container.appendChild(col);
      });
    } catch (e) {
      console.error('Error loading approved requests:', e);
      container.innerHTML = '<p class="text-danger">Failed to load approved requests.</p>';
    }
  }

  // 🔹 Load approved requests on page load
  loadApprovedRequests();

  //Load rejected and released requests can be added similarly
  async function loadRequestHistory() {
    const container = document.getElementById('requestHistoryContainer');
    container.innerHTML = '<p class="text-muted">Loading request history...</p>';

    try{
      const response = await fetch('index.php?action=getRequestsHistoryByUser');
      const requests = await response.json();
      
      if(!requests.length) {
        container.innerHTML = '<p class="text-muted">No request history found.</p>';
        return;
      }

      container.innerHTML = ''; // clear

      requests.forEach(row => {
        const col = document.createElement('div');
        col.className = 'col-sm-4 mb-sm-0 mt-2 position-relative';

        const card = document.createElement('div');
        card.className = 'card shadow-md shadow-sm position-relative';
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        cardBody.innerHTML = `
          <h5 class="card-title fs-2 document-type">
            <i class="fa fa-file" style="font-size: 2rem;"></i> ${row.document_name}
          </h5>
          <p><i class="fa-solid fa-calendar"></i> Request Date: ${row.request_date}</p>
          <p><i class="fa-solid fa-check-circle"></i> Approved Date: ${row.approval_date ? row.approval_date : 'N/A'}</p>
          <p><i class="fa-solid fa-calendar-check"></i> Released Date: ${row.release_date ? row.release_date : 'N/A'}</p>
          <p><i class="fa-solid fa-info-circle"></i> Status: ${row.status && row.release_date === null ? 'Rejected' : row.status}</p>
        `;

        if (row.requested_for && row.relation_to_requestee) {
          cardBody.innerHTML += `
            <hr>
            <p><i class="fa-solid fa-user"></i> Requested For: ${row.requested_for}</p>
            <p><i class="fa-solid fa-people-arrows"></i> Relation to Requestee: ${row.relation_to_requestee}</p>
          `;
        }

        if(row.remarks && row.status === 'Rejected') {
          cardBody.innerHTML += `
            <hr>
            <p><i class="fa-solid fa-comment-dots"></i> Remarks: ${row.remarks}</p>
          `;
        }

        card.appendChild(cardBody);
        col.appendChild(card);
        container.appendChild(col);

      });

    } catch (e) {
      console.error('Error loading request history:', e);
      container.innerHTML = '<p class="text-danger">Failed to load request history.</p>';
    }
  }

  //Load request history on tab click
  loadRequestHistory();

  // 🔹 Small helper to escape HTML
  function escapeHtml(str) {
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
    return ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
      "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
    })[s];
  });
}

});

