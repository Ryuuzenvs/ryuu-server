<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    private function getDisk()
    {
        return Storage::disk('server_root');
    }

public function index(Request $request)
{
    $disk = $this->getDisk();
    $relativeDirectory = $request->query('path', '');
    $relativeDirectory = str_replace('..', '', $relativeDirectory);
    $relativeDirectory = trim($relativeDirectory, '/');

    // Dapatkan absolute path di OS Linux
    $basePath = config('filesystems.disks.server_root.root', '/var/www/html');
    $fullPath = $relativeDirectory ? $basePath . '/' . $relativeDirectory : $basePath;

    $folders = [];
    $files = [];

    // Gunakan Native PHP scanning yang kebal Symlink & super cepat
    if (is_dir($fullPath)) {
        $scanItems = @scandir($fullPath);

        if ($scanItems !== false) {
            foreach ($scanItems as $item) {
                // Ignore dot files
                if ($item === '.' || $item === '..') continue;

                $itemFullPath = $fullPath . '/' . $item;
                $itemRelativePath = $relativeDirectory ? $relativeDirectory . '/' . $item : $item;

                if (@is_dir($itemFullPath)) {
                    $folders[] = [
                        'name' => $item,
                        'path' => $itemRelativePath,
                    ];
                } else {
                    $files[] = [
                        'name' => $item,
                        'path' => $itemRelativePath,
                        'size' => round(@filesize($itemFullPath) / 1024, 2),
                        'last_modified' => date('Y-m-d H:i:s', @filemtime($itemFullPath) ?: time()),
                        'ext' => pathinfo($item, PATHINFO_EXTENSION),
                    ];
                }
            }
        }
    }

    return view('file-manager.index', compact('folders', 'files', 'relativeDirectory'));
}
    // CREATE FILE (Touch)
    public function createFile(Request $request)
    {
        $request->validate(['file_name' => 'required|string']);
        $currentPath = trim($request->input('path', ''), '/');
        $filePath = $currentPath ? $currentPath . '/' . $request->file_name : $request->file_name;

        if (!$this->getDisk()->exists($filePath)) {
            $this->getDisk()->put($filePath, '');
        }

        return redirect()->back()->with('success', 'File baru berhasil dibuat!');
    }

    // CREATE FOLDER (Mkdir)
    public function createFolder(Request $request)
    {
        $request->validate(['folder_name' => 'required|string']);
        $currentPath = trim($request->input('path', ''), '/');
        $folderPath = $currentPath ? $currentPath . '/' . $request->folder_name : $request->folder_name;

        $this->getDisk()->makeDirectory($folderPath);

        return redirect()->back()->with('success', 'Folder baru berhasil dibuat!');
    }

    // RENAME ITEM (File / Folder)
    public function renameItem(Request $request)
    {
        $request->validate([
            'old_path' => 'required|string',
            'new_name' => 'required|string',
        ]);

        $oldPath = $request->old_path;
        $dir = dirname($oldPath);
        $dir = ($dir === '.') ? '' : $dir;
        $newPath = $dir ? $dir . '/' . $request->new_name : $request->new_name;

        if ($this->getDisk()->exists($oldPath) || $this->getDisk()->directoryExists($oldPath)) {
            $this->getDisk()->move($oldPath, $newPath);
        }

        return redirect()->back()->with('success', 'Item berhasil di-rename!');
    }

    // DELETE ITEM
    public function deleteItem(Request $request)
    {
        $request->validate(['item_path' => 'required|string']);
        $itemPath = $request->item_path;
        $disk = $this->getDisk();

        if ($disk->directoryExists($itemPath)) {
            $disk->deleteDirectory($itemPath);
        } else {
            $disk->delete($itemPath);
        }

        return redirect()->back()->with('success', 'Item berhasil dihapus!');
    }

    // READ FILE FOR EDITOR (Open Code Editor)
public function editFile(Request $request)
{
    $filePath = $request->query('filepath');
    $disk = $this->getDisk();

    if (!$filePath || !$disk->exists($filePath)) {
        return redirect()->route('files.index')->with('error', 'File tidak ditemukan!');
    }

    $rawContent = $disk->get($filePath);
    
    // ENCODE KE BASE64 AGAR AMAN UNTUK SEMUA JENIS KARAKTER/SCRIPT (JS, PHP, GO, JSON, DLL)
    $content = base64_encode($rawContent);
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    return view('file-manager.editor', compact('filePath', 'content', 'extension'));
}

    // UPDATE FILE (Save Content via AJAX)
    public function saveFile(Request $request)
    {
        $request->validate([
            'filepath' => 'required|string',
            'content' => 'present',
        ]);

        $disk = $this->getDisk();
        $disk->put($request->filepath, $request->content);

        return response()->json(['status' => 'success', 'message' => 'File berhasil disimpan!']);
    }
}
