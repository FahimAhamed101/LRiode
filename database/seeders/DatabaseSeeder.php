<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'utype' => 'ADM',
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'utype' => 'USR',
            ]
        );

        $categoryRows = [
            ['name' => "Men's Fashion", 'slug' => 'mens-fashion', 'image' => 'category1.jpg'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'image' => 'category2.jpg'],
            ['name' => "Women's Fashion", 'slug' => 'womens-fashion', 'image' => 'category3.jpg'],
            ['name' => 'Cosmetics', 'slug' => 'cosmetics', 'image' => 'category4.jpg'],
            ['name' => 'Shoes', 'slug' => 'shoes', 'image' => 'category5.jpg'],
            ['name' => 'Watches', 'slug' => 'watches', 'image' => 'category6.jpg'],
            ['name' => 'Bags & Backpacks', 'slug' => 'bags-backpacks', 'image' => 'category7.jpg'],
            ['name' => 'Sportswear', 'slug' => 'sportswear', 'image' => 'category8.jpg'],
        ];

        $categories = collect($categoryRows)->mapWithKeys(function (array $row) {
            $category = Category::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name'], 'image' => $row['image']]
            );

            return [$row['slug'] => $category];
        });

        $brandRows = [
            ['name' => 'Riode Studio', 'slug' => 'riode-studio', 'image' => '1.png'],
            ['name' => 'Urban Mode', 'slug' => 'urban-mode', 'image' => '2.png'],
            ['name' => 'North Atelier', 'slug' => 'north-atelier', 'image' => '3.png'],
            ['name' => 'Daily Vogue', 'slug' => 'daily-vogue', 'image' => '4.png'],
            ['name' => 'Luna Goods', 'slug' => 'luna-goods', 'image' => '5.png'],
            ['name' => 'Stride Supply', 'slug' => 'stride-supply', 'image' => '6.png'],
        ];

        $brands = collect($brandRows)->mapWithKeys(function (array $row) {
            $brand = Brand::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name'], 'image' => $row['image']]
            );

            return [$row['slug'] => $brand];
        });

        $sliders = [
            [
                'title' => 'Fashionable',
                'subtitle' => 'Collection',
                'tagline' => 'Buy 2 Get 1 Free',
                'link' => '/shop',
                'image' => 'slide1.jpg',
                'status' => true,
            ],
            [
                'title' => 'Riode Birthday',
                'subtitle' => 'Sale',
                'tagline' => 'Up to 70% off',
                'link' => '/shop',
                'image' => 'slide2.jpg',
                'status' => true,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::updateOrCreate(['image' => $slider['image']], $slider);
        }

        $products = [
            ['Solid Pattern Summer Dress', 'womens-fashion', 'daily-vogue', 140.00, null, true, 'product1.jpg'],
            ['Mackintosh Pocket Backpack', 'bags-backpacks', 'luna-goods', 160.99, 125.99, true, 'product2.jpg'],
            ['Fashionable Original Trucker', 'mens-fashion', 'urban-mode', 78.64, null, false, 'product3.jpg'],
            ['Women Red Fur Overcoat', 'womens-fashion', 'north-atelier', 184.00, null, true, 'product4.jpg'],
            ['Fashion Electric Wrist Watch', 'watches', 'riode-studio', 270.99, null, true, 'product5.jpg'],
            ['Hempen Hood a Mourner', 'womens-fashion', 'daily-vogue', 42.83, 12.83, true, 'product6.jpg'],
            ['Women Beautiful Headgear', 'accessories', 'luna-goods', 98.24, 78.24, false, 'product7.jpg'],
            ['Converse Training Shoes', 'shoes', 'stride-supply', 113.00, null, true, 'product8.jpg'],
            ["Women's Fashion Handbag", 'bags-backpacks', 'luna-goods', 67.99, 53.99, true, 'product9.jpg'],
            ['Classic Pearl Headband', 'accessories', 'north-atelier', 88.24, 78.24, false, 'product10.jpg'],
            ['Hand Electric Cell', 'accessories', 'riode-studio', 36.00, 26.00, false, 'product11.jpg'],
            ['Women Hempen Hood', 'womens-fashion', 'daily-vogue', 48.00, 30.00, false, 'product12.jpg'],
            ['Fashionable Trucker Jacket', 'mens-fashion', 'urban-mode', 94.64, 78.64, true, 'product13.jpg'],
            ['Men Summer Sneaker', 'shoes', 'stride-supply', 99.45, 79.45, false, 'product14.jpg'],
            ['Season Sports Cap', 'sportswear', 'urban-mode', 74.27, 64.27, false, 'product15.jpg'],
            ['Blue Sports Shoes', 'shoes', 'stride-supply', 52.00, 36.00, true, 'product16.jpg'],
            ['Fashion Handbag', 'bags-backpacks', 'luna-goods', 67.99, 53.99, false, 'product17.jpg'],
            ["Women's Beautiful Headgear", 'accessories', 'daily-vogue', 92.23, 82.23, false, 'product18.jpg'],
            ['Minimal Electric Wrist Watch', 'watches', 'riode-studio', 270.99, null, true, 'product19.jpg'],
            ['Mackintosh Travel Backpack', 'bags-backpacks', 'luna-goods', 160.99, 125.99, false, 'product20.jpg'],
            ['Cotton Hooded Mourner', 'womens-fashion', 'north-atelier', 34.83, 12.83, false, 'product21.jpg'],
        ];

        foreach ($products as $index => [$name, $categorySlug, $brandSlug, $regularPrice, $salePrice, $featured, $image]) {
            $slug = Str::slug($name);
            $gallery = collect([$image, 'product' . ((($index + 1) % 21) + 1) . '.jpg', 'product' . ((($index + 2) % 21) + 1) . '.jpg'])
                ->unique()
                ->implode(',');

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category_id' => $categories[$categorySlug]->id,
                    'brand_id' => $brands[$brandSlug]->id,
                    'short_description' => 'Demo product URL: /shop/' . $slug,
                    'description' => 'A seeded Riode demo product for the Laravel ecommerce storefront. Use it to test product cards, detail pages, cart and wishlist flows.',
                    'regular_price' => $regularPrice,
                    'sale_price' => $salePrice,
                    'SKU' => 'RIODE-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'quntity' => 25 + $index,
                    'stock_status' => 'instock',
                    'featured' => $featured,
                    'image' => $image,
                    'images' => $gallery,
                ]
            );
        }
    }
}
