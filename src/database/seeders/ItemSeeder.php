<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seller = User::where('email', 'seller@example.com')->firstOrFail();

        $categories = Category::pluck('id', 'name');

        foreach ($this->itemSeeds() as $data) {
            $this->createItemWithCategory($data, $seller->id, $categories);
        }
    }

    private function itemSeeds(): array
    {
        return [
            [
                'name' => '腕時計',
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'condition' => '良好',
                'image_path' => 'products/watch.jpg',
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'HDD',
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'products/hdd.jpg',
                'categories' => ['家電'],
            ],
            [
                'name' => '玉ねぎ3束',
                'brand' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'products/onion.jpg',
                'categories' => ['キッチン'],
            ],
            [
                'name' => '革靴',
                'brand' => null,
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'condition' => '状態が悪い',
                'image_path' => 'products/shoes.jpg',
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'ノートPC',
                'brand' => null,
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'condition' => '良好',
                'image_path' => 'products/laptop.jpg',
                'categories' => ['家電'],
            ],
            [
                'name' => 'マイク',
                'brand' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'products/mic.jpg',
                'categories' => ['家電'],
            ],
            [
                'name' => 'ショルダーバッグ',
                'brand' => null,
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'products/bag.jpg',
                'categories' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'タンブラー',
                'brand' => 'なし',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'condition' => '状態が悪い',
                'image_path' => 'products/tumbler.jpg',
                'categories' => ['インテリア', 'キッチン'],
            ],
            [
                'name' => 'コーヒーミル',
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'condition' => '良好',
                'image_path' => 'products/mill.jpg',
                'categories' => ['インテリア', 'キッチン'],
            ],
            [
                'name' => 'メイクセット',
                'brand' => null,
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'products/makeup.jpg',
                'categories' => ['コスメ', 'ファッション', 'レディース'],
            ],
        ];
    }

    private function createItemWithCategory(
        array $data,
        int $sellerId,
        $categories
    ): void {
        $categoryNames = $data['categories'] ?? [];
        unset($data['categories']);
        $itemCondition = $data['condition'];
        unset($data['condition']);

        $data['user_id'] = $sellerId;
        $data['item_condition'] = $itemCondition;

        $item = Item::create($data);

        foreach ($categoryNames as $categoryName) {
            $categoryId = $categories[$categoryName] ?? null;
            if ($categoryId) {
                $item->categories()->attach($categoryId);
            }
        }
    }
}
