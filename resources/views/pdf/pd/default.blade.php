<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Permintaan Dana - {{ $pd->no_pd }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.4; padding: 20mm; }
        .header { display: flex; justify-content: space-between; margin-bottom: 15mm; }
        .header-left img { height: 50px; }
        .header-right { text-align: right; font-size: 10pt; }
        .header-right h2 { font-size: 14pt; margin-bottom: 3px; }
        .date { margin-bottom: 10mm; text-align: left; }
        .recipient { margin-bottom: 10mm; }
        .title-section { margin-bottom: 8mm; }
        .title-section h3 { font-size: 12pt; margin-bottom: 5px; }
        .intro { margin-bottom: 8mm; }
        .detail-row { margin-bottom: 5px; }
        .detail-row strong { display: inline-block; width: 100px; }
        table { width: 100%; border-collapse: collapse; margin: 10mm 0; font-size: 10pt; }
        table th, table td { border: 1px solid #000; padding: 6px 8px; }
        table th { background-color: #f3f4f6; font-weight: bold; text-align: left; }
        table td.right { text-align: right; }
        table tfoot td { font-weight: bold; background-color: #f9fafb; }
        .footer-text { margin: 8mm 0; }
        .signature-section { margin-top: 15mm; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .label { margin-bottom: 50px; }
        .signature-box .name { border-top: 1px solid #000; padding-top: 5px; display: inline-block; min-width: 150px; }
        .qr-code { position: absolute; bottom: 20mm; right: 20mm; }
        .qr-code img { width: 80px; height: 80px; }
        .attachment-page { page-break-before: always; }
        .attachment-page h3 { margin-bottom: 10mm; }
        .attachment-page img { max-width: 100%; height: auto; margin-bottom: 10mm; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('images/logo-naj.png')))
                <img src="{{ public_path('images/logo-naj.png') }}" alt="Logo NAJ">
            @else
                <h2>PT. Nusantara Abadi Jaya</h2>
            @endif
        </div>
        <div class="header-right">
            <h2>PT. NUSANTARA ABADI JAYA</h2>
            <p>Jl. Urip Sumoharjo No. 123, Makassar</p>
            <p>Telp: (0411) 123456</p>
        </div>
    </div>

    <div class="date">
        Makassar, {{ \Carbon\Carbon::parse($pd->created_at)->isoFormat('DD MMMM YYYY') }}
    </div>

    <div class="recipient">
        <div>Kepada Yth,</div>
        <div><strong>{{ $managerName ?? 'Ibu Ratih Tirana' }}</strong></div>
        <div>Di- Tempat</div>
    </div>

    <div class="title-section">
        <h3>Permohonan Dana</h3>
        <div>No: <strong>{{ $pd->no_pd }}</strong></div>
    </div>

    <div class="intro">
        Dengan ini kami mohon untuk dapat dibayarkan, pengeluaran berikut:
    </div>

    <div class="detail-row">
        <strong>Tujuan:</strong> {{ $pd->tujuan }}
    </div>

    @if($pd->items->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">NO. PO</th>
                <th style="width: 15%;">NO. PART</th>
                <th style="width: 35%;">DESCRIPTION</th>
                <th style="width: 10%;" class="right">QTY</th>
                <th style="width: 15%;" class="right">HARGA</th>
                <th style="width: 15%;" class="right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pd->items as $item)
            <tr>
                <td>{{ $item->no_po ?? '-' }}</td>
                <td>{{ $item->no_part ?? '-' }}</td>
                <td>{{ $item->description }}</td>
                <td class="right">{{ $item->qty }}</td>
                <td class="right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="right">TOTAL</td>
                <td class="right">Rp {{ number_format($pd->total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer-text">
        <p>Mohon dana dapat segera di proses.</p>
        <p>Transfer ke rekening <strong>{{ $pd->rekening_tujuan }}</strong></p>
        @if($pd->bank_tujuan)
        <p>BANK <strong>{{ strtoupper($pd->bank_tujuan) }}</strong></p>
        @endif
        <p>Plan pembayaran <strong>{{ \Carbon\Carbon::parse($pd->plan_pembayaran)->isoFormat('DD MMMM YYYY') }}</strong></p>
        <p>Terima Kasih.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="label">Dibuat Oleh,</div>
            <div class="name">{{ $pd->createdBy->name ?? '' }}</div>
        </div>
        <div class="signature-box">
            <div class="label">Mengetahui,</div>
            <div class="name">{{ $pd->approvedBy->name ?? '' }}</div>
        </div>
    </div>

    @if($pd->qr_token)
    <div class="qr-code">
        {!! QrCode::size(80)->generate(route('verify', ['token' => $pd->qr_token])) !!}
    </div>
    @endif

    @if($pd->foto_nota || $pd->foto_barang)
    <div class="attachment-page">
        <h3>Lampiran</h3>
        
        @if($pd->foto_nota && file_exists(storage_path('app/' . $pd->foto_nota)))
            <div>
                <h4>Foto Nota:</h4>
                @if(Str::endsWith($pd->foto_nota, '.pdf'))
                    <p>Lihat file PDF terlampir: {{ basename($pd->foto_nota) }}</p>
                @else
                    <img src="{{ storage_path('app/' . $pd->foto_nota) }}" alt="Foto Nota">
                @endif
            </div>
        @endif

        @if($pd->foto_barang && file_exists(storage_path('app/' . $pd->foto_barang)))
            <div>
                <h4>Foto Barang:</h4>
                @if(Str::endsWith($pd->foto_barang, '.pdf'))
                    <p>Lihat file PDF terlampir: {{ basename($pd->foto_barang) }}</p>
                @else
                    <img src="{{ storage_path('app/' . $pd->foto_barang) }}" alt="Foto Barang">
                @endif
            </div>
        @endif
    </div>
    @endif
</body>
</html>
