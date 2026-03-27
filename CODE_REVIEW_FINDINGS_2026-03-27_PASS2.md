# Code Review Findings (Second Pass)

Status: archived review snapshot. The issues listed here were used as the basis for the follow-up fix pass on 2026-03-27.

Additional findings beyond the archived first-pass review in `CODE_REVIEW_FINDINGS_2026-03-27.md`.

Scope note: this is a static code review pass. Findings are line-referenced and concrete, but not all of them were reproduced end-to-end at runtime.

1. High - `app/Traits/Referral.php:17-19` calls `generateReferralCode()` on collision, but the trait only defines `createReferralCode()`. The first referral-code collision will fatal.
2. High - `app/Traits/Coupon.php:74-75` uses `firstOrFail()` in `isCouponValid()`. An invalid or deleted coupon becomes an exception instead of a clean `false`.
3. High - `app/Traits/Coupon.php:98-111` dereferences `$coupon` without checking for `null`. If the coupon disappears between validation and application, checkout can crash.
4. High - `app/Traits/Coupon.php:100-111` returns raw float math for discounts even though prices are stored in integer milli-units. That can introduce rounding drift.
5. High - `app/Actions/ProcessReferralAction.php:46-51` inserts directly into `user_referrals` without checking for an existing row. Repeated execution can duplicate referral links.
6. High - `app/Actions/ProcessReferralAction.php:25-51` increments credits, queues a notification, logs activity, and inserts the referral row without a transaction. Partial referral state is possible.
7. Medium - `app/Actions/ProcessReferralAction.php:23-25` does not guard against self-referrals. If the action is reused outside signup, a user can reward themselves.
8. High - `app/Listeners/Verified.php:31-35` grants email-verification rewards without a transaction or lock. Duplicate event delivery can double-credit a user.
9. High - `app/Services/ServerCreationService.php:42,58` resolves `$egg` once and never validates it before calling `createServer()`. Reusing the service outside the current request validator can pass `null`.
10. High - `app/Services/ServerCreationService.php:67` deletes the local server before `pterodactyl_id` is set. The model delete hook still fires and issues a remote delete against an empty server ID.
11. High - `app/Services/ServerCreationService.php:72-75` assumes `response->json()['attributes']` exists. Malformed upstream responses become undefined-index errors.
12. High - `app/Services/ServerUpgradeService.php:106-117` calculates credit deltas as floats even though credits are stored as integer milli-units. Upgrades can overcharge or undercharge due to precision drift.
13. High - `app/Services/ServerUpgradeService.php:43-57,93-97` updates the remote Pterodactyl server before local billing and product changes are committed. Any later DB failure leaves local and remote state diverged.
14. High - `app/Services/ServerUpgradeService.php:43-57` never verifies that the target product is compatible with the server's current egg, node, or allocation. Service-level callers can request invalid upgrades.
15. Medium - `app/Rules/EggBelongsToProduct.php:36-42` dereferences `$this->data['product_id']` without guarding for a missing key. Bad validation context turns into an undefined-index error.
16. Medium - `app/Rules/ValidateEggVariables.php:45-56` assumes decoded egg metadata is an array of arrays containing `rules`, `default_value`, and `env_variable`. Corrupt egg metadata can trigger type errors.
17. Medium - `app/Helpers/CurrencyHelper.php:55-57` truncates with `(int) ($amount * 1000)` instead of rounding. Inputs like `0.015` lose value.
18. Medium - `app/Console/Commands/GetGithubVersion.php:35` performs an external GitHub request with no timeout. A slow network call can hang the command.
19. Medium - `app/Console/Commands/GetGithubVersion.php:35-36` assumes the GitHub tags API always returns `[0]['name']`. Rate limits or API changes can break the command with undefined indexes.
20. Medium - `app/Console/Commands/MakeUserCommand.php:69` returns `0` on validation failure. Symfony/Laravel commands expect non-zero exit codes for failures.
21. Medium - `app/Console/Commands/MakeUserCommand.php:86` returns `0` when the Pterodactyl lookup fails, again reporting failure as success to automation.
22. Medium - `app/Console/Commands/MakeUserCommand.php:93` returns `0` when required keys are missing from the upstream response.
23. Medium - `app/Console/Commands/MakeUserCommand.php:103` returns `0` when the local user already exists.
24. Medium - `app/Console/Commands/MakeUserCommand.php:132` returns `0` when the admin role is missing.
25. Medium - `app/Console/Commands/MakeUserCommand.php:137` returns `1` on success. Success and failure exit codes are inverted.
26. Medium - `app/Classes/PterodactylClient.php:150` assumes `getEggs()` always receives a JSON payload containing `data`. Unexpected response shapes crash the sync.
27. Medium - `app/Classes/PterodactylClient.php:169` assumes `getNodes()` always receives `data`.
28. Medium - `app/Classes/PterodactylClient.php:189` assumes `getNode()` always receives `attributes`.
29. Medium - `app/Classes/PterodactylClient.php:203` assumes `getServers()` always receives `data`.
30. Medium - `app/Classes/PterodactylClient.php:222` assumes `getNests()` always receives `data`.
31. Medium - `app/Classes/PterodactylClient.php:385` assumes `getUser()` always receives `attributes`.
32. High - `app/Classes/PterodactylClient.php:427-428` does `first()->delete()` in `getServerAttributes(..., deleteOn404: true)` with no null check. If the local row is already gone, the cleanup path crashes.
33. High - `app/Classes/PterodactylClient.php:486` sets `oom_disabled` to `$product->oom_killer` in `updateServerBuild()`, which is inverted relative to the create path's `!$server->product->oom_killer`.
34. Medium - `app/Classes/PterodactylClient.php:252` assumes the first free allocation always has `['attributes']['id']`.
35. Medium - `app/Classes/PterodactylClient.php:269` assumes every allocation includes `['attributes']['assigned']`.
36. High - `app/Classes/PterodactylClient.php:571-576` never checks `failed()` in `checkNodeResources()` before indexing the response body. 4xx/5xx responses can become array-access errors or bogus resource calculations.
37. Medium - `app/Classes/PterodactylClient.php:592` silently accepts invalid JSON in `getEnvironmentVariables()`. Bad payloads degrade into an empty collection instead of a validation error.
38. Medium - `app/Classes/PterodactylClient.php:600` merges arbitrary caller-supplied keys into the environment without whitelisting egg variables. Unexpected env vars can be sent upstream.
39. Medium - `app/Models/Pterodactyl/Egg.php:63-72` assumes every synced egg payload includes `relationships.variables.data`. Missing relationship data breaks sync.
40. Medium - `app/Models/Pterodactyl/Location.php:22-25` cascades node deletes one by one in model events with no transaction or chunking. Large sync removals can leave partial local state.
41. Medium - `app/Models/Pterodactyl/Nest.php:26-29` cascades egg deletes one by one in model events with no transaction or chunking.
42. Medium - `app/Models/Pterodactyl/Node.php:25-26` detaches products in a model event without a surrounding transaction. Partial detach state is possible if deletion fails mid-flight.
43. High - `app/Http/Controllers/Api/ServerController.php:152-159` sends the local `user_id` to Pterodactyl when changing ownership. Pterodactyl expects the remote user ID.
44. High - `app/Http/Controllers/Api/ServerController.php:148-165` updates Pterodactyl first and saves the local server afterward without a transaction. A local save failure leaves systems out of sync.
45. Medium - `app/Http/Controllers/Api/UserController.php:359-360` hardcodes Pterodactyl `external_id` to `"0"` for every created user, which destroys cross-system uniqueness.
46. Medium - `app/Http/Controllers/Api/UserController.php:379-380` assumes the create-user response always contains `json()['attributes']['id']`.
47. High - `app/Http/Controllers/Api/UserController.php:357-380` processes referral side effects before remote Pterodactyl user creation is confirmed. Notification jobs and side effects can escape before the external create succeeds.
48. High - `app/Http/Controllers/Api/UserController.php:452-457` inserts into `user_referrals` without checking for an existing row, allowing duplicate referral links through the API path.
49. High - `app/Http/Controllers/Api/UserController.php:447-452` increments credits and queues a referral notification outside a transaction with the referral insert.
50. Medium - `app/Http/Controllers/Admin/OverViewController.php:62,71` loads long payment ranges into memory and aggregates in PHP. The dashboard will not scale well.
51. Medium - `app/Http/Controllers/Admin/OverViewController.php:164` calls `getNode()` for every node after already fetching `getNodes()`, causing an avoidable N+1 remote API pattern on every overview request.
52. Medium - `app/Http/Controllers/Admin/OverViewController.php:173` divides average usage by the count of local DB nodes even when stale nodes were skipped. The displayed average is wrong when deleted remote nodes still exist locally.
53. High - `app/Http/Controllers/Admin/OverViewController.php:179-180` assumes every synced server still has a local product row. Missing products become null dereferences.
54. Medium - `app/Http/Controllers/Admin/OverViewController.php:175-191` performs per-server local DB lookups inside the remote server loop, creating another N+1-heavy path.
55. High - `app/Http/Controllers/Admin/OverViewController.php:199-201` assumes the latest ticket still has a user row. Deleted users break the overview widget.
56. Medium - `app/Listeners/AssociateDiscordRoles.php:29-30` counts all related servers, not active uncanceled servers, when deciding whether to assign the active-client Discord role.
57. Medium - `app/Listeners/DisassociateDiscordRoles.php:30-31` removes the active-client Discord role based on the total relation count, again ignoring canceled or otherwise inactive servers.
58. Medium - `app/Services/NotificationService.php:16-18` sends notifications to the full collection in one call with no chunking. Large "notify all" operations can spike memory and queue payload size.
59. Medium - `app/Support/HtmlSanitizer.php:13-29` allows `<a>` tags but never normalizes `target`/`rel` attributes. Sanitized HTML can still preserve unsafe new-tab links.
60. High - `app/Http/Controllers/Auth/RegisterController.php:164` hardcodes `Role::findById(4)` for new registrations. Installs with different role IDs break signup role assignment.
61. High - `app/Http/Controllers/Auth/RegisterController.php:127-168` is not transactional across remote Pterodactyl creation, local user creation, role assignment, and referral processing. A local failure can orphan the remote user or leave partial referral state.
62. Medium - `app/Http/Controllers/Auth/RegisterController.php:129-130` creates remote users with `external_id => null`, so the local and remote accounts have no stable external linkage.
63. Medium - `app/Http/Controllers/Api/ServerController.php:100-112` accepts `description` in the request, but the creation path never persists it because `ServerCreationService` only writes `name`, `user_id`, `product_id`, `node_id`, `last_billed`, and `billing_priority`.
64. Medium - `app/Http/Controllers/ServerController.php:516-536` trusts client-supplied validation rules in `validateDeploymentVariables()` instead of authoritative egg metadata. Callers can fabricate rule sets and get meaningless validation results.
65. Medium - `app/Http/Controllers/ServerController.php:313-320` assigns the active-client Discord role based on the raw server relation count, not active uncanceled servers.
66. Medium - `app/Http/Controllers/ServerController.php:360-364` removes the active-client Discord role using the same raw count logic, so canceled servers can keep the role incorrectly.
67. Medium - `app/Models/Product.php:98-101` treats `0` as falsy in the `minimumCredits` setter, so explicitly setting a zero minimum is silently converted to `null`.
68. Medium - `app/Http/Requests/Api/Products/CreateProductRequest.php:39-47` validates integer-backed resource fields as `numeric` instead of `integer`. Fractional values can pass validation and then be silently truncated.
69. Medium - `app/Http/Requests/Api/Products/UpdateProductRequest.php:40-48` has the same issue on the update path.
70. Medium - `app/Http/Requests/Api/Vouchers/CreateVoucherRequest.php:28` validates `uses` as `numeric` instead of `integer`, so fractional use counts can pass validation.
71. Medium - `app/Http/Requests/Api/Vouchers/UpdateVoucherRequest.php:28` repeats the same issue on the update path.
72. High - `app/Traits/Invoiceable.php:26-27,81-89` derives invoice sequence numbers from `count()` and then writes the PDF and DB row separately. Concurrent invoice generation can produce duplicate invoice numbers and orphaned files.
73. Low - `app/Console/Commands/NotifyServerSuspension.php:67` returns `0` instead of `Command::SUCCESS`. That reports success only accidentally and is inconsistent with the rest of the command layer.
