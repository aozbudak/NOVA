@if (request()->is('admin') || request()->is('admin/*'))
    @include('errors.admin', ['code' => 401])
@else
    @include('errors.storefront', ['code' => 401])
@endif
