<?php

namespace App\Constants;

class DefaultGroupPermissions
{
    const ADMIN = [
        '*',
    ];

    const SUPPORT_TEAM = [];

    const CLIENT = [
        'user.server.create',
        'user.server.upgrade',
        'user.shop.buy',
        'user.ticket.read',
        'user.ticket.write',
        'user.referral',
    ];

    const USER = [
        'user.server.create',
        'user.server.upgrade',
        'user.shop.buy',
        'user.ticket.read',
        'user.ticket.write',
        'user.referral',
    ];
}
