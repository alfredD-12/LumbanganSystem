<?php
// app/models/AdminSurveyModel.php
// Backwards-compatible alias that extends the original SurveyModel.
require_once __DIR__ . '/SurveyModel.php';

class AdminSurveyModel extends SurveyModel
{
    // Intentionally empty - inherit everything from SurveyModel so all DB helpers remain available.
}
