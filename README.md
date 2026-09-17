# microscrap/evdev — Linux evdev (input event) bindings for ScrapyardIO

[![License: MIT](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Requires ext-posi](https://img.shields.io/badge/ext--posi-%5E0.8-777bb4?logo=php&logoColor=white)](https://github.com/php-io-extensions/posi)

PHP library that reads Linux `/dev/input/event*` nodes (the kernel evdev API) over the [**posi**](https://github.com/php-io-extensions/posi) extension plus [`microscrap/posix`](https://github.com/microscrap/posix) FD helpers. Bindings only: no policy, no button/axis semantics, no connect/disconnect events — that layer is `microscrap/scrapyard-evdev`.

This package is **Linux-only**. It cannot open `/dev/input` on macOS or any other platform; opening a device there simply returns `null`.

## Highlights

* Open a device non-blocking (`O_RDONLY | O_NONBLOCK`) — `read()` never waits
* `name()` / `id()` / `supports()` / `keyDown()` / `absInfo()` wrap the `EVIOCG*` ioctl family
* `read()` returns up to `$max_events` queued events and `[]` when idle; call it again while it comes back full
* Global `evdev_*` helper API — every function is `function_exists`-guarded
* `EvdevDevice` is not `final`: subclass it in tests to script behaviour without a kernel

## Requirements

* PHP `^8.4|^8.5|^8.6`
* Linux kernel with `/dev/input/event*` nodes
* **ext-posi** `^0.8.0` — install from [php-io-extensions/posi](https://github.com/php-io-extensions/posi)
* **microscrap/posix** `^0.8.0`

## Installation

Confirm **ext-posi** is loaded:

```bash
php -m | grep posi
```

```bash
composer require microscrap/evdev:^0.8.0
```

Composer also pulls **`microscrap/posix` `^0.8.0`**. Autoloads `src/Helpers/evdev.php`, registering the global `evdev_*` functions.

## Usage

```php
<?php

use Microscrap\Bindings\Evdev\Enums\EventType;
use Microscrap\Bindings\Evdev\Enums\KeyCode;

$device = evdev_open('/dev/input/event3');
if (is_null($device)) {
    exit("Failed to open device\n");
}

echo evdev_name($device) . "\n";

if (evdev_supports($device, EventType::KEY, KeyCode::BTN_SOUTH->value)) {
    echo "has a south button\n";
}

// Never blocks: returns [] when nothing is queued.
foreach (evdev_read($device) as $event) {
    printf("type=%d code=%d value=%d\n", $event->type, $event->code, $event->value);
}

evdev_close($device);
```

## API reference

| Helper | `EvdevDevice` method | Description |
|---|---|---|
| `evdev_open(string $path)` | `EvdevDevice::open` | Open non-blocking; returns `?EvdevDevice` |
| `evdev_name(EvdevDevice $d)` | `$d->name()` | `EVIOCGNAME`, trimmed at the first NUL; `''` on failure |
| `evdev_id(EvdevDevice $d)` | `$d->id()` | `EVIOCGID`; returns `?DeviceId` |
| `evdev_supports(EvdevDevice $d, EventType $t, int $code)` | `$d->supports()` | `EVIOCGBIT` bit test |
| `evdev_key_down(EvdevDevice $d, int $code)` | `$d->keyDown()` | `EVIOCGKEY` bit test: held right now (resync after `SYN_DROPPED`) |
| `evdev_absinfo(EvdevDevice $d, AbsCode\|int $c)` | `$d->absInfo()` | `EVIOCGABS`; returns `?AbsInfo` |
| `evdev_revents(EvdevDevice $d)` | `$d->revents()` | Ready mask (`PollFlag`), `0` when idle |
| `evdev_read(EvdevDevice $d, int $max = 64)` | `$d->read()` | Up to `$max` queued events; never waits |
| `evdev_close(EvdevDevice $d)` | `$d->close()` | Closes the file descriptor |

### Data objects

```php
final readonly class DataObjects\InputEvent { public int $sec, $usec, $type, $code, $value; }  // struct input_event
final readonly class DataObjects\AbsInfo    { public int $value, $minimum, $maximum, $fuzz, $flat, $resolution; }  // struct input_absinfo
final readonly class DataObjects\DeviceId   { public int $bustype, $vendor, $product, $version; }  // struct input_id
```

### Enums

`Enums\EventType` (`SYN KEY REL ABS MSC SW LED SND REP FF`), `Enums\KeyCode` (gamepad `BTN_*`), `Enums\AbsCode` (`X Y Z RX RY RZ HAT0X HAT0Y`), `Enums\OpenFlag` (`O_RDONLY O_NONBLOCK`), `Enums\PollFlag` (`POLLIN POLLERR POLLHUP`) — all int-backed, cases FULLY UPPERCASE.

## Notes

* `/dev/input/event*` is typically `root:input 0660` — the running user needs `input` group membership to open one. See [`.okf/traps/input-group-and-nonblock.md`](.okf/traps/input-group-and-nonblock.md).
* A blocking read would stall a dock tick, so every open is non-blocking and `read()` always polls first.
* Struct layout and ioctl numbers are proven against `linux/input.h` in this repo's tests; byte paths against real hardware are proven on the Pi with a USB pad.

## License

MIT. See [LICENSE](LICENSE).
