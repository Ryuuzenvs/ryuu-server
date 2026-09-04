<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('File Explorer & Code Editor') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            
            @if(session('success'))
                <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- ACTION BAR & BREADCRUMB -->
            <div class="bg-gray-800 p-4 rounded-lg shadow border border-gray-700 flex flex-wrap justify-between items-center gap-4">
                <div class="text-gray-300 font-mono text-xs sm:text-sm overflow-x-auto">
                    <span class="text-blue-400">/var/www/html</span>/{{ $relativeDirectory }}
                </div>

                @if(auth()->user()->role === 'owner')
                    <div class="flex gap-2 w-full sm:w-auto">
                        <!-- FORM TOUCH FILE -->
                        <form action="{{ route('files.touch') }}" method="POST" class="flex gap-1">
                            @csrf
                            <input type="hidden" name="path" value="{{ $relativeDirectory }}">
                            <input type="text" name="file_name" placeholder="script.py" required
                                   class="bg-gray-900 border border-gray-600 text-white text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-500 w-28 sm:w-36">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-2.5 py-1.5 rounded transition">
                                + File
                            </button>
                        </form>

                        <!-- FORM MKDIR FOLDER -->
                        <form action="{{ route('files.mkdir') }}" method="POST" class="flex gap-1">
                            @csrf
                            <input type="hidden" name="path" value="{{ $relativeDirectory }}">
                            <input type="text" name="folder_name" placeholder="New Folder" required
                                   class="bg-gray-900 border border-gray-600 text-white text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-500 w-28 sm:w-36">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-2.5 py-1.5 rounded transition">
                                + Folder
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- GRID LIST ITEM -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                
                <!-- UP DIRECTORY -->
                @if($relativeDirectory)
                    @php
                        $parentPath = dirname($relativeDirectory);
                        $parentPath = ($parentPath === '.') ? '' : $parentPath;
                    @endphp
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" 
                       class="bg-gray-800 hover:bg-gray-700 p-4 rounded-lg border border-gray-700 text-center block transition">
                        <div class="text-3xl text-yellow-500">📁</div>
                        <div class="text-xs font-bold text-gray-300 mt-2 truncate">.. Kembali</div>
                    </a>
                @endif

                <!-- LIST FOLDERS -->
                @foreach($folders as $folder)
                    <div class="bg-gray-800 hover:bg-gray-750 p-3 sm:p-4 rounded-lg border border-gray-700 text-center relative group flex flex-col justify-between">
                        
                        <!-- OWNER CONTROLS -->
                        @if(auth()->user()->role === 'owner')
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition flex gap-1 bg-gray-900/80 px-1 rounded">
                                <button onclick="openRenameModal('{{ $folder['path'] }}', '{{ $folder['name'] }}')" class="text-blue-400 hover:text-blue-300 text-xs">✏️</button>
                                <form action="{{ route('files.delete') }}" method="POST" onsubmit="return confirm('Hapus folder ini?')">
                                    @csrf
                                    <input type="hidden" name="item_path" value="{{ $folder['path'] }}">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs">✕</button>
                                </form>
                            </div>
                        @endif

                        <a href="{{ route('files.index', ['path' => $folder['path']]) }}" class="block my-auto">
                            <div class="text-3xl text-yellow-400">📁</div>
                            <div class="text-xs font-bold text-gray-200 mt-2 truncate">{{ $folder['name'] }}</div>
                            <span class="text-[10px] text-gray-400 bg-gray-900 px-1.5 py-0.5 rounded mt-1 inline-block">Directory</span>
                        </a>
                    </div>
                @endforeach

                <!-- LIST FILES -->
                @foreach($files as $file)
                    <div class="bg-gray-800 p-3 sm:p-4 rounded-lg border border-gray-700 text-center relative group flex flex-col justify-between">
                        
                        <!-- OWNER CONTROLS -->
                        @if(auth()->user()->role === 'owner')
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition flex gap-1 bg-gray-900/80 px-1 rounded z-10">
                                <button onclick="openRenameModal('{{ $file['path'] }}', '{{ $file['name'] }}')" class="text-blue-400 hover:text-blue-300 text-xs">✏️</button>
                                <form action="{{ route('files.delete') }}" method="POST" onsubmit="return confirm('Hapus file ini?')">
                                    @csrf
                                    <input type="hidden" name="item_path" value="{{ $file['path'] }}">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs">✕</button>
                                </form>
                            </div>
                        @endif

                        <a href="{{ route('files.editor', ['filepath' => $file['path']]) }}" class="block my-auto hover:opacity-80 transition">
                            <div class="text-3xl text-blue-400">📄</div>
                            <div class="text-xs font-bold text-gray-200 mt-2 truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                            <div class="text-[10px] text-gray-400 mt-1">{{ $file['size'] }} KB</div>
                        </a>
                    </div>
                @endforeach

            </div>

        </div>
    </div>

    <!-- MODAL RENAME -->
    <div id="renameModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center p-4 z-50">
        <div class="bg-gray-900 border border-gray-700 p-6 rounded-lg max-w-md w-full shadow-xl">
            <h3 class="text-sm font-bold text-gray-200 mb-4">Rename Item</h3>
            <form action="{{ route('files.rename') }}" method="POST">
                @csrf
                <input type="hidden" id="renameOldPath" name="old_path">
                <input type="text" id="renameNewName" name="new_name" required
                       class="w-full bg-gray-950 border border-gray-700 rounded text-sm text-white p-2.5 mb-4 focus:outline-none focus:border-blue-500">
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeRenameModal()" class="px-4 py-2 bg-gray-800 text-gray-300 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded text-xs">Save Rename</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRenameModal(oldPath, oldName) {
            document.getElementById('renameOldPath').value = oldPath;
            document.getElementById('renameNewName').value = oldName;
            document.getElementById('renameModal').classList.remove('hidden');
            document.getElementById('renameModal').classList.add('flex');
        }

        function closeRenameModal() {
            document.getElementById('renameModal').classList.add('hidden');
            document.getElementById('renameModal').classList.remove('flex');
        }
    </script>
</x-app-layout>
