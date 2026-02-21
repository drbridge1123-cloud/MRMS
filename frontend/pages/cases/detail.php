<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Case Detail';
$currentPage = 'cases';
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
            <?php include __DIR__ . '/_detail-providers.php'; ?>
            <?php include __DIR__ . '/_detail-costs.php'; ?>
            <?php include __DIR__ . '/_detail-activity.php'; ?>
            <?php include __DIR__ . '/_detail-documents.php'; ?>
            <?php include __DIR__ . '/_detail-mbds.php'; ?>
        </div>
    </template>

    <?php include __DIR__ . '/_detail-modals.php'; ?>
</div>

<style>
    /* Expanded provider highlight */
    .provider-expanded-row {
        border-left: 4px solid #C8A951;
        background-color: #FDFBF5;
    }

    .provider-expanded-row>td {
        border-left: none;
    }

    .history-panel {
        background: linear-gradient(135deg, #FBF9F1 0%, #F7F4EA 100%);
        border-left: 4px solid #C8A951;
        border-top: 1px solid #E8E0C8;
    }

    /* Scroll highlight flash */
    @keyframes historyFlash {
        0% {
            box-shadow: inset 0 0 0 2px #C8A951;
        }

        50% {
            box-shadow: inset 0 0 0 2px #C8A951, 0 0 12px rgba(200, 169, 81, 0.3);
        }

        100% {
            box-shadow: none;
        }
    }

    .history-flash {
        animation: historyFlash 1.5s ease-out;
    }
</style>

<script src="/MRMS/frontend/assets/js/pages/mbds-panel.js"></script>
<script src="/MRMS/frontend/components/template-selector.js"></script>
<script src="/MRMS/frontend/components/document-uploader.js"></script>
<script src="/MRMS/frontend/components/document-selector.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
