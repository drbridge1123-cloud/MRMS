<?php
// GET /api/cases/related?case_id=XX
// Returns cases with the same case_number but different id

$userId = requireAuth();

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$currentCase = dbFetchOne(
    "SELECT id, case_number, client_name, client_dob FROM cases WHERE id = ?",
    [$caseId]
);
if (!$currentCase) {
    errorResponse('Case not found', 404);
}

$relatedCases = dbFetchAll(
    "SELECT id, case_number, client_name, client_dob, status
     FROM cases
     WHERE case_number = ? AND id != ?
     ORDER BY client_name",
    [$currentCase['case_number'], $caseId]
);

successResponse([
    'current_case' => $currentCase,
    'related_cases' => $relatedCases,
    'total_cases' => count($relatedCases) + 1
]);
