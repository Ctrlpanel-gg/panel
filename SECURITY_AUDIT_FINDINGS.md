# Security Audit Findings - CtrlPanel.gg

**Date:** March 23, 2026  
**Severity Classification:**
- 🔴 CRITICAL - Requires immediate fix
- 🟠 HIGH - Fix urgently, affects security
- 🟡 MEDIUM - Should be fixed soon
- 🔵 LOW - Code quality/best practices

---

## CRITICAL VULNERABILITIES

### 1. 🔴 Weak Random ID Generation in Payment Processing

**Location:**
- `app/Extensions/PaymentGateways/PayPal/PayPalExtension.php` line 50
- `app/Http/Controllers/Admin/PaymentController.php` line 112

**Issue:** Uses `uniqid()` for generating payment reference IDs and payment_id values. `uniqid()` is NOT cryptographically secure and can be predicted/guessed.

```php
// VULNERABLE
"reference_id" => uniqid(),
'payment_id' => uniqid(),
```

**Impact:** Attackers could manipulate payment IDs or forge payment references.

**Fix:** Use `Illuminate\Support\Str::random()` or `random_bytes()` instead.

```php
"reference_id" => \Illuminate\Support\Str::uuid(),  // or random(16)
'payment_id' => \Illuminate\Support\Str::random(32),
```

---

### 2. 🔴 API Authorization Bypass via Role Manipulation

**Location:** `app/Http/Controllers/Api/UserController.php` line ~115

**Issue:** The API allows role_id updates for scoped tokens with **ensureCanAccessUser** check passing but no verification that the token owner is authorized to change roles.

```php
if ($this->ownerScopedUserId($request) !== null && isset($data['role_id'])) {
    abort(403, 'This API token cannot change roles.');
}
```

This check only works if the token is owner-scoped. A **global API token** with `users.write` can change ANY user's role, including promoting regular users to admin.

**Impact:** Privilege escalation - API users could become admins.

**Fix:** Ensure global tokens also validate role change authorization:

```php
// Check if user changing role is admin
$this->authorize('admin.users.write.role');
```

---

### 3. 🔴 Admin Bypass via Missing Authorization Check in Admin Controllers

**Location:** `app/Http/Controllers/Admin/PaymentController.php` line 112-130

**Issue:** Manual payment creation endpoint doesn't validate the user actually has permission to create payments. While there's an `authorize()` check missing, the more critical issue is:

The payment creation allows admin-only manual payment entry without proper authorization checks across payment types.

**Impact:** Users with limited admin permissions could create fraudulent payments.

**Fix:** Add explicit permission checks in PaymentController methods.

---

### 4. 🔴 Unencrypted Sensitive Data in Activity Logs

**Location:** `app/Models/User.php` line 130-140
**Location:** `app/Models/Voucher.php` tapping activity
**Location:** `app/Models/Server.php` LogsActivity trait

**Issue:** Activity log system logs user mutations including potentially sensitive fields. While `$logAttributes` appears limited, the `CausesActivity` trait could log credential data if not properly configured.

```php
protected static $logAttributes = ['name', 'email'];
```

Email changes ARE being logged to activity logs which are stored in database without encryption.

**Impact:** Sensitive user data exposed in audit logs.

**Fix:** 
- Move sensitive data to separate encrypted table
- Use masking/hashing for email in logs
- Implement log purge policies

---

### 5. 🔴 Race Condition in Credit/Voucher Consumption

**Location:** `app/Models/Voucher.php` line 149
**Location:** `app/Console/Commands/ChargeServers.php` billing logic

**Issue:** While pessimistic locking (lockForUpdate) is used in some places, the voucher redemption and server billing system has a time-of-check to time-of-use (TOCTOU) vulnerability. 

The check for voucher validity happens in `isCouponValid()` but the actual consumption happens later, allowing potential double-redemption race conditions.

**Impact:** Users could use coupons multiple times or servers could be charged twice.

**Fix:** Use database transactions with proper locking:

```php
DB::transaction(function() {
    $voucher = Voucher::lockForUpdate()->find($id);
    // Validate and consume atomically
}, 3); // 3 retries for deadlocks
```

