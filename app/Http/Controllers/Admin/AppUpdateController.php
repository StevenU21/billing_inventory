<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\AppUpdateCheckDTO;
use App\Http\Controllers\Controller;
use App\Services\AppUpdateService;
use App\Services\NativeUpdaterStatusStore;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppUpdateController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AppUpdateService $updateService
    ) {
    }

    public function index()
    {
        $this->authorize('viewAny', NativeUpdaterStatusStore::class);

        return view('admin.updates.index', $this->updateService->getStatus());
    }

    public function history(): JsonResponse
    {
        $this->authorize('viewAny', NativeUpdaterStatusStore::class);

        return response()->json($this->updateService->getHistory());
    }

    public function check(Request $request): JsonResponse
    {
        $this->authorize('check', NativeUpdaterStatusStore::class);

        $config = AppUpdateCheckDTO::fromConfig();

        try {
            $this->updateService->checkForUpdates($config);

            return response()->json([
                'message' => 'Verificación iniciada. Mantente en esta vista para ver los eventos.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    public function download(): JsonResponse
    {
        $this->authorize('download', NativeUpdaterStatusStore::class);

        try {
            $this->updateService->downloadUpdate();

            return response()->json([
                'message' => 'Descarga de la actualización iniciada.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    public function install(): JsonResponse
    {
        $this->authorize('install', NativeUpdaterStatusStore::class);

        try {
            $this->updateService->installUpdate();

            return response()->json([
                'message' => 'Instalador ejecutándose. La aplicación se cerrará para aplicar la actualización.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}
