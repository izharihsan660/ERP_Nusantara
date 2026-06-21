<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $documentType;

    public string $documentNumber;

    public string $createdBy;

    public string $createdAt;

    public ?string $customer;

    public ?string $totalAmount;

    public string $approvalUrl;

    public string $rejectUrl;

    public ?string $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $documentType,
        string $documentNumber,
        string $createdBy,
        string $createdAt,
        ?string $customer,
        ?string $totalAmount,
        string $approvalUrl,
        string $rejectUrl,
        ?string $pdfPath = null
    ) {
        $this->documentType = $documentType;
        $this->documentNumber = $documentNumber;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->customer = $customer;
        $this->totalAmount = $totalAmount;
        $this->approvalUrl = $approvalUrl;
        $this->rejectUrl = $rejectUrl;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Approval Request - {$this->documentType} {$this->documentNumber}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval-request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->pdfPath || ! Storage::exists($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromPath(Storage::path($this->pdfPath))
                ->as("{$this->documentNumber}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
