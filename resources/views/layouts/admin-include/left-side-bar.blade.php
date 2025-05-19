<div id="left-sidebar" class="sidebar">
    <h5 class="brand-name">{{ config('app.name', 'Laravel') }}<a href="javascript:void(0)" class="menu_option float-right"><i class="icon-grid font-16" data-toggle="tooltip" data-placement="left" title="Grid & List Toggle"></i></a></h5>
    <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#menu-uni">Admin</a></li>
        {{-- <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#menu-admin">Admin</a></li> --}}
    </ul>
    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="menu-uni" role="tabpanel">
            <nav class="sidebar-nav">
                <ul class="metismenu">
                    @can('Dashboard')
                    <li class="active"><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i><span>Dashboard</span></a></li>
                    @endcan

                    @can('Order Show')
                    <li>
                        <a href="{{ route('order.index') }}">
                            <i class="fa fa-shopping-cart"></i><span>Orders</span>
                        </a>
                    </li>
                    @endcan

                    @canany(['Permission Show', 'Role Show'])
                    <li>
                        <a href="javascript:;">
                            <i class="fa fa-lock"></i><span>Roles Permissions</span>
                        </a>
                        <ul>
                            @can('Role Show')
                            <li><a href="{{ route('roles') }}">Roles</a></li>
                            @endcan
                            @can('Permission Show')
                            <li><a href="{{ route('permission') }}">Permission</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    @canany(['Web User Show', 'System User Show'])
                    <li>
                        <a href="javascript:;">
                            <i class="fa fa-users"></i><span>Users</span>
                        </a>
                        <ul>
                            @can('Web User Show')
                            <li><a href="{{ route('web-user.index') }}">Web Users</a></li>
                            @endcan
                            @can('System User Show')
                            <li><a href="{{ route('system-user.index') }}">System Users</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    @canany(['Product Show', 'Category Show', 'Brand Show'])
                    <li>
                        <a href="javascript:;">
                            <i class="fa fa-gift"></i><span>Product Management</span>
                        </a>
                        <ul>
                            @can('Category Show')
                            <li><a href="{{ route('category.index') }}">Category</a></li>
                            @endcan
                            @can('Brand Show')
                            <li><a href="{{ route('brand.index') }}">Brand</a></li>
                            @endcan
                            @can('Product Show')
                            <li><a href="{{ route('product.index') }}">Products</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    @can('Slider Show')
                    <li class="active"><a href="{{ route('slider.index') }}"><i class="fa fa-image"></i><span>Slider</span></a></li>
                    @endcan

                    @can('Coupon Show')
                    <li class="active"><a href="{{ route('coupon.index') }}"><i class="fa fa-ticket"></i><span>Coupon</span></a></li>
                    @endcan

                    @can('Testimonial Show')
                    <li class="active"><a href="{{ route('testimonial.index') }}"><i class="fa fa-comment"></i><span>Testimonial</span></a></li>
                    @endcan

                    @can('ContactUs Show')
                    <li class="active"><a href="{{ route('contact-us.index') }}"><i class="fa fa-envelope"></i><span>Contact Us</span></a></li>
                    @endcan
                </ul>
            </nav>
        </div>
    </div>
</div>