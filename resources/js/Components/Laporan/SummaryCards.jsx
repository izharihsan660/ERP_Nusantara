function formatValue(value, type = 'number') {
    if (type === 'money') {
        return `Rp ${Number(value ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
    }

    if (type === 'percent') {
        return `${Number(value ?? 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`;
    }

    return Number(value ?? 0).toLocaleString('id-ID');
}

export default function SummaryCards({ items = [] }) {
    return (
        <div className="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            {items.map((item) => (
                <div key={item.label} className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="text-sm text-slate-500 dark:text-slate-400">{item.label}</div>
                    <div className="mt-2 text-2xl font-semibold tracking-normal text-slate-950 dark:text-white">
                        {formatValue(item.value, item.type)}
                    </div>
                </div>
            ))}
        </div>
    );
}
