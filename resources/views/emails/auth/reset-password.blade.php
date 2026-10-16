<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Atur Ulang Kata Sandi</title>
    <!--[if mso]>
    <style>body,table,td{font-family:Arial,Helvetica,sans-serif !important;}</style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;width:100%;background-color:#F2F3FF;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

    <div style="display:none;max-height:0;max-width:0;overflow:hidden;opacity:0;font-size:1px;line-height:1px;color:#F2F3FF;">
        Kami menerima permintaan untuk mengatur ulang kata sandi akun Ilmora Anda. Tautan ini berlaku 60 menit.
        &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#F2F3FF;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:600px;max-width:600px;">

                    <tr>
                        <td align="center" style="padding-bottom:28px;">
                            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Ilmora" width="132" style="display:block;width:132px;max-width:132px;height:auto;border:0;outline:none;text-decoration:none;">
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#FFFFFF;border-radius:24px;overflow:hidden;box-shadow:0 10px 30px -12px rgba(15,23,42,0.18);">

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="height:6px;line-height:6px;font-size:0;background-color:#3525CD;background-image:linear-gradient(90deg,#3525CD 0%,#4F46E5 45%,#F7931A 100%);">&nbsp;</td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="padding:40px 40px 8px 40px;">
                                        <p style="margin:0 0 20px 0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:15px;line-height:24px;color:#464555;">
                                            Halo <strong style="color:#131B2E;font-weight:700;">{{ $name }}</strong>,
                                        </p>
                                        <h1 style="margin:0 0 16px 0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:24px;line-height:32px;color:#131B2E;font-weight:800;letter-spacing:-0.02em;">
                                            Atur Ulang Kata Sandi
                                        </h1>
                                        <p style="margin:0 0 16px 0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:15px;line-height:24px;color:#464555;">
                                            Kami menerima permintaan untuk mengatur ulang kata sandi akun Ilmora Anda. Klik tombol di bawah ini untuk membuat kata sandi baru yang kuat dan unik.
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding:8px 40px 8px 40px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" bgcolor="#3525CD" style="border-radius:14px;box-shadow:0 8px 20px -8px rgba(53,37,205,0.55);">
                                                    <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:15px 34px;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:15px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:14px;background-color:#3525CD;background-image:linear-gradient(135deg,#3525CD 0%,#4F46E5 100%);">
                                                        Atur Ulang Kata Sandi &nbsp;&rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:24px 40px 0 40px;">
                                        <p style="margin:0 0 8px 0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#777587;">
                                            Tombol tidak berfungsi? Salin dan tempel tautan berikut ke browser Anda:
                                        </p>
                                        <p style="margin:0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;word-break:break-all;">
                                            <a href="{{ $url }}" target="_blank" style="color:#3525CD;text-decoration:underline;">{{ $url }}</a>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:24px 40px 40px 40px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#FEF3C7;border-radius:14px;">
                                            <tr>
                                                <td style="padding:16px 18px;">
                                                    <p style="margin:0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#855300;">
                                                        <strong style="font-weight:700;">Penting:</strong> Tautan ini hanya berlaku selama {{ $expireMinutes }} menit dan hanya dapat digunakan satu kali.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 40px 40px 40px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top:1px solid #E2E7FF;">
                                            <tr>
                                                <td style="padding-top:24px;">
                                                    <p style="margin:0 0 6px 0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#777587;">
                                                        Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini. Kata sandi Anda tidak akan berubah.
                                                    </p>
                                                    <p style="margin:0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#464555;">
                                                        Salam hormat,<br><strong style="color:#131B2E;font-weight:700;">Tim Ilmora</strong>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding-top:28px;">
                            <p style="margin:0 0 4px 0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#777587;">
                                Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.
                            </p>
                            <p style="margin:0;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#777587;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
