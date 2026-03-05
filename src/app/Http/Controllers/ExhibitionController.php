<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Item;
use App\Support\ItemConditions;
use Illuminate\Support\Facades\Auth;

class ExhibitionController extends Controller
{
    public function showSellForm()
    {
        $categories = Category::orderBy('id')->get();
        $conditions = ItemConditions::ALL;

        return view('exhibition', compact('categories', 'conditions'));
    }

    public function storeSellItem(ExhibitionRequest $request)
    {
        $path = $request->file('image')->store('items', 'public');

        $item = Item::create([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'brand' => $request->input('brand'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'item_condition' => $request->input('item_condition'),
            'image_path' => $path,
        ]);

        $categoryIds = $request->input('category_ids', []);
        $item->categories()->attach($categoryIds);

        return redirect()->route('item.show', $item);
    }
}
