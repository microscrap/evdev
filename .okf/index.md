---
okf_version: "0.2"
---

# microscrap/evdev Knowledge Bundle

Package knowledge for `microscrap/evdev` (Linux evdev bindings over **ext-posi** + **microscrap/posix**, v0.8.0).
Read this index first; open only the concepts needed for the task.

**Trust rule:** Prefer `status: stable`. Treat `deprecated` as historical only. New agent-written concepts stay `status: draft` until a human verifies them.
**Placement:** This bundle lives at the **package root** only — never under `src/`.
**Links:** Concept cross-links use paths relative to each file.
**Scope:** Document the bindings-only package (`EvdevDevice`, `Ioctl`, data objects, enums, `evdev_*` helpers). Do **not** invent ServiceProviders, Chassis/Core coupling, Fabricate remaps, or gamepad policy here — that is `microscrap/scrapyard-evdev`.
**Dist note:** `.okf/`, `tests/`, and root `AGENTS.md` are `export-ignore` in `.gitattributes` so Composer dist packages do not ship them.

# Orientation

* [Package (0.8)](orientation/package.md) - Composer identity, namespace, surface, where it sits in the evdev stack.

# Architecture

* [Structs and ioctls](architecture/structs-and-ioctls.md) - `input_event` / `input_absinfo` / `input_id` layouts, the `_IOC` formula, the `['data' => $buf]` ioctl convention.

# Traps

* [Input group and non-blocking reads](traps/input-group-and-nonblock.md) - `/dev/input/event*` permissions, why every open is `O_NONBLOCK`, how an unplug shows as `POLLHUP`.

# Log

* [Directory update log](log.md)
