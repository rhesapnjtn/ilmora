<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::query()->orderBy('group')->orderBy('key')->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->map(fn (Setting $s) => [
                'key' => $s->key,
                'value' => Setting::get($s->key),
                'type' => $s->type,
            ])->values());

        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
            'settings.*.type' => ['nullable', 'in:string,boolean,integer,json'],
            'settings.*.group' => ['nullable', 'string'],
        ]);

        foreach ($data['settings'] as $item) {
            Setting::set(
                $item['key'],
                $item['value'] ?? null,
                $item['type'] ?? 'string',
                $item['group'] ?? 'general',
            );
        }

        return response()->json(['message' => 'Pengaturan berhasil disimpan.']);
    }
}
