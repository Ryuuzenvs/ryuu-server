<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* Modern Compact Dark Theme File Manager */
        .fm-card {
            aspect-ratio: 1 / 1;
            background: rgba(33, 37, 41, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .fm-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        .fm-icon {
            font-size: 1.6rem;
            line-height: 1;
        }
        @media (min-width: 576px) {
            .fm-icon { font-size: 2.2rem; }
        }
        .fm-name {
            font-size: 0.65rem;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }
        .fm-action-btn {
            padding: 0;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        .fm-app-badge {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 20px;
            height: 20px;
            font-size: 10px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            text-decoration: none;
        }
    </style>

    <div class="container-fluid py-3 px-2 px-md-4 text-light">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 px-3 text-xs mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card bg-dark border-secondary p-2 mb-3 shadow-sm">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="font-monospace text-xs text-truncate text-secondary">
                    <span class="text-info font-bold">/var/www/html</span>/{{ $relativeDirectory }}
                </div>

                @if(auth()->user()->role === 'owner')
                    <div class="d-flex gap-2">
                        <form action="{{ route('files.touch') }}" method="POST" class="d-flex gap-1">
                            @csrf
                            <input type="hidden" name="path" value="{{ $relativeDirectory }}">
                            <input type="text" name="file_name" placeholder="script.py" required
                                   class="form-control form-control-sm bg-black text-light border-secondary text-xs" style="width: 110px;">
                            <button type="submit" class="btn btn-sm btn-success px-2 py-0" title="New File">+</button>
                        </form>

                        <form action="{{ route('files.mkdir') }}" method="POST" class="d-flex gap-1">
                            @csrf
                            <input type="hidden" name="path" value="{{ $relativeDirectory }}">
                            <input type="text" name="folder_name" placeholder="New Folder" required
                                   class="form-control form-control-sm bg-black text-light border-secondary text-xs" style="width: 110px;">
                            <button type="submit" class="btn btn-sm btn-primary px-2 py-0" title="New Folder">+</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="row row-cols-5 row-cols-sm-8 row-cols-md-10 row-cols-lg-12 g-2">

            @if($relativeDirectory)
                @php
                    $parentPath = dirname($relativeDirectory);
                    $parentPath = ($parentPath === '.') ? '' : $parentPath;
                @endphp
                <div class="col">
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" 
                       class="card fm-card text-decoration-none text-light d-flex flex-column align-items-center justify-content-center p-1 text-center"
                       title="Back">
                        <div class="fm-icon text-warning">↩️</div>
                        <div class="fm-name fw-bold text-secondary mt-1">Back</div>
                    </a>
                </div>
            @endif

            @foreach($folders as $folder)
                <div class="col">
                    <div class="card fm-card position-relative d-flex flex-column align-items-center justify-content-center p-1 text-center group-item">
                        
                        @if(auth()->user()->role === 'owner')
                            <div class="position-absolute top-0 end-0 p-1 d-flex gap-1 z-3">
                                <button type="button" onclick="openRenameModal('{{ $folder['path'] }}', '{{ $folder['name'] }}')" 
                                        class="btn btn-dark border-secondary text-info fm-action-btn" title="Rename">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('files.delete') }}" method="POST" onsubmit="return confirm('Hapus folder ini?')">
                                    @csrf
                                    <input type="hidden" name="item_path" value="{{ $folder['path'] }}">
                                    <button type="submit" class="btn btn-dark border-secondary text-danger fm-action-btn" title="Delete">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if($folder['has_web_app'])
                            <a href="{{ $folder['web_app_url'] }}" target="_blank" 
                               class="fm-app-badge bg-primary text-white shadow" title="Open App">
                                🚀
                            </a>
                        @endif

                        <a href="{{ route('files.index', ['path' => $folder['path']]) }}" 
                           class="text-decoration-none text-light w-100 h-100 d-flex flex-column align-items-center justify-content-center" 
                           title="{{ $folder['name'] }}">
                            <div class="fm-icon text-warning">📁</div>
                            <div class="fm-name mt-1">{{ $folder['name'] }}</div>
                        </a>
                    </div>
                </div>
            @endforeach

            @foreach($files as $file)
                <div class="col">
                    <div class="card fm-card position-relative d-flex flex-column align-items-center justify-content-center p-1 text-center">
                        
                        @if(auth()->user()->role === 'owner')
                            <div class="position-absolute top-0 end-0 p-1 d-flex gap-1 z-3">
                                <button type="button" onclick="openRenameModal('{{ $file['path'] }}', '{{ $file['name'] }}')" 
                                        class="btn btn-dark border-secondary text-info fm-action-btn" title="Rename">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('files.delete') }}" method="POST" onsubmit="return confirm('Hapus file ini?')">
                                    @csrf
                                    <input type="hidden" name="item_path" value="{{ $file['path'] }}">
                                    <button type="submit" class="btn btn-dark border-secondary text-danger fm-action-btn" title="Delete">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        @endif

                        @php
                            $icon = '📄';
                            $iconClass = 'text-secondary';
                            if(in_array($file['ext'], ['php', 'py', 'js', 'go', 'sh', 'html', 'css', 'json'])) {
                                $icon = '💻'; $iconClass = 'text-info';
                            } elseif(in_array($file['ext'], ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'])) {
                                $icon = '🖼️'; $iconClass = 'text-warning';
                            } elseif(in_array($file['ext'], ['zip', 'rar', '7z', 'gz', 'tar'])) {
                                $icon = '📦'; $iconClass = 'text-danger';
                            }
                        @endphp

                        <a href="{{ route('files.editor', ['filepath' => $file['path']]) }}" 
                           class="text-decoration-none text-light w-100 h-100 d-flex flex-column align-items-center justify-content-center" 
                           title="{{ $file['name'] }}">
                            <div class="fm-icon {{ $iconClass }}">{{ $icon }}</div>
                            <div class="fm-name mt-1">{{ $file['name'] }}</div>
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    <div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary py-2">
                    <h6 class="modal-title font-monospace text-xs">Rename Item</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('files.rename') }}" method="POST">
                    @csrf
                    <div class="modal-body py-3">
                        <input type="hidden" id="renameOldPath" name="old_path">
                        <input type="text" id="renameNewName" name="new_name" required
                               class="form-control form-control-sm bg-black text-light border-secondary text-xs">
                    </div>
                    <div class="modal-footer border-secondary py-1">
                        <button type="button" class="btn btn-sm btn-secondary text-xs" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary text-xs font-bold">Rename</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let bootstrapModal;
        document.addEventListener('DOMContentLoaded', function() {
            bootstrapModal = new bootstrap.Modal(document.getElementById('renameModal'));
        });

        function openRenameModal(oldPath, oldName) {
            document.getElementById('renameOldPath').value = oldPath;
            const inputNewName = document.getElementById('renameNewName');
            inputNewName.value = oldName;

            bootstrapModal.show();

            setTimeout(() => {
                inputNewName.focus();
                inputNewName.select();
            }, 200);
        }
    </script>
</x-app-layout>
