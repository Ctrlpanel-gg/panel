<?php

return [
    /*
     * Permissions for admin
    */

    'All Permissions' => '*',

    'View Roles' => 'admin.roles.read',
    'Create Role' => 'admin.roles.create',
    'Edit Role' => 'admin.roles.edit',
    'Delete Role' => 'admin.roles.delete',


    'View Tickets' => 'admin.tickets.read',
    'Manage Ticket' => 'admin.tickets.write',
    'Receive Ticket Notifications' => 'admin.tickets.get_notification',

    'Create Ticket Category' => 'admin.tickets.category.read',
    'Manage Ticket Category' => 'admin.tickets.category.write',

    'View Blacklist Tickets' => 'admin.ticket_blacklist.read',
    'Manage Blacklist Tickets' => 'admin.ticket_blacklist.write',

    'View Overview' => 'admin.overview.read',
    'Overview Sync' => 'admin.overview.sync',

    'View Api Keys' => 'admin.api.read',
    'Manage Api Keys' => 'admin.api.write',

    'View Users' => 'admin.users.read',
    'Manage Users' => 'admin.users.write',
    'Suspend Users' => 'admin.users.suspend',
    'Manage User Credits' => 'admin.users.write.credits',
    'Manage User Name' => 'admin.users.write.username',
    'Manage User Email' => 'admin.users.write.email',
    'Manage User Password' => 'admin.users.write.password',
    'Manage User Role' => 'admin.users.write.role',
    'Manage User Referral' => 'admin.users.write.referral',
    'Manage User Pterodactyl' => 'admin.users.write.pterodactyl',

    'Notify Users' => 'admin.users.notify',
    'Login As User' => 'admin.users.login_as',
    'Delete User' => 'admin.users.delete',

    'View Servers' => 'admin.servers.read',
    'Manage Servers' => 'admin.servers.write',
    'Suspend Server' => 'admin.servers.suspend',
    'Change Server Owner' => 'admin.servers.write.owner',
    'Manage Server Identifier' => 'admin.servers.write.identifier',
    'Create Server' => 'admin.servers.bypass_creation_enabled',
    'Delete Server' => 'admin.servers.delete',

    'View Products' => 'admin.products.read',
    'Create Product' => 'admin.products.create',
    'Edit Product' => 'admin.products.edit',
    'Delete Product' => 'admin.products.delete',

    'View Store' => 'admin.store.read',
    'Manage Store' => 'admin.store.write',
    'Disable Store' => 'admin.store.disable',

    'View Vouchers' => 'admin.voucher.read',
    'Manage Voucher' => 'admin.voucher.write',

    'View Useful Links' => 'admin.useful_links.read',
    'Manage Useful Links' => 'admin.useful_links.write',

    'View Legal' => 'admin.legal.read',
    'Manage Legal' => 'admin.legal.write',

    'View Payments' => 'admin.payments.read',

    'View Partners' => 'admin.partners.read',
    'Manage Partners' => 'admin.partners.write',

    'View Coupons' => 'admin.coupons.read',
    'Manage Coupons' => 'admin.coupons.write',

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
    'User Create Server' => 'user.server.create',
    'User Upgrade Server' => 'user.server.upgrade',
    'User Shop Buy' => 'user.shop.buy',
    'User View Tickets' => 'user.ticket.read',
    'User Manage Ticket' => 'user.ticket.write',
    'User View Referral' => 'user.referral',
];
