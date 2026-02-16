<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRMS Error Check</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230F1B2D' width='100' height='100' rx='20'/><text x='50' y='65' font-size='48' font-weight='bold' text-anchor='middle' fill='%23C9A84C' font-family='sans-serif'>M</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">MRMS Console Error Checker</h1>

        <div id="errorContainer" class="space-y-4">
            <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded">
                Checking for console errors...
            </div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Script Loading Test</h2>
            <div id="scriptStatus" class="space-y-2"></div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">API Test</h2>
            <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Test Login API
            </button>
            <div id="apiResult" class="mt-4"></div>
        </div>
    </div>

    <script src="/MRMS/frontend/assets/js/app.js"></script>
    <script src="/MRMS/frontend/assets/js/utils.js"></script>
    <script src="/MRMS/frontend/assets/js/alpine-stores.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Capture console errors
        const errors = [];
        const originalError = console.error;
        console.error = function(...args) {
            errors.push(args.join(' '));
            originalError.apply(console, args);
        };

        // Capture console warnings
        const warnings = [];
        const originalWarn = console.warn;
        console.warn = function(...args) {
            warnings.push(args.join(' '));
            originalWarn.apply(console, args);
        };

        // Check script loading
        setTimeout(() => {
            const container = document.getElementById('errorContainer');
            const scriptStatus = document.getElementById('scriptStatus');

            // Display errors and warnings
            container.innerHTML = '';

            if (errors.length === 0 && warnings.length === 0) {
                container.innerHTML = '<div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">✓ No console errors or warnings detected!</div>';
            } else {
                if (errors.length > 0) {
                    container.innerHTML += '<div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded"><strong>Errors (' + errors.length + '):</strong><pre class="mt-2 text-xs overflow-auto">' + errors.join('\n') + '</pre></div>';
                }
                if (warnings.length > 0) {
                    container.innerHTML += '<div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mt-4"><strong>Warnings (' + warnings.length + '):</strong><pre class="mt-2 text-xs overflow-auto">' + warnings.join('\n') + '</pre></div>';
                }
            }

            // Check if scripts loaded
            scriptStatus.innerHTML = `
                <div><strong>API Functions:</strong> ${typeof api !== 'undefined' ? '✓ Loaded' : '✗ Not loaded'}</div>
                <div><strong>Alpine.js:</strong> ${typeof Alpine !== 'undefined' ? '✓ Loaded' : '✗ Not loaded'}</div>
                <div><strong>showToast:</strong> ${typeof showToast !== 'undefined' ? '✓ Loaded' : '✗ Not loaded'}</div>
                <div><strong>formatDate:</strong> ${typeof formatDate !== 'undefined' ? '✓ Loaded' : '✗ Not loaded'}</div>
            `;

            // Test API call
            document.getElementById('testBtn').addEventListener('click', async () => {
                const resultDiv = document.getElementById('apiResult');
                resultDiv.innerHTML = '<div class="text-blue-600">Testing...</div>';

                try {
                    const response = await fetch('/MRMS/backend/api/auth/me', {
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });
                    const data = await response.json();

                    if (response.ok) {
                        resultDiv.innerHTML = '<div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">✓ API working! Response: ' + JSON.stringify(data, null, 2) + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded">Status ' + response.status + ': ' + JSON.stringify(data, null, 2) + '</div>';
                    }
                } catch (error) {
                    resultDiv.innerHTML = '<div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">✗ Error: ' + error.message + '</div>';
                }
            });
        }, 1000);

        // Listen for resource errors
        window.addEventListener('error', function(e) {
            if (e.target !== window) {
                errors.push('Resource error: ' + e.target.src || e.target.href);
            }
        }, true);
    </script>
</body>
</html>
