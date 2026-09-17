# AGENTS.md — microscrap/evdev

**Always read `.okf/index.md` first** before changing this package. Open only the concepts needed for the task; prefer `status: stable` when present. When you learn a durable package fact, update `.okf/` and append `.okf/log.md`.

## Role

Bindings-only Composer package over **ext-posi** + **microscrap/posix** for Linux evdev (`/dev/input/event*`). Global helpers, enums, data objects, and `EvdevDevice`. No ServiceProvider, no Chassis/Core coupling, and no gamepad policy — button/axis semantics, connect/disconnect events, and the IC-style contract live in `microscrap/scrapyard-evdev`, one layer up.

## Rules

* Helpers call `EvdevDevice` only — do not invent parallel APIs.
* Enums in `src/Enums/*` are int-backed with **FULLY UPPERCASE** cases.
* Prefer `is_null($var)` over `$var === null`.
* No class-level constants; no Fabricate remaps in this package.
* Never block: every open is `O_RDONLY | O_NONBLOCK`; `read()` always polls first via `revents()`.

## Quick OKF map

| Need | Concept |
|------|---------|
| Identity / scope | `.okf/orientation/package.md` |
| Structs, ioctl formula, buffer convention | `.okf/architecture/structs-and-ioctls.md` |
| Group permissions, non-blocking reads, POLLHUP | `.okf/traps/input-group-and-nonblock.md` |
