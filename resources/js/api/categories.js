import { http } from './http';

export async function fetchCategories() {
    const { data } = await http.get('/categories');
    return data.data ?? [];
}

export async function createCategory(payload) {
    const { data } = await http.post('/categories', payload);
    return data.data;
}
