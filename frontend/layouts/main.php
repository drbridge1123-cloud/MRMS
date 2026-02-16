<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'MRMS' ?> - Medical Records Management System</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230F1B2D' width='100' height='100' rx='20'/><text x='50' y='65' font-size='48' font-weight='bold' text-anchor='middle' fill='%23C9A84C' font-family='sans-serif'>M</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        // Suppress Tailwind CDN production warning
        const _warn = console.warn;
        console.warn = (...args) => {
            const msg = args.join(' ');
            if (msg.includes('cdn.tailwindcss.com') || msg.includes('Tailwind')) return;
            _warn.apply(console, args);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        franklin: ['Libre Franklin', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            DEFAULT: '#0F1B2D',
                            light: '#1A2A40',
                            border: '#243347',
                        },
                        gold: {
                            DEFAULT: '#C9A84C',
                            hover: '#B8973F',
                        },
                        'v2-bg': '#F5F5F0',
                        'v2-card': '#FFFFFF',
                        'v2-card-border': '#E5E5E0',
                        'v2-card-bg': '#F5F5F0',
                        'v2-text': '#0F1B2D',
                        'v2-text-mid': '#3D4F63',
                        'v2-text-light': '#5A6B82',
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="/MRMS/frontend/assets/css/app.css">
    <script src="/MRMS/frontend/assets/js/app.js"></script>
    <script src="/MRMS/frontend/assets/js/utils.js"></script>
</head>
<body class="bg-v2-bg font-franklin min-h-screen" x-data x-init="$store.auth.init(); $store.notifications.load();">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Main content wrapper -->
    <div class="main-content" :class="{ 'expanded': $store.sidebar.collapsed }">
        <!-- Top Header -->
        <?php include __DIR__ . '/../components/header.php'; ?>

        <!-- Page Content -->
        <main class="p-6">
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <script src="/MRMS/frontend/assets/js/alpine-stores.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
