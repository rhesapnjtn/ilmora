<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'plan',
        'billing_cycle',
        'status',
        'starts_at',
        'expires_at',
        'amount',
        'currency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'expires_at' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The `plan` column holds a denormalized plan name, so the relation that
     * exposes the related model is named after the entitlement it represents.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function planRecord(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
