<div class="account-atmosphere">
    <div class="mx-auto grid w-full max-w-[1600px] gap-4 px-4 py-5 md:grid-cols-[15rem_minmax(0,1fr)] md:gap-5 md:px-8 md:py-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <x-account.nav />
        <div class="flex min-w-0 flex-col gap-4">
            {{ $slot }}
        </div>
    </div>
</div>
