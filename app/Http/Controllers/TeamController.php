<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shops = Shop::where('user_id', $user->id)->get();

        $members = collect([
            [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'Owner',
                'status' => 'active',
                'shops_count' => $shops->count(),
            ],
        ]);

        return view('team.index', compact('user', 'shops', 'members'));
    }
}
