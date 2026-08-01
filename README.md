# Liberu Composer Installer

> Deterministic Composer 2 installer for tracked Liberu modules and themes.

[Software](https://liberusoftware.com) · [Hosting](https://liberuhosting.com) · [Services](https://liberuservices.com) · [Liberu Group](https://liberugroup.com)

[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://www.php.net/) [![Latest release](https://img.shields.io/github/v/release/liberusoftware/composer-installer?sort=semver)](https://github.com/liberusoftware/composer-installer/releases/latest) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

## Scope

Deterministic Composer 2 installer for tracked Liberu modules and themes. This package is independently versioned and has no application-specific dependency.

## Requirements and installation

| Dependency | Supported version |
|---|---|
| `php` | `^8.5` |
| `composer-plugin-api` | `^2.6` |

```bash
composer require liberu/composer-installer
```

## Public surface

- `src/LiberuInstaller.php`
- `src/Plugin.php`

Consumers should depend only on these public types and Composer metadata. Semantic-versioning rules apply to changes in their signatures or behaviour.

## Testing

Run the package tests through a compatible host checkout and verify Composer installation on PHP 8.5. Contract packages must remain free of framework, persistence, UI, and provider-SDK concerns; the installer must retain deterministic, traversal-safe install/update/remove behaviour.

## Security

Do not report vulnerabilities through public issues. Email `security@liberusoftware.com` with reproduction details and the affected version.

## License

This package is open-source software under the [MIT License](LICENSE.md). The linked licence text is authoritative.

## Feedback and contributing

Focused issues and tested pull requests are welcome in the [GitHub repository](https://github.com/liberusoftware/composer-installer). Update tests, documentation, and `CHANGELOG.md` for user-visible changes.

## Contributors

Thank you to everyone who helps improve Liberu. [View the contributors graph](https://github.com/liberusoftware/composer-installer/graphs/contributors).
