<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PurchaseOrderPDFService
{
    public function generate(PurchaseOrder $purchaseOrder): string
    {
        $purchaseOrder->loadMissing(['vendor', 'items', 'createdBy', 'approvedBy']);

        $verifyUrl = route('verify.quotation', $purchaseOrder->qr_token);
        $qrCode = base64_encode(QrCode::format('svg')->size(120)->margin(1)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.purchase-order.default', [
            'purchaseOrder' => $purchaseOrder,
            'qrCode' => "data:image/svg+xml;base64,{$qrCode}",
            'verifyUrl' => $verifyUrl,
        ])->setPaper('a4');

        $path = $this->path($purchaseOrder);
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function path(PurchaseOrder $purchaseOrder): string
    {
        return 'purchase-orders/'.$this->fileName($purchaseOrder);
    }

    private function fileName(PurchaseOrder $purchaseOrder): string
    {
        $number = Str::of($purchaseOrder->no_purchase_order)->replace('/', '-')->slug('-');

        return "{$purchaseOrder->id}-{$number}.pdf";
    }
}
