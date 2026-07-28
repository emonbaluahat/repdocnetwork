<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\Security;
use App\Models\File;
use App\Models\AuditLog;

class FileController extends Controller
{
    public function upload(): void
    {
        $this->authorize('manage_settings');
        $shopId = AuthContext::shopId();
        $userId = AuthContext::id();

        if (empty($_FILES['file'])) {
            $this->json(['error' => 'কোনো ফাইল নির্বাচন করা হয়নি।'], 400);
        }

        $file = $_FILES['file'];
        $fileId = File::upload($file, $shopId, $userId);

        if (!$fileId) {
            $this->json(['error' => 'ফাইল আপলোড ব্যর্থ হয়েছে।'], 400);
        }

        AuditLog::log('file.upload', 'file', $fileId, null, null, $userId);

        $fileRecord = File::find($fileId);
        $this->json([
            'success' => true,
            'file' => $fileRecord,
        ]);
    }

    public function download(int $id): void
    {
        $shopId = AuthContext::shopId();
        $file = File::find($id);

        if (!$file || (int) $file['shop_id'] !== $shopId) {
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        $fullPath = APP_ROOT . '/' . $file['file_path'];
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'File not found on disk.';
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
        header('Content-Length: ' . $file['size']);
        readfile($fullPath);
        exit;
    }

    public function delete(int $id): void
    {
        $this->authorize('manage_settings');
        $shopId = AuthContext::shopId();

        $file = File::find($id);
        if (!$file || (int) $file['shop_id'] !== $shopId) {
            $this->json(['error' => 'File not found.'], 404);
        }

        if (File::deleteFile($id)) {
            AuditLog::log('file.delete', 'file', $id, null, null, AuthContext::id());
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Delete failed.'], 400);
    }
}
