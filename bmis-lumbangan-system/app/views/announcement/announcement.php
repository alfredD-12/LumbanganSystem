<!-- Google Font: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- Modern announcements styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/announcement/announcements_modern.css?v=<?php echo time(); ?>">
<?php
$pageTitle = 'Barangay Lumbangan - Announcement';
include_once __DIR__ . '/../../components/admin_components/header-admin.php'  
?>
<div class="announcements-page">
    <!-- Modern Hero Header -->
    <header class="hero-header">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="bi bi-shield-check"></i>
                <span>Admin Console</span>
            </div>
            <h1 class="hero-title">Barangay Announcement Manager</h1>
            <p class="hero-subtitle">Create, curate, and schedule updates that feed the public announcements board.</p>
            <div class="hero-actions">
                <a href="index.php?page=public_announcement" class="btn-hero btn-hero-secondary" target="_blank" rel="noopener">
                    <i class="bi bi-arrow-up-right-square me-2"></i>Preview Public Board
                </a>
                <a href="#announcementForm" class="btn-hero btn-hero-primary">
                    <i class="bi bi-megaphone-fill me-2"></i>New Announcement
                </a>
            </div>
        </div>
    </header>

    <main class="ann-main container">
        <!-- Modern Filters Bar -->
        <div class="filters-bar">
            <div class="filters-compact">
                <!-- Status Filter Pills -->
                <div class="status-filters">
                          <a href="index.php?page=admin_announcements<?php echo $start_date || $end_date || $q ? '&' . http_build_query(array_filter(['start_date' => $start_date, 'end_date' => $end_date, 'q' => $q])) : ''; ?>" 
                       class="status-pill <?php echo !$status_filter ? 'active' : ''; ?>">
                        <i class="bi bi-list-ul"></i>
                        <span>All</span>
                    </a>
                          <a href="index.php?page=admin_announcements&status=published<?php echo $start_date || $end_date || $q ? '&' . http_build_query(array_filter(['start_date' => $start_date, 'end_date' => $end_date, 'q' => $q])) : ''; ?>" 
                       class="status-pill status-published <?php echo $status_filter === 'published' ? 'active' : ''; ?>">
                        <i class="bi bi-check-circle"></i>
                        <span>Published</span>
                    </a>
                          <a href="index.php?page=admin_announcements&status=draft<?php echo $start_date || $end_date || $q ? '&' . http_build_query(array_filter(['start_date' => $start_date, 'end_date' => $end_date, 'q' => $q])) : ''; ?>" 
                       class="status-pill status-draft <?php echo $status_filter === 'draft' ? 'active' : ''; ?>">
                        <i class="bi bi-pencil-square"></i>
                        <span>Drafts</span>
                    </a>
                          <a href="index.php?page=admin_announcements&status=archived<?php echo $start_date || $end_date || $q ? '&' . http_build_query(array_filter(['start_date' => $start_date, 'end_date' => $end_date, 'q' => $q])) : ''; ?>" 
                       class="status-pill status-archived <?php echo $status_filter === 'archived' ? 'active' : ''; ?>">
                        <i class="bi bi-archive"></i>
                        <span>Archived</span>
                    </a>
                </div>

                <!-- Search and Date Filters -->
                <form method="get" class="filters-inline" id="filterForm">
                    <input type="hidden" name="page" value="admin_announcements">
                    <?php if ($status_filter): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <?php endif; ?>
                    
                    <div class="filter-input-group">
                        <i class="bi bi-search"></i>
                        <input name="search" id="search" type="text" placeholder="Search announcements..." value="<?php echo htmlspecialchars($q ?? ''); ?>">
                    </div>
                    
                    <div class="filter-input-group">
                        <i class="bi bi-calendar-range"></i>
                        <input name="start_date" id="start_date" type="date" placeholder="From" value="<?php echo htmlspecialchars($start_date ?? ''); ?>">
                    </div>
                    
                    <div class="filter-input-group">
                        <i class="bi bi-calendar-check"></i>
                        <input name="end_date" id="end_date" type="date" placeholder="To" value="<?php echo htmlspecialchars($end_date ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn-filter-apply">
                        <i class="bi bi-funnel-fill"></i>
                        <span>Apply</span>
                    </button>
                    
                    <a href="index.php?page=admin_announcements" class="btn-filter-reset">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </form>
            </div>
        </div>
        <!-- Form Section -->
        <section class="form-section mb-5">
            <div class="section-header-modern">
                <div class="section-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div class="section-text">
                    <h2 class="section-title-modern">Announcement Composer</h2>
                    <p class="section-subtitle-modern">Craft new bulletins or update existing announcements.</p>
                </div>
            </div>
            <div class="modern-card form-card">
                <div class="card-body">
                    <h4 class="form-title"><i class="bi bi-<?php echo $editData ? 'pencil-square' : 'plus-circle'; ?>-fill me-2"></i><?php echo $editData ? 'Edit Announcement' : 'Add Announcement'; ?></h4>
                    <form method="post" enctype="multipart/form-data" id="announcementForm" data-confirm="<?php echo $editData ? 'Are you sure you want to update this announcement?' : 'Are you sure you want to create this announcement?'; ?>">
                        <?php if ($editData): ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="create">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-tag-fill me-2"></i>Title</label>
                                    <input name="title" class="form-control" required value="<?php echo $editData ? htmlspecialchars($editData['title']) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-eye-fill me-2"></i>Audience</label>
                                    <select name="audience" class="form-select">
                                        <?php $opts = ['all' => 'All', 'officials' => 'Officials', 'residents' => 'Residents'];
                                        $sel = $editData ? $editData['audience'] : 'all';
                                        foreach ($opts as $k => $v) : ?>
                                            <option value="<?php echo $k; ?>" <?php echo $sel === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-tags-fill me-2"></i>Type</label>
                                    <?php
                                    $typeOptions = ['general' => 'General', 'event' => 'Event', 'project' => 'Project', 'notice' => 'Notice', 'workshop' => 'Workshop', 'meeting' => 'Meeting', 'emergency' => 'Emergency', 'other' => 'Other'];
                                    $typeSel = $editData ? ($editData['type'] ?? 'general') : 'general';
                                    $isCustomType = !in_array($typeSel, array_keys($typeOptions));
                                    $customTypeValue = $isCustomType ? $typeSel : '';
                                    ?>
                                    <select name="type" id="announcementTypeSelect" class="form-select">
                                        <?php foreach ($typeOptions as $k => $v): ?>
                                            <option value="<?php echo htmlspecialchars($k); ?>" <?php echo (!$isCustomType && $typeSel === $k) ? 'selected' : ''; ?>><?php echo htmlspecialchars($v); ?></option>
                                        <?php endforeach; ?>
                                        <?php if ($isCustomType): ?>
                                            <option value="<?php echo htmlspecialchars($customTypeValue); ?>" selected><?php echo htmlspecialchars(ucfirst($customTypeValue)); ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <input type="text" id="announcementTypeCustom" class="form-control mt-2" placeholder="Specify other type" style="display:<?php echo $isCustomType ? 'block' : 'none'; ?>;" value="<?php echo htmlspecialchars($customTypeValue); ?>">
                                    <small class="text-muted">Choose a type for this announcement or select "Other" to enter a custom type.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-file-earmark-check me-2"></i>Status</label>
                                    <select name="status" class="form-select">
                                        <?php $statusOpts = ['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'];
                                        $statusSel = $editData ? $editData['status'] : 'published';
                                        foreach ($statusOpts as $k => $v) : ?>
                                            <option value="<?php echo $k; ?>" <?php echo $statusSel === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-person-fill me-2"></i>Author</label>
                                    <input name="author" class="form-control" value="<?php echo $editData ? htmlspecialchars($editData['author']) : 'Official'; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-clock-fill me-2"></i>Expiration Date (optional)</label>
                                    <input type="datetime-local" name="expires_at" class="form-control" 
                                           value="<?php echo $editData && $editData['expires_at'] ? date('Y-m-d\\TH:i', strtotime($editData['expires_at'])) : ''; ?>">
                                    <small class="text-muted">Leave empty for no expiration. Announcement will auto-hide after this date/time.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-chat-quote-fill me-2"></i>Message</label>
                                    <textarea name="message" class="form-control" rows="4" required><?php echo $editData ? htmlspecialchars($editData['message']) : ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-image-fill me-2"></i>Image (optional)</label>
                                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                                </div>
                                <div id="preview" class="mb-3">
                                        <?php if ($editData && $editData['image']): ?>
                                                <img src="<?php echo htmlspecialchars(announcement_image_url($editData['image'])); ?>" alt="preview" class="img-fluid rounded">
                                            <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle-fill me-2"></i><?php echo $editData ? 'Update' : 'Create'; ?></button>
                            <?php if ($editData): ?>
                                <a href="index.php?page=admin_announcements" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Announcements Grid -->
        <section class="announcements-section">
            <div class="section-header-modern">
                <div class="section-icon">
                    <i class="bi bi-collection-play"></i>
                </div>
                <div class="section-text">
                    <h2 class="section-title-modern">Announcement Inventory</h2>
                    <p class="section-subtitle-modern">Click any card to preview details, edit, or delete.</p>
                </div>
            </div>
            <div class="announcements-grid">
            <?php if (empty($announcements)): ?>
                <div class="empty-state">
                    <i class="bi bi-newspaper"></i>
                    <?php if (!empty($q) && isset($no_search_matches) && $no_search_matches): ?>
                        <p>No matches found for "<?php echo htmlspecialchars($q); ?>" â€” showing all announcements instead.</p>
                    <?php else: ?>
                        <p>No announcements yet.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php
                // Group announcements into rows of 3
                $rows = [];
                for ($i = 0; $i < count($announcements); $i += 3) {
                    $rows[] = array_slice($announcements, $i, 3);
                }
                ?>
                <?php foreach ($rows as $row_index => $row_announcements): ?>
                    <div class="row g-4 mb-4">
                        <?php foreach ($row_announcements as $a): ?>
                            <div class="col-md-4">
                                  <?php
                                  // prepare safe data attributes
                                  $data_title = htmlspecialchars($a['title'], ENT_QUOTES);
                                  $data_message = htmlspecialchars($a['message'], ENT_QUOTES);
                                  $data_image = $a['image'] ? htmlspecialchars($a['image'], ENT_QUOTES) : '';
                                  $data_author = htmlspecialchars($a['author'], ENT_QUOTES);
                                  $data_audience = htmlspecialchars($a['audience'], ENT_QUOTES);
                                  $data_type = htmlspecialchars($a['type'] ?? 'general', ENT_QUOTES);
                                  $data_created = htmlspecialchars($a['created_at'], ENT_QUOTES);
                                  ?>
                                <div class="announcement-card modern-card h-100" role="button"
                                     data-id="<?php echo $a['id']; ?>"
                                     data-title="<?php echo $data_title; ?>"
                                     data-message="<?php echo $data_message; ?>"
                                     data-image="<?php echo $data_image; ?>"
                                      data-type="<?php echo $data_type; ?>"
                                     data-author="<?php echo $data_author; ?>"
                                     data-audience="<?php echo $data_audience; ?>"
                                     data-created="<?php echo $data_created; ?>">
                                    <div class="card-image-wrapper">
                                        <?php if ($a['image']): ?>
                                        <img src="<?php echo htmlspecialchars(announcement_image_url($a['image'])); ?>" class="card-image" alt="announcement image">
                                        <?php else: ?>
                                            <div class="card-image-placeholder">
                                                <i class="bi bi-megaphone-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-badges">
                                            <span class="badge-modern badge-audience audience-<?php echo htmlspecialchars($a['audience']); ?>">
                                                <?php echo htmlspecialchars(ucfirst($a['audience'])); ?>
                                            </span>
                                            <span class="badge-modern badge-type type-<?php echo htmlspecialchars($a['type'] ?? 'general'); ?>">
                                                <i class="bi bi-tag-fill"></i>
                                                <?php echo htmlspecialchars(ucfirst($a['type'] ?? 'general')); ?>
                                            </span>
                                            <?php 
                                            $statusColor = ['draft' => 'warning', 'published' => 'success', 'archived' => 'secondary'];
                                            $statusIcon = ['draft' => 'pencil-square', 'published' => 'check-circle', 'archived' => 'archive'];
                                            $stat = $a['status'];
                                            ?>
                                            <span class="badge-modern badge-status status-<?php echo $stat; ?>">
                                                <i class="bi bi-<?php echo $statusIcon[$stat] ?? 'file'; ?>"></i>
                                                <?php echo htmlspecialchars(ucfirst($stat)); ?>
                                            </span>
                                            <?php 
                                            // Show expiration badge
                                            if ($a['expires_at']) {
                                                $expiresTime = strtotime($a['expires_at']);
                                                $now = time();
                                                $isExpired = $expiresTime < $now;
                                                $expiringIn24h = !$isExpired && ($expiresTime - $now) < 86400;
                                                
                                                if ($isExpired) {
                                                    echo '<span class="badge-modern badge-expired"><i class="bi bi-x-circle"></i> Expired</span>';
                                                } elseif ($expiringIn24h) {
                                                    echo '<span class="badge-modern badge-expiring"><i class="bi bi-exclamation-triangle"></i> Expiring Soon</span>';
                                                } else {
                                                    echo '<span class="badge-modern badge-expires"><i class="bi bi-clock-history"></i> Expires ' . date('M d', $expiresTime) . '</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <h5 class="card-title-modern"><?php echo htmlspecialchars($a['title']); ?></h5>
                                        <p class="card-excerpt"><?php echo nl2br(htmlspecialchars(mb_substr($a['message'], 0, 120))); ?><?php echo mb_strlen($a['message']) > 120 ? '...' : ''; ?></p>
                                        
                                        <div class="card-meta">
                                            <div class="meta-item">
                                                <i class="bi bi-person-circle"></i>
                                                <span><?php echo htmlspecialchars($a['author']); ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="bi bi-calendar-event"></i>
                                                <span><?php echo date('M d, Y', strtotime($a['created_at'])); ?></span>
                                            </div>
                                            <?php if ($a['expires_at']): ?>
                                                <div class="meta-item meta-expires">
                                                    <i class="bi bi-hourglass-bottom"></i>
                                                    <span><?php echo date('M d, Y H:i', strtotime($a['expires_at'])); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="#" class="btn-view" onclick="event.stopPropagation(); this.closest('.announcement-card').click(); return false;">
                                            <i class="bi bi-eye"></i>
                                            <span>View Details</span>
                                        </a>
                                        <div class="action-buttons">
                                            <a href="index.php?page=admin_announcements&edit=<?php echo $a['id']; ?>" class="btn-action btn-edit" onclick="event.stopPropagation();" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="post" style="display:inline" onsubmit="event.stopPropagation(); return confirm('Delete this announcement?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                                <button class="btn-action btn-delete" onclick="event.stopPropagation();" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div id="viewMoreWrap" class="load-more-container" style="<?php echo ($has_more || $offset > 0) ? '' : 'display:none;'; ?>">
                <?php if ($has_more): ?>
                    <button id="viewMoreBtn" class="btn-load-more" data-next-offset="<?php echo $next_offset; ?>">
                        <i class="bi bi-chevron-down me-2"></i>
                        <span>Load More Announcements</span>
                    </button>
                <?php endif; ?>
                <button id="viewLessBtn" class="btn-show-less" style="display:none;">
                    <i class="bi bi-chevron-up me-2"></i>
                    <span>Show Less</span>
                </button>
            </div>
            </div>
        </section>
    </main>

</div>

<script src="<?php echo BASE_URL; ?>assets/js/announcement/announcements.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const typeSelect = document.getElementById('announcementTypeSelect');
    const typeCustom = document.getElementById('announcementTypeCustom');
    const form = document.getElementById('announcementForm');

    if (!typeSelect) return;

    function toggleCustom(){
        const val = typeSelect.value || '';
        if (val === 'other') {
            typeCustom.style.display = 'block';
            typeCustom.focus();
        } else if (typeCustom && typeCustom.value && !Array.from(typeSelect.options).some(o => o.value === typeCustom.value)) {
            // if a custom option was previously added and selected, keep displaying the input
            typeCustom.style.display = 'block';
        } else {
            typeCustom.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', toggleCustom);
    toggleCustom();

    // Before submit: if custom field is visible and has text, ensure select contains that value and select it
    if (form) {
        form.addEventListener('submit', function(e){
            const customVal = (typeCustom && typeCustom.value || '').trim();
            if (customVal) {
                // add option if not exists
                if (!Array.from(typeSelect.options).some(o => o.value === customVal)) {
                    const opt = document.createElement('option');
                    opt.value = customVal;
                    opt.text = customVal.charAt(0).toUpperCase() + customVal.slice(1);
                    typeSelect.appendChild(opt);
                }
                typeSelect.value = customVal;
            }
        });
    }
});
</script>

<!-- Announcement view modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementModalLabel"><i class="bi bi-info-circle-fill me-2"></i>Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 id="modalTitle"></h4>
                <p class="text-muted small" id="modalMeta"></p>
                <div id="modalImageWrap" style="margin-bottom:12px; display:none;">
                    <img id="modalImage" src="" alt="" class="img-fluid rounded" style="cursor:pointer;">
                </div>
                <div id="modalMessage"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg me-2"></i>Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image full view modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0">
                <img id="imageModalImg" src="" alt="full image" class="w-100 rounded">
            </div>
        </div>
    </div>
</div>


<?php include_once __DIR__ . '/../../components/admin_components/footer-admin.php'?>