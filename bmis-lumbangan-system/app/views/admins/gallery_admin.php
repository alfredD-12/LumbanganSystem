<?php include_once __DIR__ . '/../../components/admin_components/header-admin.php'  ?>

<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-images"></i> Gallery Management</h2>

  <!-- Add Gallery Button -->
  <div class="mb-4">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#galleryModal" onclick="openAddModal()">
      <i class="fas fa-plus"></i> Add New Gallery Item
    </button>
  </div>

  <!-- Gallery Items Grid -->
  <div class="row" id="galleryGrid">
    <!-- Gallery items will be loaded here -->
  </div>
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title mb-0" id="modalTitle">Add Gallery Item</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="galleryForm" enctype="multipart/form-data">
        <div class="modal-body py-3">
          <input type="hidden" id="galleryId" name="id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-2">
                <label for="title" class="form-label mb-1 small">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="title" name="title" required>
              </div>
              
              <div class="mb-2">
                <label for="description" class="form-label mb-1 small">Description</label>
                <textarea class="form-control form-control-sm" id="description" name="description" rows="2"></textarea>
              </div>
              
              <div class="mb-2">
                <label for="displayOrder" class="form-label mb-1 small">Display Order</label>
                <input type="number" class="form-control form-control-sm" id="displayOrder" name="display_order" value="0" min="0">
                <small class="text-muted" style="font-size: 0.75rem;">Lower numbers appear first</small>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="mb-2">
                <label for="image" class="form-label mb-1 small">Image <span class="text-danger" id="imageRequired">*</span></label>
                <input type="file" class="form-control form-control-sm" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                <small class="text-muted" style="font-size: 0.75rem;">Recommended size: 500x350px</small>
              </div>
              
              <div class="mb-2" id="imagePreviewContainer" style="display: none;">
                <label class="form-label mb-1 small">Preview</label>
                <div style="border: 2px dashed #ddd; border-radius: 6px; padding: 8px; background: #f8f9fa; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                  <img id="imagePreview" src="" style="max-width: 100%; max-height: 180px; border-radius: 6px;">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
            <i class="fas fa-save"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this gallery item?</p>
        <p class="text-danger"><strong>This action cannot be undone!</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
          <i class="fas fa-trash"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Success Notification Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
        <h5 class="mt-3 mb-2" id="successTitle">Success!</h5>
        <p class="mb-0 text-muted" id="successMessage"></p>
      </div>
    </div>
  </div>
</div>

<!-- Error Notification Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
        <h5 class="mt-3 mb-2">Error!</h5>
        <p class="mb-0 text-muted" id="errorMessage"></p>
      </div>
    </div>
  </div>
</div>

<style>
/* Force all card elements to be fully opaque */
.gallery-card,
.gallery-card *,
.gallery-card::before,
.gallery-card::after {
    opacity: 1 !important;
}

.gallery-card {
    border: 2px solid #dee2e6;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    background-color: #ffffff !important;
    background: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.gallery-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    border-color: #1e3a5f;
}

.gallery-card-img {
    height: 200px;
    background: linear-gradient(135deg, #1e3a5f, #ff6b35);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.gallery-card-img::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: transparent;
    pointer-events: none;
}

.gallery-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    background-color: #fff;
}

.gallery-card-img i {
    font-size: 3rem;
    color: rgba(255,255,255,0.9);
}

.gallery-card-body {
    padding: 1.25rem;
    background-color: #ffffff !important;
    background: #ffffff !important;
    position: relative;
    z-index: 1;
}

.gallery-card-title {
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 0.5rem;
}

.gallery-card-desc {
    color: #333;
    font-size: 0.95rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.gallery-card-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: space-between;
}

.badge-order {
    background: #6c757d;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    color: #fff;
}

/* Slightly improve modal content appearance */
.modal .modal-header, .modal .modal-body, .modal .modal-footer {
    text-align: left;
}

/* Fix sidebar toggle button - prevent scroll issue */
.sidebar-toggle {
    position: fixed !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    z-index: 9999 !important;
}

.sidebar-toggle:hover {
    transform: translateY(-50%) scale(1.15) !important;
}

.sidebar-toggle:active {
    transform: translateY(-50%) scale(1.05) !important;
}
</style><script>
let deleteGalleryId = null;

// Show success notification
function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    setTimeout(() => modal.hide(), 2000);
}

