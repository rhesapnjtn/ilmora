<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Kata sandi saat ini tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => $request->string('password')->toString(),
        ]);

        $currentToken = $user->currentAccessToken();

        if ($currentToken !== null) {
            $user->tokens()->whereKeyNot($currentToken->getKey())->delete();
        }

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui. Perangkat lain telah dikeluarkan.',
        ]);
    }
}
