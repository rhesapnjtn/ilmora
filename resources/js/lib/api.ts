import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 401) {
            localStorage.removeItem('ilmora_token');
            delete api.defaults.headers.common.Authorization;

            if (window.location.pathname !== '/masuk') {
                window.location.assign('/masuk');
            }
        }

        return Promise.reject(error);
    },
);

export default api;
