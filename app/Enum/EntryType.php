<?php

namespace App\Enum;

enum EntryType: string
{
    case Invoice = "invoice";
    case InvoiceReversal = "invoice_reversal";
    case Payment = "payment";
    case PaymentReversal = "payment_reversal";
    case Refund = "refund";
    case OverPayment = "over_payment";
}
