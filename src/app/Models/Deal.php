<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Deal extends Model {
    protected $guarded = [];
    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if ($record->getRawOriginal('status') === 'approved') {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }

            $financialFields = [
                'customer_id',
                'quotation_id',
                'opportunity_id',
                'collection_model',
                'currency',
                'deal_amount',
                'supplier_cost',
                'platform_revenue',
            ];

            if ($record->isDirty($financialFields)
                && $record->agreements()->exists()) {
                throw new \LogicException(
                    'Deal financial terms are locked after agreement creation.'
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

    protected $casts = ['deal_amount'=>'decimal:2','supplier_cost'=>'decimal:2','platform_revenue'=>'decimal:2','approved_at'=>'datetime'];
    public function customer(): BelongsTo {return $this->belongsTo(Customer::class);}
    public function quotation(): BelongsTo {return $this->belongsTo(Quotation::class);}
    public function opportunity(): BelongsTo {return $this->belongsTo(Opportunity::class);}
    public function agreements(): HasMany {return $this->hasMany(DealAgreement::class);}
    public function commissions(): HasMany {return $this->hasMany(DealCommission::class);}
}
