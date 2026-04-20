<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Profile;

interface ProfileRepositoryInterface
{
    public function create(Profile $profile): Profile;

    public function findById(string $id): ?Profile;

    public function findByName(string $name): ?Profile;

    public function findAll(array $filters = []): array;

    public function update(Profile $profile): Profile;

    public function delete(string $id): bool;

    public function count(array $filters = []): int;
}