// Show error notification
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
    setTimeout(() => modal.hide(), 3000);
}

// Load gallery items
function loadGallery() {
    fetch('../../controllers/GalleryController.php?action=fetch&active_only=false')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayGallery(data.data);
            }
        })
        .catch(err => console.error('Error loading gallery:', err));
}

// Display gallery items
function displayGallery(items) {
    const grid = document.getElementById('galleryGrid');
    
    // Completely reset the grid
    while (grid.firstChild) {
        grid.removeChild(grid.firstChild);
    }
    
    if (items.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No gallery items found. Add your first item!</p></div>';
        return;
    }
    
    items.forEach(item => {
        const col = document.createElement('div');
        col.className = 'col-md-4 mb-4';
        col.innerHTML = `
            <div class="gallery-card" data-id="${item.id}">
                <div class="gallery-card-img">
                    ${item.image_path ? `<img src="../../uploads/gallery/${item.image_path}" alt="${item.title}">` : '<i class="fas fa-image"></i>'}
                </div>
                <div class="gallery-card-body">
                    <h5 class="gallery-card-title">${item.title}</h5>
                    <p class="gallery-card-desc">${item.description || 'No description'}</p>
                    <div class="d-flex justify-content-end align-items-center mb-2">
                        <span class="badge ${item.is_active ? 'bg-success' : 'bg-secondary'}">${item.is_active ? 'Active' : 'Inactive'}</span>
                    </div>
                    <div class="gallery-card-actions">
                        <button class="btn btn-sm btn-primary" onclick="openEditModal(${item.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-${item.is_active ? 'warning' : 'success'}" onclick="toggleStatus(${item.id})">
                            <i class="fas fa-${item.is_active ? 'eye-slash' : 'eye'}"></i> ${item.is_active ? 'Hide' : 'Show'}
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="openDeleteModal(${item.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(col);
    });
}

// Open add modal
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Gallery Item';
    document.getElementById('galleryForm').reset();
    document.getElementById('galleryId').value = '';
    document.getElementById('imageRequired').style.display = 'inline';
    document.getElementById('image').required = true;
    document.getElementById('imagePreviewContainer').style.display = 'none';
}

// Open edit modal
function openEditModal(id) {
    fetch(`../../controllers/GalleryController.php?action=fetch&active_only=false`)
        .then(res => res.json())
        .then(data => {
            const item = data.data.find(g => g.id == id);
            if (item) {
                document.getElementById('modalTitle').textContent = 'Edit Gallery Item';
                document.getElementById('galleryId').value = item.id;
                document.getElementById('title').value = item.title;
                document.getElementById('description').value = item.description;
                document.getElementById('displayOrder').value = item.display_order;
                document.getElementById('imageRequired').style.display = 'none';
                document.getElementById('image').required = false;
                
                if (item.image_path) {
                    document.getElementById('imagePreview').src = `../../uploads/gallery/${item.image_path}`;
                    document.getElementById('imagePreviewContainer').style.display = 'block';
                }
                
                new bootstrap.Modal(document.getElementById('galleryModal')).show();
            }
        });
}

// Preview image
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Handle form submit
document.getElementById('galleryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('galleryId').value;
    formData.append('action', id ? 'update' : 'create');
    
    fetch('../../controllers/GalleryController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('galleryModal')).hide();
            loadGallery();
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showError('Failed to save gallery item');
    });
});

// Toggle status
function toggleStatus(id) {
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', id);
    
    fetch('../../controllers/GalleryController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Force complete reload to refresh grid
            setTimeout(() => loadGallery(), 100);
            showSuccess('Status updated successfully');
        } else {
            showError(data.message || 'Failed to toggle status');
        }
    })
    .catch(err => {
        console.error('Toggle error:', err);
        showError('Failed to toggle status');
    });
}

// Open delete modal
function openDeleteModal(id) {
    deleteGalleryId = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Confirm delete
function confirmDelete() {
    if (!deleteGalleryId) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', deleteGalleryId);
    
    fetch('../../controllers/GalleryController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            deleteGalleryId = null;
            // Force complete reload and grid reflow
            setTimeout(() => loadGallery(), 150);
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showError('Failed to delete gallery item');
    });
}

// Load gallery on page load
loadGallery();
</script>

<?php include_once __DIR__ . '/../../components/admin_components/footer-admin.php'  ?>
