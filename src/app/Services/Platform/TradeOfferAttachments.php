<?php

namespace App\Services\Platform;

use App\Models\CompanyTradeOffer;
use Illuminate\Support\Facades\Storage;

final class TradeOfferAttachments
{
    public function download(CompanyTradeOffer $offer, int $file, CompanyTradeOfferService $service)
    {
        abort_unless($service->verify($offer), 409);
        $attachment = $offer->attachments[$file] ?? null;
        abort_unless($attachment && str_starts_with($attachment['path'], 'offers/'), 404);
        $disk = Storage::disk('rfq_private');
        abort_unless($disk->exists($attachment['path']), 404);
        abort_unless(hash_equals($attachment['sha256'], hash_file('sha256', $disk->path($attachment['path']))), 409);

        return $disk->download($attachment['path'], 'offer-'.$offer->id.'-attachment-'.($file + 1).'.'.pathinfo($attachment['path'], PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
