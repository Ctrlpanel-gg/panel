<?php

namespace App\Enums;

// Payment statuses: open, processing, paid, canceled and refunded.
// paid and refunded are terminal statuses that cannot be left.
enum PaymentStatus: String
{
    case OPEN = "open";
    case PROCESSING = "processing";
    case PAID = "paid";
    case CANCELED = "canceled";
    case REFUNDED = "refunded";
}
