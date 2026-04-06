<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Sale;
use App\Services\AutocompleteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AutocompleteController extends Controller
{
    use AuthorizesRequests;

    public function clients(Request $request, AutocompleteService $autocompleteService)
    {
        try {
            $this->authorize('viewAny', Sale::class);
            $term = $request->input('q', '');
            $limit = $request->input('limit', 10);

            $query = Entity::query()->activeClients();
            $results = $autocompleteService->search($query, $term, ['first_name', 'last_name', 'identity_card'], $limit);

            return $autocompleteService->response($results, function ($e) {
                return [
                    'id' => $e->id,
                    'text' => $e->short_name,
                ];
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function suppliers(Request $request, AutocompleteService $autocompleteService)
    {
        try {
            $this->authorize('viewAny', \App\Models\Purchase::class);
            $term = $request->input('q', '');
            $limit = $request->input('limit', 10);

            $query = Entity::query()->active()->where('is_supplier', true);
            $results = $autocompleteService->search($query, $term, ['first_name', 'last_name', 'identity_card', 'ruc'], $limit);

            return $autocompleteService->response($results, function ($e) {
                return [
                    'id' => $e->id,
                    'text' => $e->short_name,
                ];
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
}
