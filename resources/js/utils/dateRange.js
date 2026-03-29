/** Alinhado a `ProjectionService::MAX_PROJECTION_DAYS` */
export const MAX_PROJECTION_RANGE_DAYS = 20000;

export const FORM_DATE_MIN = '1900-01-01';
export const FORM_DATE_MAX = '2100-12-31';

export function isoAddDays(iso, days) {
    const dt = new Date(`${iso}T12:00:00`);
    if (Number.isNaN(dt.getTime())) return null;
    dt.setDate(dt.getDate() + days);
    return dt.toISOString().slice(0, 10);
}

/** Data final máxima permitida a partir da data inicial (projeção). */
export function maxProjectionEndFromStart(startIso) {
    if (!startIso || !/^\d{4}-\d{2}-\d{2}$/.test(startIso)) return null;
    return isoAddDays(startIso, MAX_PROJECTION_RANGE_DAYS);
}

/** Data inicial mínima permitida a partir da data final (projeção). */
export function minProjectionStartFromEnd(endIso) {
    if (!endIso || !/^\d{4}-\d{2}-\d{2}$/.test(endIso)) return null;
    return isoAddDays(endIso, -MAX_PROJECTION_RANGE_DAYS);
}

/** Menor data ISO (YYYY-MM-DD). */
export function minIso(a, b) {
    if (!a) return b;
    if (!b) return a;
    return a <= b ? a : b;
}

/** Maior data ISO (YYYY-MM-DD). */
export function maxIso(a, b) {
    if (!a) return b;
    if (!b) return a;
    return a >= b ? a : b;
}

/**
 * Limites para dois campos de projeção (intervalo ≤ MAX_PROJECTION_RANGE_DAYS, from ≤ to).
 */
export function projectionDatePickerLimits(fromIso, toIso) {
    const minFrom =
        toIso && minProjectionStartFromEnd(toIso)
            ? maxIso(FORM_DATE_MIN, minProjectionStartFromEnd(toIso))
            : FORM_DATE_MIN;
    const maxFrom = toIso ? minIso(FORM_DATE_MAX, toIso) : FORM_DATE_MAX;

    const minTo = fromIso ? maxIso(FORM_DATE_MIN, fromIso) : FORM_DATE_MIN;
    const maxEnd = fromIso ? maxProjectionEndFromStart(fromIso) : null;
    const maxTo = maxEnd ? minIso(FORM_DATE_MAX, maxEnd) : FORM_DATE_MAX;

    return { minFrom, maxFrom, minTo, maxTo };
}
