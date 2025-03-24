<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'cnpj',
        'corporate_name', //razão social
        'trade_name', //nome_fantasia
        'email',
        'cep',
        'phone',
        'address_city',
        'address_state',
    ];
}
