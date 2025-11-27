<?php
// Admin Residents View
if (!defined('BASE_URL')) require_once dirname(__DIR__, 2) . '/config/config.php';
$pageTitle = 'Residents';
$pageSubtitle = 'List of residents (searchable)';
require_once dirname(__DIR__, 2) . '/components/admin_components/header-admin.php';

// include residents specific stylesheet
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/admins/residents.css">' . "\n";

// The controller provides `$residents` and optional `$currentPage`.
// If this view is opened directly for debugging, ensure variables exist.
// original full list provided by controller
$residents = $residents ?? [];
$assessmentsByPerson = $assessmentsByPerson ?? [];

// support server-side filtering via GET params `q` and `purok`
$allResidents = $residents;
$q = trim((string)($_GET['q'] ?? ''));
$purokFilter = trim((string)($_GET['purok'] ?? ''));
if ($q !== '' || $purokFilter !== '') {
    $qLower = mb_strtolower($q);
    $pLower = mb_strtolower($purokFilter);
    $residents = array_values(array_filter($allResidents, function($r) use ($qLower, $pLower) {
        $name = mb_strtolower(trim( ($r['first_name'] ?? '') . ' ' . ($r['middle_name'] ?? '') . ' ' . ($r['last_name'] ?? '') . ' ' . ($r['suffix'] ?? '') ));
        $purok = mb_strtolower($r['purok_name'] ?? '');
        $matchQ = $qLower === '' || mb_strpos($name, $qLower) !== false || mb_strpos($purok, $qLower) !== false;
        $matchP = $pLower === '' || mb_strpos($purok, $pLower) !== false || mb_strpos($name, $pLower) !== false;
        return $matchQ && $matchP;
    }));
}

// Server-side pagination: show 30 residents per page
// Use a distinct query param `resident_page` so we don't conflict with the app's
// `page` routing parameter (which selects the view). Using `page` caused 404s.
$perPage = 30;
$totalResidents = count($residents);
$pageParam = 'resident_page';
$currentPage = isset($_GET[$pageParam]) ? intval($_GET[$pageParam]) : (isset($currentPage) ? $currentPage : 1);
// Ensure $currentPage is an integer and at least 1 to avoid arithmetic errors
$currentPage = max(1, intval($currentPage));
$totalPages = max(1, (int)ceil($totalResidents / $perPage));
$startIndex = ($currentPage - 1) * $perPage;
$pageResidents = array_slice($residents, $startIndex, $perPage);
?>

