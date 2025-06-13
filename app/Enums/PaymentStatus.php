<?php

namespace App\Enums;

// Payment status are open, processing, paid and canceled
enum PaymentStatus: String
{
    case OPEN = "open";
    case PROCESSING = "processing";
    case PAID = "paid";
    case CANCELED = "canceled";
}
