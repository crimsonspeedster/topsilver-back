<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UsersGetController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'updated_from' => ['nullable', 'date_format:Y-m-d\TH:i:s\Z'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $users = User::query()
            ->with('profile')
            ->when(
                $validated['updated_from'] ?? null,
                fn ($q, $date) => $q->where('updated_at', '>=', $date)
            )
            ->orderBy('updated_at')
            ->paginate($request->integer('per_page', 100));

        return response()->json([
            'data' => [
                'users' => UserResource::collection($users->items()),
                'pagination' => new PaginationResource($users),
            ]
        ]);
    }
}
