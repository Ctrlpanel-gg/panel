# Code Review Findings

Date: 2026-03-27
Scope: static review of the current `cpgg` workspace
Notes: archived review snapshot. The findings below were used as the remediation checklist for the follow-up fix pass on the same date and should not be treated as the current open-issues list.

## Critical

1. `app/Extensions/PaymentGateways/PayPal/PayPalExtension.php:257-278` selects sandbox vs live API base URL and credentials from `config('app.env')`. A production install running in a non-`local` test environment will hit live PayPal with the wrong credentials.
2. `app/Extensions/PaymentGateways/Stripe/StripeExtension.php:282-289` selects Stripe live vs test secret from `config('app.env')` instead of an explicit gateway mode setting.
3. `app/Extensions/PaymentGateways/Stripe/StripeExtension.php:294-299` selects the webhook signing secret from raw `env('APP_ENV')` at runtime, creating the same mode drift and bypassing Laravel config caching semantics.
4. `app/Extensions/PaymentGateways/PayPal/routes.php:6-12` protects the browser return route with `auth`. If the session is lost during the provider round-trip, a completed payment cannot be confirmed by the returning user.
5. `app/Extensions/PaymentGateways/Stripe/routes.php:6-12` has the same `auth`-gated browser return problem for Stripe.
6. `app/Extensions/PaymentGateways/Mollie/routes.php:6-12` has the same `auth`-gated browser return problem for Mollie.
7. `app/Extensions/PaymentGateways/MercadoPago/routes.php:6-12` has the same `auth`-gated browser return problem for Mercado Pago.
8. `app/Extensions/PaymentGateways/Mollie/MollieExtension.php:99-100` marks the local payment as `processing` on the success redirect without verifying the remote payment status.
9. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:120-121` marks the local payment as `processing` on the success redirect without verifying the remote payment status.
10. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:60-64` sends both `success` and `pending` returns to the same success handler, so pending payments are treated like approved browser returns.
11. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:135-137` immediately returns success for webhook topics `merchant_order` and `payment`, which can skip legitimate webhook processing depending on Mercado Pago’s payload format.
12. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:147-150` contains a hard-coded webhook test bypass for notification ID `123456` in production code.
13. `app/Models/User.php:112-143` performs destructive account deletion work outside a transaction. A mid-delete failure can leave orphaned rows and an out-of-sync remote Pterodactyl user.
14. `app/Console/Commands/ChargeServers.php:124-137` returns `false` both for canceled servers and for insufficient credits, and the caller suspends on any `false`, so canceled servers can still be suspended.

## High

