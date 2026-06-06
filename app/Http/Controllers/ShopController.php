<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = (int) $request->query('size', 12);
        $size = in_array($size, [12, 24, 36, 48], true) ? $size : 12;
        $order = (int) $request->query('order', -1);

        $selectedBrands = $this->filterIds($request->query('brands', ''));
        $selectedCategories = $this->filterIds($request->query('categories', ''));
        $f_brands = implode(',', $selectedBrands);
        $f_categories = implode(',', $selectedCategories);

        $priceCeiling = (float) Product::query()
            ->selectRaw('GREATEST(COALESCE(MAX(regular_price), 0), COALESCE(MAX(sale_price), 0)) as max_price')
            ->value('max_price');
        $priceCeiling = max(500, (int) ceil($priceCeiling));

        $min_price = max(0, (float) $request->query('min', 0));
        $max_price = min($priceCeiling, (float) $request->query('max', $priceCeiling));

        if ($min_price > $max_price) {
            [$min_price, $max_price] = [$max_price, $min_price];
        }

        $brands = Brand::withCount('products')->orderBy('name', 'ASC')->get();
        $categories = Category::withCount('products')->orderBy('name', 'ASC')->get();

        $productsQuery = Product::with(['category', 'brand'])
            ->when($selectedBrands, function ($query) use ($selectedBrands) {
                $query->whereIn('brand_id', $selectedBrands);
            })
            ->when($selectedCategories, function ($query) use ($selectedCategories) {
                $query->whereIn('category_id', $selectedCategories);
            })
            ->where(function ($query) use ($min_price, $max_price) {
                $query->where(function ($saleQuery) use ($min_price, $max_price) {
                    $saleQuery->whereNotNull('sale_price')
                        ->where('sale_price', '>', 0)
                        ->whereBetween('sale_price', [$min_price, $max_price]);
                })->orWhere(function ($regularQuery) use ($min_price, $max_price) {
                    $regularQuery->where(function ($emptySaleQuery) {
                        $emptySaleQuery->whereNull('sale_price')
                            ->orWhere('sale_price', '<=', 0);
                    })->whereBetween('regular_price', [$min_price, $max_price]);
                });
            });

        match ($order) {
            1 => $productsQuery->latest(),
            2 => $productsQuery->oldest(),
            3 => $productsQuery->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE regular_price END ASC'),
            4 => $productsQuery->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE regular_price END DESC'),
            default => $productsQuery->latest('id'),
        };

        $products = $productsQuery->paginate($size)->withQueryString();

        return view('shop', compact(
            'products',
            'size',
            'order',
            'brands',
            'f_brands',
            'categories',
            'f_categories',
            'min_price',
            'max_price',
            'priceCeiling',
            'selectedBrands',
            'selectedCategories'
        ));
    }

    private function filterIds(mixed $value): array
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);

        return collect($values)
            ->flatMap(fn ($item) => explode(',', (string) $item))
            ->map(fn ($item) => (int) trim((string) $item))
            ->filter(fn ($item) => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function product_details($product_slug)
    {
        $product = Product::with(['category', 'brand'])->where('slug', $product_slug)->firstOrFail();
        $rproducts = Product::with('category')
            ->where('slug', '<>', $product_slug)
            ->when($product->category_id, function ($query) use ($product) {
                $query->where('category_id', $product->category_id);
            })
            ->latest()
            ->limit(8)
            ->get();

        if ($rproducts->count() < 4) {
            $fallbackProducts = Product::with('category')
                ->where('slug', '<>', $product_slug)
                ->whereNotIn('id', $rproducts->pluck('id'))
                ->latest()
                ->limit(8 - $rproducts->count())
                ->get();

            $rproducts = $rproducts->concat($fallbackProducts);
        }

        return view('details', compact('product', 'rproducts'));
    }

}
