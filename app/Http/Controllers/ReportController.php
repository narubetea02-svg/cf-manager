<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Support\OrderStatus;
use Illuminate\Http\Request;
class ReportController extends Controller {
    public function index(Request $req) {
        $type = $req->get('type', 'all');
        $query = Order::query();
        if ($type === 'shipping') $query->whereNotNull('tracking_number');
        if ($type === 'pending') $query->whereIn('status', OrderStatus::groups()['wait_payment']);
        if ($type === 'paid') $query->where('status','paid');
        $orders = $query->latest()->paginate(20);
        return view('reports.index', [
            'orders' => $orders,
            'type' => $type,
            'statusLabels' => OrderStatus::labels(),
            'statusColors' => OrderStatus::badgeColors(),
        ]);
    }
}
