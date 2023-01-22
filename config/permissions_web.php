<?php

return [
    '*',

    /*
    * Permissions for admin
    */
    'admin.sidebar.read',

    'admin.roles.read',
    'admin.roles.write',

    'admin.ticket.read',

    'admin.ticket_blacklist.read',
    'admin.ticket_blacklist.write',

    'admin.overview.read',
    'admin.overview.sync',

    'admin.api.read',
    'admin.api.write',

    'admin.roles.read',
    'admin.roles.write',

    'admin.users.read',
    'admin.users.write',

    'admin.servers.read',
    'admin.servers.write',

    'admin.products.read',
    'admin.products.write',

    'admin.shop.read',
    'admin.shop.write',

    'admin.voucher.read',
    'admin.voucher.write',

    'admin.useful_links.read',
    'admin.useful_links.write',

    'admin.logs.read',

    /*
     * Permissions for settings
     */
    'settings.sidebar.read',

    'settings.invoices.read',
    'settings.invoices.write',

    'settings.language.read',
    'settings.language.write',

    'settings.misc.read',
    'settings.misc.write',

    'settings.payment.read',
    'settings.payment.write',

    'settings.system.read',
    'settings.system.write',

    /*
    * Permissions for users
    */
    'user.server.create',
    'user.server.upgrade',
    'user.shop.buy',
    'user.ticket.read',
    'user.ticket.write',
    'user.referral',
];