15. `app/Extensions/PaymentGateways/Mollie/MollieExtension.php:58` builds the payment description with `$shopProduct->name`, but `ShopProduct` exposes `display`, not `name` (`app/Models/ShopProduct.php:35-43`).
16. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:72` has the same nonexistent `$shopProduct->name` usage.
17. `app/Providers/SettingsServiceProvider.php:47` overwrites the Discord redirect URL from `app.url` on every request, ignoring any explicit redirect override in configuration.
18. `app/Providers/SettingsServiceProvider.php:64-65` swallows broad settings-load exceptions and keeps booting, which can leave the app running with partial configuration and only a log line.
19. `app/Providers/SettingsServiceProvider.php:71-73` falls back to theme `default` only in memory. The invalid theme value is never repaired, so the fallback work repeats every request.
20. `app/Providers/SettingsServiceProvider.php:84-85` swallows mail/theme bootstrap failures the same way, hiding broken runtime state behind a generic log entry.
21. `app/Providers/SettingsServiceProvider.php:35` calls `Schema::hasColumn('settings', 'payload')` during every boot cycle, which is an avoidable schema lookup on every request.
22. `app/Http/Controllers/Admin/SettingsController.php:81-82` dereferences `$optionInputData[$key]['type']` without a null check. Settings without metadata trigger an undefined index notice.
23. `app/Models/DiscordUser.php:92` logs `$this->role_id_on_purchase`, but that property is not defined on the model, so the activity log message is wrong or noisy.
24. `app/Http/Controllers/Admin/UserController.php:438` hard-codes role ID `1` to detect the last admin instead of checking by role name/permission.
25. `app/Listeners/UserPayment.php:98-99` hard-codes role IDs `4` and `3`, so role table reordering or reseeding breaks referral-role promotion.
26. `app/Console/Commands/ImportUsersFromPteroCommand.php:58-60` calls `array_key_exists(2, $json)` without verifying that `json_decode()` returned an array-like structure, so invalid JSON can trigger a fatal type error.
27. `app/Console/Commands/ImportUsersFromPteroCommand.php:64` assumes `$json[2]->data` exists. Any export format drift breaks the import path immediately.
28. `app/Console/Commands/ImportUsersFromPteroCommand.php:119-120` writes the discontinued `role` column instead of assigning Spatie roles, so imported admin/member state is not migrated correctly.
29. `app/Console/Commands/ImportUsersFromPteroCommand.php:119` copies the external password field blindly, with no hash compatibility check or rehash flow.
30. `app/Console/Commands/update.php:48-49` claims the minimum supported PHP version is `8.0.0`, which no longer matches the project’s current runtime requirements.
31. `app/Console/Commands/update.php:54-166` only performs work inside the interactive branch. In non-interactive runs it becomes a silent no-op.
32. `app/Console/Commands/update.php:101-105` ignores the exit status of `git pull`, so a failed update can continue as if the source tree changed successfully.
33. `app/Console/Commands/update.php:128-132` ignores the exit status of `composer install`, so the command can continue after a dependency failure.
34. `app/Console/Commands/update.php:150-156` recursively `chown`s the whole project root, including `.git` and non-runtime files, which is unsafe for many deployment layouts.
35. `app/Models/Server.php:98-99` assumes the Pterodactyl error payload always contains `errors[0]`. Unexpected API bodies will cause undefined index errors while deleting servers.
36. `app/Models/Server.php:126-134` returns the server model even when remote suspension fails, so callers cannot distinguish success from no-op failure.
37. `app/Models/Server.php:142-153` has the same silent-failure behavior for unsuspension.
38. `app/Models/User.php:281-283` unsuspends each server if the user has enough credits for that one server, but never reserves/decrements credits across the loop, so one balance can unlock multiple servers.
39. `app/Models/User.php:267-289` ignores whether `Server::suspend()` or `Server::unSuspend()` actually succeeded remotely before updating the user’s suspended state.
40. `app/Console/Commands/CleanupOpenPayments.php:33` deletes all locally open payments after one hour without checking provider state first, so slow-but-valid offsite payments can be discarded.
41. `app/Http/Controllers/Admin/CouponController.php:75-86` bulk coupon creation is not wrapped in a transaction. A duplicate code or DB error leaves a partial batch committed.
42. `app/Models/Coupon.php:139-149` generates coupon codes without any uniqueness check against the database or the current batch, so collisions are possible.
43. `app/Http/Controllers/TicketsController.php:246` renders an extra stray `</form>` in the actions column HTML, which can break the DOM around the datatable row.

## Medium

44. `app/Http/Controllers/Api/NotificationController.php:40` accepts an unbounded `per_page` query value, allowing very large paginated reads from the notifications API.
45. `app/Http/Requests/Api/Notifications/SendToUsersNotificationRequest.php:28` has no maximum length for notification titles.
46. `app/Http/Requests/Api/Notifications/SendToUsersNotificationRequest.php:29` has no maximum length for notification content.
47. `app/Http/Requests/Api/Notifications/SendToAllUsersNotificationRequest.php:26` has no maximum length for global notification titles.
48. `app/Http/Requests/Api/Notifications/SendToAllUsersNotificationRequest.php:27` has no maximum length for global notification content.
49. `app/Http/Middleware/ApiAuthToken.php:33` updates `last_used` before the request is actually handled, so failed requests still mutate token usage and every API hit performs an immediate write.
50. `app/Http/Middleware/SecurityHeaders.php:31-33` only emits HSTS when `app.env === production`, so HTTPS staging or custom environments miss HSTS entirely.
51. `app/Http/Middleware/LastSeen.php:20-21` disables last-seen and IP tracking for all `local` environments, which makes local parity debugging and QA less accurate.
52. `app/Models/User.php:108-110` sends `WelcomeMessage` on every user creation, including imports, seeders, tests, and admin-created accounts.
53. `app/Models/User.php:115-118` deletes owned servers one-by-one without chunking, which is a poor fit for large accounts and can exhaust memory or lock time.
54. `app/Models/User.php:142` ignores the result of the remote Pterodactyl user deletion request entirely.
55. `app/Console/Commands/ChargeServers.php:51-145` does not return a proper command status code from `handle()`.
56. `app/Console/Commands/update.php:92-94` returns `false` instead of a Symfony command status constant.
57. `app/Console/Commands/ImportUsersFromPteroCommand.php:30` still uses the placeholder description `Command description`.
58. `app/Console/Commands/ImportUsersFromPteroCommand.php:100` returns `true` instead of a Symfony command status constant.
59. `database/factories/PaymentFactory.php:20` still defines `payer_id`, which is not part of the current `Payment` model fillable schema (`app/Models/Payment.php:21-35`).
60. `database/factories/PaymentFactory.php:27` still defines `payer`, which is also absent from the current `Payment` model schema.
61. `database/factories/PaymentFactory.php:23` uses the legacy status string `Completed` instead of the current payment status enum values (`app/Enums/PaymentStatus.php:6-11`).
62. `database/factories/PaymentFactory.php:18-28` no longer reflects the modern payment shape at all: it omits fields like `tax_value`, `tax_percent`, `total_price`, `shop_item_product_id`, and `payment_method`, making factory-backed tests misleading.
63. `app/Helpers/CallHomeHelper.php:32-35` performs an outbound install telemetry request with no timeout.
64. `app/Helpers/CallHomeHelper.php:32-35` uses `Http::async()->post(...)->wait()`, so the code still blocks the request path even though it is labeled async.
65. `app/Helpers/CallHomeHelper.php:31-33` hashes the installation host with MD5 into a stable external identifier. Even if pseudonymous, it is still persistent installation fingerprinting.
66. `app/Helpers/CallHomeHelper.php:37` writes the flag file without file locking, so concurrent first-hit requests can race.
67. `app/Classes/PterodactylClient.php:52-56` builds the user client without any request timeout, so panel calls can hang indefinitely on network issues.
68. `app/Classes/PterodactylClient.php:61-65` builds the admin client without any request timeout.
69. `app/Models/DiscordUser.php:64-77` calls Discord role add/remove endpoints without a timeout.
70. `app/Http/Controllers/Auth/SocialiteController.php:86-94` calls the Discord guild join endpoint without a timeout.
71. `app/Extensions/PaymentGateways/Mollie/MollieExtension.php:50-53` creates Mollie payments without a timeout.
72. `app/Extensions/PaymentGateways/Mollie/MollieExtension.php:111-114` looks up Mollie webhook payments without a timeout.
73. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:56-59` creates Mercado Pago checkout preferences without a timeout.
74. `app/Extensions/PaymentGateways/MercadoPago/MercadoPagoExtension.php:159-162` looks up Mercado Pago payments without a timeout.
75. `app/Http/Controllers/Admin/CouponController.php:217` validates `range_codes` with `digits_between:1,100`, which constrains the number of digits, not the actual batch size. Values like `9999999999` still pass as “10 digits”.

