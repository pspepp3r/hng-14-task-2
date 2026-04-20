<?php

declare(strict_types=1);

namespace App\Models;

use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

final class Profile
{
    private string $id;
    private string $name;
    private ?string $gender;
    private ?float $genderProbability;
    private ?int $sampleSize;
    private ?int $age;
    private ?string $ageGroup;
    private ?string $countryId;
    private ?float $countryProbability;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        string $name,
        ?string $gender = null,
        ?float $genderProbability = null,
        ?int $sampleSize = null,
        ?int $age = null,
        ?string $ageGroup = null,
        ?string $countryId = null,
        ?float $countryProbability = null,
        ?string $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->validateName($name);
        $this->id = $id ?? Uuid::uuid7()->toString();
        $this->name = $name;
        $this->gender = $gender;
        $this->genderProbability = $genderProbability;
        $this->sampleSize = $sampleSize;
        $this->age = $age;
        $this->ageGroup = $ageGroup;
        $this->countryId = $countryId;
        $this->countryProbability = $countryProbability;
        $this->createdAt = $createdAt ?? $this->getCurrentTimestamp();
        $this->updatedAt = $updatedAt ?? $this->getCurrentTimestamp();
    }

    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Name cannot be empty');
        }
    }

    private function getCurrentTimestamp(): string
    {
        return (new \DateTime('now', new \DateTimeZone('UTC')))
            ->format('c');
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getGenderProbability(): ?float
    {
        return $this->genderProbability;
    }

    public function getSampleSize(): ?int
    {
        return $this->sampleSize;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getAgeGroup(): ?string
    {
        return $this->ageGroup;
    }

    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function getCountryProbability(): ?float
    {
        return $this->countryProbability;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gender' => $this->gender,
            'gender_probability' => $this->genderProbability,
            'sample_size' => $this->sampleSize,
            'age' => $this->age,
            'age_group' => $this->ageGroup,
            'country_id' => $this->countryId,
            'country_probability' => $this->countryProbability,
            'created_at' => $this->createdAt,
        ];
    }

    public function toArrayFull(): array
    {
        $array = $this->toArray();
        $array['updated_at'] = $this->updatedAt;
        return $array;
    }

    public function toArrayMinimal(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gender' => $this->gender,
            'age' => $this->age,
            'age_group' => $this->ageGroup,
            'country_id' => $this->countryId,
        ];
    }
}
