<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Response;
use App\Services\ProfileService;
use App\Repositories\ProfileRepository;
use Exception;

final class ProfileController
{
    private ProfileService $profileService;

    public function __construct()
    {
        $this->profileService = new ProfileService(new ProfileRepository());
    }

    public function create()
    {
        try {
            $name = $_POST['name'] ?: \json_decode(\file_get_contents('php://input'))->name;

            // Validate name
            if (empty($name ?? null)) {
                Response::error('Missing or empty name', 400)->send();
                return;
            }

            // Validate type
            if (!\is_string($name)) {
                Response::error('Invalid type', 422)->send();
                return;
            }

            // Create or get profile
            $result = $this->profileService->createOrGetProfile($name);
            /**
             * @var \App\Models\Profile */
            $profile = $result['profile'];
            $isNew = $result['isNew'];
            $message = $result['message'];

            $statusCode = $isNew ? 201 : 200;
            $response = Response::success(
                $profile->toArray(),
                $statusCode,
                $message
            );
            $response->send();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function getById(string $id): void
    {
        try {
            $profile = $this->profileService->getProfileById($id);

            if ($profile === null) {
                Response::error('Profile not found', 404)->send();
                return;
            }

            Response::success([$profile->toArray()])->send();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function getAll(): void
    {
        try {
            $filters = [];

            // Extract query parameters
            if (isset($_GET['gender'])) {
                $filters['gender'] = (string)$_GET['gender'];
            }

            if (isset($_GET['country_id'])) {
                $filters['country_id'] = (string)$_GET['country_id'];
            }

            if (isset($_GET['age_group'])) {
                $filters['age_group'] = (string)$_GET['age_group'];
            }

            $profiles = $this->profileService->getAllProfiles($filters);
            $count = $this->profileService->getProfileCount($filters);

            $data = array_map(fn($p) => $p->toArrayMinimal(), $profiles);

            Response::success($data, 200, null, $count)->send();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function delete(string $id): void
    {
        try {
            $deleted = $this->profileService->deleteProfile($id);

            if (!$deleted) {
                Response::error('Profile not found', 404)->send();
                return;
            }

            http_response_code(204);
            exit;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(Exception $e): void
    {
        $message = $e->getMessage();

        if (str_contains($message, 'returned an invalid response')) {
            Response::error($message, 502)->send();
        } else {
            Response::error('Internal server error', 500)->send();
        }
    }
}
