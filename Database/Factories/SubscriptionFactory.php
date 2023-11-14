<?php

namespace WinLocalInc\Chjs\Database\Factoriess;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory()->create(),
            'subscription_id' => random_int(1000000, 9999999),
            'product_handle' => Str::random(),
        ];
    }

    public function user(User $user): SubscriptionFactory
    {
        return $this->state(['user_id' => $user->user_id]);
    }

    public function workspace(Workspace $workspace): SubscriptionFactory
    {
        return $this->state(['workspace_id' => $workspace->workspace_id]);
    }

    public function productPrice(ProductPrice $productPrice): SubscriptionFactory
    {
        return $this->state(['product_price_handle' => $productPrice->product_price_handle, 'product_handle' => $productPrice->product->product_handle]);
    }
}
