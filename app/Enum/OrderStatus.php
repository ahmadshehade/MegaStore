<?php

namespace App\Enum;

enum OrderStatus: string
{
    case Pending = "pending";

    case Processing = "processing";

    case Completed = "completed";

    case Cancelled = "cancelled";
}
