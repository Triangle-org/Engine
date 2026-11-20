# Triangle Engine

Triangle Engine is the HTTP and application runtime layer used by Triangle Framework projects. It provides request and response objects, configuration and execution context, application bootstrap helpers, and adapters around Localzet Server.

This development source requires **PHP 8.1 or newer** and **Localzet Server compatible with `^7.1`**. It is not a standalone web server: the network listener, event loop, protocol handling, and process lifecycle belong to Localzet Server.

## Requirements

- PHP `^8.1`, `ext-intl`, and `ext-json`.
- `localzet/server:^7.1`.
- Monolog `^3.0` and PSR Container `^1.0` or `^2.0`.
- Symfony Cache and Translation `~6.4.0`.

The latest published package is `v8.1.11` and requires `localzet/server:^4`. This development source targets Server 7.1. The two versions are not interchangeable:

| Engine version | Server requirement | Status |
| --- | --- | --- |
| `v8.1.11` | `^4` | Published on Packagist |
| Development source | `^7.1` | Server 7 migration; not yet a compatible stable release |

Do not install the published `v8.1.11` into an application that uses Server 7.1. Inspect the selected packages before upgrading:

```sh
composer show triangle/engine
composer show localzet/server
```

For the current development source, configure the repository and install its development branch only in a development environment:

```sh
composer config repositories.triangle-engine vcs https://github.com/Triangle-org/Engine
composer require triangle/engine:dev-main@dev
```

When the compatible major release is published, use its stable version constraint for production.

## Main components

- `Triangle\Engine\Config` loads and reads application configuration.
- `Triangle\Engine\Context` stores values for the active Fiber or coroutine; call `Context::destroy()` when a request or task finishes.
- `Triangle\Engine\Request` and `Triangle\Engine\Response` adapt Localzet Server HTTP messages for Triangle applications.
- `support\App` assembles configured listeners and processes for a Triangle application.
- `Triangle\Exception` contains the framework exception types and rendering contracts.

Use the application bootstrap already provided by your Triangle project. Engine does not create a listener or choose a deployment topology on its own.

## Compatibility with Server 7.1

Server 7 defines global `response()`, `json()`, and `redirect()` helpers. Their names overlap with older Engine helpers but their return values and behavior differ. Engine now exposes explicit names for its own behavior:

| Helper | Engine behavior | With Server 7.1 loaded |
| --- | --- | --- |
| `triangle_response()` | Engine response envelope and content negotiation | Use this to retain Engine behavior |
| `triangle_json()` | Encodes a PHP value as a JSON string | Use this when a string is needed |
| `triangle_redirect()` | Returns `Triangle\Engine\Response` and strips CR/LF from the location | Use this when Engine's response type is required |
| `responseJson()` / `responseBlob()` | Build an Engine response | Keep using these helpers |

The unprefixed names resolve to Server's helpers when Server is loaded first. This source guards its optional legacy declarations so Composer autoload does not redeclare those functions. Update direct application calls to `response()`, `json()`, or `redirect()` when they relied on Engine-specific semantics.

### Migrating Engine 8 helper calls

| Engine 8 call | Engine 9 replacement | Return contract |
| --- | --- | --- |
| `json($data)` | `triangle_json($data)` | JSON string |
| `response($data, $status)` | `triangle_response($data, $status)` | Engine response envelope and content negotiation |
| `redirect($url, $status)` | `triangle_redirect($url, $status)` | `Triangle\Engine\Response` |

If the application needs a JSON HTTP response without the Engine envelope, use `responseJson($data, $status)`. Calls without the `triangle_` prefix now follow the Server 7 contract, so update the call site according to whether it needs a string, an Engine response, or a Server response.

The current `Context` implementation keys storage by the active PHP Fiber regardless of the selected event-loop class. This matters when asynchronous handlers interleave on Server 7's standard event loops.

## Tests

Install the development dependencies and run the declared PHPUnit suite:

```sh
composer install
composer test
```

The suite covers configuration, Fiber context isolation, Server 7 helper interoperability, and a real loopback HTTP request through Server 7 using the Engine request and response classes. It does not replace application-level tests for routing, middleware, storage, or deployment behavior.

## Documentation and support

- [Project source](https://github.com/Triangle-org/Engine)
- [Report a security issue](SECURITY.md)
- [Contribution guide](CONTRIBUTING.md)

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved.
