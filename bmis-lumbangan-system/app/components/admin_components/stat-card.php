<?php
/**
 * Reusable Stat Card Component
 * Usage: renderStatCard($data)
 * $data = ['icon', 'icon_color', 'number', 'label', 'change', 'change_type']
 */

function renderStatCard($data) {
    $icon = $data['icon'] ?? 'fas fa-question';
    $icon_color = $data['icon_color'] ?? 'blue';
    $number = $data['number'] ?? '0';
    $label = $data['label'] ?? 'N/A';
    $change = $data['change'] ?? '';
    $change_type = $data['change_type'] ?? 'up';
    ?>
    <div class="stat-card">
        <div class="stat-icon <?php echo htmlspecialchars($icon_color); ?>">
            <i class="<?php echo htmlspecialchars($icon); ?>"></i>
        </div>
        <div class="stat-number"><?php echo htmlspecialchars($number); ?></div>
        <div class="stat-label"><?php echo htmlspecialchars($label); ?></div>
        <div class="stat-change <?php echo htmlspecialchars($change_type); ?>">
            <i class="fas fa-arrow-<?php echo $change_type === 'up' ? 'up' : 'down'; ?>"></i> 
            <?php echo htmlspecialchars($change); ?>
        </div>
    </div>
    <?php
}
