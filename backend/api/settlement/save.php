<?php
// PUT /api/settlement/{case_id} - Save settlement settings
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    errorResponse('Invalid request body');
}

$fields = [];

if (isset($data['settlement_amount'])) $fields['settlement_amount'] = (float)$data['settlement_amount'];
if (isset($data['attorney_fee_percent'])) $fields['attorney_fee_percent'] = (float)$data['attorney_fee_percent'];
if (isset($data['coverage_3rd_party'])) $fields['coverage_3rd_party'] = $data['coverage_3rd_party'] ? 1 : 0;
if (isset($data['coverage_um'])) $fields['coverage_um'] = $data['coverage_um'] ? 1 : 0;
if (isset($data['coverage_uim'])) $fields['coverage_uim'] = $data['coverage_uim'] ? 1 : 0;
if (isset($data['policy_limit'])) $fields['policy_limit'] = $data['policy_limit'] ? 1 : 0;
if (isset($data['um_uim_limit'])) $fields['um_uim_limit'] = $data['um_uim_limit'] ? 1 : 0;
if (isset($data['pip_subrogation_amount'])) $fields['pip_subrogation_amount'] = (float)$data['pip_subrogation_amount'];
if (isset($data['pip_insurance_company'])) $fields['pip_insurance_company'] = $data['pip_insurance_company'];
if (isset($data['settlement_method'])) $fields['settlement_method'] = $data['settlement_method'];

if (empty($fields)) {
    errorResponse('No fields to update');
}

dbUpdate('cases', $fields, 'id = ?', [$caseId]);

logActivity($userId, 'settlement_update', 'case', $caseId, $fields);

jsonResponse([
    'success' => true,
    'message' => 'Settlement settings saved',
]);
