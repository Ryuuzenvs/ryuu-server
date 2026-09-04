<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monaco Code Editor - {{ basename($filePath) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Monaco Editor Loader -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.39.0/min/vs/loader.min.js"></script>
</head>
<body class="bg-gray-950 text-gray-200 h-screen flex flex-col overflow-hidden">

    <!-- NAVBAR EDITOR -->
    <header class="bg-gray-900 border-b border-gray-800 px-4 py-2.5 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 px-3 py-1.5 rounded transition border border-gray-700">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <div class="h-4 w-px bg-gray-700"></div>
            <span class="text-xs font-mono font-bold text-blue-400">
                <i class="bi bi-file-earmark-code me-1"></i> /var/www/html/{{ $filePath }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()->role === 'owner')
                <button id="btn-save" onclick="saveContent()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-4 py-1.5 rounded transition flex items-center gap-1 shadow">
                    <i class="bi bi-cloud-arrow-up-fill"></i> SAVE (Ctrl+S)
                </button>
            @else
                <span class="text-xs text-yellow-500 bg-yellow-500/10 px-3 py-1 rounded border border-yellow-500/20">
                    READ-ONLY MODE
                </span>
            @endif
        </div>
    </header>

    <!-- CONTAINER MONACO EDITOR -->
    <main class="flex-1 w-full h-full relative">
        <div id="monaco-editor-container" class="w-full h-full"></div>
    </main>

    <script>
        let editor;
        const fileExt = "{{ $extension }}";
        const isOwner = "{{ auth()->user()->role }}" === "owner";

        // Helper untuk Decode Base64 UTF-8 dengan aman
        function decodeBase64Utf8(str) {
            try {
                return decodeURIComponent(escape(window.atob(str)));
            } catch (e) {
                return window.atob(str);
            }
        }

        // Ambil data Base64 dari backend PHP
        const base64Code = "{{ $content }}";
        const decodedContent = decodeBase64Utf8(base64Code);

        // Mapping ekstensi file ke mode Monaco Language
        const langMap = {
            'js': 'javascript', 'ts': 'typescript', 'php': 'php',
            'py': 'python', 'html': 'html', 'css': 'css',
            'json': 'json', 'sh': 'shell', 'md': 'markdown',
            'env': 'ini', 'sql': 'sql', 'xml': 'xml', 'go': 'go'
        };

        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.39.0/min/vs' }});

        require(['vs/editor/editor.main'], function() {
            editor = monaco.editor.create(document.getElementById('monaco-editor-container'), {
                value: decodedContent,
                language: langMap[fileExt] || 'plaintext',
                theme: 'vs-dark',
                readOnly: !isOwner,
                automaticLayout: true,
                fontSize: 14,
                minimap: { enabled: true },
                scrollBeyondLastLine: false,
            });

            // Add Command Shortcut CTRL + S / CMD + S
            editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
                if (isOwner) saveContent();
            });
        });

        // Function Save AJAX
        function saveContent() {
            const btn = document.getElementById('btn-save');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Saving...';
            btn.disabled = true;

            fetch("{{ route('files.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    filepath: "{{ $filePath }}",
                    content: editor.getValue()
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> SAVED!';
                btn.classList.replace('bg-blue-600', 'bg-green-600');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.replace('bg-green-600', 'bg-blue-600');
                    btn.disabled = false;
                }, 1500);
            })
            .catch(err => {
                alert('Gagal menyimpan file: ' + err);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
