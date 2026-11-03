<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityModerationEvent extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['was_hidden' => 'boolean', 'is_hidden' => 'boolean', 'version' => 'integer', 'created_at' => 'immutable_datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Moderation history is immutable.');
        });
        static::deleting(function () {
            throw new \LogicException('Moderation history cannot be deleted.');
        });
    }
}
