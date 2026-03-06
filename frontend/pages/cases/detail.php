<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Case Detail';
$currentPage = 'cases';
$pageHeadScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
];
$pageScripts = ['/MRMS/frontend/assets/js/pages/case-detail.js'];
ob_start();
?>

<div x-data="caseDetailPage()" x-init="init()">

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex items-center justify-center py-20">
            <div class="spinner"></div>
        </div>
    </template>

    <template x-if="!loading && caseData">
        <div>
            <?php include __DIR__ . '/_detail-header.php'; ?>

            <!-- C1 Main Card — all accordion sections inside one card -->
            <div class="c1-card">
                <?php include __DIR__ . '/_detail-providers.php'; ?>
                <?php include __DIR__ . '/_detail-activity.php'; ?>
                <?php include __DIR__ . '/_detail-documents.php'; ?>

                <!-- Workflow Section Divider -->
                <div class="c1-workflow-divider">
                    <span class="c1-workflow-label">WORKFLOW</span>
                </div>

                <?php include __DIR__ . '/_detail-costs.php'; ?>
                <?php include __DIR__ . '/_detail-mbds.php'; ?>
                <?php include __DIR__ . '/_detail-health-ledger.php'; ?>
                <?php include __DIR__ . '/_detail-negotiate.php'; ?>
                <?php include __DIR__ . '/_detail-disbursement.php'; ?>
            </div>
        </div>
    </template>

    <?php include __DIR__ . '/_detail-modals.php'; ?>
</div>

<?php
$detailScripts = [
    '/MRMS/frontend/assets/js/pages/mbds-panel.js',
    '/MRMS/frontend/assets/js/pages/negotiate-panel.js',
    '/MRMS/frontend/assets/js/pages/disbursement-panel.js',
    '/MRMS/frontend/assets/js/pages/health-ledger-panel.js',
    '/MRMS/frontend/components/template-selector.js',
    '/MRMS/frontend/components/document-uploader.js',
    '/MRMS/frontend/components/document-selector.js',
];
foreach ($detailScripts as $s):
?>
<script src="<?= $s ?>?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . $s) ?>"></script>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
