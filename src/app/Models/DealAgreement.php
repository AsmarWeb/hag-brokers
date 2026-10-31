<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DealAgreement extends Model {
    protected $guarded = [];
    protected $casts = ['approved_at'=>'datetime','fixed_fee'=>'decimal:2','commission_bps'=>'integer'];
    public function deal(): BelongsTo {return $this->belongsTo(Deal::class);}
}