<main class="main-content" id="residents-admin-scope">
    <div class="content-section" style="padding: 2rem;">
        <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px;">
            <div class="page-header-left" style="display:flex;gap:12px;align-items:center;">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="background:#eaf6ff;border-radius:10px;padding:8px;box-shadow:0 2px 8px rgba(30,58,95,0.04)">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" fill="#1e3a5f" opacity="0.95"/>
                    <path d="M4 20c0-2.21 3.58-4 8-4s8 1.79 8 4v1H4v-1z" fill="#93a4b4" opacity="0.35"/>
                </svg>
                <div>
                    <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
                    <div class="page-subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></div>
                </div>
            </div>
            <div></div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <?php
                    // preserve current route page param so front controller still routes correctly
                    $routePage = $_GET['page'] ?? 'admin_residents';
                    $actionPath = htmlspecialchars(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                ?>
                <form id="residentFilterForm" method="get" action="<?php echo $actionPath; ?>">
                    <input type="hidden" name="page" value="<?php echo htmlspecialchars($routePage); ?>">
                    <input type="hidden" name="resident_page" value="1">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <input id="residentNameSearch" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search residents by name...">
                        </div>
                        <div class="col-md-3">
                            <select id="residentPurokFilter" name="purok" class="form-select">
                                <option value="">All Puroks</option>
                                <?php foreach (($puroks ?? []) as $pr): ?>
                                    <option value="<?php echo htmlspecialchars($pr['name']); ?>" <?php echo ($purokFilter === ($pr['name'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($pr['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 text-end text-muted">
                            <small>Showing <span id="residentsCountPage"><?php echo count($pageResidents); ?></span> of <span id="residentsTotal"><?php echo $totalResidents; ?></span> residents</small>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="accordion resident-accordion" id="residentsAccordion">
            <?php if (!empty($residents)): ?>
                <?php foreach ($pageResidents as $r):
                    $id = 'resident_' . intval($r['id']);
                    $full = trim(($r['first_name'] ?? '') . ' ' . ($r['middle_name'] ?? '') . ' ' . ($r['last_name'] ?? '') . ' ' . ($r['suffix'] ?? ''));
                    $initials = strtoupper(substr(trim($r['first_name'] ?? ''),0,1) . substr(trim($r['last_name'] ?? ''),0,1));
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?php echo $id; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $id; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $id; ?>">
                            <div style="display:flex;align-items:center;gap:12px;width:100%;">
                                <div style="width:44px;height:44px;border-radius:8px;background:#eaf6ff;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1e3a5f;font-size:1.05rem;flex:0 0 44px;">
                                    <?php echo htmlspecialchars($initials ?: 'NN'); ?>
                                </div>
                                <div style="flex:1;text-align:left;">
                                    <div style="font-weight:600;line-height:1;"><?php echo htmlspecialchars($full ?: 'Unnamed'); ?></div>
                                    <div style="font-size:.86rem;color:#6b7280;margin-top:2px;"><?php echo htmlspecialchars($r['purok_name'] ?? 'Purok unknown'); ?></div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-<?php echo $id; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $id; ?>" data-bs-parent="#residentsAccordion">
                        <div class="accordion-body">
                            <div class="resident-details">

                                <div class="detail-address"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#1e3a5f" d="M12 2C8.13 2 5 5.13 5 9c0 5 7 13 7 13s7-8 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg></span>Address</div><div class="detail-value"><?php echo htmlspecialchars($r['address'] ?? ($r['household_no'] ? $r['household_no'] : 'N/A')); ?></div></div>

                                <div class="detail-purok"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.14 2 5 5.14 5 9c0 5 7 13 7 13s7-8 7-13c0-3.86-3.14-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg></span>Purok</div><div class="detail-value"><?php echo htmlspecialchars($r['purok_name'] ?? 'N/A'); ?></div></div>

                                <div class="detail-sex"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5z"/></svg></span>Sex</div><div class="detail-value"><?php echo htmlspecialchars($r['sex'] ?? 'N/A'); ?></div></div>

                                <div class="detail-birthdate"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M7 10h5v5H7z" opacity=".0001"/><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 14H5V9h14v9z"/></svg></span>Birthdate</div><div class="detail-value"><?php echo (!empty($r['birthdate']) ? date('M d, Y', strtotime($r['birthdate'])) : 'N/A'); ?></div></div>

                                <div class="detail-marital"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-6-4.35-9-7.1C0 10.6 3 7 6.5 7c2.04 0 3.5 1.4 5.5 3.9C13 8.4 14.46 7 16.5 7 20 7 23 10.6 21 13.9 18 16.65 12 21 12 21z"/></svg></span>Marital status</div><div class="detail-value"><?php echo htmlspecialchars($r['marital_status'] ?? 'N/A'); ?></div></div>

                                <div class="detail-email"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M2 6v12h20V6H2zm2 2l8 5 8-5v8H4V8z"/></svg></span>Email</div><div class="detail-value"><?php echo htmlspecialchars($r['email'] ?? 'N/A'); ?></div></div>

                                <div class="detail-mobile"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M17 1H7a2 2 0 0 0-2 2v18a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm-5 22a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5z"/></svg></span>Mobile</div><div class="detail-value"><?php echo htmlspecialchars($r['contact_no'] ?? 'N/A'); ?></div></div>


                                <div class="detail-household"><div class="detail-label"><span class="detail-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="#1e3a5f" xmlns="http://www.w3.org/2000/svg"><path d="M12 3l10 9h-3v8h-6v-6H11v6H5v-8H2z"/></svg></span>Household</div><div class="detail-value"><?php echo htmlspecialchars($r['household_no'] ?? 'N/A'); ?></div></div>

                                <div class="detail-action"><button type="button" class="btn btn-sm btn-show-assessments" data-id="<?php echo intval($r['id']); ?>" data-name="<?php echo htmlspecialchars($full); ?>">View CVD/NCD Assessments</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">No residents found.</div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Residents pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $totalPages; $p++):
                        // preserve current request path and query params, but set `resident_page`
                        $qs = $_GET;
                        $qs['resident_page'] = $p;
                        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                        $link = htmlspecialchars($path . '?' . http_build_query($qs));
                    ?>
                        <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $link; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <?php if (!empty($debugData)): ?>
            <pre style="margin-top:12px;background:#f8f9fa;padding:12px;border-radius:6px;">Debug: <?php echo htmlspecialchars(json_encode($debugData, JSON_PRETTY_PRINT)); ?></pre>
        <?php endif; ?>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const nameInput = document.getElementById('residentNameSearch');
    const purokSelect = document.getElementById('residentPurokFilter');
    const filterForm = document.getElementById('residentFilterForm');
    const items = Array.from(document.querySelectorAll('#residentsAccordion .accordion-item'));
    const countElPage = document.getElementById('residentsCountPage');
    const totalEl = document.getElementById('residentsTotal');

    // When server-side filtering is used we submit the form so results come from the server
    // Debounce form submit on typing to avoid too many requests
    function debounce(fn, wait){ let t; return function(){ clearTimeout(t); t = setTimeout(fn, wait); }; }

    function submitFilters(){
        if (!filterForm) return;
        // reset to first page when filtering
        const rp = filterForm.querySelector('input[name="resident_page"]');
        if (rp) rp.value = '1';
        filterForm.submit();
    }

    const debouncedSubmit = debounce(submitFilters, 450);

    if (nameInput) nameInput.addEventListener('input', function(){ debouncedSubmit(); });
    if (purokSelect) purokSelect.addEventListener('change', function(){ submitFilters(); });

    // Keep page-visible count accurate (server filtered + client-side show/hide for accordion state)
    (function updateVisibleCount(){
        let visible = items.filter(i => i.style.display !== 'none').length;
        if (countElPage) countElPage.textContent = visible;
        if (totalEl) totalEl.textContent = <?php echo $totalResidents; ?>;
    })();
});
</script>

<!-- Assessments modal -->
<div class="modal fade" id="assessmentsModal" tabindex="-1" aria-labelledby="assessmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assessmentsModalLabel">Assessments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="assessmentsModalBody">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Full Assessment modal (shows all survey sections paginated) -->
<div class="modal fade" id="fullAssessmentModal" tabindex="-1" aria-labelledby="fullAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullAssessmentModalLabel">Full Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fullAssessmentModalBody">
                <!-- content generated dynamically by JS -->
                <div class="text-center text-muted">Loading assessment...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
// preload assessments json (exposed to JS file)
$assessmentsJson = json_encode($assessmentsByPerson ?? []);
echo '<script>window.assessmentsByPerson = ' . $assessmentsJson . ';</script>' . "\n";

// include residents specific script
echo '<script src="' . BASE_URL . 'assets/js/admins/residents.js"></script>' . "\n";

?>

<?php require_once dirname(__DIR__, 2) . '/components/admin_components/footer-admin.php'; ?>
