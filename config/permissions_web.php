<?php

return [
    '*',

    /*
        * Permissions for admin
        */

    'admin.roles.read',
    'admin.roles.create',
    'admin.roles.edit',
    'admin.roles.delete',


    'admin.ticket.read',
    'admin.tickets.write',

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
    'admin.users.write.email',
    'admin.users.notify',
    'admin.users.login_as',
    'admin.users.delete',

    'admin.servers.read',
    'admin.servers.write',
    'admin.servers.suspend',
    'admin.servers.write.owner',
    'admin.servers.write.identifier',
    'admin.servers.delete',

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

    'admin.legal.read',
    'admin.legal.write',

    'admin.payments.read',

    'admin.partners.read',
    'admin.partners.write',

    'admin.logs.read',

    /*
     * Settings Permissions
     */
    'settings.discord.read',
    'settings.discord.write',

    'settings.general.read',
    'settings.general.write',

    'settings.invoice.read',
    'settings.invoice.write',

    'settings.locale.read',
    'settings.locale.write',

    'settings.mail.read',
    'settings.mail.write',

    'settings.pterodactyl.read',
    'settings.pterodactyl.write',

    'settings.referral.read',
    'settings.referral.write',

    'settings.server.read',
    'settings.server.write',

    'settings.ticket.read',
    'settings.ticket.write',

    'settings.user.read',
    'settings.user.write',

    'settings.website.read',
    'settings.website.write',

    'settings.paypal.read',
    'settings.paypal.write',

    'settings.stripe.read',
    'settings.stripe.write',

    'settings.mollie.read',
    'settings.mollie.write',


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
