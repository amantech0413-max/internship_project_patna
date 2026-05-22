<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Services\BinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BinController extends Controller
{
    public function __construct(protected BinService $bin) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->bin->list(
            $request->only(['search', 'type', 'sort_by', 'sort_dir', 'page']),
            (int) $request->get('per_page', 10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Bin items loaded.',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function restore(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:college,student'],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        if (! $this->bin->restore($request->type, (int) $request->id)) {
            return $this->error('Item not found in recycle bin.', 404);
        }

        return $this->success(null, 'Item restored successfully.');
    }

    public function forceDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:college,student'],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        if (! $this->bin->forceDelete($request->type, (int) $request->id)) {
            return $this->error('Item not found in recycle bin.', 404);
        }

        return $this->success(null, 'Item permanently deleted.');
    }
}
