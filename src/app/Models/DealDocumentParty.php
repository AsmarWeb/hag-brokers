<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealDocumentParty extends Model
{
    protected $guarded = [];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if ($record->getRawOriginal('status') === 'signed') {
                throw new \LogicException('Recorded party signatures are immutable.');
            }

            $document = $record->document;
            $identityFields = ['role_key', 'company_name', 'contact_name', 'email', 'signing_order'];

            if ($document?->locked_at !== null && $record->isDirty($identityFields)) {
                throw new \LogicException('Party details are locked after document preparation.');
            }
        });

        static::deleting(function (self $record): void {
            if ($record->document?->locked_at !== null || $record->status === 'signed') {
                throw new \LogicException('Prepared document parties cannot be removed.');
            }
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(DealDocument::class, 'deal_document_id');
    }

    public function signatureReceivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signature_received_by');
    }
}
