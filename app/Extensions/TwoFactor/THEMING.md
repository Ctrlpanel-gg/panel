## 2FA Views Override System

One of the strongest aspects of the current implementation is how flexible view overriding is.
By using Laravel’s standard `loadViewsFrom` mechanism with namespaces, any theme can override views of any 2FA extension.

---

### How It Works

When an extension (for example, TOTP) calls:

`view('twofactor_totp::profile_card')`

Laravel (along with the theme system) resolves the view using the following priority:

1. Active theme  
   `themes/{theme}/views/vendor/twofactor_totp/profile_card.blade.php`

2. Default extension view (fallback)  
   `app/Extensions/TwoFactor/Totp/views/profile_card.blade.php`

---

### Examples for Theme Developers

#### 1. Override the 2FA method picker screen

Create the file:  
`themes/{theme}/views/auth/two-factor/picker.blade.php`

This view is not namespaced, so it behaves like a regular panel view.

---

#### 2. Override the TOTP profile card

Create the file:  
`themes/{theme}/views/vendor/twofactor_totp/profile_card.blade.php`

---

#### 3. Override the TOTP challenge screen (code input)

Create the file:  
`themes/{theme}/views/vendor/twofactor_totp/auth/two-factor/totp-challenge.blade.php`

---

### Notes for Developers

In ExtensionServiceProvider, the view namespace is automatically generated using the pattern:

`{namespace_folder}_{method_name}`

Example:
`twofactor_totp`

This ensures that any new 2FA extension works out of the box with the same override system.
