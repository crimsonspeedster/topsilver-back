<?php
namespace App\Events;

use App\Models\User;

readonly class UserLoggedIn
{
    public function __construct(
        public User $user,
        public ?string $wishlistToken,
        public ?string $cartToken,
    ) {}
}
