import FormRow from '@/Components/Form/FormRow';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function Create() {
    const [items, setItems] = useState([
        { no_part: '', description: '', qty: '', harga: '', total: 0, remarks: '' }
    ]);
    
    const form = useForm({
        tujuan: '',
        rekening_tujuan: '',
        bank_tujuan: '',
        plan_pembayaran: '',
        keterangan: '',
        attachments: [],
    });

    const addItem = () => {
        setItems([...items, { no_part: '', description: '', qty: '', harga: '', total: 0, remarks: '' }]);
    };

    const removeItem = (index) => {
        setItems(items.filter((_, i) => i !== index));
    };

    const updateItem = (index, field, value) => {
        const newItems = [...items];
        newItems[index][field] = value;
        
        if (field === 'qty' || field === 'harga') {
            const qty = parseFloat(newItems[index].qty) || 0;
            const harga = parseFloat(newItems[index].harga) || 0;
            newItems[index].total = qty * harga;
        }
        
        setItems(newItems);
    };

    const grandTotal = items.reduce((sum, item) => sum + item.total, 0);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const data = {
            ...form.data,
            items: items.filter(item => item.description),
        };
        
        router.post(route('permintaan-dana.store'), data, {
            onSuccess: () => form.reset(),
        });
    };

    return (
        <AppLayout>
            <Head title="Buat Permintaan Dana" />
            
            <PageHeader
                title="Buat Permintaan Dana"
                description="Buat dokumen permintaan pencairan dana baru"
                backHref={route('permintaan-dana.index')}
            />

            <div className="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormRow label="Tujuan" required error={form.errors.tujuan}>
                            <Input
                                value={form.data.tujuan}
                                onChange={(e) => form.setData('tujuan', e.target.value)}
                                placeholder="Nama vendor/toko"
                            />
                        </FormRow>
                        <FormRow label="Rekening Tujuan" required error={form.errors.rekening_tujuan}>
                            <Input
                                value={form.data.rekening_tujuan}
                                onChange={(e) => form.setData('rekening_tujuan', e.target.value)}
                                placeholder="Nomor rekening"
                            />
                        </FormRow>
                        <FormRow label="Bank Tujuan" error={form.errors.bank_tujuan}>
                            <Input
                                value={form.data.bank_tujuan}
                                onChange={(e) => form.setData('bank_tujuan', e.target.value)}
                                placeholder="Nama bank (opsional)"
                            />
                        </FormRow>
                        <FormRow label="Plan Pembayaran" required error={form.errors.plan_pembayaran}>
                            <Input
                                type="date"
                                value={form.data.plan_pembayaran}
                                onChange={(e) => form.setData('plan_pembayaran', e.target.value)}
                            />
                        </FormRow>
                    </div>

                    <FormRow label="Keterangan" error={form.errors.keterangan}>
                        <Textarea
                            value={form.data.keterangan}
                            onChange={(e) => form.setData('keterangan', e.target.value)}
                            rows={3}
                        />
                    </FormRow>

                    {/* Items Section */}
                    <div>
                        <div className="mb-3 flex items-center justify-between">
                            <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Item *</h3>
                            <Button type="button" size="sm" onClick={addItem}>
                                <Plus className="h-4 w-4" />Tambah Item
                            </Button>
                        </div>
                        
                        <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                            <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                <thead className="bg-slate-50 dark:bg-slate-800">
                                    <tr>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300 w-40">NO. PART</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300">DESCRIPTION *</th>
                                        <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-24">QTY *</th>
                                        <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-32">HARGA *</th>
                                        <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-32">TOTAL</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300 w-40">REMARKS</th>
                                        <th className="px-3 py-2 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                                    {items.map((item, index) => (
                                        <tr key={index}>
                                            <td className="px-3 py-2">
                                                <Input
                                                    value={item.no_part}
                                                    onChange={(e) => updateItem(index, 'no_part', e.target.value)}
                                                    className="text-sm"
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <Input
                                                    value={item.description}
                                                    onChange={(e) => updateItem(index, 'description', e.target.value)}
                                                    required
                                                    className="text-sm"
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={item.qty}
                                                    onChange={(e) => updateItem(index, 'qty', e.target.value)}
                                                    required
                                                    className="text-right text-sm"
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={item.harga}
                                                    onChange={(e) => updateItem(index, 'harga', e.target.value)}
                                                    required
                                                    className="text-right text-sm"
                                                />
                                            </td>
                                            <td className="px-3 py-2 text-right text-sm font-semibold text-slate-900 dark:text-white">
                                                {formatRupiah(item.total)}
                                            </td>
                                            <td className="px-3 py-2">
                                                <Input
                                                    value={item.remarks}
                                                    onChange={(e) => updateItem(index, 'remarks', e.target.value)}
                                                    className="text-sm"
                                                />
                                            </td>
                                            <td className="px-3 py-2 text-center">
                                                {items.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => removeItem(index)}
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-600" />
                                                    </Button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot className="bg-slate-50 dark:bg-slate-800">
                                    <tr>
                                        <td colSpan="4" className="px-3 py-2 text-right text-sm font-semibold text-slate-900 dark:text-white">
                                            GRAND TOTAL
                                        </td>
                                        <td className="px-3 py-2 text-right text-sm font-bold text-slate-900 dark:text-white">
                                            {formatRupiah(grandTotal)}
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        {form.errors.items && (
                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{form.errors.items}</p>
                        )}
                    </div>

                    {/* Attachments */}
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormRow label="Foto Nota" conditionalNote="JPG/PNG/PDF, maks 5MB" error={form.errors['attachments.0']}>
                            <Input
                                type="file"
                                accept="image/jpeg,image/png,application/pdf"
                                onChange={(e) => form.setData('attachments', [e.target.files[0], form.data.attachments[1]].filter(Boolean))}
                            />
                        </FormRow>
                        <FormRow label="Foto Barang" conditionalNote="JPG/PNG/PDF, maks 5MB" error={form.errors['attachments.1']}>
                            <Input
                                type="file"
                                accept="image/jpeg,image/png,application/pdf"
                                onChange={(e) => form.setData('attachments', [form.data.attachments[0], e.target.files[0]].filter(Boolean))}
                            />
                        </FormRow>
                    </div>

                    <div className="flex flex-col justify-end gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => router.visit(route('permintaan-dana.index'))}>
                            Batal
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            Simpan
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
