const numberOrZero = (amount) => {
    const number = Number(amount ?? 0);

    return Number.isFinite(number) ? number : 0;
};

export const formatRupiah = (amount, options = {}) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: options.minimumFractionDigits ?? 2,
    maximumFractionDigits: options.maximumFractionDigits ?? 2,
}).format(numberOrZero(amount)).replace(/\u00a0/g, ' ');

export const formatRupiahInput = (amount) => formatRupiah(amount, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});

export const parseRupiah = (rupiahString) => {
    if (typeof rupiahString === 'number') {
        return rupiahString;
    }

    const value = String(rupiahString ?? '').replace(/[^\d,.-]/g, '').trim();

    if (! value) {
        return 0;
    }

    const normalized = value.includes(',')
        ? value.replace(/\./g, '').replace(',', '.')
        : value.replace(/\./g, '');
    const number = Number(normalized);

    return Number.isFinite(number) ? number : 0;
};
