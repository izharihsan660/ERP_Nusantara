<?php

namespace Tests\Unit;

use App\Mail\Transport\MicrosoftGraphTransport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class MicrosoftGraphTransportTest extends TestCase
{
    public function test_it_requests_token_and_sends_email_through_graph(): void
    {
        Cache::flush();

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response([
                'access_token' => 'fake-access-token',
                'expires_in' => 3600,
            ]),
            'https://graph.microsoft.com/*' => Http::response(status: 202),
        ]);

        $transport = new MicrosoftGraphTransport(
            tenantId: 'test-tenant',
            clientId: 'test-client',
            clientSecret: 'test-secret',
            sender: 'no-reply@nusantaraabadijaya.com',
        );

        $transport->send(
            (new Email)
                ->from('no-reply@nusantaraabadijaya.com')
                ->to('recipient@example.com')
                ->subject('Graph transport test')
                ->html('<p>Test email</p>'),
        );

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://login.microsoftonline.com/test-tenant/oauth2/v2.0/token'
                && $request['grant_type'] === 'client_credentials'
                && $request['scope'] === 'https://graph.microsoft.com/.default';
        });

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://graph.microsoft.com/v1.0/users/no-reply%40nusantaraabadijaya.com/sendMail'
                && $request->hasHeader('Authorization', 'Bearer fake-access-token')
                && $request['message']['toRecipients'][0]['emailAddress']['address'] === 'recipient@example.com';
        });

        Http::assertSentCount(2);
    }
}
