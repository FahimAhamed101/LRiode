<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slider::where('status', 1)->latest()->take(3)->get();
        $categories = Category::withCount('products')->orderBy('name')->get();

        $sproducts = Product::with('category')
            ->whereNotNull('sale_price')
            ->where('sale_price', '!=', '')
            ->latest()
            ->take(12)
            ->get();

        $fproducts = Product::with('category')
            ->where('featured', 1)
            ->latest()
            ->take(12)
            ->get();

        $productcats = Category::whereHas('products', function ($query) {
            $query->where('sale_price', '<=', 190);
        })->with([
            'products' => function ($query) {
                $query->where('sale_price', '<=', 190)
                    ->orderBy('sale_price', 'asc')
                    ->take(2);
            }
        ])->get()->take(2);

        return view('index', compact('slides', 'categories', 'sproducts', 'fproducts', 'productcats'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function contact_store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in first');
        }

        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'comment' => 'required',
        ]);

        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $result = Product::where('name', 'LIKE', "%$query%")
            ->orWhere('short_description', 'LIKE', "%$query%")
            ->get();

        return response()->json($result);
    }

    public function about()
    {
        return view('about');
    }
}
