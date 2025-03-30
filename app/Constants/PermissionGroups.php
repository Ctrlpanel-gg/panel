<?php

namespace App\Constants;

class PermissionGroups
{
    const TICKET_PERMISSIONS = [
        "user.ticket.read",
        "user.ticket.write"
    ];

    const OVERVIEW_PERMISSIONS = [
        'admin.overview.read',
        'admin.overview.sync'
    ];

    const TICKET_ADMIN_PERMISSIONS = [
        'admin.ticket.read',
        'admin.tickets.write'
    ];

    const TICKET_BLACKLIST_PERMISSIONS = [
        'admin.ticket_blacklist.read',
        'admin.ticket_blacklist.write'
    ];

    const ROLES_PERMISSIONS = [
        'admin.roles.read',
        'admin.roles.write'
    ];

    const SETTINGS_PERMISSIONS = [
        'settings.discord.read',
        'settings.discord.write',
        'settings.terms.read',
        'settings.terms.write',
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
        'settings.mercadopago.read',
        'settings.mercadopago.write'
    ];

    const API_PERMISSIONS = [
        'admin.api.read',
        'admin.api.write'
    ];

    const USERS_PERMISSIONS = [
        'admin.users.read',
        'admin.users.write',
        'admin.users.suspend',
        'admin.users.write.credits',
        'admin.users.write.username',
        'admin.users.write.password',
        'admin.users.write.role',
        'admin.users.write.referral',
        'admin.users.write.pterodactyl'
    ];

    const SERVERS_PERMISSIONS = [
        'admin.servers.read',
        'admin.servers.write',
        'admin.servers.suspend',
        'admin.servers.write.owner',
        'admin.servers.write.identifier',
        'admin.servers.delete'
    ];

    const PRODUCTS_PERMISSIONS = [
        'admin.products.read',
        'admin.products.create',
        'admin.products.edit',
        'admin.products.delete'
    ];

    const STORE_PERMISSIONS = [
        'admin.store.read',
        'admin.store.write',
        'admin.store.disable'
    ];

    const VOUCHERS_PERMISSIONS = [
        'admin.voucher.read',
        'admin.voucher.write'
    ];

    const PARTNERS_PERMISSIONS = [
        'admin.partners.read',
        'admin.partners.write'
    ];

    const COUPONS_PERMISSIONS = [
        'admin.coupons.read',
        'admin.coupons.write'
    ];

    const USEFUL_LINKS_PERMISSIONS = [
        'admin.useful_links.read',
        'admin.useful_links.write'
    ];

    const PAYMENTS_PERMISSIONS = [
        'admin.payments.read'
    ];

    const LOGS_PERMISSIONS = [
        'admin.logs.read'
    ];
}
