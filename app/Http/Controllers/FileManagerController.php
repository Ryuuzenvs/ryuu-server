<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        $basePath = '/var/www/html';
        
        // Ambil path dari query URL
        $relativeDirectory = $request->query('path', '');
        
        // Sanitize path dari directory traversal (mencegah ../)
        $relativeDirectory = str_replace(['..', '\\'], '', $relativeDirectory);
        $relativeDirectory = trim($relativeDirectory, '/');

        // Path absolut yang akan di-scan
        $targetPath = $relativeDirectory ? $basePath . '/' . $relativeDirectory : $basePath;

        $folders = [];
        $files = [];

        // Validasi apakah target path valid & ada di dalam root
        if (File::exists($targetPath) && File::isDirectory($targetPath)) {
            
            // Scan direktori menggunakan Laravel File Facade / Native PHP
            $scannedItems = File::directories($targetPath);
            $scannedFiles = File::files($targetPath);

            // Format Folders
            foreach ($scannedItems as $item) {
                $folderName = basename($item);
                
                // Skip folder internal v2-server jika berada di root agar tidak rekursif berlebih (opsional)
                $folders[] = [
                    'name' => $folderName,
                    'path' => $relativeDirectory ? $relativeDirectory . '/' . $folderName : $folderName,
                ];
            }

            // Format Files
            foreach ($scannedFiles as $file) {
                $fileName = basename($file);
                $files[] = [
                    'name' => $fileName,
                    'path' => $relativeDirectory ? $relativeDirectory . '/' . $fileName : $fileName,
                    'size' => round(File::size($file) / 1024, 2), // Size in KB
                ];
            }
        }

        return view('file-manager.index', compact('folders', 'files', 'relativeDirectory'));
    }

    public function createFolder(Request $request)
    {
        $request->validate(['folder_name' => 'required|string']);
        
        $basePath = '/var/www/html';
        $currentPath = trim($request->input('path', ''), '/');
        
        $targetDir = $currentPath ? $basePath . '/' . $currentPath . '/' . $request->folder_name : $basePath . '/' . $request->folder_name;

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0775, true);
        }

        return redirect()->back()->with('success', 'Folder berhasil dibuat!');
    }

    public function deleteItem(Request $request)
    {
        $request->validate(['item_path' => 'required|string']);
        
        $basePath = '/var/www/html';
        $targetPath = $basePath . '/' . trim($request->item_path, '/');

        if (File::exists($targetPath)) {
            if (File::isDirectory($targetPath)) {
                File::deleteDirectory($targetPath);
            } else {
                File::delete($targetPath);
            }
        }

        return redirect()->back()->with('success', 'Item berhasil dihapus!');
    }
}
