<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
class StockController extends Controller {
    public function index() {
        $products = Product::where('is_active', true)->paginate(20);
        return view('stocks.index', compact('products'));
    }
}
