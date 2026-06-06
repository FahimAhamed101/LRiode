<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Events\Dispatcher;
use Surfsidemedia\Shoppingcart\Contracts\Buyable;
use Surfsidemedia\Shoppingcart\Exceptions\UnknownModelException;
use Surfsidemedia\Shoppingcart\Exceptions\InvalidRowIDException;
use Surfsidemedia\Shoppingcart\Exceptions\CartAlreadyStoredException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function add_to_cart(Request $request)
    {
        $requiresVariants = $request->boolean('has_variants');

        $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'has_variants' => ['nullable', 'boolean'],
            'color' => [$requiresVariants ? 'required' : 'nullable', 'string', 'max:40'],
            'size' => [$requiresVariants ? 'required' : 'nullable', 'string', 'max:40'],
        ]);

        $product = Product::findOrFail($request->id);
        $quantity = max(1, (int) $request->input('quantity', 1));
        $price = $product->sale_price ?: $product->regular_price;
        $stockQuantity = (int) ($product->quntity ?? 0);

        if ($product->stock_status === 'outofstock' || $stockQuantity < 1) {
            return redirect()->back()->with('error', 'This product is out of stock.');
        }

        $cartQuantity = $this->cartQuantityForProduct($product);
        $availableQuantity = $stockQuantity - $cartQuantity;

        if ($availableQuantity < 1) {
            return redirect()->back()->with('error', 'This product is already at the available stock limit in your cart.');
        }

        if ($quantity > $availableQuantity) {
            return redirect()->back()->with('error', 'Only ' . $availableQuantity . ' more item(s) are available.');
        }

        Cart::instance('cart')
            ->add($product->id, $product->name, $quantity, $price, $this->cartItemOptionsFromRequest($request))
            ->associate(Product::class);

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    public function increase_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        $product = Product::find($cartItem->id);

        if ($product) {
            $stockQuantity = (int) ($product->quntity ?? 0);

            if ($product->stock_status === 'outofstock' || $this->cartQuantityForProduct($product) >= $stockQuantity) {
                return redirect()->back()->with('error', 'Only ' . $stockQuantity . ' item(s) are available.');
            }
        }

        $qty = $cartItem->qty + 1;
        Cart::instance('cart')->update($rowId, $qty);
        return redirect()->back();
    }


    public function decrease_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId, $qty);
        return redirect()->back();

    }

    public function remove_item($rowId)
    {
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }

    public function empty_cart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();

    }

    public function apply_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code;
        if (isset($coupon_code)) {
            // Fix the expiry date comparison (should be >= today)
            $coupon = Coupon::where('code', $coupon_code)
                ->where('expiry_date', '>=', Carbon::today()) // Changed from <= to >=
                ->where('cart_value', '<=', (float)str_replace(',', '', Cart::instance('cart')->subtotal()))
                ->first();
    
            if (!$coupon) {
                return redirect()->back()->with('error', 'Invalid coupon code!');
            }
    
            Session::put('coupon', [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'cart_value' => $coupon->cart_value
            ]);
            
            $this->calculateDiscount();
            return redirect()->back()->with('success', 'Coupon has been applied!');
        }
    
        return redirect()->back()->with('error', 'Invalid coupon code!');
    }
    public function calculateDiscount()
    {
        $discount = 0;
        if (Session::has('coupon')) {
            // Get raw subtotal without formatting
            $rawSubtotal = (float)str_replace(',', '', Cart::instance('cart')->subtotal());
            
            if (Session::get('coupon')['type'] == 'fixed') {
                $discount = (float)Session::get('coupon')['value'];
            } else {
                $discount = ($rawSubtotal * (float)Session::get('coupon')['value']) / 100;
            }
            
            $subtotalAfterDiscount = $rawSubtotal - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;
    
            // Store raw numbers without formatting
            Session::put('discounts', [
                'discount' => $discount,
                'subtotal' => $subtotalAfterDiscount,
                'tax' => $taxAfterDiscount,
                'total' => $totalAfterDiscount,
            ]);
        }
    }
    public function remove_coupon_code()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        return redirect()->back()->with('success', 'Coupon has been removed');

    }

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in first');
        }

        if (Cart::instance('cart')->content()->count() < 1) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $this->setAmountforCheckout();
        $address = Address::where('user_id', Auth::user()->id)->where('isdefault', 1)->first();
        return view('checkout', compact('address'));
    }

    public function place_on_order(Request $request)
    {
        return $this->processCheckoutOrder($request);

        $user_id = optional(Auth::user())->id;
        // dd($request->all()); // تأكد من وصول البيانات قبل أي عمليات أخرى

        $address = Address::where('user_id', $user_id)->where('isdefault', true)->first();
        if (!$address) {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'address' => 'required',
                'zip' => 'required|numeric|digits:6',
                'city' => 'required',
                'landmark' => 'required',
                'locality' => 'required',
                'state' => 'required'
            ]);
            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->address = $request->address;
            $address->zip = $request->zip;
            $address->city = $request->city;
            $address->landmark = $request->landmark;
            $address->locality = $request->locality;
            $address->state = $request->state;
            $address->country = 'Syria';
            $address->user_id = $user_id;
            $address->isdefault = true;
            $address->save();
        }
        $this->setAmountforCheckout();

        $order = new Order();
        //dd($order);
        $order->user_id = $user_id;
        //  dd(Session::get('checkout'));
        //dd($order);

        $order->subtotal = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total = Session::get('checkout')['total'];
        $order->name = $address->name;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->phone = $address->phone;
        $order->save();

        foreach (Cart::instance('cart')->content() as $item) {
            $orderitem = new OrderItem();
            $orderitem->product_id = $item->id;
            $orderitem->order_id = $order->id;
            $orderitem->price = $item->price;
            $orderitem->quantity = $item->qty;
            $orderitem->options = $this->formatCartItemOptions($item->options);
            $orderitem->save();
        }

        if ($request->mode == "card") {
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';
            $transaction->save();
        } else if ($request->mode == "paypal") {
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';
            $transaction->save();
        } else if ($request->mode == "cod") {
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';
            $transaction->save();
        }

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
        // return view('order-confirmation',compact('order'));
        return redirect()->route('cart.order.confirmation');
    }

    public function stripe_success(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in first');
        }

        $sessionId = (string) $request->query('session_id');

        if ($sessionId === '') {
            return redirect()->route('cart.checkout')->with('error', 'Stripe did not return a checkout session.');
        }

        $snapshot = Session::get('stripe_checkout.' . $sessionId);

        if (!$snapshot) {
            if (Session::has('order_id')) {
                return redirect()->route('cart.order.confirmation');
            }

            return redirect()->route('cart.index')->with('error', 'This Stripe checkout session has expired.');
        }

        if ((string) ($snapshot['user_id'] ?? '') !== (string) Auth::id()) {
            return redirect()->route('cart.index')->with('error', 'This checkout session does not belong to your account.');
        }

        try {
            $stripeSession = $this->retrieveStripeCheckoutSession($sessionId);
        } catch (\Throwable $exception) {
            Log::error('Stripe checkout verification failed.', [
                'message' => $exception->getMessage(),
                'session_id' => $sessionId,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('cart.checkout')->with('error', 'Stripe payment verification failed. Please try again.');
        }

        $expectedAmount = $this->stripeAmount($snapshot['totals']['total']);
        $paymentIsPaid = ($stripeSession['payment_status'] ?? null) === 'paid';
        $referenceMatches = (string) ($stripeSession['metadata']['reference'] ?? '') === (string) ($snapshot['reference'] ?? '');
        $amountMatches = (int) ($stripeSession['amount_total'] ?? 0) === $expectedAmount;

        if (!$paymentIsPaid || !$referenceMatches || !$amountMatches) {
            return redirect()->route('cart.checkout')->with('error', 'Stripe payment was not completed.');
        }

        $order = $this->createOrderFromSnapshot($snapshot);
        $this->createTransaction($order, 'card', 'paid');
        $this->completeOrderCheckout($order);
        Session::forget('stripe_checkout.' . $sessionId);

        return redirect()->route('cart.order.confirmation')->with('success', 'Payment received and order placed.');
    }

    public function stripe_cancel()
    {
        return redirect()->route('cart.checkout')->with('error', 'Stripe checkout was cancelled. Your cart is still saved.');
    }

    public function setAmountforCheckout()
    {
        if (Cart::instance('cart')->content()->count() < 1) {
            Session::forget('checkout');
            return;
        }
        
        if (Session::has('coupon')) {
            Session::put('checkout', [
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'tax' => Session::get('discounts')['tax'],
                'total' => Session::get('discounts')['total'],
            ]);
        } else {
            // Get raw values without formatting
            $subtotal = (float)str_replace(',', '', Cart::instance('cart')->subtotal());
            $tax = (float)str_replace(',', '', Cart::instance('cart')->tax());
            $total = (float)str_replace(',', '', Cart::instance('cart')->total());
            
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);
        }
    }

    public function order_confirmation()
    {
        if (Session::has('order_id')) {
            $order = Order::with(['orderItem.product', 'transaction'])->find(Session::get('order_id'));

            if (!$order) {
                return redirect()->route('cart.index');
            }

            return view('order-confirmation', compact('order'));
        }
        return redirect()->route('cart.index');
    }

    private function processCheckoutOrder(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in first');
        }

        if (Cart::instance('cart')->content()->count() < 1) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'mode' => ['required', 'in:card,cod'],
        ]);

        $address = $this->checkoutAddress($request);
        $this->setAmountforCheckout();

        if (!Session::has('checkout')) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $snapshot = $this->checkoutSnapshot($address, $request->input('mode'));

        if ($request->input('mode') === 'card') {
            try {
                $stripeSession = $this->createStripeCheckoutSession($snapshot);
                Session::put('stripe_checkout.' . $stripeSession['id'], $snapshot);

                return redirect()->away($stripeSession['url']);
            } catch (\Throwable $exception) {
                Log::error('Stripe checkout session failed.', [
                    'message' => $exception->getMessage(),
                    'user_id' => Auth::id(),
                ]);

                return redirect()
                    ->route('cart.checkout')
                    ->with('error', 'Stripe checkout could not be started. Please check your Stripe keys and try again.');
            }
        }

        $order = $this->createOrderFromSnapshot($snapshot);
        $this->createTransaction($order, 'cod', 'pending');
        $this->completeOrderCheckout($order);

        return redirect()->route('cart.order.confirmation')->with('success', 'Order placed successfully.');
    }

    private function checkoutAddress(Request $request): Address
    {
        $address = Address::where('user_id', Auth::id())->where('isdefault', true)->first();

        if ($address) {
            return $address;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:80'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'locality' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
        ]);

        $address = new Address();
        $address->name = $validated['name'];
        $address->phone = $validated['phone'];
        $address->address = $validated['address'];
        $address->zip = $validated['zip'];
        $address->city = $validated['city'];
        $address->landmark = $validated['landmark'] ?? '';
        $address->locality = $validated['locality'];
        $address->state = $validated['state'];
        $address->country = $validated['country'] ?? 'United States';
        $address->user_id = Auth::id();
        $address->isdefault = true;
        $address->save();

        return $address;
    }

    private function checkoutSnapshot(Address $address, string $mode): array
    {
        $checkout = Session::get('checkout');

        return [
            'reference' => uniqid('checkout-' . Auth::id() . '-', true),
            'user_id' => Auth::id(),
            'mode' => $mode,
            'totals' => [
                'subtotal' => (float) $checkout['subtotal'],
                'discount' => (float) $checkout['discount'],
                'tax' => (float) $checkout['tax'],
                'total' => (float) $checkout['total'],
            ],
            'address' => [
                'name' => $address->name,
                'phone' => $address->phone,
                'locality' => $address->locality,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'landmark' => $address->landmark,
                'zip' => $address->zip,
            ],
            'items' => Cart::instance('cart')->content()
                ->map(fn ($item) => [
                    'product_id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->qty,
                    'options' => $this->formatCartItemOptions($item->options),
                ])
                ->values()
                ->all(),
        ];
    }

    private function createStripeCheckoutSession(array $snapshot): array
    {
        $secret = config('services.stripe.secret');

        if (!$secret) {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        $amount = $this->stripeAmount($snapshot['totals']['total']);

        if ($amount < 1) {
            throw new \RuntimeException('Stripe checkout total must be greater than zero.');
        }

        $response = Http::asForm()
            ->withToken($secret)
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'client_reference_id' => $snapshot['reference'],
                'customer_email' => optional(Auth::user())->email,
                'success_url' => route('cart.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cart.stripe.cancel'),
                'metadata[reference]' => $snapshot['reference'],
                'metadata[user_id]' => (string) $snapshot['user_id'],
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => strtolower(config('services.stripe.currency', 'usd')),
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][price_data][product_data][name]' => 'Riode cart order',
                'line_items[0][price_data][product_data][description]' => count($snapshot['items']) . ' item(s), including tax and discounts',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Stripe returned ' . $response->status() . ': ' . $response->body());
        }

        $payload = $response->json();

        if (empty($payload['id']) || empty($payload['url'])) {
            throw new \RuntimeException('Stripe did not return a checkout URL.');
        }

        return [
            'id' => $payload['id'],
            'url' => $payload['url'],
        ];
    }

    private function retrieveStripeCheckoutSession(string $sessionId): array
    {
        $secret = config('services.stripe.secret');

        if (!$secret) {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        $response = Http::withToken($secret)
            ->get('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId));

        if (!$response->successful()) {
            throw new \RuntimeException('Stripe returned ' . $response->status() . ': ' . $response->body());
        }

        return $response->json();
    }

    private function stripeAmount(float $total): int
    {
        return (int) round($total * 100);
    }

    private function createOrderFromSnapshot(array $snapshot): Order
    {
        $address = $snapshot['address'];
        $totals = $snapshot['totals'];

        $order = new Order();
        $order->user_id = $snapshot['user_id'];
        $order->subtotal = $totals['subtotal'];
        $order->discount = $totals['discount'];
        $order->tax = $totals['tax'];
        $order->total = $totals['total'];
        $order->name = $address['name'];
        $order->locality = $address['locality'];
        $order->address = $address['address'];
        $order->city = $address['city'];
        $order->state = $address['state'];
        $order->country = $address['country'];
        $order->landmark = $address['landmark'];
        $order->zip = $address['zip'];
        $order->phone = $address['phone'];
        $order->save();

        foreach ($snapshot['items'] as $item) {
            $orderitem = new OrderItem();
            $orderitem->product_id = $item['product_id'];
            $orderitem->order_id = $order->id;
            $orderitem->price = $item['price'];
            $orderitem->quantity = $item['quantity'];
            $orderitem->options = $item['options'];
            $orderitem->save();
        }

        return $order->load(['orderItem.product', 'transaction']);
    }

    private function createTransaction(Order $order, string $mode, string $status): Transaction
    {
        $transaction = new Transaction();
        $transaction->user_id = $order->user_id;
        $transaction->order_id = $order->id;
        $transaction->mode = $mode;
        $transaction->status = $status;
        $transaction->save();

        return $transaction;
    }

    private function completeOrderCheckout(Order $order): void
    {
        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
    }

    private function cartItemOptionsFromRequest(Request $request): array
    {
        return collect($request->only(['color', 'size']))
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->all();
    }

    private function cartQuantityForProduct(Product $product): int
    {
        return (int) Cart::instance('cart')->content()
            ->filter(fn ($item) => (string) $item->id === (string) $product->id)
            ->sum('qty');
    }

    private function formatCartItemOptions($options): ?string
    {
        $formattedOptions = collect($options ?? [])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, $key) => ucwords(str_replace('_', ' ', $key)) . ': ' . $value);

        return $formattedOptions->isEmpty() ? null : $formattedOptions->implode(', ');
    }

}
