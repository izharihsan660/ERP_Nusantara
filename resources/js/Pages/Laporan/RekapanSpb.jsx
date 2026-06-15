import ReportPage, { StatusPill } from './ReportPage';

export default function RekapanSpb({ data, filters, routeName, exportType, summary, customers, statuses }) {
    return (
        <ReportPage
            title="Rekapan SPB"
            description="Rekap surat pengiriman barang dari alur spare part dan pallet/vendor."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'customer_id', label: 'Customer', type: 'select', options: customers, placeholder: 'Semua Customer' },
                { key: 'status', label: 'Status', type: 'select', options: statuses, placeholder: 'Semua Status' },
                { key: 'date_from', label: 'Dari Tanggal', type: 'date' },
                { key: 'date_to', label: 'Sampai Tanggal', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total SPB', value: summary.total_spb },
                { label: 'Total Item Dikirim', value: summary.total_item_dikirim },
            ]}
            columns={[
                { key: 'no_spb', label: 'No. SPB' },
                { key: 'customer', label: 'Customer' },
                { key: 'site', label: 'Site' },
                { key: 'referensi', label: 'Referensi' },
                { key: 'no_referensi', label: 'No. Referensi' },
                { key: 'ekspedisi', label: 'Ekspedisi' },
                { key: 'etd', label: 'ETD' },
                { key: 'eta', label: 'ETA' },
                { key: 'status', label: 'Status', render: (row) => <StatusPill value={row.status}>{row.status_label}</StatusPill> },
                { key: 'total_item', label: 'Total Item' },
            ]}
        />
    );
}
