@if (request()->is('admin') || request()->is('admin/*'))
    @include('errors.admin', ['code' => 404])
@else
    @include('errors.storefront', ['code' => 404])
@endif
