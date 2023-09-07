<?php

namespace WinLocalInc\Chjs\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Exceptions\CustomerAlreadyCreated;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;

trait HandleCustomer
{

    public function chargifyId() :?int
    {
        return $this->chargify_id;
    }


    public function hasChargifyId(): bool
    {
        return ! is_null($this->chargify_id);
    }


    /**
     * @throws InvalidCustomer
     */
    protected function assertCustomerExists() :void
    {
        if (! $this->hasChargifyId()) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }


    /**
     * @throws CustomerAlreadyCreated
     */
    public function createAsChargifyCustomer(?string $token = null): object
    {
        if ($this->hasChargifyId()) {
            throw CustomerAlreadyCreated::exists($this);
        }
        $paymentProfile = null;
        $customer = maxio()->customer->create($this);

        if($token)
        {
            $paymentProfile = maxio()->paymentProfile->create(customerId: $customer->id, chargifyToken: $token);
        }

        $this->chargify_id = $customer->id;

        $this->saveQuietly();

        return (object) ['chargifyId' => $customer->id, 'paymentProfile' => $paymentProfile];
    }

    /**
     * Update the underlying Chargify customer information for the model.
     *
     * @param  array  $options
     * @return \Chargify\Customer
     */
    public function updateChargifyCustomer(array $options = [])
    {
        return $this->chargify()->customers->update(
            $this->chargify_id, $options
        );
    }

    /**
     * Get the Chargify customer instance for the current user or create one.
     *
     * @param  array  $options
     * @return \Chargify\Customer
     */
    public function createOrGetChargifyCustomer(array $options = [])
    {
        if ($this->hasChargifyId()) {
            return $this->asChargifyCustomer($options['expand'] ?? []);
        }

        return $this->createAsChargifyCustomer($options);
    }

    /**
     * Get the Chargify customer for the model.
     *
     * @param  array  $expand
     * @return \Chargify\Customer
     */
    public function asChargifyCustomer(array $expand = [])
    {
        $this->assertCustomerExists();

        return $this->chargify()->customers->retrieve(
            $this->chargify_id, ['expand' => $expand]
        );
    }

    /**
     * Get the name that should be synced to Chargify.
     *
     * @return string|null
     */
    public function chargifyName()
    {
        return $this->name ?? null;
    }

    /**
     * Get the email address that should be synced to Chargify.
     *
     * @return string|null
     */
    public function chargifyEmail()
    {
        return $this->email ?? null;
    }

    /**
     * Get the phone number that should be synced to Chargify.
     *
     * @return string|null
     */
    public function chargifyPhone()
    {
        return $this->phone ?? null;
    }

    /**
     * Get the address that should be synced to Chargify.
     *
     * @return array|null
     */
    public function chargifyAddress()
    {
        // return [
        //     'city' => 'Little Rock',
        //     'country' => 'US',
        //     'line1' => '1 Main St.',
        //     'line2' => 'Apartment 5',
        //     'postal_code' => '72201',
        //     'state' => 'Arkansas',
        // ];
    }

    /**
     * Get the locales that should be synced to Chargify.
     *
     * @return array|null
     */
    public function chargifyPreferredLocales()
    {
        // return ['en'];
    }

    /**
     * Get the metadata that should be synced to Chargify.
     *
     * @return array|null
     */
    public function chargifyMetadata()
    {
        // return [];
    }

    /**
     * Sync the customer's information to Chargify.
     *
     * @return \Chargify\Customer
     */
    public function syncChargifyCustomerDetails()
    {
        return $this->updateChargifyCustomer([
            'name' => $this->chargifyName(),
            'email' => $this->chargifyEmail(),
            'phone' => $this->chargifyPhone(),
            'address' => $this->chargifyAddress(),
            'preferred_locales' => $this->chargifyPreferredLocales(),
            'metadata' => $this->chargifyMetadata(),
        ]);
    }

    /**
     * The discount that applies to the customer, if applicable.
     *
     * @return \Laravel\Cashier\Discount|null
     */
    public function discount()
    {
        $customer = $this->asChargifyCustomer(['discount.promotion_code']);

        return $customer->discount
            ? new Discount($customer->discount)
            : null;
    }

    /**
     * Apply a coupon to the customer.
     *
     * @param  string  $coupon
     * @return void
     */
    public function applyCoupon($coupon)
    {
        $this->assertCustomerExists();

        $this->updateChargifyCustomer([
            'coupon' => $coupon,
        ]);
    }

    /**
     * Apply a promotion code to the customer.
     *
     * @param  string  $promotionCodeId
     * @return void
     */
    public function applyPromotionCode($promotionCodeId)
    {
        $this->assertCustomerExists();

        $this->updateChargifyCustomer([
            'promotion_code' => $promotionCodeId,
        ]);
    }

    /**
     * Retrieve a promotion code by its code.
     *
     * @param  string  $code
     * @param  array  $options
     * @return \Laravel\Cashier\PromotionCode|null
     */
    public function findPromotionCode($code, array $options = [])
    {
        $codes = $this->chargify()->promotionCodes->all(array_merge([
            'code' => $code,
            'limit' => 1,
        ], $options));

        if ($codes && $promotionCode = $codes->first()) {
            return new PromotionCode($promotionCode);
        }
    }

    /**
     * Retrieve a promotion code by its code.
     *
     * @param  string  $code
     * @param  array  $options
     * @return \Laravel\Cashier\PromotionCode|null
     */
    public function findActivePromotionCode($code, array $options = [])
    {
        return $this->findPromotionCode($code, array_merge($options, ['active' => true]));
    }