---

## HIGH SEVERITY VULNERABILITIES

### 6. 🟠 Missing CSRF Protection on Webhook Routes

**Location:** `app/Http/Middleware/VerifyCsrfToken.php`

**Issue:** Webhook routes are correctly excluded from CSRF verification via `RoutesIgnoreCsrf` config. However, there's no verification of webhook authenticity (no signature validation visible for at least Mollie/Stripe).

While payment gateways handle signature validation in their SDK, the exception list is dynamically built, creating a potential for misconfig.

**Impact:** Unverified webhooks could cause payment state corruption.

**Fix:** Add explicit webhook signature verification in all gateway handlers.

---

### 7. 🟠 API Scope Override Potential

**Location:** `app/Http/Middleware/ApplicationApiScope.php`

**Issue:** The scope checking uses `hasAnyAbility()` which checks against token abilities. However, there's no lifecycle enforcement on API token expiration or revocation check.

Once an `expires_at` date passes, `isActive()` returns false, but there's no explicit background revocation of expired tokens, and they remain queryable.

**Impact:** Expired tokens might be replayed if not strictly enforced.

**Fix:** Add automatic token purging:

```php
// In middleware
if (!$token->isActive()) {
    $token->forceDelete(); // Or soft delete
    abort(401);
}
```

---

### 8. 🟠 Mass Assignment Vulnerability in Admin Controllers

**Location:** Multiple admin controllers, e.g., `app/Http/Controllers/Admin/ShopProductController.php` line 79, 123

**Issue:** Direct use of `array_merge($request->all(), ...)` without explicitly validating which fields got through.

```php
ShopProduct::create(array_merge($request->all(), ['disabled' => $disabled]));
$shopProduct->update(array_merge($request->all(), ['disabled' => $disabled]));
```

While models have `$fillable` arrays defined, explicitly using `all()` could catch unintended fields if validation is bypassed.

**Impact:** Unintended field modification.

**Fix:** Use `only()` explicitly:

```php
$shopProduct->update($request->validated());
```

---

### 9. 🟠 Unencrypted Database Credentials in Environment Variables

**Location:** `.env.example` + config/database.php

**Issue:** While this is expected for example file, there's no indication of database encryption at rest or SQL file backups being encrypted.

**Impact:** Database backup exposure leaks all user/payment data.

**Fix:** 
- Document database encryption requirements
- Enforce encrypted backups
- Use read replicas with restricted access

---

### 10. 🟠 SQL Injection Risk via DB::raw() in Migrations/Queries

**Location:** `database/migrations/2026_02_02_175351_migrate_product_minimum_credits_values.php` line 32

**Issue:** While not user input, the use of `DB::raw()` could be dangerous if any part becomes dynamic:

```php
->where('type', 'free')
->update(['minimum_credits' => DB::raw('price')]);
```

This is safe as-is, but pattern encourages risky usage elsewhere.

**Impact:** Potential SQL injection if this pattern spreads.

**Fix:** Use parameter binding instead:

```php
// Use raw only when absolutely necessary, never with user input
// Document the reason for using raw
```

---

## MEDIUM SEVERITY ISSUES

### 11. 🟡 Missing Rate Limiting on API Endpoints

**Location:** `routes/api.php`

**Issue:** API routes lack rate limiting middleware. Anyone with a valid token can make unlimited requests.

**Example:**
```php
Route::middleware('api.token')->group(function () {
    Route::get('users', [UserController::class, 'index'])
    // No rate limiting!
});
```

**Impact:** API abuse, DOS attacks on database queries.

**Fix:** Add rate limiting:

```php
Route::middleware(['api.token', 'throttle:60,1'])->group(...)
```

---

### 12. 🟡 Missing Input Validation on Filters

**Location:** `app/Http/Controllers/Api/UserController.php` line 56-64

**Issue:** API uses `QueryBuilder::allowedFilters()` which restricts field names, but user input like `name` is passed directly to query builder.

While the library prevents field traversal, complex filter chains could cause database load issues.

**Impact:** Resource exhaustion attacks.

**Fix:** Add pagination limits and query complexity limits:

