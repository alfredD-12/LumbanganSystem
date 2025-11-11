<?php
// Shared helper for Dashboard header/footer assets.
// Ensures Poppins, dashboard.css, and dashboard.js are attached exactly once.
// Works even if the header/footer components are included after <head> (injects into <head>).

// Build a robust web path to /app/assets no matter where this file is included from
if (!function_exists('dash_asset_base')) {
  function dash_asset_base(): string {
    // e.g., /Github/LumbanganSystem/bmis-lumbangan-system/app/views/Survey
    $scriptDir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // keep everything before /app/
    $projectBase = rtrim(preg_replace('#/app/.*$#', '', $scriptDir), '/');
    return $projectBase . '/app/assets';
  }
}

// Append a <link> or <script> into <head> once (idempotent) even if called from body
if (!function_exists('dash_append_to_head_once')) {
  function dash_append_to_head_once(string $htmlId, string $tagHtml): void {
    static $emitted = [];
    if (isset($emitted[$htmlId])) return;
    $emitted[$htmlId] = true;

    // If not present, create and append to <head> at runtime
    echo '<script>(function(){var d=document;if(!d.getElementById(' . json_encode($htmlId) . ')){var t=d.createElement("template");t.innerHTML=' . json_encode($tagHtml) . ';d.head.appendChild(t.content.firstChild);}})();</script>' . PHP_EOL;
  }
}

// Public entry point used by headerdashboard.php and footerdashboard.php
if (!function_exists('ensure_dashboard_assets')) {
  function ensure_dashboard_assets(): void {
    $assetBase = dash_asset_base();

    // 1) Google Fonts (Poppins)
    if (!defined('DASHBOARD_FONTS_EMITTED')) {
      define('DASHBOARD_FONTS_EMITTED', true);
      dash_append_to_head_once(
        'dash-fonts',
        '<link id="dash-fonts" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">'
      );
    }

    // 2) Dashboard CSS (animations, navbar/footer styles)
    if (!defined('DASHBOARD_CSS_EMITTED')) {
      define('DASHBOARD_CSS_EMITTED', true);
      dash_append_to_head_once(
        'dash-css',
        '<link id="dash-css" rel="stylesheet" href="' . htmlspecialchars($assetBase . '/css/Dashboard/dashboard.css') . '">'
      );
    }

    // 3) Dashboard JS (navbar interactions, etc.)
    if (!defined('DASHBOARD_JS_EMITTED')) {
      define('DASHBOARD_JS_EMITTED', true);
      echo '<script defer src="' . htmlspecialchars($assetBase . '/js/Dashboard/dashboard.js') . '"></script>' . PHP_EOL;
    }
  }
}