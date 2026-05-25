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
        $size = $request->query('size') ? $request->query('size') : 12;
        $o_colum = "";
        $o_order = "";
        $order = $request->query('order') ? $request->query('order') : -1;
        $f_brands = $request->query('brands', '');
        $f_categories = $request->query('categories', '');
        $min_price = $request->query(key: 'min') ? $request->query('min') : 1;
        $max_price = $request->query('max') ? $request->query('max') : 500;

        switch ($order) {
            case 1:
                $o_colum = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_colum = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_colum = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_colum = 'sale_price';
                $o_order = 'DESC';
                break;
            default:
                $o_colum = 'id';
                $o_order = 'DESC';

        }
        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();
        /*  $products = Product::where(function($query) use($f_brands){
             $query->whereIn('brand_id',explode(',',$f_brands))->orWhereRaw("'".$f_brands."'=''");
         })
         ->orderBy($o_colum, $o_order)->paginate(12); */

        /* $products = Product::
            when(!empty($f_brands), function ($query) use ($f_brands) {
                return $query->whereIn('brand_id', explode(',', $f_brands));
            })
            ->when(!empty($f_categories), function ($query) use ($f_categories) {
                return $query->whereIn('category_id', explode(',', $f_categories));
            })
            ->when( function ($query) use ($min_price, $max_price) {
                return $query->whereBetween('regular_price', [$min_price, $max_price])
                    ->orWhereBetween('sale_price', [$min_price, $max_price]);
                //return $query->where('sale_price','>=',$max_price)->where('regular_price ','<=',$max_price);
            })
            ->orderBy($o_colum, $o_order)
            ->paginate(12); */

            $products = Product::query()
            ->when(!empty($f_brands), function ($query) use ($f_brands) {
                return $query->whereIn('brand_id', explode(',', $f_brands));
            })
            ->when(!empty($f_categories), function ($query) use ($f_categories) {
                return $query->whereIn('category_id', explode(',', $f_categories));
            })
            ->when($min_price !== null && $max_price !== null, function ($query) use ($min_price, $max_price) {
                return $query->where(function ($q) use ($min_price, $max_price) {
                    $q->whereBetween('regular_price', [$min_price, $max_price])
                      ->orWhereBetween('sale_price', [$min_price, $max_price]);
                });
            })
            ->orderBy($o_colum, $o_order)
            ->paginate(12);



        return view('shop', compact('products', 'size', 'order', 'brands', 'f_brands', 'categories', 'f_categories', 'min_price', 'max_price'));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();
        $rproducts = Product::where('slug', '<>', $product_slug)->get()->take(8);
        // تحسين الكود لعرض المنتجات عشوائيًا بدلاً من ترتيبها حسب قاعدة البيانات
        /*   $rproducts = Product::where('slug', '<>', $product_slug)
              ->inRandomOrder()
              ->limit(8)
              ->get(); */
        return view('details', compact('product', 'rproducts'));
    }

}