    /**
     * Get the total balance of the customer.
     *
     * @return string
     */
    public function balance()
    {
        return $this->formatAmount($this->rawBalance());
    }

    /**
     * Get the raw total balance of the customer.
     *
     * @return int
     */
    public function rawBalance()
    {
        if (! $this->hasChargifyId()) {
            return 0;
        }

        return $this->asChargifyCustomer()->balance;
    }

    /**
     * Return a customer's balance transactions.
     *
     * @param  int  $limit
     * @param  array  $options
     * @return \Illuminate\Support\Collection
     */
    public function balanceTransactions($limit = 10, array $options = [])
    {
        if (! $this->hasChargifyId()) {
            return new Collection();
        }

        $transactions = $this->chargify()
            ->customers
            ->allBalanceTransactions($this->chargify_id, array_merge(['limit' => $limit], $options));

        return Collection::make($transactions->data)->map(function ($transaction) {
            return new CustomerBalanceTransaction($this, $transaction);
        });
    }

    /**
     * Credit a customer's balance.
     *
     * @param  int  $amount
     * @param  string|null  $description
     * @param  array  $options
     * @return \Laravel\Cashier\CustomerBalanceTransaction
     */
    public function creditBalance($amount, $description = null, array $options = [])
    {
        return $this->applyBalance(-$amount, $description, $options);
    }

    /**
     * Debit a customer's balance.
     *
     * @param  int  $amount
     * @param  string|null  $description
     * @param  array  $options
     * @return \Laravel\Cashier\CustomerBalanceTransaction
     */
    public function debitBalance($amount, $description = null, array $options = [])
    {
        return $this->applyBalance($amount, $description, $options);
    }

    /**
     * Apply a new amount to the customer's balance.
     *
     * @param  int  $amount
     * @param  string|null  $description
     * @param  array  $options
     * @return \Laravel\Cashier\CustomerBalanceTransaction
     */
    public function applyBalance($amount, $description = null, array $options = [])
    {
        $this->assertCustomerExists();

        $transaction = $this->chargify()
            ->customers
            ->createBalanceTransaction($this->chargify_id, array_filter(array_merge([
                'amount' => $amount,
                'currency' => $this->preferredCurrency(),
                'description' => $description,
            ], $options)));

        return new CustomerBalanceTransaction($this, $transaction);
    }

    /**
     * Get the Chargify supported currency used by the customer.
     *
     * @return string
     */
    public function preferredCurrency()
    {
        return config('cashier.currency');
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->preferredCurrency());
    }

    /**
     * Get the Chargify billing portal for this customer.
     *
     * @param  string|null  $returnUrl
     * @param  array  $options
     * @return string
     */
    public function billingPortalUrl($returnUrl = null, array $options = [])
    {
        $this->assertCustomerExists();

        return $this->chargify()->billingPortal->sessions->create(array_merge([
            'customer' => $this->chargifyId(),
            'return_url' => $returnUrl ?? route('home'),
        ], $options))['url'];
    }

    /**
     * Generate a redirect response to the customer's Chargify billing portal.
     *
     * @param  string|null  $returnUrl
     * @param  array  $options
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToBillingPortal($returnUrl = null, array $options = [])
    {
        return new RedirectResponse(
            $this->billingPortalUrl($returnUrl, $options)
        );
    }

    /**
     * Get a collection of the customer's TaxID's.
     *
     * @return \Illuminate\Support\Collection|\Chargify\TaxId[]
     */
    public function taxIds(array $options = [])
    {
        $this->assertCustomerExists();

        return new Collection(
            $this->chargify()->customers->allTaxIds($this->chargify_id, $options)->data
        );
    }

    /**
     * Find a TaxID by ID.
     *
     * @return \Chargify\TaxId|null
     */
    public function findTaxId($id)
    {
        $this->assertCustomerExists();

        try {
            return $this->chargify()->customers->retrieveTaxId(
                $this->chargify_id, $id, []
            );
        } catch (ChargifyInvalidRequestException $exception) {
            //
        }
    }

    /**
     * Create a TaxID for the customer.
     *
     * @param  string  $type
     * @param  string  $value
     * @return \Chargify\TaxId
     */
    public function createTaxId($type, $value)
    {
        $this->assertCustomerExists();

        return $this->chargify()->customers->createTaxId($this->chargify_id, [
            'type' => $type,
            'value' => $value,
        ]);
    }

    /**
     * Delete a TaxID for the customer.
     *
     * @param  string  $id
     * @return void
     */
    public function deleteTaxId($id)
    {
        $this->assertCustomerExists();

        try {
            $this->chargify()->customers->deleteTaxId($this->chargify_id, $id);
        } catch (ChargifyInvalidRequestException $exception) {
            //
        }
    }

    /**
     * Determine if the customer is not exempted from taxes.
     *
     * @return bool
     */
    public function isNotTaxExempt()
    {
        return $this->asChargifyCustomer()->tax_exempt === ChargifyCustomer::TAX_EXEMPT_NONE;
    }

    /**
     * Determine if the customer is exempted from taxes.
     *
     * @return bool
     */
    public function isTaxExempt()
    {
        return $this->asChargifyCustomer()->tax_exempt === ChargifyCustomer::TAX_EXEMPT_EXEMPT;
    }

    /**
     * Determine if reverse charge applies to the customer.
     *
     * @return bool
     */
    public function reverseChargeApplies()
    {
        return $this->asChargifyCustomer()->tax_exempt === ChargifyCustomer::TAX_EXEMPT_REVERSE;
    }


}
