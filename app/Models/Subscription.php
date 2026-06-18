<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'site_id',
        'service_type',
        'price',
        'currency',
        'billing_cycle',
        'start_date',
        'renewal_date',
        'status',
        'payment_method',
        'payment_link',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'renewal_date' => 'date',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
