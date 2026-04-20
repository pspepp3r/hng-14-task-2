<?php

declare(strict_types=1);

namespace App\Services;

use App\External\Clients\GenderizeClient;
use App\External\Clients\AgifyClient;
use App\External\Clients\NationalizeClient;
use App\Models\Profile;
use App\Repositories\ProfileRepositoryInterface;
use Exception;

final class ProfileService
{
    private GenderizeClient $genderizeClient;
    private AgifyClient $agifyClient;
    private NationalizeClient $nationalizeClient;
    private ProfileRepositoryInterface $repository;

    public function __construct(ProfileRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->genderizeClient = new GenderizeClient();
        $this->agifyClient = new AgifyClient();
        $this->nationalizeClient = new NationalizeClient();
    }

    /**
     * Create or retrieve a profile
     * @throws Exception
     */
    public function createOrGetProfile(string $name): array
    {
        // Check if profile already exists
        $existing = $this->repository->findByName($name);
        if ($existing !== null) {
            return [
                'profile' => $existing,
                'isNew' => false,
                'message' => 'Profile already exists',
            ];
        }

        // Fetch data from external APIs
        try {
            $genderizeData = $this->fetchGenderizeData($name);
            $agifyData = $this->fetchAgifyData($name);
            $nationalizeData = $this->fetchNationalizeData($name);
        } catch (Exception $e) {
            throw new Exception($this->extractApiName($e) . ' returned an invalid response');
        }

        // Classify age group
        $ageGroup = AgeGroupClassifier::classify($agifyData['age']);

        // Create profile model
        $profile = new Profile(
            name: $name,
            gender: $genderizeData['gender'],
            genderProbability: $genderizeData['gender_probability'],
            sampleSize: $genderizeData['sample_size'],
            age: $agifyData['age'],
            ageGroup: $ageGroup,
            countryId: $nationalizeData['country_id'],
            countryProbability: $nationalizeData['country_probability'],
        );

        // Store in database
        $this->repository->create($profile);

        return [
            'profile' => $profile,
            'isNew' => true,
            'message' => null,
        ];
    }

    public function getProfileById(string $id): ?Profile
    {
        return $this->repository->findById($id);
    }

    /**
     * @return Profile[]
     */
    public function getAllProfiles(array $filters = []): array
    {
        return $this->repository->findAll($filters);
    }

    public function getProfileCount(array $filters = []): int
    {
        return $this->repository->count($filters);
    }

    public function deleteProfile(string $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * @throws Exception
     */
    private function fetchGenderizeData(string $name): array
    {
        try {
            $response = $this->genderizeClient->fetch($name);
            return $this->genderizeClient->validate($response);
        } catch (Exception $e) {
            throw new Exception('Genderize: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function fetchAgifyData(string $name): array
    {
        try {
            $response = $this->agifyClient->fetch($name);
            return $this->agifyClient->validate($response);
        } catch (Exception $e) {
            throw new Exception('Agify: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function fetchNationalizeData(string $name): array
    {
        try {
            $response = $this->nationalizeClient->fetch($name);
            return $this->nationalizeClient->validate($response);
        } catch (Exception $e) {
            throw new Exception('Nationalize: ' . $e->getMessage());
        }
    }

    private function extractApiName(Exception $e): string
    {
        $message = $e->getMessage();
        if (str_contains($message, 'Genderize')) return 'Genderize';
        if (str_contains($message, 'Agify')) return 'Agify';
        if (str_contains($message, 'Nationalize')) return 'Nationalize';
        return 'Unknown';
    }
}
