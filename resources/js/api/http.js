import axios from 'axios';

const TOKEN_KEY = 'divida_zero_token';

const http = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

http.interceptors.request.use((config) => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

export { http, TOKEN_KEY };
