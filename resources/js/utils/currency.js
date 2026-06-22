const numberOrZero = (amount) => {
    const number = Number(amount ?? 0);

    return Number.isFinite(number) ? number : 0;
};

export const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
}).format(numberOrZero(amount)).replace(/\u00a0/g, ' ');

export const formatRupiahInput = (amount) => formatRupiah(amount);

export const parseRupiah = (rupiahString) => {
    if (typeof rupiahString === 'number') {
        return rupiahString;
    }

    const value = String(rupiahString ?? '').replace(/[^\d,.-]/g, '').trim();

    if (! value) {
        return 0;
    }

    const hasIndonesianDecimal = value.includes(',');
    const hasDatabaseDecimal = /^-?\d+\.\d{1,2}$/.test(value);
    const normalized = hasIndonesianDecimal
        ? value.replace(/\./g, '').replace(',', '.')
        : (hasDatabaseDecimal ? value : value.replace(/\./g, ''));
    const number = Number(normalized);

    return Number.isFinite(number) ? number : 0;
};
