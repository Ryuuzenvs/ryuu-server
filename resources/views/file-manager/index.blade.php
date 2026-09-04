<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('File Explorer Server') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            
            <!-- ALERT NOTIFIKASI -->
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

                <!-- DOKUMEN OWNER: BISA CREATE FOLDER -->
                @if(auth()->user()->role === 'owner')
                    <form action="{{ route('files.mkdir') }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="path" value="{{ $relativeDirectory }}">
                        <input type="text" name="folder_name" placeholder="New Folder..." required
                               class="bg-gray-900 border border-gray-600 text-white text-xs rounded px-3 py-1.5 focus:outline-none focus:border-blue-500 w-full sm:w-40">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                            + Folder
                        </button>
                    </form>
                @endif
            </div>

            <!-- GRID LIST FOLDER & FILE -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                
                <!-- TOMBOL KEMBALI (UP DIRECTORY) -->
                @if($relativeDirectory)
                    @php
                        $parentPath = dirname($relativeDirectory);
                        $parentPath = ($parentPath === '.') ? '' : $parentPath;
                    @endphp
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" 
                       class="bg-gray-800 hover:bg-gray-700 p-4 rounded-lg border border-gray-700 text-center block transition">
                        <div class="text-2xl text-yellow-500">📁</div>
                        <div class="text-xs font-bold text-gray-300 mt-2 truncate">.. Kembali</div>
                    </a>
                @endif

                <!-- LIST FOLDERS -->
                @foreach($folders as $folder)
                    <div class="bg-gray-800 hover:bg-gray-750 p-3 sm:p-4 rounded-lg border border-gray-700 text-center relative group">
                        
                        <!-- TOMBOL DELETE OWNER -->
                        @if(auth()->user()->role === 'owner')
                            <form action="{{ route('files.delete') }}" method="POST" onsubmit="return confirm('Hapus folder ini?')"
                                  class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                                @csrf
                                <input type="hidden" name="item_path" value="{{ $folder['path'] }}">
                                <button type="submit" class="text-red-400 hover:text-red-300 text-xs px-1">✕</button>
                            </form>
                        @endif

                        <a href="{{ route('files.index', ['path' => $folder['path']]) }}" class="block">
                            <div class="text-3xl text-yellow-400">📁</div>
                            <div class="text-xs font-bold text-gray-200 mt-2 truncate">{{ $folder['name'] }}</div>
                            <span class="text-[10px] text-gray-400 bg-gray-900 px-1.5 py-0.5 rounded mt-1 inline-block">Folder</span>
                        </a>
                    </div>
                @endforeach

                <!-- LIST FILES -->
                @foreach($files as $file)
                    <div class="bg-gray-800 p-3 sm:p-4 rounded-lg border border-gray-700 text-center relative group">
                        
                        <!-- TOMBOL DELETE OWNER -->
                        @if(auth()->user()->role === 'owner')
                            <form action="{{ route('files.delete') }}" method="POST" onsubmit="return confirm('Hapus file ini?')"
                                  class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                                @csrf
                                <input type="hidden" name="item_path" value="{{ $file['path'] }}">
                                <button type="submit" class="text-red-400 hover:text-red-300 text-xs px-1">✕</button>
                            </form>
                        @endif

                        <div class="text-3xl text-blue-400">📄</div>
                        <div class="text-xs font-bold text-gray-200 mt-2 truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                        <div class="text-[10px] text-gray-400 mt-1">{{ $file['size'] }} KB</div>
                    </div>
                @endforeach

            </div>

        </div>
    </div>
</x-app-layout>
