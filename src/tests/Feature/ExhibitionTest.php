<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Support\ItemConditions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class ExhibitionTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:15】出品フォーム送信時に商品本体・カテゴリ中間テーブル・商品画像が正しく保存されるかを検証
    public function test_stores_exhibition_item() {
        Storage::fake('public');
        $user = $this->createVerifiedUser('seller');
        $category1 = Category::create(['name' => 'ファッション']);
        $category2 = Category::create(['name' => 'メンズ']);

        $response = $this->actingAs($user)->post(route('sell.store'), [
            'name' => 'レザージャケット',
            'brand' => 'テストブランド',
            'description' => 'しっかりした革素材です',
            'price' => 50000,
            'item_condition' => ItemConditions::ALL[0],
            'category_ids' => [$category1->id, $category2->id],
            'image' => UploadedFile::fake()->create('item.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'レザージャケット',
            'brand' => 'テストブランド',
            'description' => 'しっかりした革素材です',
            'price' => 50000,
            'item_condition' => ItemConditions::ALL[0],
        ]);
        $item = Item::where('name', 'レザージャケット')->firstOrFail();
        $this->assertDatabaseHas('item_category', [
            'item_id' => $item->id,
            'category_id' => $category1->id,
        ]);
        $this->assertDatabaseHas('item_category', [
            'item_id' => $item->id,
            'category_id' => $category2->id,
        ]);
        Storage::disk('public')->assertExists($item->image_path);
    }

}
