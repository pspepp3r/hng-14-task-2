<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use App\Models\Profile;
use PDO;
use PDOStatement;

final class ProfileRepository implements ProfileRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function create(Profile $profile): Profile
    {
        $query = <<<SQL
        INSERT INTO profiles (
            id, name, gender, gender_probability, sample_size,
            age, age_group, country_id, country_probability,
            created_at, updated_at
        ) VALUES (
            UNHEX(REPLACE(?, '-', '')),
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $profile->getId(),
            $profile->getName(),
            $profile->getGender(),
            $profile->getGenderProbability(),
            $profile->getSampleSize(),
            $profile->getAge(),
            $profile->getAgeGroup(),
            $profile->getCountryId(),
            $profile->getCountryProbability(),
            $profile->getCreatedAt(),
            $profile->getUpdatedAt(),
        ]);

        return $profile;
    }

    public function findById(string $id): ?Profile
    {
        $query = 'SELECT * FROM profiles WHERE id = UNHEX(REPLACE(?, \'-\', \'\'))';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->mapToProfile($result) : null;
    }

    public function findByName(string $name): ?Profile
    {
        $query = 'SELECT * FROM profiles WHERE LOWER(name) = LOWER(?)';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$name]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->mapToProfile($result) : null;
    }

    public function findAll(array $filters = []): array
    {
        $query = 'SELECT * FROM profiles WHERE 1=1';
        $params = [];

        if (!empty($filters['gender'])) {
            $query .= ' AND LOWER(gender) = LOWER(?)';
            $params[] = $filters['gender'];
        }

        if (!empty($filters['country_id'])) {
            $query .= ' AND LOWER(country_id) = LOWER(?)';
            $params[] = $filters['country_id'];
        }

        if (!empty($filters['age_group'])) {
            $query .= ' AND LOWER(age_group) = LOWER(?)';
            $params[] = $filters['age_group'];
        }

        $query .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'mapToProfile'], $results);
    }

    public function count(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as total FROM profiles WHERE 1=1';
        $params = [];

        if (!empty($filters['gender'])) {
            $query .= ' AND LOWER(gender) = LOWER(?)';
            $params[] = $filters['gender'];
        }

        if (!empty($filters['country_id'])) {
            $query .= ' AND LOWER(country_id) = LOWER(?)';
            $params[] = $filters['country_id'];
        }

        if (!empty($filters['age_group'])) {
            $query .= ' AND LOWER(age_group) = LOWER(?)';
            $params[] = $filters['age_group'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($result['total'] ?? 0);
    }

    public function update(Profile $profile): Profile
    {
        $query = <<<SQL
        UPDATE profiles SET
            gender = ?,
            gender_probability = ?,
            sample_size = ?,
            age = ?,
            age_group = ?,
            country_id = ?,
            country_probability = ?,
            updated_at = ?
        WHERE id = UNHEX(REPLACE(?, '-', ''))
        SQL;

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $profile->getGender(),
            $profile->getGenderProbability(),
            $profile->getSampleSize(),
            $profile->getAge(),
            $profile->getAgeGroup(),
            $profile->getCountryId(),
            $profile->getCountryProbability(),
            $profile->getUpdatedAt(),
            $profile->getId(),
        ]);

        return $profile;
    }

    public function delete(string $id): bool
    {
        $query = 'DELETE FROM profiles WHERE id = UNHEX(REPLACE(?, \'-\', \'\'))';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    private function mapToProfile(array $data): Profile
    {
        return new Profile(
            name: $data['name'],
            gender: $data['gender'],
            genderProbability: $data['gender_probability'] !== null ? (float)$data['gender_probability'] : null,
            sampleSize: $data['sample_size'] !== null ? (int)$data['sample_size'] : null,
            age: $data['age'] !== null ? (int)$data['age'] : null,
            ageGroup: $data['age_group'],
            countryId: $data['country_id'],
            countryProbability: $data['country_probability'] !== null ? (float)$data['country_probability'] : null,
            id: $this->binaryToUuid($data['id']),
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
        );
    }

    private function binaryToUuid(string $binary): string
    {
        $hex = bin2hex($binary);
        return substr($hex, 0, 8) . '-' .
            substr($hex, 8, 4) . '-' .
            substr($hex, 12, 4) . '-' .
            substr($hex, 16, 4) . '-' .
            substr($hex, 20, 12);
    }
}
