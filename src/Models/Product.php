<?php

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;
class Product extends Model
{
    protected static string $table = 'products';

    protected static array $searchable = ['name', 'ncm', 'notes'];

    protected static array $fillable = [
        'name', 'product_type', 'usage_segment', 'unit', 'capacity',
        'unit_price', 'standard_price', 'ncm', 'cylinder_type_id', 'notes', 'is_active',
    ];
}