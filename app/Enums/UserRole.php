<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Kasir = 'kasir';
    case Dapur = 'dapur';
    case Customer = 'customer';
}
