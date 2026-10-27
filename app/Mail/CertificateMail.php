<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Certificate $certificate) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Sertifikat Kelulusan Ilmora');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.certificate');
    }
}
