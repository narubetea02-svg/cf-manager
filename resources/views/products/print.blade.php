<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Products Print</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 24px; }
        h1 { margin-bottom: 4px; }
        p { color: #4b5563; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body onload="window.print()">
    <h1>ข้อมูลสินค้า</h1>
    <p>สรุปรายการสินค้าแบบพิมพ์ได้</p>
    <table>
        <thead>
            <tr>
                <th>ชื่อ</th>
                <th>รายละเอียด</th>
                <th>ตัวเลือก</th>
                <th>จัดการข้อมูล</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>ราคา {{ number_format($product->price, 2) }} ฿ / รหัส {{ $product->code_pattern ?: '-' }}</td>
                    <td>{{ $product->stock }} ชิ้น</td>
                    <td>{{ $product->is_active ? 'พร้อมขาย' : 'ปิดใช้งาน' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">ยังไม่มีข้อมูลสินค้า</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
