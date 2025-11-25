<div class="container py-4">

    <div class="d-flex justify-content-between align-items-end mb-2">
        <div>
            <h3 style="color: #1E3A5F; font-weight: 700;">Document Types</h3>
            <p class="text-muted mb-0">Manage pricing and requirements</p>
        </div>
    </div>

    <div class="control-bar d-flex flex-wrap gap-3 align-items-center justify-content-between">

        <div class="d-flex gap-3 flex-grow-1">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Search documents..." id="docSearchInput">
            </div>

            <select class="form-select w-auto" id="categoryFilter" style="min-width: 180px;">
                <option value="">All Categories</option>
            </select>
        </div>

        <button class="btn-elegant" id="addDocumentTypeBtn">
            <i class="fas fa-plus"></i> Add New Document
        </button>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="docTypeContainer"></div>

</div>