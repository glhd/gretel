# Changelog

All notable changes will be documented in this file following the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) 
format. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.9.1] - 2025-03-10

## [1.9.0] - 2025-03-04

## [1.8.0] - 2024-03-12

## [1.7.0] - 2023-02-17

### Added

-   Added Laravel 10 support

## [1.6.1] - 2023-01-06

### Fixed

-   Resource routes now respect global names set via `ResourceRegistrar::setParameters()`
-   Resource routes now respect the `ResourceRegistrar::singularParameters()` config
-   Resource routes now account for setting `parameters` to `"singular"` on a case-by-case basis
-   Resource routes now correctly handle resource names that are hyphenated

## [1.6.0] - 2022-06-30

### Added

-   Added the option to register breadcrumbs manually with `Gretel::breadcrumb()`

### Fixed

-   Fixed issue where resource routes didn't work when [nested in route groups with parameters (#7)](https://github.com/glhd/gretel/issues/7)

## [1.5.0] - 2022-02-11

### Added

-   Added support for Laravel 9.0

## [1.4.0]

### Added

-   Added support for `Route::resource()`

## [1.3.0]

### Added

-   Added support for Inertia.js

### [1.2.0]

### Added

-   Added `Gretel` facade
-   Added options for handling missing or mis-configured breadcrumbs (see README)
-   Added additional exceptions for more granular handling

### Fixed

-   Calling `Collection` methods on `RequestBreadcrumbs` now automatically populates the collection first

## [1.1.0]

### Changed

-   Updated the internal `Resolver` API
-   Improved parent resolver so that you can refer to routes that haven't been defined yet

## [1.0.0]

### Fixed

-   Added `forwardDecoratedCallTo` for better backwards-compatibility

## [0.1.1]

### Fixed

-   Enabled package autoloading

## [0.1.0]

### Fixed

-   Now actually loads cached breadcrumbs on boot :)

## [0.0.2]

### Added

-   Added support for most popular CSS frameworks
-   Introduced `Breadcrumb` and `BreadcrumbCollection` to make UI work easier
-   Added helpers to blade component for easier custom themes and better consistency across views

### Changed

-   Upgraded to PHPUnit 9.5
-   Improved route-bound breadcrumb structure

## [0.0.1]

### Added

-   Initial release

# Keep a Changelog Syntax

-   `Added` for new features.
-   `Changed` for changes in existing functionality.
-   `Deprecated` for soon-to-be removed features.
-   `Removed` for now removed features.
-   `Fixed` for any bug fixes. 
-   `Security` in case of vulnerabilities.

[Unreleased]: https://github.com/glhd/gretel/compare/1.9.1...HEAD

[1.9.1]: https://github.com/glhd/gretel/compare/1.9.0...1.9.1

[1.9.0]: https://github.com/glhd/gretel/compare/1.8.0...1.9.0

[1.8.0]: https://github.com/glhd/gretel/compare/1.7.0...1.8.0

[1.7.0]: https://github.com/glhd/gretel/compare/1.6.1...1.7.0

[1.6.1]: https://github.com/glhd/gretel/compare/1.6.0...1.6.1

[1.6.0]: https://github.com/glhd/gretel/compare/1.5.0...1.6.0

[1.5.0]: https://github.com/glhd/gretel/compare/1.4.0...1.5.0

[1.4.0]: https://github.com/glhd/gretel/compare/1.3.0...1.4.0

[1.3.0]: https://github.com/glhd/gretel/compare/1.2.0...1.3.0

[1.2.0]: https://github.com/glhd/gretel/compare/1.1.0...1.2.0

[1.1.0]: https://github.com/glhd/gretel/compare/1.0.0...1.1.0

[1.0.0]: https://github.com/glhd/gretel/compare/0.1.1...1.0.0

[0.1.1]: https://github.com/glhd/gretel/compare/0.1.0...0.1.1

[0.1.0]: https://github.com/glhd/gretel/compare/0.0.2...0.1.0

[0.0.2]: https://github.com/glhd/gretel/compare/0.0.1...0.0.2

[0.0.1]: https://github.com/glhd/gretel/releases/tag/0.0.1