## Security Hygiene

76. `app/Http/Controllers/Admin/ServerController.php:361` opens a new tab with `target="_blank"` and no `rel="noopener noreferrer"`, allowing reverse-tabnabbing.
77. `app/Http/Controllers/Admin/UserController.php:715` has the same reverse-tabnabbing issue.
78. `app/Http/Controllers/Admin/VoucherController.php:226` has the same reverse-tabnabbing issue.
79. `themes/BlueInfinity/views/layouts/main.blade.php:476` has the same reverse-tabnabbing issue.
80. `themes/BlueInfinity/views/layouts/main.blade.php:479` has the same reverse-tabnabbing issue.
81. `themes/BlueInfinity/views/layouts/main.blade.php:482` has the same reverse-tabnabbing issue.
82. `themes/default/views/admin/activitylogs/index.blade.php:32` has the same reverse-tabnabbing issue.
83. `themes/default/views/admin/store/create.blade.php:78` has the same reverse-tabnabbing issue.
84. `themes/default/views/admin/store/edit.blade.php:77` has the same reverse-tabnabbing issue.
85. `themes/default/views/admin/usefullinks/create.blade.php:45` has the same reverse-tabnabbing issue.
86. `themes/default/views/admin/usefullinks/edit.blade.php:47` has the same reverse-tabnabbing issue.
87. `themes/default/views/auth/login.blade.php:144` has the same reverse-tabnabbing issue.
88. `themes/default/views/auth/login.blade.php:147` has the same reverse-tabnabbing issue.
89. `themes/default/views/auth/login.blade.php:150` has the same reverse-tabnabbing issue.
90. `themes/default/views/auth/passwords/email.blade.php:94` has the same reverse-tabnabbing issue.
91. `themes/default/views/auth/passwords/email.blade.php:97` has the same reverse-tabnabbing issue.
92. `themes/default/views/auth/passwords/email.blade.php:100` has the same reverse-tabnabbing issue.
93. `themes/default/views/auth/passwords/reset.blade.php:88` has the same reverse-tabnabbing issue.
94. `themes/default/views/auth/passwords/reset.blade.php:91` has the same reverse-tabnabbing issue.
95. `themes/default/views/auth/passwords/reset.blade.php:94` has the same reverse-tabnabbing issue.
96. `themes/default/views/auth/register.blade.php:162` has the same reverse-tabnabbing issue.
97. `themes/default/views/auth/register.blade.php:194` has the same reverse-tabnabbing issue.
98. `themes/default/views/auth/register.blade.php:197` has the same reverse-tabnabbing issue.
99. `themes/default/views/auth/register.blade.php:200` has the same reverse-tabnabbing issue.
100. `themes/default/views/information/privacy-content.blade.php:6` has the same reverse-tabnabbing issue.
101. `themes/default/views/information/privacy-content.blade.php:103` has the same reverse-tabnabbing issue.
102. `themes/default/views/layouts/main.blade.php:474` has the same reverse-tabnabbing issue.
103. `themes/default/views/layouts/main.blade.php:477` has the same reverse-tabnabbing issue.
104. `themes/default/views/layouts/main.blade.php:480` has the same reverse-tabnabbing issue.
105. `themes/default/views/mail/server/suspended.blade.php:6` has the same reverse-tabnabbing issue.
106. `themes/default/views/mail/server/unsuspended.blade.php:6` has the same reverse-tabnabbing issue.
107. `themes/default/views/servers/index.blade.php:41` has the same reverse-tabnabbing issue.

## Summary

- Total findings: 107
- Highest-risk areas: payment gateway state handling, environment-coupled gateway config, destructive model hooks, updater/import commands, and link security hygiene.
- Existing safe exception: `themes/default/views/information/privacy-content.blade.php:53` already includes `rel="external nofollow noopener"`.
