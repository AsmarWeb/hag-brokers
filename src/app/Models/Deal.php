<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Deal extends Model {
    protected $guarded = [];
    protected $casts = ['deal_amount'=>'decimal:2','supplier_cost'=>'decimal:2','platform_revenue'=>'decimal:2','approved_at'=>'datetime'];
    public function customer(): BelongsTo {return $this->belongsTo(Customer::class);}
    public function quotation(): BelongsTo {return $this->belongsTo(Quotation::class);}
    public function opportunity(): BelongsTo {return $this->belongsTo(Opportunity::class);}
    public function agreements(): HasMany {return $this->hasMany(DealAgreement::class);}
    public function commissions(): HasMany {return $this->hasMany(DealCommission::class);}
}
