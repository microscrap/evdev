---
type: Orientation
title: Package (0.8)
description: "microscrap/evdev 0.8.0 — Linux evdev PHP bindings over ext-posi + microscrap/posix; no ServiceProvider, no gamepad policy."
resource: .
tags: [orientation, evdev, microscrap, bindings, 0.8]
generated: { by: "claude-sonnet-5", at: "2026-09-17T00:00:00Z" }
status: draft
sources:
  - id: composer
    resource: composer.json
    title: Package name, namespace, autoload helpers
  - id: readme
    resource: README.md
    title: Package README (surface and requirements)
  - id: agents
    resource: AGENTS.md
    title: Agent rules for this package
  - id: device
    resource: src/EvdevDevice.php
    title: EvdevDevice — the whole surface
---

# What it is

Composer package `microscrap/evdev` at **0.8.0** — PHP helpers, enums, data objects, and `EvdevDevice` over [**php-io-extensions/posi**](https://github.com/php-io-extensions/posi) plus [`microscrap/posix`](https://github.com/microscrap/posix) FD helpers, wrapping the Linux kernel evdev API (`/dev/input/event*`).[^readme][^agents]

| Field | Value |
|-------|-------|
| Name | `microscrap/evdev`[^composer] |
| Version | `0.8.0`[^composer] |
| PHP | `^8.4\|^8.5\|^8.6`[^composer] |
| Namespace | `Microscrap\Bindings\Evdev\` → `src/`[^composer] |
| Require | `ext-posi` `^0.8.0`; `microscrap/posix` `^0.8.0`[^composer] |
| Homepage | Ecosystem docs overview |
| Source | https://github.com/microscrap/evdev |
| Discovery | **None** — no provider / Chassis registration in this package[^agents] |
| Role | Bindings layer only (global helpers + `EvdevDevice` + enums + DTOs)[^agents][^readme] |
| Platform | **Linux-only**; `open()` returns `null` on any other OS[^readme] |

Autoloads `src/Helpers/evdev.php` (each helper guarded with `function_exists`).[^composer]

# What it is not

- Not `php-io-extensions/posi` (the native extension) — this package wraps `ioctl` / `posix_*` and uses `microscrap/posix` for FD I/O.[^readme]
- Not a ServiceProvider package — no Chassis / Core / Machine coupling; no Fabricate remaps.[^agents]
- Not the POSIX FD package — FD open/read/close/poll live in `microscrap/posix`.
- Not gamepad policy — button/axis semantics, connect/disconnect events, and the `Surface\Contracts\HumanInput\Circuits\*` implementation live in `microscrap/scrapyard-evdev`, one layer up.[^agents]

# Where it sits

```
ext-posi (native extension)
    │
microscrap/posix (FD helpers: posix_open/read/close/ppoll, ioctl)
    │
microscrap/evdev  ← this package (EvdevDevice, Ioctl, structs, enums)
    │
microscrap/scrapyard-evdev (gamepad adapter: button/axis policy, connect/disconnect)
    │
venusian-gtk / Surface HumanInput (dock resource, sketch-facing contracts)
```

# Public surface (summary)

| Layer | Location | Role |
|-------|----------|------|
| Helpers | `src/Helpers/evdev.php` | Global `evdev_*` API, one line over `EvdevDevice` |
| Device | `src/EvdevDevice.php` | `open`/`name`/`id`/`supports`/`absInfo`/`revents`/`read`/`close`; not `final` (adapter tests subclass it) |
| Ioctl numbers | `src/Ioctl.php` | `_IOC` encoding for `EVIOCGVERSION`/`GID`/`GNAME`/`GBIT`/`GABS` |
| Data objects | `src/DataObjects/*` | `InputEvent`, `AbsInfo`, `DeviceId` — kernel struct layouts |
| Enums | `src/Enums/*` | `EventType`, `KeyCode`, `AbsCode`, `OpenFlag`, `PollFlag` |
| Extension / peer | `ioctl()`, `posix_open/read/close/ppoll` | Native + FD targets — not reimplemented here |

# Related

| Topic | Concept |
|-------|---------|
| Structs / ioctls | [Structs and ioctls](../architecture/structs-and-ioctls.md) |
| Permissions / non-blocking | [Input group and non-blocking reads](../traps/input-group-and-nonblock.md) |

[^composer]: Package name, namespace, autoload helpers
[^readme]: Package README (surface and requirements)
[^agents]: Agent rules for this package
[^device]: EvdevDevice — the whole surface
