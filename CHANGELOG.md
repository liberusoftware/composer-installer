# Changelog

All notable changes to this package are documented here. This project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.1.0

### Added

- `extra.plugin-modifies-install-path`, so Composer activates the plugin as early as
  possible. Without it Composer can assume packages live in `vendor/` while this
  installer places them in `modules/` and `themes/`, which surfaces as unexplained
  churn in the tracked package directories.
- Collision detection against the working tree, not only the current process. A target
  directory whose `composer.json` names a different package now fails the install
  instead of being silently written over. An absent, unreadable or nameless
  `composer.json` is not treated as evidence of ownership and does not block anything.

  Note for renames: a package that keeps its install directory while changing its
  Composer name now fails until the old directory is removed. That is the intended
  behaviour — an unremoved directory is exactly the state this check exists to catch —
  but it means a rename is `rm -rf` the old target, then install.
- A test suite and a CI workflow. This package gates every install in the fleet, so a
  silent regression here breaks every module and theme at once.

### Changed

- `LiberuInstaller` accepts an optional root directory, defaulting to the current
  working directory as before. Composer resolves install paths relative to the working
  directory, so this only makes the existing behaviour injectable for tests.

### Removed

- `Plugin` no longer implements `Composer\Plugin\Capable`. It declared no capabilities,
  so the interface and its empty `getCapabilities()` were dead weight.
