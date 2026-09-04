<?php

namespace App\Support;

class OrderStatus
{
    public static function groups(): array
    {
        return [
            'wait_payment' => ['pending', 'pending_payment'],
            'pending_review' => ['confirmed'],
            'to_ship' => ['paid'],
            'packed' => ['shipped', 'packing'],
            'cod' => ['cod'],
            'completed' => ['delivered'],
            'hold' => ['hold'],
            'cancelled' => ['cancelled', 'merchant_cancel', 'customer_cancel'],
        ];
    }

    public static function filterMap(): array
    {
        return [
            'all' => [],
            'wait_payment' => self::groups()['wait_payment'],
            'pending_payment' => self::groups()['wait_payment'],
            'pending' => self::groups()['wait_payment'],
            'reject_payment' => self::groups()['pending_review'],
            'pending_review' => self::groups()['pending_review'],
            'hold' => self::groups()['hold'],
            'to_ship' => self::groups()['to_ship'],
            'paid' => self::groups()['to_ship'],
            'packing' => self::groups()['packed'],
            'shipping' => self::groups()['packed'],
            'cod' => self::groups()['cod'],
            'completed' => self::groups()['completed'],
            'delivered' => self::groups()['completed'],
            'cancelled' => self::groups()['cancelled'],
        ];
    }

    public static function labels(): array
    {
        return [
            'pending' => 'รอชำระเงิน',
            'pending_payment' => 'รอชำระเงิน',
            'confirmed' => 'รอตรวจสอบ',
            'paid' => 'ต้องจัดส่ง',
            'packing' => 'พิมพ์/แพ็ค',
            'shipped' => 'พิมพ์/แพ็ค',
            'hold' => 'ฝากของ',
            'cod' => 'รอชำระ COD',
            'delivered' => 'สำเร็จแล้ว',
            'cancelled' => 'ยกเลิกแล้ว',
            'merchant_cancel' => 'ร้านค้ายกเลิก',
            'customer_cancel' => 'ลูกค้ายกเลิก',
        ];
    }

    public static function badgeColors(): array
    {
        return [
            'pending' => 'warning',
            'pending_payment' => 'warning',
            'confirmed' => 'info',
            'paid' => 'primary',
            'packing' => 'secondary',
            'shipped' => 'secondary',
            'hold' => 'dark',
            'cod' => 'dark',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'merchant_cancel' => 'danger',
            'customer_cancel' => 'danger',
        ];
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function color(string $status): string
    {
        return self::badgeColors()[$status] ?? 'secondary';
    }
}
