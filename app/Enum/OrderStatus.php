<?php

namespace App\Enum;

/**
 * Summary of OrderStatus
 */
enum OrderStatus: string
{
    case Pending = "pending";

    case Processing = "processing";

    case Completed = "completed";

    case Cancelled = "cancelled";
}
