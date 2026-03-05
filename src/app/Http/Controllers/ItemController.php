<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;
use App\Models\Favorite;
use App\Models\Item;

class ItemController extends Controller
{
    public function showIndex(Request $request)
    {
        $tab = $request->query('tab');
        $keyword = trim((string) $request->query('keyword', ''));
        $keyword = $keyword !== '' ? $keyword : null;

        $query = Item::with(['categories'])
            ->withCount(['favorites', 'comments', 'orders']);

        if ($tab === 'mylist' && !Auth::check()) {
            $items = collect();

            return view('index', compact('tab', 'items', 'keyword'));
        }

        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }

        if ($tab === 'mylist') {
            $query->whereHas('favorites', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $items = $query->latest()->get();

        return view('index', compact('tab', 'items', 'keyword'));
    }

    public function showItemDetail(Item $item)
    {
        $item->load([
            'categories',
            'user',
            'comments' => function ($query) {
                $query->latest();
            },
            'comments.user.profile',
        ]);
        $item->loadCount(['favorites', 'comments', 'orders']);

        $isFavorited = Auth::check()
            ? Favorite::where('user_id', Auth::id())->where('item_id', $item->id)->exists()
            : false;

        return view('item', compact('item', 'isFavorited'));
    }

    public function storeComment(CommentRequest $request, Item $item)
    {
        $item->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->input('comment'),
        ]);

        return redirect()->route('item.show', $item);
    }

    public function toggleFavorite(Item $item)
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('item_id', $item->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'item_id' => $item->id,
            ]);
        }

        return redirect()->route('item.show', $item);
    }
}
