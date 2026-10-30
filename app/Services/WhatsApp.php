<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp
 *
 * Tells the office on WhatsApp (Meta's Cloud API) when something comes in from the public site: every user who may
 * handle enquiries (enquiries.manage) and has a phone, plus any extra numbers in WHATSAPP_TO. Off until WHATSAPP_TOKEN
 * and WHATSAPP_PHONE_ID are set. A plain text message only reaches a number that has written to ours in the last 24
 * hours; set WHATSAPP_TEMPLATE to an approved template to reach it any time.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Services
 */
class WhatsApp
{
    /**
     * Tell the office about a new enquiry: the listing's code, the visitor's name and phone, a link to the enquiry
     *
     * @param  Enquiry  $enquiry
     * @return void
     */
    public function enquiry(Enquiry $enquiry): void
    {
        $values = [
            $enquiry->enquirable?->code ?? '-',
            $enquiry->name,
            $enquiry->phone,
            route('backend.enquiries.show', $enquiry),
        ];

        // the template's body takes {{1}} code, {{2}} name, {{3}} phone, {{4}} link
        $template = config('services.whatsapp.template');
        $message = $template
            ? ['type' => 'template', 'template' => [
                'name' => $template,
                'language' => ['code' => config('services.whatsapp.language')],
                'components' => [[
                    'type' => 'body',
                    'parameters' => array_map(fn ($value) => ['type' => 'text', 'text' => $value], $values),
                ]],
            ]]
            : ['type' => 'text', 'text' => ['body' => "New enquiry {$values[0]}\n{$values[1]} {$values[2]}\n{$values[3]}"]];

        $this->send($message);
    }

    /**
     * Who gets told: the phones of the users who handle enquiries, then WHATSAPP_TO, in international form (66…)
     *
     * @return array<int, string>
     */
    public function recipients(): array
    {
        $phones = User::permission('enquiries.manage')->whereNotNull('phone')->pluck('phone')
            ->merge(explode(',', (string) config('services.whatsapp.to')));

        return $phones->map(fn ($phone) => preg_replace('/^0/', '66', preg_replace('/\D/', '', $phone)))
            ->filter(fn ($phone) => strlen($phone) >= 10)
            ->unique()->values()->all();
    }

    /**
     * Send a message to every recipient; a refusal is logged, never thrown at the visitor
     *
     * @param  array<string, mixed>  $message
     * @return void
     */
    protected function send(array $message): void
    {
        $config = config('services.whatsapp');

        if (! $config['token'] || ! $config['phone_id']) {
            return;
        }

        foreach ($this->recipients() as $number) {
            $response = Http::withToken($config['token'])->timeout(10)
                ->post("https://graph.facebook.com/{$config['version']}/{$config['phone_id']}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                ] + $message);

            if ($response->failed()) {
                Log::warning("WhatsApp to {$number} failed: ".$response->body());
            }
        }
    }
}
