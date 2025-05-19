@extends('layouts.web-app')

@section('title') Wishlist @endsection

@section('content')
<!-- Page item Area -->
<div id="page_item_area">
    <div class="container">
        <div class="row">
            <div class="col-sm-6 text-left">
                <h3>Wishlist</h3>
            </div>		
            <div class="col-sm-6 text-right">
                <ul class="p_items">
                    <li><a href="{{ route('home') }}">home</a></li>
                    <li><span>Wishlist</span></li>
                </ul>					
            </div>	
        </div>
    </div>
</div>

<!-- Wishlist Page -->
<div class="wishlist-page">
    <div class="container">
        <div class="table-responsive">
            <table class="table cart-table cart_prdct_table text-center">
                <thead>
                    <tr>
                        <th class="cpt_no">#</th>
                        <th class="cpt_img">image</th>
                        <th class="cpt_pn">product name</th>
                        <th class="cpt_p">price</th>
                        <th class="add-cart">add to cart</th>
                        <th class="cpt_r">remove</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wishlists as $wishlist)
                    <tr>
                        <td><span class="cart-number">{{ $loop->iteration }}</span></td>
                        <td><a href="{{ route('product.details',$wishlist->product?->slug) }}" class="cp_img"><img src="{{ getProductMainImage($wishlist->product?->id) }}" alt="" /></a></td>
                        <td><a href="{{ route('product.details',$wishlist->product?->slug) }}" class="cart-pro-title">{{ $wishlist->product?->name }}</a></td>
                        <td><p class="cart-pro-price">Rs {{ $wishlist->product?->total_price }}</p></td>
                        <td><a href="javascript:void(0);" class="btn border-btn add-to-cart-btn" data-product-id="{{ $wishlist->product?->id }}">add to cart</a></td>
                        <td><a href="{{ route('wishlist.delete',$wishlist->id) }}" style="background: transparent;border: none;font-size: 20px;transition: .5s;"><i class="fa fa-trash"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection