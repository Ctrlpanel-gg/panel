<div align="center">
    <img src="https://ctrlpanel.gg/img/controlpanel.png" width="128" alt="" />
</div>

# CtrlPanel.gg

CtrlPanel offers an easy-to-use and free billing solution for all starting and experienced hosting providers that seamlessly integrates with the Pterodactyl panel. It facilitates account creation, server ordering, and management, while offering addons, multiple payment methods, and customizable themes for a comprehensive solution.

> **Important:** CtrlPanel is **only compatible with Pterodactyl**. It does not support Pelican or any other hosting panels.

![GitHub tag](https://img.shields.io/github/tag/Ctrlpanel-gg/panel)
![Overall Installations](https://img.shields.io/badge/Overall%20Installations-8000%2B-green)
![GitHub stars](https://img.shields.io/github/stars/Ctrlpanel-gg/panel)
![License](https://img.shields.io/github/license/Ctrlpanel-gg/panel)
![Discord](https://img.shields.io/discord/787829714483019826)

![CtrlPanel](https://user-images.githubusercontent.com/67899387/214684708-739c1d21-06e8-4dec-a4f1-81533a46cc7e.png)

## Features

- Store with Credit-based system
- Popular payment gateways: PayPal, Stripe, Mollie, MercadoPago and more via thirdparty extensions
- Dynamic server billing (From hourly to yearly billing cycles)
- Referral and partner system
- Vouchers
- Ticket system
- Discord integration for verification and role assignment
- Role system with granular permissions
- Invoice generation with email delivery
- One account per IP registration limit
- HTTP API

And that's not all! Install CtrlPanel and you will be able to test all the available features that is being improved from version to version.

## Live Demo

Demo server: [demo.CtrlPanel.gg](https://demo.CtrlPanel.gg)

*Temporary demo - all data is periodically wiped.*

## Installation

Full installation documentation is available at [ctrlpanel.gg/docs](https://ctrlpanel.gg/docs/).

### Docker

> **Beta:** Docker support is experimental and not officially documented. Functionality is not guaranteed. Improvements are planned for a future release.

```bash
docker run -d -p 8080:80 -p 8443:443 --name ctrlpanel ghcr.io/ctrlpanel-gg/panel:latest
```

After starting, configure the database and Pterodactyl connection manually. See [.github/docker/README.md](https://github.com/Ctrlpanel-gg/panel/blob/main/.github/docker/README.md) for what's currently available.

### Linux

Supported on major distributions - Debian, Ubuntu, CentOS, Fedora, Arch, and others. Follow the [documentation](https://ctrlpanel.gg/docs/) for a full setup guide.

## Updating

See the [update instructions](https://ctrlpanel.gg/docs/category/updating) before upgrading.

## Marketplace

Looking for addons and extensions? Visit the [CtrlPanel Marketplace](https://market.ctrlpanel.gg/).

## Roadmap

Planned features and upcoming work: [CtrlPanel Roadmap](https://github.com/orgs/Ctrlpanel-gg/projects/1)

## Community and Support

For questions and help, join the [CtrlPanel Discord](https://discord.gg/ctrlpanel-gg-787829714483019826).

If you find CtrlPanel useful, consider [supporting the project](https://ctrlpanel.gg/docs/contributing/donating).

## Contributing

Contributions are welcome. Please read the following before getting started:

- [CONTRIBUTING.md](https://github.com/Ctrlpanel-gg/panel/blob/development/.github/CONTRIBUTING.md) - contribution guidelines and pull request process
- [CODE_OF_CONDUCT.md](https://github.com/Ctrlpanel-gg/panel/blob/development/.github/CODE_OF_CONDUCT.md) - community standards
- [CONTRIBUTOR_LICENSE_AGREEMENT](https://github.com/Ctrlpanel-gg/panel/blob/development/CONTRIBUTOR_LICENSE_AGREEMENT) - required for all contributors
- [LICENSE](https://github.com/Ctrlpanel-gg/panel/blob/main/LICENSE) - project license

## Security

To report a vulnerability, please follow the process described in [SECURITY.md](https://github.com/Ctrlpanel-gg/panel/blob/development/.github/SECURITY.md). Do not open public issues for security-related matters.
