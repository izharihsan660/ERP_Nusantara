import ReportPage, { money, percent } from './ReportPage';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

export default function Profit({ data, filters, routeName, exportType, summary, customers, chart }) {
    return (
        <ReportPage
            title="Laporan Profit"
            description="Analisis profit dan margin dari nilai quotation."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'customer_id', label: 'Customer', type: 'select', options: customers, placeholder: 'Semua Customer' },
                { key: 'mode', label: 'Mode', type: 'select', options: [{ value: 'transaksi', label: 'Per Transaksi' }, { value: 'bulan', label: 'Per Bulan' }], placeholder: 'Per Transaksi' },
                { key: 'date_from', label: 'Dari Tanggal', type: 'date' },
                { key: 'date_to', label: 'Sampai Tanggal', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total Nilai', value: summary.total_nilai, type: 'money' },
                { label: 'Total HPP', value: summary.total_hpp, type: 'money' },
                { label: 'Total Profit', value: summary.total_profit, type: 'money' },
                { label: 'Rata-rata Margin', value: summary.rata_rata_margin, type: 'percent' },
            ]}
            chart={(
                <div className="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h2 className="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-100">Profit per Bulan</h2>
                    <div className="h-72">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={chart}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="month" />
                                <YAxis tickFormatter={(value) => `${Number(value) / 1000000}jt`} />
                                <Tooltip formatter={(value) => [`Rp ${money(value)}`, 'Profit']} />
                                <Bar dataKey="profit" fill="#0f766e" radius={[4, 4, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            )}
            columns={[
                { key: 'no_quotation', label: 'No. Quotation' },
                { key: 'customer', label: 'Customer' },
                { key: 'total_nilai', label: 'Total Nilai', render: (row) => money(row.total_nilai) },
                { key: 'total_hpp', label: 'Total HPP', render: (row) => money(row.total_hpp) },
                { key: 'profit', label: 'Profit', render: (row) => money(row.profit) },
                { key: 'margin', label: 'Margin (%)', render: (row) => percent(row.margin) },
                { key: 'tanggal', label: 'Tanggal' },
            ]}
        />
    );
}
