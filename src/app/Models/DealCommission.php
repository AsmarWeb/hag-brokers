<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DealCommission extends Model {
    protected $guarded = [];
    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if (true) {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }
        });

        static::deleting(function (self $record): void {
            if (true) {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }
        });
    }

    protected $casts = ['basis_amount'=>'decimal:2','amount'=>'decimal:2','rate_bps'=>'integer','earned_at'=>'datetime'];
    public function deal(): BelongsTo {return $this->belongsTo(Deal::class);}
    public function agreement(): BelongsTo {return $this->belongsTo(DealAgreement::class,'deal_agreement_id');}

    public function invoiceDraft(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InvoiceDraft::class, 'deal_commission_id');
    }
}
