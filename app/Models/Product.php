<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = ['shop_id', 'name', 'description', 'price', 'stock', 'code_pattern', 'image', 'is_active'];
    
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    public function shop() { return $this->belongsTo(Shop::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
    
    /**
     * Total stock = product stock + sum of all variant quantities
     */
    public function getTotalStockAttribute(): int
    {
        $variantStock = $this->variants()->sum('quantity');
        return $variantStock > 0 ? (int) $variantStock : (int) $this->stock;
    }
}
