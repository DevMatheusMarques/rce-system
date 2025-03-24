<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
//    use HasFactory;
    protected $fillable = [
        'internal_information',
        'user_id',
        'requester_user_id',
        'status',
        'order_proof_path'
    ];

    public function user(): BelongsTo //modelo atual ($this) depende de um "user".
    {
        //(relação, chave no modelo atual, chave primária)
        return $this->belongsTo(User::class, 'user_id',   'id');
    }

    public function requesterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

}
