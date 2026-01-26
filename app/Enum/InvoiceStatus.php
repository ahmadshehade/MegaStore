<?php

namespace App\Enum;

/**
 * Summary of InvoiceStatus
 */
enum InvoiceStatus: string
{

    case Partial = "partial";

    case Issued = "issued";

    case Paid = "paid";

    case Cancelled = "cancelled";

    case Revised = "revised";
}
