import ReportPage, { money, StatusPill } from './ReportPage';

export default function Outstanding({ data, filters, routeName, exportType, summary, customers, metodeOptions }) {
    return (
        <ReportPage
            title="Outstanding Pembayaran"
            description="Invoice yang belum lunas berdasarkan jatuh tempo."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'customer_id', label: 'Customer', type: 'select', options: customers, placeholder: 'Semua Customer' },
                { key: 'metode_pembayaran', label: 'Metode Bayar', type: 'select', options: metodeOptions, placeholder: 'Semua Metode' },
                { key: 'date_from', label: 'Dari Jatuh Tempo', type: 'date' },
                { key: 'date_to', label: 'Sampai Jatuh Tempo', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total Outstanding', value: summary.total_outstanding, type: 'money' },
                { label: 'Total Invoice Belum Lunas', value: summary.total_invoice_belum_lunas },
            ]}
            columns={[
                { key: 'no_invoice', label: 'No. Invoice' },
                { key: 'customer', label: 'Customer' },
                { key: 'total_nilai', label: 'Total Nilai', render: (row) => money(row.total_nilai) },
                { key: 'sudah_dibayar', label: 'Sudah Dibayar', render: (row) => money(row.sudah_dibayar) },
                { key: 'sisa', label: 'Sisa', render: (row) => money(row.sisa) },
                { key: 'metode_bayar', label: 'Metode Bayar' },
                { key: 'jatuh_tempo', label: 'Jatuh Tempo', render: (row) => <StatusPill value={row.due_badge}>{row.jatuh_tempo ?? '-'}</StatusPill> },
                { key: 'hari_tersisa', label: 'Hari Tersisa', render: (row) => row.hari_tersisa ?? '-' },
            ]}
        />
    );
}
