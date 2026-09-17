---
type: Trap
title: Input group and non-blocking reads
description: "/dev/input/event* is root:input 0660 — the runtime user needs the input group. A blocking read would stall the dock, so every open is O_NONBLOCK and revents()/read() poll instead of block."
resource: src/EvdevDevice.php
tags: [trap, permissions, input-group, nonblock, poll, evdev, linux]
generated: { by: "claude-sonnet-5", at: "2026-09-17T00:00:00Z" }
revised: { by: "claude-opus-5/claude-code", at: "2026-09-17T12:00:00Z", note: "any read failure latches hang-up; ppoll < 0 is idle" }
status: draft
sources:
  - id: device
    resource: src/EvdevDevice.php
    title: open() flags, revents(), read()
  - id: ppoll-c
    resource: ../../php-io-extensions/posi/src/system-api.c
    title: ppoll_file / posix_ppoll — what the extension actually returns
---

# Symptom

`EvdevDevice::open('/dev/input/eventN')` returns `null` even though the node exists, or `read()` never sees a keypress from a device the kernel is clearly delivering events for.

# Cause 1 — group permissions

On typical Linux images `/dev/input/event*` is owned `root:input` mode `0660`. A process whose user is not in the `input` group gets `EACCES` from `open(2)`, surfacing here as `posix_open() < 0` → `EvdevDevice::open()` returns `null`. Add the runtime user to `input` and re-login (or restart the service) for group membership to apply.

# Cause 2 — this package never blocks

`EvdevDevice::open()` always opens `O_RDONLY | O_NONBLOCK`. A dock `tick()` cannot afford to stall on a `read(2)` waiting for the next event, so nothing in this package ever calls a blocking read. `read()` always checks `revents()` first and returns `[]` immediately when nothing is queued — it never waits.[^device] One call returns at most `$max_events` (64); a busy pad (thousands of `EV_ABS`/s) needs repeated calls, or the kernel buffer overflows and delivers `SYN_DROPPED` (`SynCode::DROPPED`): drop events to the next `SYN_REPORT`, then resync from `keyDown()` / `absInfo()->value`.

# Ruling — what `posix_ppoll()` actually returns

The task brief for this package assumed `posix_ppoll($fd, 0, POLLIN)` returns the ready **mask** (`revents`). Verified against the extension source (`php-io-extensions/posi/src/system-api.c`): `ppoll_file()` captures the kernel's `revents` into a local `int *revents` parameter, but the exported `posix_ppoll()` **discards that value** — it returns only `ppoll_file()`'s own result, which is a **ready count**: `<= 0` on timeout/error/idle, `1` when the fd is ready. The revents bitmask itself never crosses the extension boundary into PHP.[^ppoll-c]

Consequence for `EvdevDevice`:[^device]

* `revents()` calls `posix_ppoll($this->fd, 0, PollFlag::POLLIN->value)` and folds the **count** into a mask: `PollFlag::POLLIN` when the count is `> 0`, `0` when idle (`<= 0`, error or nothing queued — this package does not distinguish the two from `ppoll` alone).
* `ppoll < 0` (error) reads as idle, same as `0`: `revents()` answers `0`.
* **Any** `posix_read()` failure latches hang-up — not only `ENODEV` (unplug); the errno is not inspected.
* An unplug (or any hang-up) shows up when `posix_read()` returns `false`. `read()` latches an internal `$hungUp` flag on that `false`; every `revents()` call after that point answers `PollFlag::POLLHUP` instead of re-polling. There is no separate signal for `POLLHUP`/`POLLERR` from `ppoll` itself under this extension's contract — the hang-up is inferred from the failed read, not observed directly on the poll.

# Mitigation / usage rule

* Never add a blocking read path to this package. Every `open()` stays `O_NONBLOCK`; every `read()` goes through `revents()` first.
* Treat `revents() === 0` as "nothing queued right now", not as "device is fine" — the extension does not currently distinguish idle from error at the `ppoll` layer.
* Treat `revents() === PollFlag::POLLHUP->value` as terminal for that `EvdevDevice`: the adapter above this package should close and drop the device rather than keep polling it.

# Related

* [Structs and ioctls](../architecture/structs-and-ioctls.md)
* [Package (0.8)](../orientation/package.md)

[^device]: open() flags, revents(), read()
[^ppoll-c]: ppoll_file / posix_ppoll — what the extension actually returns
