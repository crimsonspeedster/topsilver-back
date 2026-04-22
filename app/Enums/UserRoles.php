<?php

namespace App\Enums;

enum UserRoles : string
{
    case Admin = 'admin';
    case Customer = 'customer';
    case Developer = 'developer';
}
