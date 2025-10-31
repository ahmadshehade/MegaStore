<?php

namespace App\Enum;


/**
 * Summary of UserRoles
 */
enum UserRoles  :string{

    case  SuperAdmin='admin';

    case User ='guest';

    case Seller ='seller';

    case Customer ='customer';
}
