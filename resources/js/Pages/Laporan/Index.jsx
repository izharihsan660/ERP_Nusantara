import Outstanding from './Outstanding';
import Profit from './Profit';
import RekapanInvoice from './RekapanInvoice';
import RekapanPd from './RekapanPd';
import RekapanPo from './RekapanPo';
import RekapanSpb from './RekapanSpb';
import RekapanWip from './RekapanWip';

const pages = {
    'rekapan-po': RekapanPo,
    'rekapan-wip': RekapanWip,
    'rekapan-spb': RekapanSpb,
    'rekapan-invoice': RekapanInvoice,
    'rekapan-pd': RekapanPd,
    profit: Profit,
    outstanding: Outstanding,
};

export default function LaporanIndex({ activeTab = 'rekapan-po', filters = {}, ...props }) {
    const Page = pages[activeTab] ?? RekapanPo;

    return (
        <Page
            {...props}
            filters={{ ...filters, tab: activeTab }}
            routeName="laporan.index"
            exportType={activeTab}
        />
    );
}
