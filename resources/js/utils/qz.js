const RECEIPT_WIDTH = 48;
const ESC = '\x1B';
const GS = '\x1D';

let qzReadyPromise = null;

export function loadQzScript() {
    if (qzReadyPromise) return qzReadyPromise;

    qzReadyPromise = new Promise((resolve, reject) => {
        if (window.qz && window.qz.websocket) {
            return resolve();
        }

        const script = document.createElement('script');
        script.src = '/js/qz-tray.js';
        script.onload = () => resolve();
        script.onerror = (err) => {
            qzReadyPromise = null;
            reject(err);
        };
        document.head.appendChild(script);
    });

    return qzReadyPromise;
}

export function setupQzSecurity() {
    return loadQzScript().then(() => {
        window.qz.security.setCertificatePromise((resolve, reject) => {
            fetch('/qz/digital-certificate.txt', { cache: 'no-store' })
                .then((res) => (res.ok ? res.text() : Promise.reject(res.statusText)))
                .then((text) => {
                    if (text.indexOf('BEGIN CERTIFICATE') === -1) {
                        reject(new Error('Sertifikat QZ tidak valid'));
                        return;
                    }
                    resolve(text);
                }, reject);
        });

        window.qz.security.setSignatureAlgorithm('SHA512');
        window.qz.security.setSignaturePromise((toSign) =>
            fetch('/qz/sign', {
                method: 'POST',
                cache: 'no-store',
                headers: { 'Content-Type': 'text/plain' },
                body: toSign,
            }).then((res) => {
                if (!res.ok) {
                    return res.text().then((body) => {
                        throw new Error(`POST /qz/sign gagal: HTTP ${res.status} - ${body.slice(0, 200)}`);
                    });
                }
                return res.text();
            }).then((text) => {
                if (!/^[A-Za-z0-9+/=\s]+$/.test(text)) {
                    throw new Error(`Respons tanda tangan tidak valid (mungkin sesi login berakhir)`);
                }
                return text;
            })
        );
    });
}

export function printerSettings(defaults, localStorage) {
    return {
        host: localStorage.getItem('posPrinterHost') || defaults?.host,
        port: parseInt(localStorage.getItem('posPrinterPort') || defaults?.port || 9100, 10),
    };
}

function sanitizeText(str) {
    let out = String(str || '');
    if (out.normalize) {
        out = out.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    return out.replace(/[^\x20-\x7E]/g, '?');
}

function money(n) {
    return Number(n || 0).toLocaleString('id-ID');
}

function padLeft(text, width) {
    text = String(text);
    return (' '.repeat(Math.max(0, width - text.length)) + text).slice(-width);
}

export function buildReceiptData(receipt) {
    const W = RECEIPT_WIDTH;
    const store = receipt.store || {};
    const o = receipt.order || {};
    const lines = [];
    const raw = (t) => lines.push(t);
    const txt = (t) => lines.push(sanitizeText(t));

    raw(ESC + '@');
    raw(ESC + 'a' + '\x01');
    raw(ESC + 'E' + '\x01');
    txt(store.name || '');
    raw(ESC + 'E' + '\x00');
    if (store.address) txt(store.address);
    if (store.phone) txt(store.phone);
    raw(ESC + 'a' + '\x00');
    txt('-'.repeat(W));
    txt('No       : ' + (o.number || '-'));
    txt('Waktu    : ' + (o.date || '-'));
    txt('Kasir    : ' + (o.cashier || '-'));
    txt('Tipe     : ' + (o.type || '-'));
    if (o.table) txt('Meja     : ' + o.table);
    if (o.customer_name) txt('Pelanggan: ' + o.customer_name);
    txt('-'.repeat(W));

    (o.items || []).forEach((item) => {
        raw(ESC + 'E' + '\x01');
        txt(item.name || 'Item');
        raw(ESC + 'E' + '\x00');
        const qtyPrice = item.qty + 'x' + money(item.price);
        const sub = money(item.subtotal);
        const gap = Math.max(1, W - qtyPrice.length - sub.length - 2);
        txt('  ' + qtyPrice + ' '.repeat(gap) + sub);
        if (item.notes) txt('   * ' + item.notes);
    });

    txt('-'.repeat(W));
    txt('Subtotal'.padEnd(W - 12) + padLeft(money(o.subtotal), 12));
    if (Number(o.discount) > 0) {
        txt('Diskon'.padEnd(W - 12) + padLeft('-' + money(o.discount), 12));
    }
    txt('Tax (11%)'.padEnd(W - 12) + padLeft(money(o.tax), 12));
    raw(ESC + 'E' + '\x01');
    raw(ESC + '!' + '\x10');
    txt('TOTAL'.padEnd(W - 12) + padLeft(money(o.total), 12));
    raw(ESC + '!' + '\x00');
    raw(ESC + 'E' + '\x00');
    txt('-'.repeat(W));

    (o.payments || []).forEach((p) => {
        txt('Bayar (' + p.method + ')' + (p.reference ? ' - ' + p.reference : ''));
        txt('  Jumlah'.padEnd(W - 12) + padLeft(money(p.amount), 12));
    });

    if (o.notes) {
        txt('Catatan:');
        txt(o.notes);
    }

    raw(ESC + 'a' + '\x01');
    txt(store.footer || 'Terima kasih');
    raw(ESC + 'a' + '\x00');
    txt('');
    txt('');
    txt('');
    txt('');
    raw(GS + 'V' + '\x42' + '\x01');

    return lines.join('\n');
}

export function printRaw(data, settings, attempt = 1) {
    return window.qz.websocket.connect({ retries: 2, delay: 1 }).then(() => {
        const config = window.qz.configs.create({ host: settings.host, port: settings.port }, { encoding: 'ISO-8859-1' });
        return window.qz.print(config, [data]);
    }).catch((err) => {
        if (attempt < 3) {
            return window.qz.websocket.disconnect().catch(() => {}).then(() => new Promise((resolve) => setTimeout(resolve, 1500))).then(() => printRaw(data, settings, attempt + 1));
        }
        throw err;
    });
}

export function showPrintError(err) {
    const settings = printerSettings(null, window.localStorage);
    alert(
        'Gagal mencetak. Pastikan:\n' +
            '1. QZ Tray sudah diinstall dan berjalan di laptop ini (qz.io/download).\n' +
            '2. Laptop terhubung ke jaringan yang sama dengan printer.\n' +
            '3. IP printer benar (' + settings.host + ':' + settings.port + ').\n\nDetail: ' + err
    );
}