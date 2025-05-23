<ul id="account-panel" class="nav nav-pills flex-column">
    <li class="nav-item">
        <a href="{{ route('account.profile') }}" class="nav-link font-weight-bold" role="tab" aria-controls="tab-login"
            aria-expanded="false"><i class="fas fa-user-alt"></i>{{ __('My Profile') }}</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('account.orders') }}" class="nav-link font-weight-bold" role="tab"
            aria-controls="tab-register" aria-expanded="false"><i
                class="fas fa-shopping-bag"></i>{{ __('My Orders') }}</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('account.wishlist') }}" class="nav-link font-weight-bold" role="tab"
            aria-controls="tab-register" aria-expanded="false"><i class="fas fa-heart"></i> {{ __('Wishlist') }}</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('account.changePassword') }}" class="nav-link font-weight-bold" role="tab"
            aria-controls="tab-register" aria-expanded="false"><i class="fas fa-lock"></i>{{ __('Change Password') }}
        </a>
    </li>
    <li class="nav-item">
        <form action="{{ route('account.logout') }}" method="GET" style="display: inline;">
            @csrf
            <button type="submit" class="nav-link font-weight-bold" style="background: none; border: none; color: inherit; padding: 0;">
                <i class="fas fa-sign-out-alt"></i>{{ __('Logout') }}
            </button>
        </form>
    </li>
</ul>
