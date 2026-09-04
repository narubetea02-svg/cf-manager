<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class LineSetting extends Model
{
    use HasFactory;
    protected $fillable = ['shop_id', 'line_token', 'line_group_id', 'is_active'];
    public function shop() { return $this->belongsTo(Shop::class); }
}
