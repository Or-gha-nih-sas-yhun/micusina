<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use App\Support\Audit;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        Audit::record('user.deleted', $user, $user->only(['name', 'email', 'usertype', 'staff_role']));
        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
