<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Order;
use App\Models\Item;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    public function mypage(Request $request)
    {
        $page = $request->query('page');
        $user = Auth::user();
        $sellItems = collect();
        $buyOrders = collect();

        if ($user) {
            $user->load('profile');
            if ($page === 'sell') {
                $sellItems = Item::where('user_id', $user->id)->latest()->get();
            } else {
                $buyOrders = Order::with('item')->where('buyer_id', $user->id)->latest()->get();
            }
        }

        return view('mypage', compact('page', 'user', 'sellItems', 'buyOrders'));
    }

    public function mypageProfile()
    {
        $user = Auth::user();
        $profile = $user ? $user->profile : null;
        return view('profile', compact('user', 'profile'));
    }

    public function mypageProfileStore(ProfileRequest $request, ProfileService $profiles)
    {
        $user = Auth::user();

        $profiles->upsert($user, [
            'display_name' => $request->input('name'),
            'postal_code' => $request->input('postal_code'),
            'address' => $request->input('address'),
            'building' => $request->input('building'),
        ], $request->file('image'));

        $user->name = $request->input('name');
        $user->save();

        return redirect()->route('home', ['tab' => 'mylist']);
    }
}
