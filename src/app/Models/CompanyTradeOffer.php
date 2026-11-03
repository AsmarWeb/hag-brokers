<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTradeOffer extends Model
{
    protected $guarded = [];

    protected $casts = ['attachments' => 'array', 'snapshot' => 'array', 'valid_until' => 'immutable_date', 'version' => 'integer', 'delivery_days' => 'integer'];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function events()
    {
        return $this->hasMany(CompanyTradeOfferEvent::class);
    }

    protected static function booted(): void
    {
        static::updating(function ($o) {
            foreach (array_keys($o->getDirty()) as $field) {
                if (! in_array($field, ['status', 'version', 'updated_at'], true)) {
                    throw new \LogicException('Offer terms are immutable.');
                }
            }
        });
        static::deleting(function () {
            throw new \LogicException('Offers cannot be deleted.');
        });
    }
}
