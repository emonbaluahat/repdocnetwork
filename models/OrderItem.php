<?php

namespace App\Models;

use App\Core\Model;

class OrderItem extends Model
{
    protected static string $table = 'order_items';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'order_id',
        'service_id',
        'name',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];
}