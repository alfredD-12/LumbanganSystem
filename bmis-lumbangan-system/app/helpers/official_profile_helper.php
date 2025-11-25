<?php
/**
 * Helper to expose the logged-in official's profile and render client-side bootstrap hooks
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Official.php';

function get_official_profile() {
    if (!isset($_SESSION['official_id'])) return null;

    $db = (new Database())->getConnection();
    $officialModel = new Official($db);
    $official = $officialModel->getById($_SESSION['official_id']);

    return $official ?: null;
}

function render_official_profile_script() {
    $profile = get_official_profile();
    $data = [
        'id' => $profile['id'] ?? null,
        'full_name' => $profile['full_name'] ?? 'Admin Secretary',
        'role' => $profile['role'] ?? 'Barangay Administrator',
        'email' => $profile['email'] ?? '',
        'contact_no' => $profile['contact_no'] ?? ''
    ];

    // Ensure BASE_PUBLIC is available
    if (!defined('BASE_PUBLIC')) {
        @require_once __DIR__ . '/../config/config.php';
    }
    $endpoint = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?action=';

    // Output a small script that populates the modal and handles save
    echo "<script>\n";
    echo "window.officialProfile = " . json_encode($data) . ";\n";

    // populate UI on DOMContentLoaded
    echo "document.addEventListener('DOMContentLoaded', function(){\n";
    echo "  try {\n";
    echo "    // Fill topbar/header placeholders if present\n";
    echo "    var nameEls = document.querySelectorAll('#adminDisplayName, .admin-info .name, #adminProfileName');\n";
    echo "    nameEls.forEach(function(el){ if(el) el.textContent = window.officialProfile.full_name; });\n";
    echo "    var roleEls = document.querySelectorAll('#adminDisplayRole, .admin-info .role, #adminProfileRole');\n";
    echo "    roleEls.forEach(function(el){ if(el) el.textContent = window.officialProfile.role; });\n";
    echo "    // Populate modal fields if exists\n";
    echo "    var pName = document.getElementById('adminProfileName'); if(pName) pName.textContent = window.officialProfile.full_name;\n";
    echo "    var pRole = document.getElementById('adminProfileRole'); if(pRole) pRole.textContent = window.officialProfile.role;\n";
    echo "    var pEmail = document.getElementById('adminProfileEmail'); if(pEmail) pEmail.textContent = window.officialProfile.email;\n";
    echo "    var pContact = document.getElementById('adminProfileContact'); if(pContact) pContact.textContent = window.officialProfile.contact_no;\n";

    // Setup edit modal fields
    echo "    var editName = document.getElementById('editAdminName'); if(editName) editName.value = window.officialProfile.full_name;\n";
    echo "    var editEmail = document.getElementById('editAdminEmail'); if(editEmail) editEmail.value = window.officialProfile.email;\n";
    echo "    var editContact = document.getElementById('editAdminContact'); if(editContact) editContact.value = window.officialProfile.contact_no;\n";

    // Save handler
    echo "    window.saveAdminProfileChanges = async function(){\n";
    echo "      var btn = document.querySelector('#editAdminProfileModal .btn-sm[onclick]');\n";
    echo "      if(btn) btn.disabled = true;\n";
    echo "      var form = document.getElementById('editAdminProfileForm');\n";
    echo "      var fd = new FormData(form);\n";
    echo "      fd.append('official_id', window.officialProfile.id);\n";
    echo "      try {\n";
    echo "        var res = await fetch('" . $endpoint . "update_official_profile', { method: 'POST', body: fd, credentials: 'same-origin' });\n";
    echo "        var j = await res.json();\n";
    echo "        if (j.success) {\n";
    echo "           // update UI\n";
    echo "           window.officialProfile.full_name = fd.get('full_name');\n";
    echo "           window.officialProfile.email = fd.get('email');\n";
    echo "           window.officialProfile.contact_no = fd.get('contact_no');\n";
    echo "           var nameEls2 = document.querySelectorAll('#adminDisplayName, .admin-info .name, #adminProfileName'); nameEls2.forEach(function(el){ if(el) el.textContent = window.officialProfile.full_name; });\n";
    echo "           var emailEl = document.getElementById('adminProfileEmail'); if(emailEl) emailEl.textContent = window.officialProfile.email;\n";
    echo "           var contactEl = document.getElementById('adminProfileContact'); if(contactEl) contactEl.textContent = window.officialProfile.contact_no;\n";
    echo "           // Close edit modal and show profile modal\n";
    echo "           var editModal = bootstrap.Modal.getInstance(document.getElementById('editAdminProfileModal')); if(editModal) editModal.hide();\n";
    echo "           var profileModal = new bootstrap.Modal(document.getElementById('adminProfileModal')); profileModal.show();\n";
    echo "        } else { alert(j.message || 'Unable to save profile'); }\n";
    echo "      } catch(e){ console.error(e); alert('Error saving profile'); } finally { if(btn) btn.disabled = false; }\n";
    echo "    };\n";

    echo "  } catch(e){ console.error('profile script error', e); }\n";
    echo "});\n";
    echo "</script>\n";
}
