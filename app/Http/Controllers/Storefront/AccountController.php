<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Support\CustomerAccount;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(CustomerAccount $account): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.index', [
            'orders' => $account->orderSummaries($this->customerRecord()),
        ]);
    }

    public function orders(CustomerAccount $account): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.orders', [
            'orders' => $account->orderSummaries($this->customerRecord()),
        ]);
    }

    public function profile(): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.profile');
    }

    public function update(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $customer = $this->authenticatedCustomer();

        if ($customer === null) {
            return redirect()->route('login');
        }

        $currentEmail = (string) ($customer['email'] ?? '');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($currentEmail, 'email'),
                Rule::unique('customers', 'email')->ignore($currentEmail, 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $records->updateCustomerProfile($currentEmail, $data);

        $this->storeCustomerSession($data);

        return redirect()
            ->route('account.profile')
            ->with('status', __('storefront.account.profile_updated'));
    }

    public function password(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $customer = $this->authenticatedCustomer();

        if ($customer === null) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $records->updateCustomerPassword(
            (string) ($customer['email'] ?? ''),
            $request->string('current_password')->toString(),
            $request->string('password')->toString(),
        )) {
            return back()->withErrors(['current_password' => __('auth.password')]);
        }

        return redirect()
            ->route('account.profile')
            ->with('status', __('storefront.account.password_updated'));
    }

    public function addresses(): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.addresses', $this->addressViewData());
    }

    public function editAddress(CustomerAddress $address): View|RedirectResponse
    {
        $customer = $this->customerRecord();

        if ($this->authenticatedCustomer() === null) {
            return redirect()->route('login');
        }

        abort_if($customer === null, 404);

        $this->ownedAddress($customer, $address);

        return $this->guardedView('storefront.account.addresses', $this->addressViewData($address));
    }

    public function storeAddress(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $customer = $this->writableCustomer();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        $records->saveCustomerAddress($customer, $this->validatedAddress($request));

        return redirect()
            ->route('account.addresses')
            ->with('status', __('storefront.account.address_saved'));
    }

    public function updateAddress(Request $request, CustomerAddress $address, DatabaseRecords $records): RedirectResponse
    {
        $customer = $this->writableCustomer();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        $records->saveCustomerAddress($customer, $this->validatedAddress($request), $this->ownedAddress($customer, $address));

        return redirect()
            ->route('account.addresses')
            ->with('status', __('storefront.account.address_saved'));
    }

    public function destroyAddress(CustomerAddress $address, DatabaseRecords $records): RedirectResponse
    {
        $customer = $this->writableCustomer();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        $records->deleteCustomerAddress($customer, $this->ownedAddress($customer, $address));

        return redirect()
            ->route('account.addresses')
            ->with('status', __('storefront.account.address_deleted'));
    }

    public function defaultAddress(CustomerAddress $address, DatabaseRecords $records): RedirectResponse
    {
        $customer = $this->writableCustomer();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        $records->setDefaultCustomerAddress($customer, $this->ownedAddress($customer, $address));

        return redirect()
            ->route('account.addresses')
            ->with('status', __('storefront.account.address_default_updated'));
    }

    public function settings(): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.settings');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('storefront.customer');

        return redirect()->route('home');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardedView(string $view, array $data = []): View|RedirectResponse
    {
        if ($this->authenticatedCustomer() === null) {
            return redirect()->route('login');
        }

        return view($view, $data);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function authenticatedCustomer(): ?array
    {
        $customer = session('storefront.customer');

        return is_array($customer) ? $customer : null;
    }

    /**
     * @param  array{first_name: string, last_name: string, email: string, phone?: string|null}  $customer
     */
    private function storeCustomerSession(array $customer): void
    {
        session([
            'storefront.customer' => [
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'] ?? '',
            ],
        ]);
    }

    private function customerRecord(): ?Customer
    {
        $session = $this->authenticatedCustomer();
        $email = (string) ($session['email'] ?? '');

        if ($email === '') {
            return null;
        }

        return Customer::query()->where('email', $email)->first();
    }

    private function writableCustomer(): Customer|RedirectResponse
    {
        if ($this->authenticatedCustomer() === null) {
            return redirect()->route('login');
        }

        $customer = $this->customerRecord();

        if ($customer === null) {
            return redirect()->route('account.addresses');
        }

        return $customer;
    }

    private function ownedAddress(Customer $customer, CustomerAddress $address): CustomerAddress
    {
        abort_unless($address->customer_id === $customer->id, 404);

        return $address;
    }

    /**
     * @return array{addresses: Collection<int, CustomerAddress>, editing: CustomerAddress|null}
     */
    private function addressViewData(?CustomerAddress $editing = null): array
    {
        $customer = $this->customerRecord();

        return [
            'addresses' => $customer === null
                ? collect()
                : $customer->addresses()->orderByDesc('is_default')->orderBy('created_at')->orderBy('id')->get(),
            'editing' => $editing,
        ];
    }

    /**
     * @return array{title: string, first_name: string, last_name: string, phone: string|null, city: string, district: string, address_line: string, postal_code: string|null, is_default: bool}
     */
    private function validatedAddress(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }
}
