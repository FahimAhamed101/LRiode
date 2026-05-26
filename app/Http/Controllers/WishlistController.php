<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Cart::instance('wishlist')->content();
        return view('wishlist', compact('items'));
    }
    public function add_to_wishlist(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'color' => ['nullable', 'string', 'max:40'],
            'size' => ['nullable', 'string', 'max:40'],
        ]);

        $product = Product::findOrFail($request->id);
        $quantity = max(1, (int) $request->input('quantity', 1));
        $price = $product->sale_price ?: $product->regular_price;

        Cart::instance('wishlist')
            ->add($product->id, $product->name, $quantity, $price, $this->cartItemOptionsFromRequest($request))
            ->associate(Product::class);

        return redirect()->back()->with('success', 'Product added to wishlist.');
    }

    public function remove_item($rowId){
        Cart::instance('wishlist')->remove($rowId);
        return redirect()->back();
    }

    public function empty_wishlist(){
        Cart::instance('wishlist')->destroy();
        return redirect()->back();
    }

    public function move_to_cart($rowId){
        $item=Cart::instance('wishlist')->get($rowId);
        Cart::instance('wishlist')->remove($rowId);
        Cart::instance('cart')->add($item->id,$item->name,$item->qty,$item->price,$item->options->toArray())->associate('App\Models\Product');
        return redirect()->back();
    }

    private function cartItemOptionsFromRequest(Request $request): array
    {
        return collect($request->only(['color', 'size']))
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->all();
    }
}
