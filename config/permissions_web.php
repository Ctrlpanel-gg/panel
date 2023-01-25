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

    'admin.users.read',
    'admin.users.write',
    'admin.users.suspend',
    'admin.users.write.credits',
    'admin.users.write.username',
    'admin.users.write.password',
    'admin.users.write.role',
    'admin.users.write.referal',
    'admin.users.write.pterodactyl',

    'admin.servers.read',
    'admin.servers.write',
    'admin.servers.suspend',
    'admin.server.write.owner',
    'admin.server.write.identifier',
    'admin.server.delete',

    'admin.products.read',
    'admin.products.create',
    'admin.products.edit',
    'admin.products.delete',

    'admin.store.read',
    'admin.store.write',
    'admin.store.disable',

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
