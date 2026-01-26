<?php

namespace App\Enum;

enum InvoiceStatus: string
{

    case Partial = "partial";

    case Issued = "issued";

    case Paid = "paid";

    case Cancelled = "cancelled";

    case Revised = "revised";
}
