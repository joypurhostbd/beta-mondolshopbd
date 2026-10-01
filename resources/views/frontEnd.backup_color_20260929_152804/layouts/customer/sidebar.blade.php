<div class="customer-auth">
    <div class="customer-img">
        <img src="{{asset(Auth::guard('customer')->user()->image)}}" alt="">
    </div>
    <div class="customer-name">
        <p><small>Hello</small></p>
        <p>{{Auth::guard('customer')->user()->name}}</p>
    </div>
</div>
<div class="sidebar-menu">
    <ul>
        <li><a href="{{route('customer.account')}}" class="{{request()->is('customer/account')?'active':''}}"><i data-feather="user"></i> My Account</a></li>
        <li><a href="{{route('customer.orders')}}" class="{{request()->is('customer/orders') || request()->is('customer/orders/all') ?'active':''}}"><i data-feather="shopping-bag"></i> My Orders</a></li>
        <li style="padding-left: 15px;"><a href="{{route('customer.orders', 'courier-pending')}}" class="{{request()->is('customer/orders/courier-pending')?'active fw-bold text-primary':''}}"><i data-feather="truck"></i> Courier Pending</a></li>
        <li style="padding-left: 15px;"><a href="{{route('customer.orders', 'courier-partial')}}" class="{{request()->is('customer/orders/courier-partial')?'active fw-bold text-primary':''}}"><i data-feather="alert-circle"></i> Courier Partial</a></li>
        <li style="padding-left: 15px;"><a href="{{route('customer.orders', 'courier-cancel')}}" class="{{request()->is('customer/orders/courier-cancel')?'active fw-bold text-danger':''}}"><i data-feather="x-circle"></i> Courier Cancel</a></li>
        <li style="padding-left: 15px;"><a href="{{route('customer.orders', 'courier-delivered')}}" class="{{request()->is('customer/orders/courier-delivered')?'active fw-bold text-success':''}}"><i data-feather="check-circle"></i> Courier Delivered</a></li>
        <li><a href="{{route('customer.profile_edit')}}" class="{{request()->is('customer/profile-edit')?'active':''}}"><i data-feather="edit"></i> Profile Edit</a></li>
        <li><a href="{{route('customer.change_pass')}}" class="{{request()->is('customer/change-password')?'active':''}}"><i data-feather="lock"></i> Change Password</a></li>
        <li><a href="{{ route('customer.logout') }}"
            onclick="event.preventDefault();
            document.getElementById('logout-form').submit();"><i data-feather="log-out"></i> Logout</a></li>
        <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </ul>
</div>