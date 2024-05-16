# Contributing Guidelines

Thank you for considering contributing to this repository! Before making a contribution, please take a moment to review the following guidelines.

## 🕵️‍♂️ Finding Tasks

Check the open issues to see if there's something you can contribute to. If you have an idea or encounter a bug that's not already listed, feel free to create a new issue and wait for feedback from the development team.

## 🤝 Code of Conduct

Please adhere to our [Code of Conduct](https://github.com/Ctrlpanel-gg/panel/blob/main/.github/CODE_OF_CONDUCT.md) in all your interactions with the project.

## 🌍 Localization

If you add any strings that are displayed on the frontend, please localize them using the following format:
```
"New String" -> {{ __('New String') }}
```
After adding localized strings, run the following command to generate localization files:
```cmd
php artisan translatable:export en
```

## 🚀 Pull Request Process

1. Give your pull request (PR) a clear and descriptive title that summarizes the changes.
2. The development team will review your code and provide feedback or approve/merge it when appropriate.
3. Ensure that your PR follows our Code of Conduct and coding style guidelines.

### 💻 Coding Style

We follow the PSR12 code standard for PHP.

Thank you for your contributions! 🎉
