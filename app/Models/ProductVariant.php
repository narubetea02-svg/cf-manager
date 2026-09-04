<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'code', 'cost', 'price', 'quantity', 'weight', 'is_active'];
    
    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    public function product() { return $this->belongsTo(Product::class); }
}
