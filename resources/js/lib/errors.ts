import type { AxiosError } from 'axios';

export interface ValidationErrorPayload {
    message?: string;
    errors?: Record<string, string[]>;
}

export interface NormalizedErrors {
    messages: Record<string, string>;
    message: string | null;
    status: number | null;
}

export function normalizeApiError(error: unknown): NormalizedErrors {
    const response = (error as AxiosError<ValidationErrorPayload>)?.response;
    const status = response?.status ?? null;
    const data = response?.data;
    const messages: Record<string, string> = {};

    if (data?.errors) {
        for (const [field, value] of Object.entries(data.errors)) {
            messages[field] = Array.isArray(value) ? value[0] : String(value);
        }
    }

    let message = data?.message ?? null;

    if (status === 429) {
        message = 'Terlalu banyak percobaan. Silakan tunggu beberapa saat sebelum mencoba lagi.';
    } else if (status === 401 && !message) {
        message = 'Sesi Anda telah berakhir. Silakan masuk kembali.';
    } else if (status !== null && status >= 500) {
        message = 'Terjadi gangguan pada server. Silakan coba lagi nanti.';
    }

    return {
        messages,
        message,
        status,
    };
}
