---
type: Architecture
title: Structs and ioctls
description: "input_event / input_absinfo / input_id byte layouts, the _IOC ioctl formula, and the ['data' => $buf] read-buffer convention EvdevDevice::fetch() uses."
resource: src/
tags: [architecture, bindings, evdev, ioctl, structs]
generated: { by: "claude-sonnet-5", at: "2026-09-17T00:00:00Z" }
revised: { by: "claude-opus-5/claude-code", at: "2026-09-17T12:00:00Z", note: "spi source path" }
status: draft
sources:
  - id: ioctl
    resource: src/Ioctl.php
    title: _IOC encoding for evdev ioctls
  - id: input-event
    resource: src/DataObjects/InputEvent.php
    title: struct input_event layout, 64/32-bit
  - id: abs-info
    resource: src/DataObjects/AbsInfo.php
    title: struct input_absinfo layout
  - id: device-id
    resource: src/DataObjects/DeviceId.php
    title: struct input_id layout
  - id: device
    resource: src/EvdevDevice.php
    title: fetch() — the ioctl read-buffer convention
  - id: spi-device
    resource: ../spi/src/Device.php
    title: spiIoctlReadU32 — same ['data' => $buf] convention in a peer package
---

# The `_IOC` formula

`linux/input.h` ioctls are encoded by `asm-generic/ioctl.h`:

```
_IOC(dir, type, nr, size) = dir<<30 | size<<16 | type<<8 | nr
```

Every evdev ioctl `Ioctl` builds is a **read** (`_IOC_READ = 2`) on type `'E'` (`0x45`). `Ioctl::read(nr, size)` is that formula fixed to `dir=2, type=0x45`:[^ioctl]

```php
return (2 << 30) | ($size << 16) | (0x45 << 8) | $nr;
```

| Method | `nr` | `size` | Encodes |
|---|---|---|---|
| `gversion()` | `0x01` | `4` | `EVIOCGVERSION` |
| `gid()` | `0x02` | `8` | `EVIOCGID` |
| `gname($len)` | `0x06` | `$len` | `EVIOCGNAME($len)` |
| `gkey($len)` | `0x18` | `$len` | `EVIOCGKEY($len)` — held-key bitmap; `keyDown()` |
| `gbit($t, $len)` | `0x20 + $t` | `$len` | `EVIOCGBIT($t, $len)` |
| `gabs($c)` | `0x40 + $c` | `24` | `EVIOCGABS($c)` (`struct input_absinfo`, 6 × `s32`) |

Numbers are proven in `tests/IoctlTest.php` against hand-computed hex from `linux/input.h`.[^ioctl]

# The ioctl read-buffer convention

`ioctl()` here is the `microscrap/posix` global (`Posi\System::ioctl` under the hood): `ioctl(int $fd, int $cmd, mixed $arg, mixed &$value): int`. A **read** ioctl passes the buffer both as the `$arg` payload and as the by-ref `$value` it gets overwritten into:

```php
$buffer = str_repeat("\0", $len);
$ret = ioctl($fd, $cmd, ['data' => $buffer], $buffer);
```

Same convention as `microscrap/spi`'s `Device::spiIoctlReadU32`.[^spi-device] `EvdevDevice::fetch()` is the one private helper every read ioctl (`name`, `id`, `supports`, `absInfo`) goes through:[^device]

```php
private function fetch(int $cmd, int $len): ?string
{
    $buffer = str_repeat("\0", $len);
    $ret = ioctl($this->fd, $cmd, ['data' => $buffer], $buffer);
    return ($ret < 0 || ! is_string($buffer) || strlen($buffer) < $len) ? null : $buffer;
}
```

`EVIOCGNAME` returns the string length on success (not `0`), so `fetch()` tests `$ret < 0` — not `!== 0` the way `spi`'s byte/u32 reads do.[^device]

# Struct layouts

## `struct input_event` (`InputEvent`)

Machine-word-sized timeval, then `__u16 type`, `__u16 code`, `__s32 value`:[^input-event]

| Machine word | Layout | `size()` |
|---|---|---|
| 64-bit | `q sec / q usec / v type / v code / l value` | 24 bytes |
| 32-bit | `l sec / l usec / v type / v code / l value` | 16 bytes |

`InputEvent::manyFromBytes()` splits a raw read by `size()` and drops any trailing chunk shorter than one full event (a torn read at the end of a buffer).[^input-event]

## `struct input_absinfo` (`AbsInfo`)

6 × `__s32`: `value, minimum, maximum, fuzz, flat, resolution`. Unpacked with `unpack('l6', ...)`-equivalent named fields — all signed 32-bit.[^abs-info]

## `struct input_id` (`DeviceId`)

4 × `__u16`: `bustype, vendor, product, version`. Unpacked with `unpack('v4', ...)`-equivalent named fields — all unsigned 16-bit.[^device-id]

# Related

* [Package (0.8)](../orientation/package.md)
* [Input group and non-blocking reads](../traps/input-group-and-nonblock.md)

[^ioctl]: _IOC encoding for evdev ioctls
[^input-event]: struct input_event layout, 64/32-bit
[^abs-info]: struct input_absinfo layout
[^device-id]: struct input_id layout
[^device]: fetch() — the ioctl read-buffer convention
[^spi-device]: spiIoctlReadU32 — same ['data' => $buf] convention in a peer package
