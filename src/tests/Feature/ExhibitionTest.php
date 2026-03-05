<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Support\ItemConditions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExhibitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_出品情報を保存できる(): void
    {
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

    private function createVerifiedUser(string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $name . '@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);

        $user->email_verified_at = now();
        $user->save();

        return $user;
    }
}
