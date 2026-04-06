<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\BackupOperationDTO;
use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BackupService $backupService
    ) {
    }

    public function index()
    {
        $this->authorize('viewAny', Backup::class);

        $page = request()->input('page', 1);

        return view('admin.backups.index', $this->backupService->getIndexData($page));
    }

    public function store(): RedirectResponse
    {
        $this->authorize('create', Backup::class);

        try {
            $filename = $this->backupService->createBackup();

            // Dispatch BackupCreated event
            try {
                $backupsPath = $this->backupService->getBackupPath();
                $filePath = $backupsPath . DIRECTORY_SEPARATOR . $filename;
                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

                $payload = [
                    'title' => 'Respaldo creado exitosamente',
                    'message' => "Se ha creado un nuevo respaldo de la base de datos: {$filename}",
                    'filename' => $filename,
                    'size' => $fileSize,
                    'formatted_size' => \App\Models\Backup::formatSize($fileSize),
                    'created_at' => now()->toIso8601String(),
                    'backup_path' => $backupsPath,
                ];

                event(new \App\Events\BackupCreated($payload));
            } catch (\Throwable $e) {
                // Ignore notification errors to avoid breaking the user flow
                \Illuminate\Support\Facades\Log::warning('Failed to dispatch backup event from controller: ' . $e->getMessage());
            }

            return redirect()
                ->route('backups.index')
                ->with('status', 'Backup creado: ' . $filename);
        } catch (\Exception $e) {
            return redirect()
                ->route('backups.index')
                ->withErrors($e->getMessage());
        }
    }

    public function download(Request $request): BinaryFileResponse
    {
        $this->authorize('download', Backup::class);

        $data = $request->validate(['filename' => 'required|string']);

        $operation = BackupOperationDTO::fromRequest(
            $data,
            $this->backupService->getBackupPath(),
            config('native-backups.sqlite_path')
        );

        try {
            $file = $this->backupService->getBackupFile($operation);

            return response()->download($file, $operation->filename);
        } catch (\Exception $e) {
            abort($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        $this->authorize('restore', Backup::class);

        $data = $request->validate(['filename' => 'required|string']);

        $operation = BackupOperationDTO::fromRequest(
            $data,
            $this->backupService->getBackupPath(),
            config('native-backups.sqlite_path')
        );

        try {
            $this->backupService->restoreBackup($operation);

            return redirect()
                ->route('backups.index')
                ->with('status', 'Base de datos restaurada correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('backups.index')
                ->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', Backup::class);

        $data = $request->validate(['filename' => 'required|string']);

        $operation = BackupOperationDTO::fromRequest(
            $data,
            $this->backupService->getBackupPath(),
            config('native-backups.sqlite_path')
        );

        try {
            $this->backupService->deleteBackup($operation);

            return redirect()
                ->route('backups.index')
                ->with('status', 'Respaldo eliminado.');
        } catch (\Exception $e) {
            return redirect()
                ->route('backups.index')
                ->withErrors($e->getMessage());
        }
    }
}
