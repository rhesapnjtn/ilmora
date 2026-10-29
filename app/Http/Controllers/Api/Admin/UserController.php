<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        return response()->json([
            'message' => 'Pengguna berhasil dibuat.',
            'user' => $this->present($user->loadCount(['subscriptions', 'payments'])),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();
        $role = $request->string('role')->toString();

        $users = User::query()
            ->withCount(['subscriptions', 'payments'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'users' => collect($users->items())->map(fn (User $user): array => $this->present($user))->all(),
            'meta' => [
                'page' => $users->currentPage(),
                'perPage' => $users->perPage(),
                'total' => $users->total(),
                'lastPage' => $users->lastPage(),
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadCount(['subscriptions', 'payments']);

        return response()->json([
            'user' => [
                ...$this->present($user),
                'subscriptions' => $user->subscriptions()->latest()->get(),
                'payments' => $user->payments()->with('course:id,title,slug')->latest()->get(),
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (($data['role'] ?? null) === 'user' && $user->isAdmin() && $this->adminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'Tidak dapat menurunkan admin terakhir.',
            ]);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'user' => $this->present($user->fresh()->loadCount(['subscriptions', 'payments'])),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->id === $user->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun sendiri.'], 422);
        }

        if ($user->isAdmin() && $this->adminCount() <= 1) {
            return response()->json(['message' => 'Tidak dapat menghapus admin terakhir.'], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }

    private function adminCount(): int
    {
        return User::query()->where('role', 'admin')->count();
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $users = User::query()->latest()->get();

        return Response::streamDownload(function () use ($users): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Role', 'Created At']);
            foreach ($users as $user) {
                fputcsv($handle, [$user->id, $user->name, $user->email, $user->role, $user->created_at]);
            }
            fclose($handle);
        }, 'users.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'subscriptionsCount' => $user->subscriptions_count ?? 0,
            'paymentsCount' => $user->payments_count ?? 0,
            'createdAt' => $user->created_at?->toIso8601String(),
        ];
    }
}
