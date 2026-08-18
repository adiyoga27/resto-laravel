export function money(value, currency = 'Rp ') {
    const n = Number(value ?? 0);
    return currency + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

export function moneyFull(value) {
    return 'Rp ' + Number(value ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

export function dateTime(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

export function dateOnly(value) {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

export function titleize(str) {
    if (!str) return '';
    return String(str).replace(/[-_]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}