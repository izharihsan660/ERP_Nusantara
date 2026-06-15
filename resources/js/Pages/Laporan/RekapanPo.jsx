import ReportPage, { money, StatusPill } from './ReportPage';

export default function RekapanPo({ data, filters, routeName, exportType, summary, customers, statuses }) {
    return (
        <ReportPage
            title="Rekapan PO"
            description="Rekap PO Customer yang masuk dari alur quotation."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'customer_id', label: 'Customer', type: 'select', options: customers, placeholder: 'Semua Customer' },
                { key: 'status', label: 'Status PO', type: 'select', options: statuses, placeholder: 'Semua Status' },
                { key: 'date_from', label: 'Dari Tanggal', type: 'date' },
                { key: 'date_to', label: 'Sampai Tanggal', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total PO', value: summary.total_po },
                { label: 'Total Nilai', value: summary.total_nilai, type: 'money' },
                { label: 'Total Profit', value: summary.total_profit, type: 'money' },
            ]}
            columns={[
                { key: 'no_quotation', label: 'No. Quotation' },
                { key: 'customer', label: 'Customer' },
                { key: 'no_po_customer', label: 'No. PO Customer' },
                { key: 'metode_bayar', label: 'Metode Bayar' },
                { key: 'total_nilai', label: 'Total Nilai', render: (row) => money(row.total_nilai) },
                { key: 'total_hpp', label: 'Total HPP', render: (row) => money(row.total_hpp) },
                { key: 'profit', label: 'Profit', render: (row) => money(row.profit) },
                { key: 'status_po', label: 'Status PO', render: (row) => <StatusPill value={row.status_po}>{row.status_po_label}</StatusPill> },
                { key: 'tanggal_po', label: 'Tanggal PO' },
            ]}
        />
    );
}
