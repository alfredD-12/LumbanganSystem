<div class="col-md-4">
    <?php
    $data_title = htmlspecialchars($a['title'], ENT_QUOTES);
    $data_message = htmlspecialchars($a['message'], ENT_QUOTES);
    $data_image = $a['image'] ? htmlspecialchars($a['image'], ENT_QUOTES) : '';
    $data_author = htmlspecialchars($a['author'], ENT_QUOTES);
    $data_audience = htmlspecialchars($a['audience'], ENT_QUOTES);
    $data_created = htmlspecialchars($a['created_at'], ENT_QUOTES);
    ?>
    <div class="announcement-card modern-card h-100" role="button"
         data-id="<?php echo $a['id']; ?>"
         data-title="<?php echo $data_title; ?>"
         data-message="<?php echo $data_message; ?>"
         data-image="<?php echo $data_image; ?>"
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
                <?php 
                $statusIcon = ['draft' => 'pencil-square', 'published' => 'check-circle', 'archived' => 'archive'];
                $stat = $a['status'];
                ?>
                <span class="badge-modern badge-status status-<?php echo htmlspecialchars($stat); ?>">
                    <i class="bi bi-<?php echo $statusIcon[$stat] ?? 'file'; ?>"></i>
                    <?php echo htmlspecialchars(ucfirst($stat)); ?>
                </span>
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
