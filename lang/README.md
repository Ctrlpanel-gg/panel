# 🌍 Localization

If you add any strings that are displayed on the frontend, please localize them using the following format:
```
"New String" -> {{ __('New String') }}
```
After adding localized strings, run the following command to generate localization files:
```cmd
php artisan translatable:export en
```
