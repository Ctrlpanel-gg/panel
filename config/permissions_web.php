<?php

return [
    /*
     * Permissions for admin
    */

    'All Permissions' => '*',

    'View Roles Backend' => 'admin.roles.read',
    'Create Role' => 'admin.roles.create',
    'Edit Role' => 'admin.roles.edit',
    'Delete Role' => 'admin.roles.delete',


    'View Tickets Backend' => 'admin.tickets.read',
    'Manage Ticket Backend' => 'admin.tickets.write',
    'Receive new Ticket Notifications' => 'admin.tickets.get_notification',

    'Create Ticket Category' => 'admin.tickets.category.read',
    'Manage Ticket Category Backend' => 'admin.tickets.category.write',

    'View Ticket-Blacklist' => 'admin.ticket_blacklist.read',
    'Manage Ticket-Blacklist' => 'admin.ticket_blacklist.write',

    'View Overview' => 'admin.overview.read',
    'Overview Sync' => 'admin.overview.sync',

    'View Api Keys' => 'admin.api.read',
    'Manage Api Keys' => 'admin.api.write',

    'View User List' => 'admin.users.read',
    'Edit anything on User' => 'admin.users.write',
    'Suspend Users' => 'admin.users.suspend',
    'Edit User Credits' => 'admin.users.write.credits',
    'Edit User Name' => 'admin.users.write.username',
    'Edit User Email' => 'admin.users.write.email',
    'Edit User Password' => 'admin.users.write.password',
    'Edit User Role' => 'admin.users.write.role',
    'Edit User Referral' => 'admin.users.write.referral',
    'Edit User Pterodactyl' => 'admin.users.write.pterodactyl',
    'Edit User Serverlimit' => 'admin.users.write.serverlimit',

    "Manage Icons" => "admin.icons.edit",

    'Notify Users' => 'admin.users.notify',
    'Login As User' => 'admin.users.login_as',
    'Delete User' => 'admin.users.delete',

    'View Server List' => 'admin.servers.read',
    'Manage all Servers' => 'admin.servers.write',
    'Suspend any Server' => 'admin.servers.suspend',
    'Change any Servers Owner' => 'admin.servers.write.owner',
    'Manage any Servers Identifier' => 'admin.servers.write.identifier',
    'Bypass Server-creation restriction ' => 'admin.servers.bypass_creation_enabled',
    'Delete any Servers' => 'admin.servers.delete',

    'View Product List' => 'admin.products.read',
    'Create Product' => 'admin.products.create',
    'Edit Product' => 'admin.products.edit',
    'Delete Product' => 'admin.products.delete',

    'View Store Backend' => 'admin.store.read',
    'Manage Store Backend' => 'admin.store.write',
    'Disable Store' => 'admin.store.disable',

    'View Vouchers Backend' => 'admin.voucher.read',
    'Manage Voucher Backend' => 'admin.voucher.write',

    'View Useful Links Backend' => 'admin.useful_links.read',
    'Manage Useful Links Backend' => 'admin.useful_links.write',

    'View Legal Backend' => 'admin.legal.read',
    'Manage Legal Backend' => 'admin.legal.write',

    'View Payments Backend' => 'admin.payments.read',

    'View Partners Backend' => 'admin.partners.read',
    'Manage Partners Backend' => 'admin.partners.write',

    'View Coupons Backend' => 'admin.coupons.read',
    'Manage Coupons Backend' => 'admin.coupons.write',

    'View Logs' => 'admin.logs.read',

    /*
     * Settings Permissions
    */
    'View Discord Settings' => 'settings.discord.read',
    'Manage Discord Settings' => 'settings.discord.write',

    'View General Settings' => 'settings.general.read',
    'Manage General Settings' => 'settings.general.write',

    'View Invoice Settings' => 'settings.invoice.read',
    'Manage Invoice Settings' => 'settings.invoice.write',

    'View Locale Settings' => 'settings.locale.read',
    'Manage Locale Settings' => 'settings.locale.write',

    'View Mail Settings' => 'settings.mail.read',
    'Manage Mail Settings' => 'settings.mail.write',

    'View Pterodactyl Settings' => 'settings.pterodactyl.read',
    'Manage Pterodactyl Settings' => 'settings.pterodactyl.write',

    'View Referral Settings' => 'settings.referral.read',
    'Manage Referral Settings' => 'settings.referral.write',

    'View Server Settings' => 'settings.server.read',
    'Manage Server Settings' => 'settings.server.write',

    'View Ticket Settings' => 'settings.ticket.read',
    'Manage Ticket Settings' => 'settings.ticket.write',

    'View User Settings' => 'settings.user.read',
    'Manage User Settings' => 'settings.user.write',

    'View Website Settings' => 'settings.website.read',
    'Manage Website Settings' => 'settings.website.write',

    'View Paypal Settings' => 'settings.paypal.read',
    'Manage Paypal Settings' => 'settings.paypal.write',

    'View Mercado Pago Settings' => 'settings.mercadopago.read',
    'Manage Mercado Pago Settings' => 'settings.mercadopago.write',

    'View Stripe Settings' => 'settings.stripe.read',
    'Manage Stripe Settings' => 'settings.stripe.write',

    'View Mollie Settings' => 'settings.mollie.read',
    'Manage Mollie Settings' => 'settings.mollie.write',

    /*
     * Permissions for users
    */
    'Customer Create Server' => 'user.server.create',
    'Customer Upgrade Server' => 'user.server.upgrade',
    'Customer Shop Buy' => 'user.shop.buy',
    'Customer View Supportticket' => 'user.ticket.read',
    'Customer Write Supportticket' => 'user.ticket.write',
    'Customer View Referral' => 'user.referral',
];
