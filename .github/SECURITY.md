# Security Policy

CtrlPanel is self-hosted software. This policy explains how to report a
security vulnerability, what we consider in scope, and what you can expect
from us in return.

## Supported Versions

Security fixes are applied to the latest stable release only. We do not
backport fixes to older versions, and we do not support pre-releases.

We accept reports for:

- the latest stable release
- the current `development` branch

A fix for a `development` issue ships in the next release. If you are
running an older version, upgrade before reporting. If you cannot upgrade,
tell us in the report; for critical issues we may advise mitigations even
when no patch is available for your version.

## Reporting a Vulnerability

> [!CAUTION]
> Do **not** open a public issue, discussion, or pull request for security
> vulnerabilities. Do **not** discuss the vulnerability publicly anywhere
> until an official writeup has been published.

Open a private advisory via [GitHub Security Advisories][advisories]. This
is the only way to report a vulnerability, and the only place vulnerability
details belong.

After submitting, you may **optionally** send a direct message (DM) on
Discord to `1day2die` or `mrweez_` with a link to the advisory, so the report
does not sit unseen. This is not required - the advisory alone is enough.
Never send vulnerability details over Discord.

### What to include

- Affected version or tag, and the commit hash if you use `development`
- The component or feature affected
- A clear description of the issue and its impact
- Step-by-step reproduction steps
- A proof of concept (request/response, script, or screenshots)
- Any preconditions (required role, configuration)
- Whether the issue is already public
- Your preferred name or handle for credit, if different from your GitHub
  username

### Report quality and AI-generated reports

We judge the finding, not the tools. You may use AI assistance to prepare a
report, and you may tick GitHub's "I used AI assistance" disclosure box. It
is optional, and it does not disqualify your report. An unticked box does
not exempt you from the rules below.

AI output is not evidence. A report must demonstrate the issue, not describe
it. Filling GitHub's required proof-of-concept field is not the same as
providing a working proof of concept. Reports without a reproducible proof
of concept, that describe a theoretical or speculative issue, or that cannot
be reproduced will be closed. This applies to all reports, AI-assisted or
not.

We may ask follow-up questions. If you cannot explain or reproduce your own
report, it will be closed. You are responsible for verifying anything you
submit.

Keep it concise and lead with the impact and the proof of concept. Long
write-ups do not compensate for a missing proof of concept.

We do not penalize or block people for reporting in good faith. We may block
accounts that repeatedly submit spam, reports that do not relate to this
software, or low-quality reports.

### Duplicate reports

Some reports duplicate an issue already accepted or under private review.
Filed advisories are private, so you cannot know this in advance. We close
duplicates without penalty. Credit goes to the first report that
demonstrates the issue with a proof of concept.

## What to Expect

We are a small team of volunteer maintainers. We do not make guarantees on
response or fix time, but we do our best to:

- acknowledge the report within **14 business days**
- triage and assign a severity within **30 calendar days**

If you have not received a response, contact us again through the advisory
or ping a maintainer on Discord. We commit to keeping reports moving: we
will not leave a report unacknowledged or a fix pending without a status
update.

We may ask you to retest a fix before or after release. We keep your report
confidential until the advisory is published.

## Severity

Severity is assigned by the maintainers. You are not required to calculate a
CVSS score. We use [CVSS 4.0][cvss40] as our reference where practical; if
you prefer to include a vector, please use 4.0 over 3.1.

## Disclosure Policy

We publish an advisory as soon as a fix is available. The embargo windows
below are maximums, not minimums: if a fix ships sooner, or the issue carries
little user risk, we publish sooner.

We do not disclose a vulnerability before a fix is available, unless it is
already public or being actively exploited, in which case we publish
immediately with mitigations.

| Severity     | Exploit conditions                                                  | Embargo                          |
|--------------|---------------------------------------------------------------------|----------------------------------|
| Critical     | Remote, unauthenticated, broad impact                               | 14 days after the fix release    |
| Moderate     | Requires an account or a specific configuration                     | 7 days after the fix release     |
| Low          | Requires a privileged or admin account, or very specific conditions | With the fix release, no embargo |

When we publish, we release a GitHub Security Advisory and request a CVE. We
notify the reporter before publication so they can review their credit.

Do not publish or share details of the vulnerability before the applicable
window has passed. We do not grant a right to unilateral disclosure.

## Scope

CtrlPanel is self-hosted software. The project does not operate a hosted or
SaaS instance. This policy covers the code in this repository only, and we
do not manage or have access to your deployment.

We accept reports for instances that are:

- deployed following the official documentation
- running the latest stable release or the current `development` branch
- free of extensions, themes, and other modifications that alter the
  database

For reproduction, use a clean instance with no extensions installed.
Extensions can change the database structure, and a database that was
modified by an extension, now or previously, is not a valid test environment
and may make an issue unreproducible. If a problem only occurs with an
extension installed, report it to that extension's author.

Issues that only occur in a misconfigured or unsupported deployment are out
of scope. We cannot verify or support every configuration.

### In scope

- Authentication and authorization bypass
- Remote code execution
- SQL injection
- Cross-site scripting (XSS)
- Server-side request forgery (SSRF)
- Insecure deserialization
- Sensitive data exposure
- Privilege escalation
- Insecure direct object references (IDOR) affecting other users' data

### Out of scope

"Out of scope" means we may close a report without recognition or a fix. It
does not mean the issue is not real; we may still fix it if capacity allows.

