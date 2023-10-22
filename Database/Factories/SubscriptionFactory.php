<?php

namespace WinLocalInc\Chjs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
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

    public function product(Product $product): SubscriptionFactory
    {
        return $this->state(['product_id' => $product->product_id, 'product_handle' => $product->product_handle]);
    }
}
