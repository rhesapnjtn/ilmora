import type { AxiosError } from 'axios';

export interface ValidationErrorPayload {
    message?: string;
    errors?: Record<string, string[]>;
}

export interface NormalizedErrors {
    messages: Record<string, string>;
    message: string | null;
}

export function normalizeApiError(error: unknown): NormalizedErrors {
    const response = (error as AxiosError<ValidationErrorPayload>)?.response;
    const data = response?.data;
    const messages: Record<string, string> = {};

    if (data?.errors) {
        for (const [field, value] of Object.entries(data.errors)) {
            messages[field] = Array.isArray(value) ? value[0] : String(value);
        }
    }

    return {
        messages,
        message: data?.message ?? null,
    };
}
