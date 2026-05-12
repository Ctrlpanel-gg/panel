# Modular Two-Factor Authentication (2FA) System

CtrlPanel.gg features a fully modular 2FA system that allows developers to add new authentication methods (e.g., SMS, Email, WebAuthn) without modifying the core codebase. Each method is a self-contained extension.

---

## 1. Directory Structure

Every 2FA extension resides in `app/Extensions/TwoFactor/{MethodName}/`.

```text
app/Extensions/TwoFactor/YourMethod/
├── migrations/             # Database changes specific to this method
├── views/                  # Blade templates (settings, challenges)
│   ├── auth/two-factor/    # Recommended path for challenge views
│   └── profile_card.blade.php
├── YourMethodExtension.php # The main extension class
├── routes.php              # (Optional) Custom routes for this method
└── YourMethodService.php   # (Optional) Helper services
```

---

## 2. The Extension Class

Your main class must extend `App\Classes\TwoFactorExtension`.

### Required Methods
- `getName()`: Technical identifier (e.g., `totp`). Used in routes and namespaces.
- `getLabel()`: User-friendly name (e.g., `Authenticator App`).
- `getIcon()`: FontAwesome icon class (e.g., `fas fa-mobile-alt`).
- `getDescription()`: Short description shown in the picker.
- `getSettingsView()`: Name of the Blade view for the profile card.
- `getChallengeView()`: Name of the Blade view for the login screen.
- `verify(Request $request)`: Logic to validate the user's code/token.
- `setup(Request $request)`: Initialization logic (e.g., generating a QR code).
- `enable(Request $request)`: Final logic to enable the method for the user.
- `disable(Request $request)`: Logic to remove the method from the user.

---

## 3. Advanced Features

### Dynamic Rate Limiting
You can control the load of your extension by overriding `getRateLimit(string $action)`. This prevents abuse, such as OTP spamming.

```php
public function getRateLimit(string $action): array
{
    if ($action === 'action') { // Custom actions like "Resend Email"
        return ['attempts' => 1, 'minutes' => 5]; // 1 request every 5 minutes
    }
    return parent::getRateLimit($action);
}
```

### Action Whitelisting
To call custom methods on your extension via the universal route `POST /profile/security/2fa/{method}/{action}`, you must whitelist them in `getAllowedActions()`.

```php
public function getAllowedActions(): array
{
    return ['resendEmail', 'verifyCustomToken'];
}
```

### Database & Migrations
If your method needs custom columns (e.g., `phone_number`), create a migration in your extension's `migrations/` folder. It will be loaded automatically.
**Note:** Use `Schema::table('user_two_factor_methods', ...)` to extend the core table.

### Custom Routes
If the standard `setup`, `enable`, `disable`, and `action` routes are not enough for your method, you can define your own routes in a `routes.php` file within your extension directory.

The system will automatically load this file. It is recommended to use your extension's name as a prefix and apply the necessary middleware:

```php
// app/Extensions/TwoFactor/YourMethod/routes.php
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'two_factor.verified'])->group(function () {
    Route::post('profile/security/2fa/your-method/special-action', [YourController::class, 'handle'])
        ->name('profile.2fa.your-method.special-action');
});
```

---

## 4. Theming & View Overrides

The system uses Laravel’s `loadViewsFrom` with a dynamic namespace pattern: `twofactor_{method_name}`.

### How It Works
When you call `view('twofactor_totp::profile_card')`, the system searches in this order:
1. **Active Theme**: `themes/{theme}/views/vendor/twofactor_totp/profile_card.blade.php`
2. **Extension Default**: `app/Extensions/TwoFactor/Totp/views/profile_card.blade.php`

### Common Override Paths
- **Method Picker**: `themes/{theme}/views/auth/two-factor/picker.blade.php` (Core view)
- **Method Card**: `themes/{theme}/views/vendor/twofactor_{method}/profile_card.blade.php`
- **Method Challenge**: `themes/{theme}/views/vendor/twofactor_{method}/auth/two-factor/{method}-challenge.blade.php`

---

## 5. Security Best Practices
1. **Environment Checks**: For development-only methods, use `app()->environment('local')` in `isAvailable()`.
2. **Encryption**: Always `encrypt()` sensitive data (secrets, tokens) before saving to the database and `decrypt()` when retrieving.
3. **Verification**: Always require a password check or a valid 2FA code in the `disable()` method.
