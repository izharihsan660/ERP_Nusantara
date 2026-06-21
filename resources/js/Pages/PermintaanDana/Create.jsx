import FormRow from '@/Components/Form/FormRow';
import PageHeader from '@/Components/PageHeader';
import { LoadingButtonContent } from '@/Components/UiPolish';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah, parseRupiah } from '@/utils/currency';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus, Save, Send, Trash2 } from 'lucide-react';
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
        
        if (field === 'qty' || field === 'harga') {
            if (field === 'harga') {
                newItems[index][field] = value;
                const qty = parseFloat(newItems[index].qty) || 0;
                const harga = parseRupiah(value);
                newItems[index].total = qty * harga;
            } else {
                newItems[index][field] = value;
                const qty = parseFloat(value) || 0;
                const harga = parseRupiah(newItems[index].harga) || 0;
                newItems[index].total = qty * harga;
            }
        } else {
            newItems[index][field] = value;
        }
        
        setItems(newItems);
    };

    const grandTotal = items.reduce((sum, item) => sum + item.total, 0);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const data = {
            ...form.data,
            items: items
                .filter(item => item.description)
                .map(item => ({
                    ...item,
                    harga: parseRupiah(item.harga),
                })),
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
                actions={<Button type="button" variant="outline" onClick={() => router.visit(route('permintaan-dana.index'))}>Kembali</Button>}
            />

            <form onSubmit={handleSubmit} className="space-y-6">
                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                    <h2 className="mb-4 text-base font-semibold text-[hsl(var(--foreground))]">Info PD</h2>
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
                </section>

                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                        <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 className="text-base font-semibold text-[hsl(var(--foreground))]">Item <span className="text-red-600">*</span></h3>
                            <Button type="button" size="sm" onClick={addItem}>
                                <Plus className="h-4 w-4" />Tambah Item
                            </Button>
                        </div>
                        
                        <div className="overflow-x-auto rounded-lg border border-[hsl(var(--border))]">
                            <table className="min-w-full divide-y divide-[hsl(var(--border))]">
                                <thead className="bg-[hsl(var(--muted))]/60">
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
                                <tbody className="divide-y divide-[hsl(var(--border))] bg-[hsl(var(--card))]">
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
                                                    type="text"
                                                    value={item.harga}
                                                    onChange={(e) => {
                                                        const rawValue = e.target.value;
                                                        // Allow typing numbers and formatting characters
                                                        if (/^[0-9.,\s]*$/.test(rawValue)) {
                                                            updateItem(index, 'harga', rawValue);
                                                        }
                                                    }}
                                                    onBlur={(e) => {
                                                        // Format on blur
                                                        const parsed = parseRupiah(e.target.value);
                                                        if (parsed > 0) {
                                                            updateItem(index, 'harga', formatRupiah(parsed).replace('Rp', '').trim());
                                                        }
                                                    }}
                                                    required
                                                    className="text-right text-sm"
                                                    placeholder="0"
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
                                <tfoot className="bg-[hsl(var(--muted))]/60">
                                    <tr>
                                        <td colSpan="4" className="px-3 py-3 text-right text-sm font-semibold text-[hsl(var(--foreground))]">
                                            GRAND TOTAL
                                        </td>
                                        <td className="px-3 py-3 text-right text-sm font-bold text-[hsl(var(--foreground))]">
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
                </section>

                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                    <h2 className="mb-4 text-base font-semibold text-[hsl(var(--foreground))]">Attachment</h2>
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
                </section>

                    <div className="sticky bottom-4 z-10 flex flex-col justify-end gap-2 rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))]/95 p-3 shadow-lg backdrop-blur sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => router.visit(route('permintaan-dana.index'))}>
                            Batal
                        </Button>
                        <Button type="submit" variant="secondary" disabled={form.processing}>
                            <Save className="h-4 w-4" />Simpan Draft
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            <Send className="h-4 w-4" /><LoadingButtonContent loading={form.processing}>Submit ke Manager</LoadingButtonContent>
                        </Button>
                    </div>
                </form>
        </AppLayout>
    );
}
