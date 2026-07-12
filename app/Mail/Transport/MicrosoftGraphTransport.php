<?php

namespace App\Mail\Transport;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class MicrosoftGraphTransport extends AbstractTransport
{
    private const GRAPH_SCOPE = 'https://graph.microsoft.com/.default';

    public function __construct(
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $sender,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('Microsoft Graph transport hanya mendukung Symfony Email messages.');
        }

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post($this->sendMailUrl(), [
                'message' => $this->graphMessage($email),
                'saveToSentItems' => true,
            ]);

        if ($response->failed()) {
            $this->throwGraphException('sendMail', $response);
        }
    }

    private function accessToken(): string
    {
        $expiresAt = now();

        return Cache::remember($this->tokenCacheKey(), $expiresAt, function () use ($expiresAt) {
            $response = Http::asForm()->post($this->tokenUrl(), [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => self::GRAPH_SCOPE,
            ]);

            if ($response->failed()) {
                $this->throwGraphException('access token', $response);
            }

            $token = $response->json('access_token');
            $expiresIn = (int) $response->json('expires_in', 3600);

            if (! is_string($token) || $token === '') {
                throw new TransportException('Microsoft Graph token response tidak berisi access_token.');
            }

            $expiresAt->addSeconds(max(1, $expiresIn - 60));

            return $token;
        });
    }

    private function graphMessage(Email $email): array
    {
        $htmlBody = $email->getHtmlBody();
        $isHtml = is_string($htmlBody) && $htmlBody !== '';

        return array_filter([
            'subject' => $email->getSubject() ?? '',
            'body' => [
                'contentType' => $isHtml ? 'HTML' : 'Text',
                'content' => $isHtml ? $htmlBody : (string) $email->getTextBody(),
            ],
            'toRecipients' => $this->recipients($email->getTo()),
            'ccRecipients' => $this->recipients($email->getCc()),
            'bccRecipients' => $this->recipients($email->getBcc()),
            'attachments' => $this->attachments($email->getAttachments()),
        ], static fn (mixed $value) => $value !== []);
    }

    private function recipients(array $addresses): array
    {
        return array_map(static fn (Address $address) => [
            'emailAddress' => array_filter([
                'address' => $address->getAddress(),
                'name' => $address->getName(),
            ], static fn (?string $value) => filled($value)),
        ], $addresses);
    }

    private function attachments(array $attachments): array
    {
        return array_map(static fn (DataPart $attachment) => array_filter([
            '@odata.type' => '#microsoft.graph.fileAttachment',
            'name' => $attachment->getFilename() ?? 'attachment',
            'contentType' => $attachment->getContentType(),
            'contentBytes' => base64_encode($attachment->bodyToString()),
            'isInline' => $attachment->getDisposition() === 'inline',
            'contentId' => $attachment->hasContentId() ? $attachment->getContentId() : null,
        ], static fn (mixed $value) => $value !== null), $attachments);
    }

    private function tokenUrl(): string
    {
        return 'https://login.microsoftonline.com/'.rawurlencode($this->tenantId).'/oauth2/v2.0/token';
    }

    private function sendMailUrl(): string
    {
        return 'https://graph.microsoft.com/v1.0/users/'.rawurlencode($this->sender).'/sendMail';
    }

    private function tokenCacheKey(): string
    {
        return 'mail.microsoft_graph.token.'.hash('sha256', $this->tenantId.'|'.$this->clientId.'|'.$this->sender);
    }

    private function throwGraphException(string $operation, Response $response): never
    {
        Log::error('Microsoft Graph mail request failed.', [
            'operation' => $operation,
            'status' => $response->status(),
            'response' => $response->body(),
            'sender' => $this->sender,
        ]);

        throw new TransportException("Microsoft Graph {$operation} request gagal dengan status {$response->status()}.");
    }
}