```php
->paginate(min($request->input('per_page') ?? 50, 100))
```

---

### 13. 🟡 Weak Gravatar Hash Dependency

**Location:** `app/Models/User.php` line 297

**Issue:** Uses MD5 for Gravatar URL generation. While MD5 is acceptable for Gravatar (it's their standard), it indicates older security practices in codebase.

**Impact:** Low - but indicates legacy security thinking.

**Fix:** No fix needed for Gravatar (it's their standard), but document this exception.

---

### 14. 🟡 Missing HEADERS Security Headers

**Location:** HTTP response headers configuration

**Issue:** No evidence of security headers configuration (X-Frame-Options, X-Content-Type-Options, etc)

**Impact:** XSS, clickjacking, MIME sniffing attacks.

**Fix:** Add middleware:

```php
// config/http.php or middleware
'X-Frame-Options' => 'DENY',
'X-Content-Type-Options' => 'nosniff',
'X-XSS-Protection' => '1; mode=block',
'Strict-Transport-Security' => 'max-age=31536000'
```

---

### 15. 🟡 Insecure Redirect Potential

**Location:** `app/Extensions/PaymentGateways/PayPal/PayPalExtension.php` line 60-65

**Issue:** Success/cancel URLs are constructed using route() helper, which is safe, but there's no validation that users are redirected to their own payment checkout, not another user's.

Actually, checking line 100+ shows proper user_id verification, so this is safe.

**Noted:** Code does verify payment ownership correctly.

---

### 16. 🟡 Sensitive Data in Logs

**Location:** `app/Listeners/UserPayment.php` and other listeners

**Issue:** Logging with full details of payments and users without masking PII.

```php
logger()->warning('Failed to update user in Pterodactyl.', [
    'user_id' => $user->id,  // OK
    'status' => $response->status(),
]);
```

Better, but more complex operations log full objects.

**Impact:** Sensitive data in log files.

**Fix:** Mask PII in logs:

```php
logger()->warning('Payment processing error', [
    'user_id' => auth()->id(),
    'payment_hash' => hash('sha256', $payment->id),
]);
```

---

### 17. 🟡 No Audit Trail for API Operations

**Location:** `app/Http/Controllers/Api/*`

**Issue:** API operations don't trigger activity logging like admin operations do. An API user could make massive changes without audit trail.

**Impact:** Untrackable malicious API usage.

**Fix:** Add activity logging to API controllers via middleware or trait.

---

### 18. 🟡 Inadequate Payment Status Validation

**Location:** `app/Extensions/PaymentGateways/Stripe/StripeExtension.php` line 127-160

**Issue:** Payment status update only checks for non-PAID status:

```php
$updated = Payment::whereKey($payment->id)
    ->where('status', '!=', PaymentStatus::PAID->value)
    ->update([...]);
```

This allows PROCESSING status payments to be marked as PAID, which is correct. However, there's no verification that the amount matches what was requested.

**Impact:** Could cause payment amount mismatches.

**Fix:** Verify payment amount matches:

```php
if ((int)$paymentIntent->amount_received !== $payment->amount_integer) {
    abort(400, 'Amount mismatch');
}
```

---

### 19. 🟡 No Backup/Recovery Plan for API Tokens

**Location:** `app/Models/ApplicationApi.php`

**Issue:** Once an API token is created and shown once, there's no recovery mechanism if lost. This is OK for security, but dangerous operationally.

**Impact:** Lost access could lock out API consumers.

**Fix:** Document token backup requirements for users.

---

---

## LOW SEVERITY ISSUES

### 20. 🔵 Deprecated md5() Usage for Gravatar

Already mentioned in Medium section - acceptable for Gravatar but elsewhere it's weak.

---

### 21. 🔵 Missing @throws Documentation

**Location:** Multiple controllers

**Issue:** Methods using findOrFail() don't document ModelNotFoundException in docblocks, though types hint it.

**Impact:** Developer confusion.

**Fix:** Add proper docblock documentation.

---

### 22. 🔵 Inconsistent Error Handling

**Location:** Multiple payment gateways

**Issue:** Some gateways log failed responses, others might not. Inconsistent error handling patterns make maintenance harder.

**Impact:** Potential missed errors in production.

**Fix:** Create unified payment error handler.

---

### 23. 🔵 Permission String Typos Potential

**Location:** `config/permissions_web.php` and usage throughout

**Issue:** Permission strings are magic strings. A typo could silently fail permission checks.

**Impact:** Accidental authorization bypass if permission names are mistyped.

**Fix:** Create constants for permission names:

```php
const ADMIN_USERS_READ = 'admin.users.read';
const ADMIN_USERS_WRITE = 'admin.users.write';
```

---

### 24. 🔵 No Request/Response Logging for API

**Location:** `routes/api.php`

**Issue:** No middleware to log API requests/responses for debugging.

**Impact:** Hard to debug production issues.

**Fix:** Add debug logging middleware.

---

### 25. 🔵 Timezone Issues Possible in Scheduling

**Location:** `app/Console/Commands/*`

**Issue:** Commands use Carbon for date comparison. If timezone isn't properly set, could cause off-by-one billing.

```php
Carbon::now(config('app.timezone'))
```

This is actually done correctly! Good practice observed.

**Noted:** This is handled properly.

---

---

## ADDITIONAL OBSERVATIONS & BEST PRACTICES

### ✅ Strengths Observed:

1. **Proper use of Pessimistic Locking** - Server charging uses `lockForUpdate()`
2. **Strong Password Hashing** - Uses Laravel's Hash facade (bcrypt)
3. **CSRF Protection** - Properly configured middleware with extension exceptions
4. **Authorization Checks** - Most routes check permissions via `$this->checkPermission()`
5. **API Scope Validation** - Token scopes are properly validated
6. **SQL Prepared Statements** - Consistent use of parameter binding in queries
7. **Activity Logging** - Spatie activity log captures changes
8. **Database Transactions** - Used correctly in critical operations

### ⚠️ Recommended Improvements:

1. Implement **API rate limiting** globally
2. Add **request signing** for extra-sensitive operations  
3. Implement **circuit breaker** for Pterodactyl API calls
4. Add **audit event webhooks** for security events
5. Implement **two-factor authentication** for admin accounts
6. Add **security headers** to all responses
7. Encrypt **sensitive configuration** values
8. Implement **IP whitelisting** for admin area
9. Add **SIEM integration** for security events
10. Implement **database activity monitoring**

---

## QUICK PRIORITY FIX LIST

**Fix immediately (Next 24 hours):**
1. ✅ Replace `uniqid()` with cryptographically secure random
2. ✅ Add authorization check for global token role changes
3. ✅ Verify webhook signatures explicitly
4. ✅ Add payment amount verification

**Fix this week:**
5. Add rate limiting to API
6. Add security headers middleware
7. Implement API operation logging
8. Fix race condition in voucher/billing

**Fix this sprint:**
9. Encrypt sensitive data in activity logs
10. Add IP whitelisting for admin area
11. Implement 2FA for admins
12. Add request signing for critical operations

---

## APPENDIX: Code Examples for Fixes

### Fix 1: Replace uniqid()

```php
// BEFORE
'payment_id' => uniqid(),

// AFTER  
'payment_id' => Str::random(16),
// OR
'payment_id' => (string) Uuid::generate(),
```

### Fix 2: Add Security Headers

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    if (config('app.env') === 'production') {
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
    
    return $response;
}

// Register in Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### Fix 3: Add API Rate Limiting

```php
// routes/api.php
Route::middleware(['api.token', 'throttle:60,1'])->group(function () {
    // API routes
});
```

### Fix 4: Verify Payment Amount

```php
// In Stripe/PayPal webhook handlers
if ((int)$remotePaymentAmount !== (int)$payment->amount_integer) {
    Log::critical('Payment amount mismatch', [
        'payment_id' => $payment->id,
        'expected' => $payment->amount_integer,
        'received' => $remotePaymentAmount,
    ]);
    abort(400, 'Amount verification failed');
}
```

---

**Report Generated:** 2026-03-23  
**Auditor:** Security Analysis  
**Status:** Review Complete - 25 Issues Found