- Third-party extensions, themes, and marketplace addons
- Vulnerabilities in third-party dependencies (report those upstream)
- Forks and third-party distributions
- Pterodactyl and other upstream projects
- Third-party integrations and providers, such as Stripe, PayPal, Discord,
  or email providers
- The project website, documentation, and marketplace sites, unless the
  issue also affects the panel
- Issues in a specific operator's deployment, or secrets leaked in one
- Misconfigured, undocumented, or otherwise unsupported deployments
- Reports that do not demonstrate a security impact, including hardening and
  best-practice suggestions
- Automated scanner or AI-generated output without a reproducible proof of
  concept
- Disclosure of non-sensitive information, version numbers, or error
  messages without a security impact
- User or account enumeration without a security impact
- Brute force or missing rate limits without a concrete bypass
- Missing security headers, cookie flags, or TLS config without an exploit
- Self-XSS, clickjacking on non-sensitive pages, CSRF on logout
- Actions an administrator can perform by design, with no privilege boundary
  crossed
- Issues that only affect the actor performing them
- Denial of service and resource exhaustion
- Social engineering
- Issues requiring physical access to the server

### Testing

- Test against your own instance. Do not use the public demo server, and do
  not disrupt other users.
- Do not access, modify, or exfiltrate data that is not yours.
- Do not run denial of service, brute force, or load tests.
- Keep the test footprint small and stop once you have proven impact.

## Safe Harbor

Security research conducted in accordance with this policy is considered
authorized. We will not initiate or support legal action against researchers
who:

- act in good faith and follow this policy
- test only against their own instance or an authorized target
- avoid privacy violations, data destruction, and service disruption
- give us a reasonable time to fix the issue before disclosing

This safe harbor applies to the project only and cannot bind third parties,
including hosting providers, Pterodactyl, or other upstream projects. If in
doubt, ask us through an advisory before testing.

## Recognition

We do not offer monetary rewards, but we do recognize researchers who report
valid vulnerabilities.

### Roles

Confirmed reports are rewarded with a role on our Discord server:

| Role                | Awarded for                                                                                                          |
|---------------------|----------------------------------------------------------------------------------------------------------------------|
| `Bug Hunter`        | Any confirmed report with a proof of concept, whether a regular bug or a vulnerability reported through an advisory |
| `Golden Bug Hunter` | Maintainer discretion, for a notable contribution to finding non-critical bugs                                      |
| `White Hat`         | Maintainer discretion, for a notable contribution to reporting vulnerabilities                                      |

`Golden Bug Hunter` and `White Hat` are granted by the maintainers based on
the impact and quality of the contribution, not on report count alone. All
valid reports receive `Bug Hunter`; `Golden Bug Hunter` and `White Hat` are
additional honors.

### Hall of Fame

This list recognizes researchers who have responsibly disclosed
vulnerabilities to this project. Entries are one per advisory and are added
after the advisory and CVE are published. Entries are listed newest first.

Accepting credit in the GitHub Security Advisory automatically gives us
permission to list you here. By default we use your GitHub username. If you
want a different name or handle, ask in the advisory thread. If you accept
credit but prefer not to appear in this list, tell us and we will omit you.

| Researcher | Finding | Advisory |
|------------|---------|----------|
| [Wiktor Dudek](https://github.com/wiktordudek) | Unauthenticated RCE via Installer Accessible After Installation and Unsanitized Shell Arguments | [GHSA-jmhr-q9q5-fqwh](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-jmhr-q9q5-fqwh) (CVE-2026-34234) |
| [Simba](https://github.com/simbabimba-dev) | Unsafe Dynamic Class Instantiation in Admin Settings Allows Potential Remote Code Execution | [GHSA-vcg3-fjrx-rg5q](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-vcg3-fjrx-rg5q) (CVE-2026-34216) |
| [Simba](https://github.com/simbabimba-dev) | Stored XSS in Admin Role Management via Unescaped DataTable HTML Output | [GHSA-wpqj-xwhq-2mmh](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-wpqj-xwhq-2mmh) (CVE-2026-34246) |
| [Lighthouse Keeper](https://github.com/lighthousekeeper1212) | Missing Authorization on Admin Write Endpoints Allows RBAC Bypass | [GHSA-pxmw-gj52-9p68](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-pxmw-gj52-9p68) (CVE-2026-34358) |
| [R34P3R](https://github.com/knull-reaper) | Stored XSS in Ticket Reply Notifications Allows Session Hijacking | [GHSA-cmrr-q3hw-3vqh](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-cmrr-q3hw-3vqh) (CVE-2026-34241) |
| [Arda](https://github.com/ardapaydin) | Missing Authorization on Admin Datatable Endpoints Allows Unauthorized Access to Sensitive Data | [GHSA-mj5g-j7fq-7hc4](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-mj5g-j7fq-7hc4) (CVE-2026-34233) |
| [Arda](https://github.com/ardapaydin) | Stored XSS vulnerability in TicketsController priority field | [GHSA-2q43-grv2-jxwh](https://github.com/Ctrlpanel-gg/panel/security/advisories/GHSA-2q43-grv2-jxwh) (CVE-2025-25203) |

### Published advisories

Past advisories are listed on the [published advisories page][published].

[advisories]: https://github.com/ctrlpanel-gg/panel/security/advisories/new
[published]: https://github.com/ctrlpanel-gg/panel/security/advisories
[cvss40]: https://www.first.org/cvss/calculator/4.0
