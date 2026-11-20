# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

This development version migrates the Engine source to Localzet Server 7.1. It has not been published as a stable release.

### Changed
- Requires `localzet/server:^7.1`; the latest published `v8.1.11` package still requires Server 4.
- Upgrades the logging contract to Monolog 3.
- Server-owned global `response()`, `json()`, and `redirect()` helpers now keep the Server 7 contract. Use `triangle_response()`, `triangle_json()`, and `triangle_redirect()` for the Engine behavior.
- Fiber-backed request context is isolated by the active Fiber across Server 7 event loops.

### Fixed
- Prevented Composer autoload from redeclaring Server 7 global helpers.
- Kept Engine JSON responses encoded as strings when Server 7 is loaded first.
- Removed implicit nullable parameter deprecations reported by PHP 8.5.

### Security
- Strip CR and LF from redirect locations created by `triangle_redirect()`.
