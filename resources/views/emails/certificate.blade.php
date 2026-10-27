<!DOCTYPE html>
<html>
<body>
    <p>Halo {{ $certificate->user?->name }},</p>
    <p>Selamat! Anda telah menyelesaikan kursus {{ $certificate->course?->title }}.</p>
    <p>Kode sertifikat: {{ $certificate->code }}</p>
</body>
</html>