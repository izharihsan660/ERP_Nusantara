import ReportPage, { money, StatusPill } from './ReportPage';

export default function RekapanInvoice({ data, filters, routeName, exportType, summary, customers, tipeDokumenOptions, statusPembayaranOptions, metodeOptions }) {
    return (
        <ReportPage
            title="Rekapan Invoice"
            description="Rekap invoice dan nota penjualan beserta status pembayarannya."
            data={data}
            filters={filters}
            routeName={routeName}
            exportType={exportType}
            fields={[
                { key: 'customer_id', label: 'Customer', type: 'select', options: customers, placeholder: 'Semua Customer' },
                { key: 'tipe_dokumen', label: 'Tipe Dokumen', type: 'select', options: tipeDokumenOptions, placeholder: 'Semua Tipe' },
                { key: 'status_pembayaran', label: 'Status Pembayaran', type: 'select', options: statusPembayaranOptions, placeholder: 'Semua Status' },
                { key: 'metode_pembayaran', label: 'Metode Bayar', type: 'select', options: metodeOptions, placeholder: 'Semua Metode' },
                { key: 'date_from', label: 'Dari Tanggal', type: 'date' },
                { key: 'date_to', label: 'Sampai Tanggal', type: 'date' },
            ]}
            summaryItems={[
                { label: 'Total Tagihan', value: summary.total_tagihan, type: 'money' },
                { label: 'Total Lunas', value: summary.total_lunas, type: 'money' },
                { label: 'Total Outstanding', value: summary.total_outstanding, type: 'money' },
            ]}
            columns={[
                { key: 'no_dokumen', label: 'No. Dokumen' },
                { key: 'tipe_label', label: 'Tipe' },
                { key: 'customer', label: 'Customer' },
                { key: 'no_faktur_pajak', label: 'No. Faktur Pajak' },
                { key: 'total_nilai', label: 'Total Nilai', render: (row) => money(row.total_nilai) },
                { key: 'metode_bayar', label: 'Metode Bayar' },
                { key: 'jatuh_tempo', label: 'Jatuh Tempo' },
                { key: 'status_pembayaran', label: 'Status Pembayaran', render: (row) => <StatusPill value={row.status_pembayaran}>{row.status_pembayaran_label}</StatusPill> },
                { key: 'tanggal_bayar', label: 'Tanggal Bayar' },
            ]}
        />
    );
}
