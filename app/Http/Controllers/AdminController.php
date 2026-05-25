<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Slider;
use App\Models\Transaction;
use App\Models\Contact;
use App\Models\User;
use Carbon\Carbon;
//use File;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageManagerInterface;
//use Intervention\Image\Drivers\Gd\Driver;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Drivers\Imagick\ImagickDriver;
use Intervention\Image\Drivers\Imagick\ImagickDriverFactory;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ModifierInterface;
use Intervention\Image\Interfaces\SizeInterface;
use Intervention\Image\Laravel\ServiceProvider;
use Intervention\Image\ImageManagerStatic as ImageManagerStatic;
use Illuminate\Support\Facades\Storage as StorageBase;
use Intervention\Image\FacadesImage;




class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = DB::select("SELECT
                                                SUM(total) AS TotalAmount,
                                                SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
                                                SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
                                                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                                                COUNT(*) AS Total,
                                                SUM(IF(status = 'ordered', 1, 0)) AS TotalOrdered,
                                                SUM(IF(status = 'delivered', 1, 0)) AS TotalDelivered,
                                                SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                                                FROM Orders;

        ");
        $monthlyDatas = DB::select("SELECT M.id As MonthNo, M.name As MonthName,
                                        IFNULL(D.TotalAmount,0) As TotalAmount,
                                        IFNULL(D.TotalOrderedAmount,0) As TotalOrderedAmount,
                                        IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,
                                        IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount
                                        FROM month_names M
                                        LEFT JOIN (Select DATE_FORMAT(created_at, '%b') As MonthName,
                                        MONTH(created_at) As MonthNo,
                                        sum(total) As TotalAmount,
                                        sum(if(status='ordered',total,0)) As TotalOrderedAmount,
                                        sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
                                        sum(if(status='canceled',total,0)) As TotalCanceledAmount
                                        From Orders WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY YEAR(created_at), MONTH(created_at) , DATE_FORMAT(created_at, '%b')
                                        Order By MONTH(created_at)) D On D.MonthNo=M.id
    ");
        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $orderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $deliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $canceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');


        return view("admin.index", compact(
            'orders',
            'dashboardDatas',
            'AmountM',
            'orderedAmountM',
            'deliveredAmountM',
            'canceledAmountM',
            'TotalAmount',
            'TotalOrderedAmount',
            'TotalOrderedAmount',
            'TotalDeliveredAmount',
            'TotalCanceledAmount'
        ));
    }
    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }
    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->getClientOriginalExtension();
        $file_name = Carbon::now()->format('Y-m-d-H-i-s') . '.' . $file_extention;
        /* $file_name = Carbon::now()->timestamp('') .. '.' . $file_extention;  */
        $this->GenerateBrandThumbailsImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has added succesfully!');
    }
    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit ', compact("brand"));

    }
    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->getClientOriginalExtension();
            $file_name = Carbon::now()->format('Y-m-d-H-i-s') . '.' . $file_extention;
            $this->GenerateBrandThumbailsImage($image, $file_name);
            $brand->image = $file_name;

        }
        /* $brand->update(); */
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has updated succesfully!');

    }

    public function GenerateBrandThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        if (!file_exists($destinationPath)) {
        }

        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . "/" . $imageName);
        /*              Storage::putFileAs('app/public/uploads/brands', $image, $imageName);
         */
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has deleted succesfully!');
    }


    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->getClientOriginalExtension();
        $file_name = Carbon::now()->format('Y-m-d-H-i-s') . '.' . $file_extention;
        /* $file_name = Carbon::now()->timestamp('') .. '.' . $file_extention;  */
        $this->GenerateCategoryThumbailsImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has added succesfully!');

    }

    public function GenerateCategoryThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        if (!file_exists($destinationPath)) {
        }

        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . "/" . $imageName);
        /*              Storage::putFileAs('app/public/uploads/brands', $image, $imageName);
         */
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view("admin.category-edit", compact("category"));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->getClientOriginalExtension();
            $file_name = Carbon::now()->format('Y-m-d-H-i-s') . '.' . $file_extention;
            $this->GenerateCategoryThumbailsImage($image, $file_name);
            $category->image = $file_name;

        }
        $category->update();
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has updated succesfully!');

    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has deleted succesfully!');
    }

    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product-add', compact('categories', 'brands'));

    }
    public function products_store(Request $request)
    {
        //dd($request->all());

        DB::beginTransaction();
        try {

            $request->validate([
                'name' => 'required',
                'slug' => 'required|unique:products,slug',
                'short_description' => 'required',
                'description' => 'required',
                'regular_price' => 'required',
                'sale_price' => 'required',
                'SKU' => 'required',
                'stock_status' => 'required',
                'featured' => 'required',
                'quntity' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,webp|max:2048',
                'category_id' => 'required',
                'brand_id' => 'required'

            ]);
            $product = new Product();
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->short_description = $request->short_description;
            $product->description = $request->description;
            /* $product->regular_price = $request->regular_price;
            $product->sale_price = $request->sale_price; */
            $product->regular_price = floatval(str_replace('$', '', $request->input('regular_price')));
            $product->sale_price = floatval(str_replace('$', '', $request->input('sale_price')));
            $product->SKU = $request->SKU;
            $product->stock_status = $request->stock_status;
            $product->featured = $request->featured;
            $product->quntity = $request->quntity;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;

            $current_timestamp = Carbon::now()->format('Y-m-d-H-i-s');

            if ($request->hasFile('image')) {
                // if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
                $image = $request->file('image');
                $imageName = $current_timestamp . '.' . $image->getClientOriginalExtension();
                $this->GenerateProductThumbnailImage($image, $imageName);
                $product->image = $imageName;
                //}
            }
            $gallery_arr = array();
            //$gallery_arr = [];
            $gallery_images = "";
            $counter = 1;
            if ($request->hasFile("images")) {
                $allowedfileExtention = ['jpg', 'png', 'jpeg', 'webp'];
                $files = $request->file('images');
                foreach ($files as $counter => $file) {
                    $gextension = $file->getClientOriginalExtension();
                    $gcheck = in_array($gextension, $allowedfileExtention);
                    if ($gcheck) {
                        $gfileName = $current_timestamp . "-" . ($counter + 1) . "." . $gextension;
                        $this->GenerateProductThumbnailImage($file, $gfileName);
                        array_push($gallery_arr, $gfileName);
                        $counter = $counter + 1;
                    }
                }
                $gallery_images = implode(",", $gallery_arr);
            }
            $product->images = $gallery_images;
            //dd($product);r

            /*  try {
                $product->save();
             } catch (\Exception $e) {
                dd($e->getMessage());
             } */
            $product->save();
            DB::commit();

            //return redirect()->back()->with("status", "Product has been added successfully! ");
            return redirect()->route("admin.products")->with("status", "Product has been added successfully! ");

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products/');
        /*       if (!file_exists($destinationPath)) {} */
        $img = Image::read($image->path());
        $img->cover(540, 689, "top");
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail . '/' . $imageName);
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product-edit ', compact("product", "categories", "brands"));

    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $request->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quntity' => 'required',
            'image' => 'mimes:png,jpg,jpeg,webp|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = floatval(str_replace('$', '', $request->input('regular_price')));
        $product->sale_price = floatval(str_replace('$', '', $request->input('sale_price')));
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quntity = $request->quntity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->format('Y-m-d-H-i-s');

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }
            if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->getClientOriginalExtension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile("images")) {
            foreach (explode(',', $product->images) as $ofile) {
                if (File::exists(public_path('uploads/products') . '/' . $ofile)) {
                    File::delete(public_path('uploads/products') . '/' . $ofile);
                }
                if (File::exists(public_path('uploads/products/thumbnails') . '/' . $ofile)) {
                    File::delete(public_path('uploads/products/thumbnails') . '/' . $ofile);
                }
            }

            $allowedfileExtention = ['jpg', 'png', 'jpeg', 'webp'];
            $files = $request->file('images');
            foreach ($files as $counter => $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedfileExtention);
                if ($gcheck) {
                    $gfileName = $current_timestamp . "-" . ($counter + 1) . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(",", $gallery_arr);
            $product->images = $gallery_images;

        }
        $product->update();
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product has been updated sccessfully!');

    }

    public function product_delete($id)
    {
        $product = Product::find($id);
        if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
            File::delete(public_path('uploads/products') . '/' . $product->image);
        }
        if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
            File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
        }
        foreach (explode(',', $product->images) as $ofile) {
            if (File::exists(public_path('uploads/products') . '/' . $ofile)) {
                File::delete(public_path('uploads/products') . '/' . $ofile);
            }
            if (File::exists(public_path('uploads/products/thumbnails') . '/' . $ofile)) {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product has been deleted sccussfully!');
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));

    }

    public function coupon_add()
    {
        return view('admin.coupon_add');
    }

    public function coupons_store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been added Successfully!');

    }
    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view("admin.coupon-edit", compact("coupon"));

    }
    public function coupon_update(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been updated Successfully!');

    }
    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been deleted Successfully!');
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $orders = Order::find($order_id);
        $orderItem = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transation = Transaction::where('order_id', $order_id)->first();
        return view('admin.order-details', compact('orders', 'orderItem', 'transation'));

    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } else if ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }
        $order->save();
        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        } else if ($request->order_status == 'canceled') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'pending';
            $transaction->save();
        }
        return redirect()->back()->with('status', 'Status changed successfully!');


    }

    public function slides()
    {
        $slides = Slider::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }
    public function slide_add()
    {
        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg,webp|max:3000',
            'status' => 'required'

        ]);
        $slide = new Slider();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $image = $request->file('image');
        $file_extention = $request->file('image')->getClientOriginalExtension();
        $file_name = Carbon::now()->format('Y-m-d-H-i-s') . '.' . $file_extention;
        $this->GenerateSlideThumbailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->status = $request->status;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide added successfully!');


    }
    public function GenerateSlideThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        if (!file_exists($destinationPath)) {
        }
        $img = Image::read($image->path());
        $img->cover(400, 690, "top");
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . "/" . $imageName);

        /*  $image = Image::read($image->path());

         $image->fit(400, 690)->save($destinationPath . "/" . $imageName); */

    }
    public function slide_edit($id)
    {
        $slide = Slider::find($id);
        return view("admin.slide-edit", compact("slide"));

    }
    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'mimes:png,jpg,jpeg,webp|max:3000',
            'status' => 'required'

        ]);
        $slide = Slider::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
                File::delete(public_path('uploads/slides') . '/' . $slide->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->getClientOriginalExtension();
            $file_name = Carbon::now()->format('Y-m-d-H-i-s') . '.' . $file_extention;
            $this->GenerateSlideThumbailsImage($image, $file_name);
            $slide->image = $file_name;

        }
        $slide->status = $request->status;
        $slide->update();
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slidehas been updated Successfully!');
    }
    public function slide_delete($id)
    {
        $slide = Slider::find($id);
        if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
            File::delete(public_path('uploads/slides') . '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide has deleted succesfully!');
    }

    public function contacts(){
        $contacts=Contact::orderBy('created_at','DESC')->paginate(10);
        return view('admin.contacts',compact('contacts'));
    }

    public function contact_delete($id)
    {
        $contacts = Contact::find($id);
        $contacts->delete();
        return redirect()->route('admin.contacts')->with('status', 'Contact has deleted succesfully!');
    }


    public function user_All(){
        $users = User::where('utype', 'USR')->get();
        return view('admin.user', compact('users'));
    }

    public function user_delete($id)
    {
        $user = user::find($id);

        $user->delete();
        return redirect()->route('admin.user')->with('status', 'User has deleted succesfully!');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $result = Product::where('name', 'LIKE', "%{$query}%")
           ->take(8)
            ->get();
        return response()->json($result);
    }



}
//$regularPrice = floatval(str_replace('$', '', $request->input('regular_price')));
//$salePrice = $request->input('sale_price') ? floatval(str_replace('$', '', $request->input('sale_price'))) : null;
// dd($product);
//$image = $request->file('image');
// $imagePath = $request->file('image')->store('uploads/products');
// $product->image = $imageName->store('uploads/products');
// $product->save();
//$product->save();
//$product->images = $gallery_images->store('uploads/products/thumbnails');
