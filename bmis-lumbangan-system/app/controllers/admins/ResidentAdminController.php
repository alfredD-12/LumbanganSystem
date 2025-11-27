<?php
require_once __DIR__ . '/../../models/Resident.php';
require_once __DIR__ . '/../../models/Official.php';
require_once __DIR__ . '/../../config/Database.php';

class ResidentAdminController {
    public function index() {
        // ensure admin header will highlight the correct menu
        $currentPage = 'admin_residents';

        $model = new Resident();
        $residents = $model->getAll();
        $puroks = $model->getPuroks();

        // preload assessments per person to allow modal listing without extra ajax
        $assessmentsByPerson = [];
        // preload officials map to show names instead of ids
        $db = (new Database())->getConnection();
        $officialModel = new Official($db);
        $officials = $officialModel->getAll(true);
        $officialMap = [];
        foreach ($officials as $o) {
            $officialMap[intval($o['id'])] = $o['full_name'] ?? ($o['username'] ?? 'Official');
        }

        foreach ($residents as $r) {
            $id = intval($r['id'] ?? 0);
            if ($id > 0) {
                $assessments = $model->getAssessmentsByPerson($id);
                // map official ids to names for front-end convenience
                foreach ($assessments as &$a) {
                    $sid = intval($a['surveyed_by_official_id'] ?? 0);
                    $aid = intval($a['approved_by_official_id'] ?? 0);
                    $a['surveyed_by_official_name'] = $officialMap[$sid] ?? ($sid ? 'Official #' . $sid : null);
                    $a['approved_by_official_name'] = $officialMap[$aid] ?? ($aid ? 'Official #' . $aid : null);
                }
                unset($a);
                $assessmentsByPerson[$id] = $assessments;
            }
        }

        // optional debug when ?debug_residents=1 is present
        $debugData = null;
        if (!empty($_GET['debug_residents'])) {
            $debugData = [
                'count' => count($residents),
                'sample' => $residents[0] ?? null
            ];
        }

        // make $currentPage, $residents, $puroks, and optional $debugData available to the view
        include __DIR__ . '/../../views/admins/residents.php';
    }
}
