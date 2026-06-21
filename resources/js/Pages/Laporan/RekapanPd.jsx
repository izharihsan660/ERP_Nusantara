import ReportPage, { money, StatusPill } from './ReportPage';

export default function RekapanPd({ data, filters, routeName, exportType, summary, statuses }) {
    return (
        <ReportPage
            title="Rekapan PD"
            description="Rekap permintaan dana dan realisasinya."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'status', label: 'Status', type: 'select', options: statuses, placeholder: 'Semua Status' },
                { key: 'date_from', label: 'Dari Tanggal', type: 'date' },
                { key: 'date_to', label: 'Sampai Tanggal', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total PD', value: summary.total_pd },
                { label: 'Total Nominal', value: summary.total_nominal, type: 'money' },
                { label: 'Total Realisasi', value: summary.total_realisasi, type: 'money' },
            ]}
            columns={[
                { key: 'no_pd', label: 'No. PD' },
                { key: 'tujuan', label: 'Tujuan' },
                { key: 'nominal', label: 'Nominal', render: (row) => money(row.nominal) },
                { key: 'jumlah_realisasi', label: 'Jumlah Realisasi', render: (row) => money(row.jumlah_realisasi) },
                { key: 'status', label: 'Status', render: (row) => <StatusPill value={row.status}>{row.status_label}</StatusPill> },
                { key: 'dibuat_oleh', label: 'Dibuat oleh' },
                { key: 'diapprove_oleh', label: 'Diapprove oleh' },
                { key: 'tanggal', label: 'Tanggal' },
            ]}
        />
    );
}
