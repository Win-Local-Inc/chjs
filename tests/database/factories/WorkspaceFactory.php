<?php

namespace WinLocalInc\Chjs\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Uuid::uuid4()->toString(),
            'workspace_name' => $this->faker->company,
        ];
    }
}
