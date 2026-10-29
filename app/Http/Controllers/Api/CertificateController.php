<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Support\DemoCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $certificates = $request->user()
            ?->certificates()
            ->with('course')
            ->latest()
            ->get()
            ->map(fn (Certificate $certificate): array => $this->present($certificate))
            ->values()
            ->all() ?? [];

        return response()->json([
            'certificates' => $certificates,
            'recipient' => $request->user()?->name,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $certificate = Certificate::query()
            ->with('course', 'user')
            ->where('code', $id)
            ->orWhere('id', $id)
            ->first();

        if ($certificate !== null) {
            $data = $this->present($certificate);
            $data['recipient'] = $certificate->user?->name ?? $request->user()?->name ?? 'Peserta Ilmora';

            return response()->json(['certificate' => $data]);
        }

        $demo = DemoCatalog::findCertificate($id);

        if ($demo !== null) {
            $demo['recipient'] = $request->user()?->name ?? 'Peserta Ilmora';

            return response()->json(['certificate' => $demo]);
        }

        return response()->json(['message' => 'Sertifikat tidak ditemukan.'], 404);
    }

    public function download(Request $request, string $id): Response
    {
        $certificate = Certificate::query()
            ->with('course', 'user')
            ->where('code', $id)
            ->orWhere('id', $id)
            ->first();

        if ($certificate !== null) {
            $data = $this->present($certificate);
            $recipient = $certificate->user?->name ?? $request->user()?->name ?? 'Peserta Ilmora';

            $pdf = Pdf::loadView('certificates.pdf', [
                'recipient' => $recipient,
                'courseTitle' => $data['courseTitle'] ?? 'Kursus',
                'level' => $data['level'] ?? '-',
                'hours' => $data['hours'] ?? 0,
                'instructor' => $data['instructor'] ?? '-',
                'issuedAt' => $data['issuedAt'] ?? now()->toDateString(),
                'code' => $data['code'],
                'verifyUrl' => url('/api/certificates/verify/'.$data['code']),
            ]);

            return $pdf->download('sertifikat-'.$data['code'].'.pdf');
        }

        $demo = DemoCatalog::findCertificate($id);

        if ($demo !== null) {
            $recipient = $request->user()?->name ?? 'Peserta Ilmora';
            $pdf = Pdf::loadView('certificates.pdf', [
                'recipient' => $recipient,
                'courseTitle' => $demo['courseTitle'] ?? 'Kursus',
                'level' => $demo['level'] ?? '-',
                'hours' => $demo['hours'] ?? 0,
                'instructor' => $demo['instructor'] ?? '-',
                'issuedAt' => $demo['issuedAt'] ?? now()->toDateString(),
                'code' => $demo['code'] ?? $id,
                'verifyUrl' => url('/api/certificates/verify/'.($demo['code'] ?? $id)),
            ]);

            return $pdf->download('sertifikat-'.($demo['code'] ?? $id).'.pdf');
        }

        abort(404, 'Sertifikat tidak ditemukan.');
    }

    public function verify(string $code): JsonResponse
    {
        $certificate = Certificate::query()
            ->with('course', 'user')
            ->where('code', $code)
            ->first();

        if ($certificate === null) {
            return response()->json(['message' => 'Sertifikat tidak ditemukan.'], 404);
        }

        return response()->json([
            'valid' => true,
            'certificate' => $this->present($certificate),
            'recipient' => $certificate->user?->name,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Certificate $certificate): array
    {
        return [
            'id' => $certificate->id,
            'code' => $certificate->code,
            'courseSlug' => $certificate->course?->slug,
            'courseTitle' => $certificate->course?->title,
            'level' => $certificate->course?->level,
            'hours' => $certificate->course?->duration_hours,
            'issuedAt' => $certificate->issued_at?->toDateString(),
            'instructor' => $certificate->course?->instructor_name,
        ];
    }
}
