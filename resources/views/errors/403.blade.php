@if (request()->is('admin') || request()->is('admin/*'))
    @include('errors.admin', ['code' => 403])
@else
    @include('errors.storefront', ['code' => 403])
@endif
