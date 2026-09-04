<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('System Monitor (Go Engine - Port 8081)') }}
        </h2>
    </x-slot>
<!-- Import CDN Xterm.js CSS & JS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
<script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- CPU Load -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-700">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">CPU Load</h3>
                    <div id="cpu-value" class="text-3xl font-extrabold text-blue-500 mt-2">0%</div>
                </div>

                <!-- CPU Temp -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-700">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">CPU Temperature</h3>
                    <div id="temp-value" class="text-3xl font-extrabold text-red-500 mt-2">-- °C</div>
                </div>

                <!-- Memory Usage -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-700">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Memory Usage</h3>
                    <div id="ram-value" class="text-3xl font-extrabold text-green-500 mt-2">0%</div>
                    <div id="ram-detail" class="text-xs text-gray-400 mt-1">0 / 0 MB</div>
                </div>

                <!-- Total Processes -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-700">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Processes</h3>
                    <div id="procs-value" class="text-3xl font-extrabold text-purple-500 mt-2">0</div>
                </div>

                <!-- Battery -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-700">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Battery</h3>
                    <div id="battery-value" class="text-3xl font-extrabold text-yellow-500 mt-2">--</div>
                </div>

                <!-- Uptime & Host -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-700">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Uptime & Server</h3>
                    <div id="host-name" class="text-lg font-bold text-white mt-2">-</div>
                    <div id="uptime-value" class="text-xs text-gray-400 mt-1">Loading...</div>
                </div>

                <!-- TERMINAL SECTION (HANYA MUNCUL/BISA DIAKSES OWNER) -->
            <div class="bg-gray-900 p-3 sm:p-6 rounded-lg shadow-sm border border-gray-700 w-full overflow-hidden">
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h3 class="text-xs sm:text-sm font-bold text-gray-300 uppercase tracking-wider">
            <i class="bi bi-terminal me-2"></i>Interactive Web Terminal
        </h3>
        @if(auth()->user()->role === 'owner')
            <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded border border-green-500/20">OWNER ACCESS</span>
        @else
            <span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs rounded border border-red-500/20">GUEST READ-ONLY</span>
        @endif
    </div>

    <!-- Container dibuat responsive dengan max-width 100% dan touch scroll overflow -->
    <div id="terminal-container" class="w-full overflow-x-auto rounded bg-black p-2 min-h-[300px] sm:min-h-[400px]">
        <div id="terminal" class="w-full"></div>
    </div>
</div>

            </div>
        </div>
    </div>

    <script>
        const ws = new WebSocket('ws://' + window.location.hostname + ':8081/ws/system');

        ws.onopen = () => console.log('Connected to Go Monitor WebSocket (Port 8081)!');
        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            
            document.getElementById('cpu-value').innerText = data.cpu_usage.toFixed(1) + '%';
            document.getElementById('temp-value').innerText = data.cpu_temp.toFixed(1) + ' °C';
            document.getElementById('ram-value').innerText = data.ram_usage.toFixed(1) + '%';
            document.getElementById('ram-detail').innerText = `${data.ram_used_mb} / ${data.ram_total_mb} MB`;
            document.getElementById('procs-value').innerText = data.total_procs;
            document.getElementById('battery-value').innerText = data.battery;
            document.getElementById('host-name').innerText = `${data.hostname} (${data.platform})`;
            document.getElementById('uptime-value').innerText = `Up: ${data.uptime}`;
        };
        ws.onclose = () => console.log('WebSocket Connection Closed');
    </script>
<script>
    const isOwner = "{{ auth()->user()->role }}" === "owner";

    if (isOwner) {
        const term = new Terminal({
            cursorBlink: true,
            theme: { background: '#000000', foreground: '#00ff00' },
            fontSize: window.innerWidth < 640 ? 11 : 14, // Automatic font scaling untuk HP
            fontFamily: 'monospace',
            convertEol: true
        });

        // Load Fit Addon agar auto-resize sesuai lebar layar HP
        const fitAddon = new FitAddon.FitAddon();
        term.loadAddon(fitAddon);

        term.open(document.getElementById('terminal'));
        fitAddon.fit();

        // Handle auto-resize pas HP di-rotate (Landscape / Portrait)
        window.addEventListener('resize', () => fitAddon.fit());

        const termWs = new WebSocket('ws://' + window.location.hostname + ':8082/ws/terminal');

        termWs.onopen = () => {
            term.clear();
            term.writeln('\x1b[1;32m=== Ryuu Interactive Web Terminal ===\x1b[0m');
            fitAddon.fit();
        };

        termWs.onmessage = (event) => term.write(event.data);
        term.onData((data) => {
            if (termWs.readyState === WebSocket.OPEN) termWs.send(data);
        });

        termWs.onclose = () => term.writeln('\r\n\x1b[1;31m[Terminal Disconnected]\x1b[0m');
    }
</script>
</x-app-layout>
