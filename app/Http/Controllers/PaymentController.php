<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AutoMessageService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected AutoMessageService $autoMessageService
    ) {
    }

    public function index() {
        $payments = Payment::with('order')->orderBy('created_at','desc')->get();
        $orders = Order::where('status','pending_payment')->get();
        return view('payments.index', compact('payments','orders'));
    }
    public function store(Request $r) {
        $r->validate(['order_id'=>'required|exists:orders,id','amount'=>'required|numeric']);
        Payment::create($r->all());
        return back()->with('success','บันทึกการชำระเงินแล้ว');
    }
    public function verify($id) {
        $p = Payment::with('order.shop')->findOrFail($id);
        $p->update(['status'=>'verified','verified_at'=>now()]);
        $p->order->update(['status'=>'paid']);
        $this->autoMessageService->queueForOrderEvent($p->order->fresh(['shop']), 'payment');
        return back()->with('success','✅ ยืนยันสลิปแล้ว');
    }
}