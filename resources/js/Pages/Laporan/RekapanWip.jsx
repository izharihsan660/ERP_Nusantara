import ReportPage, { StatusPill } from './ReportPage';

export default function RekapanWip({ data, filters, routeName, exportType, summary, tipeOptions, statusSupplyOptions }) {
    return (
        <ReportPage
            title="Rekapan WIP"
            description="Pantau WIP dari PO Customer dan status supply."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'tipe_order', label: 'Tipe Order', type: 'select', options: tipeOptions, placeholder: 'Semua Tipe' },
                { key: 'status_supply', label: 'Status Supply', type: 'select', options: statusSupplyOptions, placeholder: 'Semua Status' },
                { key: 'date_from', label: 'Dari Tanggal', type: 'date' },
                { key: 'date_to', label: 'Sampai Tanggal', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total WIP', value: summary.total_wip },
                { label: 'Belum Tersupply', value: summary.belum_tersupply },
                { label: 'Tersupply', value: summary.tersupply },
            ]}
            columns={[
                { key: 'no_wip', label: 'No. WIP' },
                { key: 'tipe', label: 'Tipe' },
                { key: 'no_quotation', label: 'No. Quotation' },
                { key: 'customer', label: 'Customer' },
                { key: 'ekspedisi', label: 'Ekspedisi' },
                { key: 'status_supply', label: 'Status Supply', render: (row) => <StatusPill value={row.status_supply}>{row.status_supply_label}</StatusPill> },
                { key: 'tanggal_input', label: 'Tanggal Input' },
                { key: 'tanggal_tersupply', label: 'Tanggal Tersupply' },
            ]}
        />
    );
}
