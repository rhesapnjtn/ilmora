<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Ilmora</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; background: #f8fafc; }
        .page { width: 100%; height: 100%; padding: 40px; box-sizing: border-box; }
        .border { border: 8px solid #7c3aed; padding: 40px; height: 100%; box-sizing: border-box; position: relative; }
        .header { text-align: center; }
        .logo { font-size: 28px; font-weight: 800; color: #7c3aed; letter-spacing: 2px; }
        .title { font-size: 36px; font-weight: 800; color: #1e293b; margin-top: 20px; }
        .subtitle { font-size: 16px; color: #64748b; margin-top: 8px; }
        .recipient { font-size: 32px; font-weight: 700; color: #7c3aed; margin-top: 40px; text-align: center; }
        .course { font-size: 22px; font-weight: 600; color: #1e293b; margin-top: 20px; text-align: center; }
        .meta { font-size: 14px; color: #64748b; margin-top: 30px; text-align: center; }
        .footer { position: absolute; bottom: 40px; left: 40px; right: 40px; display: flex; justify-content: space-between; align-items: center; }
        .qr { width: 80px; height: 80px; }
        .signature { text-align: center; }
        .signature .line { width: 160px; border-top: 1px solid #94a3b8; margin: 0 auto 6px; }
        .signature .name { font-size: 14px; font-weight: 600; color: #1e293b; }
        .signature .role { font-size: 12px; color: #64748b; }
        .code { font-size: 11px; color: #94a3b8; margin-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="page">
        <div class="border">
            <div class="header">
                <div class="logo">ILMORA</div>
                <div class="title">Sertifikat Penyelesaian</div>
                <div class="subtitle">Diberikan kepada</div>
            </div>

            <div class="recipient">{{ $recipient }}</div>
            <div class="subtitle" style="text-align:center; margin-top:8px;">telah menyelesaikan kursus</div>
            <div class="course">{{ $courseTitle }}</div>

            <div class="meta">
                Level: {{ $level }} &nbsp;|&nbsp; Durasi: {{ $hours }} jam &nbsp;|&nbsp; Instruktur: {{ $instructor }}
                <br>
                Diterbitkan: {{ $issuedAt }}
            </div>

            <div class="footer">
                <div class="signature">
                    <div class="line"></div>
                    <div class="name">{{ $instructor }}</div>
                    <div class="role">Instruktur</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:11px; color:#64748b; margin-bottom:4px;">Verifikasi</div>
                    <div style="font-size:10px; color:#7c3aed; word-break:break-all; max-width:120px;">{{ $verifyUrl }}</div>
                </div>
                <div class="signature">
                    <div class="line"></div>
                    <div class="name">Ilmora Edutech</div>
                    <div class="role">Platform</div>
                </div>
            </div>

            <div class="code">Kode sertifikat: {{ $code }}</div>
        </div>
    </div>
</body>
</html>
