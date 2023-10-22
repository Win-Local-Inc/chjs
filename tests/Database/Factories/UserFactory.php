<?php

namespace WinLocalInc\Chjs\Tests\Database\Factories;

use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'user_id' => Uuid::uuid4()->toString(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'workspace_id' => null,
            'chargify_id' => null
        ];
    }


    public
    function workspace (Workspace $workspace): UserFactory
    {
        return $this->state( ['workspace_id' => $workspace->workspace_id] );
    }

    public
    function withChargifyId (): UserFactory
    {
        return $this->state( ['chargify_id' => random_int(1000000, 9999999)] );
    }
}
