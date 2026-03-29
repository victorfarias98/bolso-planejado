/**
 * Compara dois valores para ordenação (string, número ou data ISO / Y-m-d).
 */
export function compareForSort(a, b) {
    const na = Number(a);
    const nb = Number(b);
    if (!Number.isNaN(na) && !Number.isNaN(nb) && String(a) !== '' && String(b) !== '') {
        if (String(a).match(/^\d/) && String(b).match(/^\d/)) {
            return na - nb;
        }
    }
    const sa = a === null || a === undefined ? '' : String(a);
    const sb = b === null || b === undefined ? '' : String(b);
    if (/^\d{4}-\d{2}-\d{2}/.test(sa) && /^\d{4}-\d{2}-\d{2}/.test(sb)) {
        return sa.localeCompare(sb);
    }
    return sa.localeCompare(sb, 'pt-BR', { sensitivity: 'base', numeric: true });
}

/**
 * Ordena uma cópia do array por campo (ou getter).
 *
 * @param {Array} rows
 * @param {string} key
 * @param {'asc'|'desc'} dir
 * @param {Record<string, (row: object) => unknown>} [getters]
 */
export function sortRows(rows, key, dir, getters = {}) {
    const get = getters[key] ?? ((row) => row[key]);
    const mult = dir === 'asc' ? 1 : -1;
    return [...rows].sort((ra, rb) => compareForSort(get(ra), get(rb)) * mult);
}
