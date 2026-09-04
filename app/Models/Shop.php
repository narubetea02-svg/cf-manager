<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Shop extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'email', 'phone', 'country', 
        'address', 'sub_district', 'postal_code', 'tiktok_username', 'logo', 'instagram', 'is_active', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function products() { return $this->hasMany(Product::class); }
    public function liveStreams() { return $this->hasMany(LiveStream::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function messengerSetting() { return $this->hasOne(MessengerSetting::class); }
}
