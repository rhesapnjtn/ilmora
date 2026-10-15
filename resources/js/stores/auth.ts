import { computed, reactive } from 'vue';
import api from '@/lib/api';

export type UserRole = 'user' | 'admin';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
}

interface AuthState {
    user: AuthUser | null;
    token: string | null;
    ready: boolean;
}

const STORAGE_KEY = 'ilmora_token';

const state = reactive<AuthState>({
    user: null,
    token: localStorage.getItem(STORAGE_KEY),
    ready: false,
});

if (state.token) {
    api.defaults.headers.common.Authorization = `Bearer ${state.token}`;
}

export function useAuth() {
    const isAuthenticated = computed(() => state.user !== null);
    const isAdmin = computed(() => state.user?.role === 'admin');

    function applySession(user: AuthUser, token: string): void {
        state.user = user;
        state.token = token;
        localStorage.setItem(STORAGE_KEY, token);
        api.defaults.headers.common.Authorization = `Bearer ${token}`;
    }

    function clearSession(): void {
        state.user = null;
        state.token = null;
        localStorage.removeItem(STORAGE_KEY);
        delete api.defaults.headers.common.Authorization;
    }

    async function fetchUser(): Promise<void> {
        if (!state.token) {
            state.ready = true;

            return;
        }

        try {
            const { data } = await api.get<{ user: AuthUser }>('/user');
            state.user = data.user;
        } catch {
            clearSession();
        } finally {
            state.ready = true;
        }
    }

    async function login(credentials: {
        email: string;
        password: string;
    }): Promise<AuthUser> {
        const { data } = await api.post<{ user: AuthUser; token: string }>(
            '/login',
            credentials,
        );
        applySession(data.user, data.token);

        return data.user;
    }

    async function register(payload: {
        name: string;
        email: string;
        password: string;
        password_confirmation: string;
    }): Promise<AuthUser> {
        const { data } = await api.post<{ user: AuthUser; token: string }>(
            '/register',
            payload,
        );
        applySession(data.user, data.token);

        return data.user;
    }

    async function logout(): Promise<void> {
        try {
            await api.post('/logout');
        } catch {
            // Token may already be invalid; clearing locally is enough.
        } finally {
            clearSession();
        }
    }

    return {
        state,
        user: computed(() => state.user),
        isAuthenticated,
        isAdmin,
        ready: computed(() => state.ready),
        fetchUser,
        login,
        register,
        logout,
        clearSession,
    };
}
