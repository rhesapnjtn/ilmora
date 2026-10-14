<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DemoCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'certificates' => DemoCatalog::certificates(),
            'recipient' => $request->user()?->name,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $certificate = DemoCatalog::findCertificate($id);

        if ($certificate === null) {
            return response()->json(['message' => 'Sertifikat tidak ditemukan.'], 404);
        }

        $certificate['recipient'] = $request->user()?->name ?? 'Peserta Ilmora';

        return response()->json(['certificate' => $certificate]);
    }
}
