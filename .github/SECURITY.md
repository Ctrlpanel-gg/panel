# Security Policy

## Supported Versions

Security fixes are applied to the latest release only. We do not backport
fixes to older versions.

## Reporting a Vulnerability

> [!CAUTION]
> Do **not** open a public issue, discussion, or pull request for security
> vulnerabilities. Do **not** discuss the vulnerability publicly anywhere
> until an official writeup has been published.

Open a private advisory via [GitHub Security Advisories][advisories], then
**you must notify** the project owner `1day2die` on Discord via direct
message with a link to the advisory. This step is required - reports
submitted without a notification may be missed entirely.

## Response Timeline

We are a small team of volunteer maintainers. We do not make guarantees
on response or fix time, but we will do our best to acknowledge and
address reports as capacity allows.

## Disclosure Policy

Once a fix is ready, it will be included in the next scheduled release.
Full details of the vulnerability will be made public no earlier than
**two weeks after the release** containing the fix. At that point, a
GitHub Security Advisory and a CVE will be published.

Do not publish or share details of the vulnerability before this window
has passed.

## Recognition

We do not offer monetary rewards, but we do recognize researchers who
report valid vulnerabilities.

### Roles

Confirmed reports are rewarded with a role on our Discord server:

| Severity   | Role            |
|------------|-----------------|
| Minor      | Bug Hunter Lv.1 |
| Serious    | Bug Hunter Lv.2 |

### Hall of Fame

Researchers who have responsibly disclosed vulnerabilities to this project
(listed with their consent):

| Researcher | Finding | Date |
|------------|---------|------|
| -          | -       | -    |

To be added to this list, let us know in your report whether you consent
to public recognition and under what name or handle.

## Scope

The following are considered in scope:

- Authentication and authorization bypass
- Remote code execution
- SQL injection
- Cross-site scripting (XSS)
- Sensitive data exposure
- Privilege escalation

The following are out of scope:

- Vulnerabilities in third-party dependencies (report those upstream)
- Issues requiring physical access to the server
- Social engineering

[advisories]: https://github.com/ctrlpanel-gg/panel/security/advisories/new
