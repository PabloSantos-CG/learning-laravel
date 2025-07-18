<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getFirstUser(string $label, string $identifier)
    {
        return User::where("$label", $identifier)->first();
    }

    /** @param array<string, mixed> $userAttributes */
    public function createUser(array $userAttributes)
    {
        return User::create($userAttributes);
    }

    /** @param array<string, string> $userAttributes */
    public function updateUser(string $id, array $userAttributes)
    {
        $affectedLines = User::where('id', $id)->update($userAttributes);

        if (!$affectedLines) return false;
        
        return true;
    }
}
