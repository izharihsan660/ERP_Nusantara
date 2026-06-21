import { Activity } from 'lucide-react';
import { formatRupiah } from '@/utils/currency';

function formatValue(value, type = 'number') {
    if (type === 'money') {
        return formatRupiah(value);
    }

    if (type === 'percent') {
        return `${Number(value ?? 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`;
    }

    return Number(value ?? 0).toLocaleString('id-ID');
}

export default function SummaryCards({ items = [] }) {
    return (
        <div className="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
            {items.map((item) => (
                <div key={item.label} className="relative rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 text-[hsl(var(--card-foreground))]">
                    <Activity className="absolute right-6 top-6 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                    <div className="pr-8 text-sm font-medium text-[hsl(var(--muted-foreground))]">{item.label}</div>
                    <div className="mt-3 text-2xl font-bold tracking-tight">
                        {formatValue(item.value, item.type)}
                    </div>
                    {item.trend && (
                        <div className={`mt-2 text-xs ${Number(item.trend) >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {Number(item.trend) >= 0 ? '+' : ''}{item.trend}% dari periode lalu
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
