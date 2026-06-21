@php
    $tanggal = $pd->created_at ? \Carbon\Carbon::parse($pd->created_at) : now();
    $grandTotal = (float) $pd->items->sum(fn ($item) => (float) $item->total);
    $status = $pd->status?->value ?? $pd->status;
@endphp
<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
@page{margin:1.5cm}body{font-family:Arial,sans-serif;font-size:11px;color:#111;line-height:1.35}table{width:100%;border-collapse:collapse}td,th{padding:4px 8px;vertical-align:top}.header td{border-bottom:1px solid #ccc}.company{font-size:13px;font-weight:bold}.items th,.items td{border:1px solid #ccc}.items th{background:#f5f5f5;text-align:center;font-weight:bold}.right{text-align:right}.center{text-align:center}.sign td{border:1px solid #ccc;height:95px;text-align:center;vertical-align:bottom}.qr{position:fixed;right:0;bottom:0;text-align:center}.page-break{page-break-before:always}.attachment{max-width:100%;max-height:24cm}
</style></head><body>
<table class="header"><tr><td style="width:70px;font-weight:bold">NAJ</td><td><div class="company">PT. NUSANTARA ABADI JAYA</div><div>JL.Wiyata No.81 RT23, Kalimantan Timur</div></td></tr></table>
<p>Makassar, {{ $tanggal->translatedFormat('d F Y') }}</p>
<p>Kepada Yth,<br>Ibu Ratih Tirana<br>Di- Tempat</p>
<p><strong>Permohonan Dana</strong><br>No: {{ $pd->no_pd }}</p>
<p>Dengan ini kami mohon untuk dapat dibayarkan, pengeluaran berikut:</p>
<p><strong>Tujuan :</strong> {{ $pd->tujuan ?? '-' }}</p>
<table class="items"><thead><tr><th>NO. PART</th><th>DESCRIPTION</th><th style="width:45px">QTY</th><th style="width:95px">HARGA</th><th style="width:105px">TOTAL</th><th>REMARKS</th></tr></thead><tbody>@foreach($pd->items as $item)<tr><td>{{ $item->no_part }}</td><td>{{ $item->description }}</td><td class="center">{{ number_format((float) $item->qty,0,',','.') }}</td><td class="right">@rupiah($item->harga)</td><td class="right">@rupiah($item->total)</td><td>{{ $item->remarks }}</td></tr>@endforeach</tbody></table>
<p style="text-align:right"><strong>Total: @rupiah($grandTotal)</strong></p>
<p>Mohon dana dapat segera di proses.<br>Transfer ke rekening {{ $pd->rekening_tujuan ?? '-' }}<br>BANK {{ $pd->bank_tujuan ?? '-' }}<br>Plan pembayaran {{ $pd->plan_pembayaran?->translatedFormat('d F Y') ?? '-' }}</p>
<p>Terima Kasih.</p>
<table class="sign"><tr><td>Dibuat Oleh,<br><br>{{ $pd->createdBy?->name ?? '-' }}</td><td>Mengetahui,<br><br>{{ $pd->approvedBy?->name ?? '-' }}</td></tr></table>
@if (($pd->qr_token || isset($qrCode)) && $status === 'APPROVED')<div class="qr">@isset($qrCode)<img src="{{ $qrCode }}" style="width:80px;height:80px" alt="QR Code">@else{!! QrCode::size(80)->generate(url('/verify/' . $pd->qr_token)) !!}@endisset</div>@endif
@foreach($pd->documents as $document)
    @if($document->file_path)
        <div class="page-break"><strong>Attachment: {{ $document->nama_file }}</strong><br><img class="attachment" src="{{ storage_path('app/private/' . $document->file_path) }}" alt="Attachment"></div>
    @endif
@endforeach
</body></html>
