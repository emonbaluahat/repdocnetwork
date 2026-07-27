<?php

namespace App\Models;

use App\Core\Model;

class OrderTimeline extends Model
{
    protected static string $table = 'order_timeline';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'order_id',
        'shop_id',
        'user_id',
        'action',
        'description',
        'metadata',
    ];
}