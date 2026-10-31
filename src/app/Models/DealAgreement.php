<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DealAgreement extends Model {
    protected $guarded = [];
    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if ($record->getRawOriginal('status') === 'approved') {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }
        });

        static::deleting(function (self $record): void {
            if ($record->getRawOriginal('status') === 'approved') {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }
        });
    }

    protected $casts = ['approved_at'=>'datetime','fixed_fee'=>'decimal:2','commission_bps'=>'integer'];
    public function deal(): BelongsTo {return $this->belongsTo(Deal::class);}
}
