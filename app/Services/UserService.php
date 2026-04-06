<?php

namespace App\Services;

use App\DTOs\ProfileData;
use App\DTOs\UserData;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected FileNativeService $fileService
    ) {}

    /**
     * Create a new user with profile and optional avatar
     */
    public function createUser(UserData $userData, ProfileData $profileData, ?UploadedFile $avatar = null): User
    {
        return DB::transaction(function () use ($userData, $profileData, $avatar) {
            // Create user
            $user = User::create([
                'first_name' => $userData->firstName,
                'last_name' => $userData->lastName,
                'email' => $userData->email,
                'is_active' => $userData->isActive,
                'password' => Hash::make($userData->password),
            ]);

            $profileAttributes = [
                'user_id' => $user->id,
                'phone' => $profileData->phone,
                'identity_card' => $profileData->identityCard,
                'gender' => $profileData->gender,
                'address' => $profileData->address,
            ];

            if ($avatar) {
                $avatarPath = $this->fileService->store($user, $avatar);
                $profileAttributes['avatar'] = $avatarPath;
            } elseif ($profileData->avatar) {
                $profileAttributes['avatar'] = $profileData->avatar;
            }

            Profile::create($profileAttributes);

            $user->assignRole($userData->role);

            $user->load(['roles', 'profile']);

            return $user;
        });
    }

    /**
     * Update an existing user with profile and optional avatar
     */
    public function updateUser(User $user, UserData $userData, ProfileData $profileData, ?UploadedFile $avatar = null): User
    {
        return DB::transaction(function () use ($user, $userData, $profileData, $avatar) {
            $lockedUser = User::lockForUpdate()->find($user->id);

            if (! $lockedUser) {
                throw new \Exception('El usuario que intentas editar ya no existe.');
            }

            $userAttributes = [
                'first_name' => $userData->firstName,
                'last_name' => $userData->lastName,
                'email' => $userData->email,
                'is_active' => $userData->isActive,
            ];

            if ($userData->password) {
                $userAttributes['password'] = Hash::make($userData->password);
            }

            $lockedUser->update($userAttributes);

            $profileAttributes = [
                'phone' => $profileData->phone,
                'identity_card' => $profileData->identityCard,
                'gender' => $profileData->gender,
                'address' => $profileData->address,
            ];

            if ($avatar) {
                if ($lockedUser->profile && $lockedUser->profile->avatar) {
                    $avatarPath = $this->fileService->replace($lockedUser->profile, $avatar, 'avatar');
                } else {
                    $avatarPath = $this->fileService->store($lockedUser, $avatar);
                }
                $profileAttributes['avatar'] = $avatarPath;
            } elseif ($profileData->avatar) {
                $profileAttributes['avatar'] = $profileData->avatar;
            }

            if ($lockedUser->profile) {
                $lockedUser->profile->update($profileAttributes);
            } else {
                $profileAttributes['user_id'] = $lockedUser->id;
                Profile::create($profileAttributes);
            }

            $lockedUser->syncRoles($userData->role);

            $lockedUser->load(['roles', 'profile']);

            return $lockedUser;
        });
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user): User
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return $user;
    }
}
