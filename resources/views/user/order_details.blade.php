@extends('layouts.app')
@section('content')
    <style>
        .pt-90 {
            padding-top: 90px !important;
        }

        .pr-6px {
            padding-right: 6px;
            text-transform: uppercase;
        }

        .my-account .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 40px;
            border-bottom: 1px solid;
            padding-bottom: 13px;
        }

        .my-account .wg-box {
            display: -webkit-box;
            display: -moz-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            padding: 24px;
            flex-direction: column;
            gap: 24px;
            border-radius: 12px;
            background: var(--White);
            box-shadow: 0px 4px 24px 2px rgba(20, 25, 38, 0.05);
        }

        .table-transaction>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #fff !important;

        }

        .table-transaction th,
        .table-transaction td {
            padding: 0.625rem 1.5rem .25rem !important;
            color: #000 !important;
        }

        .table> :not(caption)>tr>th {
            padding: 0.625rem 1.5rem .25rem !important;
            background-color: #6a6e51 !important;
        }

        .table-bordered>:not(caption)>*>* {
            border-width: inherit;
            line-height: 32px;
            font-size: 14px;
            border: 1px solid #e1e1e1;
            vertical-align: middle;
        }

        .table-striped .image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        .table-striped td:nth-child(1) {
            min-width: 250px;
            padding-bottom: 7px;
        }

        .pname {
            display: flex;
            gap: 13px;
        }

        .table-bordered> :not(caption)>tr>th,
        .table-bordered> :not(caption)>tr>td {
            border-width: 1px 1px;
            border-color: #6a6e51;
        }

        .table> :not(caption)>tr>th {
            padding: 0.625rem 1.5rem .625rem !important;
            background-color: #6a6e51 !important;
        }

        .table>tr>td {
            padding: 0.625rem 1.5rem .625rem !important;
        }

        .table-bordered> :not(caption)>tr>th,
        .table-bordered> :not(caption)>tr>td {
            border-width: 1px 1px;
            border-color: #6a6e51;
        }

        .table> :not(caption)>tr>td {
            padding: .8rem 1rem !important;
        }

        .custom-badge-success {
            background-color: #40c710 !important;
        }

        .custom-badge-danger {
            background-color: #f44032 !important;
        }

        .custom-badge-warning {
            background-color: #ffc107 !important;
            /* لون أصفر داكن */
            color: rgb(0, 0, 0) !important;
        }
    </style>

    <main class="pt-90" style="padding-top: 0px;">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <h2 class="page-title">Orders Details</h2>
            <div class="row">
                <div class="col-lg-2">
                    @include('user.account-nav')
                </div>

                <div class="col-lg-10">
                    <div class="wg-box">
                        <div class="flex items-center justify-between gap10 flex-wrap">

                            <div class="row">
                                <div class="col-6">
                                    <h5>Ordered Details</h5>
                                </div>
                                <div class="col-6 text-right">
                                    <a class="btn btn-sm btn-danger" href="{{ route('user.orders') }}">Back</a>
                                </div>



                            </div>
                        </div>
                        <div class="table-responsive ">
                            @if (Session::has('status'))
                            <p class="alert alert-success">{{ Session::get('status') }}</p>
                        @endif
                            <table class="table table-bordered table-striped table-transaction">
                                <tr>
                                    <th>Order No</th>
                                    <td>{{ $orders->id }}</td>
                                    <th class="text-center">Mobile</th>
                                    <td class="text-center">{{ $orders->phone }}</td>
                                    <th class="text-center">Zip code</th>
                                    <td class="text-center">{{ $orders->zip }}</td>
                                </tr>
                                <tr>
                                    <th>Order Date</th>
                                    <td>{{ $orders->created_at }}</td>
                                    <th class="text-center">Delivery Date</th>
                                    <td class="text-center">{{ $orders->delivered_date }}</td>
                                    <th class="text-center">Canceled Date</th>
                                    <td class="text-center">{{ $orders->canceled_date }}</td>

                                </tr>
                                <tr>
                                    <th>Order Status</th>
                                    <td colspan="5 " style="font-size: large">
                                        @if ($orders->status == 'delivered')
                                            <span class="badge custom-badge-success">Delivered</span>
                                        @elseif($orders->status == 'canceled')
                                            <span class="badge custom-badge-danger">Canceled</span>
                                        @else
                                            <span class="badge custom-badge-warning">Ordered</span>
                                        @endif
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>


                    <div class="wg-box mt-5">
                        <div class="flex items-center justify-between gap10 flex-wrap">
                            <div class="wg-filter flex-grow">
                                <h5>Ordered Items</h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">SKU</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Brand</th>
                                        <th class="text-center">Options</th>
                                        <th class="text-center">Return Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orderItem as $item)
                                        <tr>

                                            <td class="pname">
                                                <div class="image">
                                                    <img src="{{ asset('uploads/products/thumbnails') }}/{{ $item->product->image }}"
                                                        alt="{{ $item->product->name }}" class="image">
                                                </div>
                                                <div class="name">
                                                    <a href="{{ route('shop.product.details', ['product_slug' => $item->product->slug]) }}"
                                                        target="_blank" class="body-title-2">{{ $item->product->name }}</a>
                                                </div>
                                            </td>
                                            <td class="text-center">${{ $item->price }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-center">{{ $item->product->SKU }}</td>
                                            <td class="text-center">{{ $item->product->category->name }}</td>
                                            <td class="text-center">{{ $item->product->barnd->name }}</td>
                                            <td class="text-center">{{ $item->options }}</td>
                                            <td class="text-center">{{ $item->rstatus == 0 ? 'NO' : 'Yes' }}</td>
                                            <td class="text-center">
                                                <div class="list-icon-function view-icon">
                                                    <div class="item eye">
                                                        <i class="icon-eye"></i>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach



                                </tbody>
                            </table>
                        </div>

                        <div class="divider"></div>
                        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">

                            {{ $orderItem->links('pagination::bootstrap-5') }}
                        </div>
                    </div>

                    <div class="wg-box mt-5">
                        <h5>Shipping Address</h5>
                        <div class="my-account__address-item col-md-6">
                            <div class="my-account__address-item__detail">
                                <p>{{ $orders->name }}</p>
                                <p>{{ $orders->address }}</p>
                                <p>{{ $orders->locality }}</p>
                                <p>{{ $orders->city }} ,{{ $orders->country }}</p>
                                <p>{{ $orders->landmark }}</p>
                                <p>{{ $orders->zip }}</p>
                                <br>
                                <p>Mobile : {{ $orders->phone }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="wg-box mt-5">
                        <h5>Transactions</h5>
                        <table class="table table-striped table-bordered table-transaction">
                            <tbody>
                                <tr>
                                    <th>Subtotal</th>
                                    <td>${{ $orders->subtotal }}</td>
                                    <th>Tax</th>
                                    <td>${{ $orders->tax }}</td>
                                    <th>Discount</th>
                                    <td>${{ $orders->discount }}</td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td>${{ $orders->total }}</td>
                                    <th>Payment Mode</th>
                                    <td>${{ $transation->mode }}</td>
                                    <th>Status</th>
                                    <td style="font-size: large">
                                        @if ($transation->status == 'approved')
                                            <span class="badge custom-badge-success">Approved</span>
                                        @elseif ($transation->status == 'declined')
                                            <span class="badge custom-badge-danger">Declined</span>
                                        @elseif ($transation->status == 'refunded')
                                            <span class="badge bg-secondery">Refunded</span>
                                        @else
                                            <span class="badge custom-badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    @if ($orders->status=='ordered')


                    <div class="wg-box mt-5 text-right">
                        <form action="{{ route('user.order.canceled') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_id" value="{{ $orders->id }}">
                            <button type="button" class="btn btn-danger cancel-order">Canceled Order</button>
                        </form>
                    </div>
                    @endif
                </div>

            </div>
        </section>
    </main>
@endsection
@push('scripts')
    <script>
        $(function() {
            $('.cancel-order').on('click', function(event) {
                event.preventDefault();
                var form = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "You want to cancel this order ?",
                    type: "warning",
                    buttons: ["No", "Yes"],
                    confirmButtonColor: '#FF0019FF'
                }).then(function(result) {
                    if (result) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
