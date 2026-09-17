<?php

use Microscrap\Bindings\Evdev\DataObjects\AbsInfo;
use Microscrap\Bindings\Evdev\DataObjects\DeviceId;
use Microscrap\Bindings\Evdev\DataObjects\InputEvent;
use Microscrap\Bindings\Evdev\Enums\AbsCode;
use Microscrap\Bindings\Evdev\Enums\EventType;
use Microscrap\Bindings\Evdev\EvdevDevice;

if (! function_exists('evdev_open')) {
    function evdev_open(string $path): ?EvdevDevice
    {
        return EvdevDevice::open($path);
    }
}

if (! function_exists('evdev_name')) {
    function evdev_name(EvdevDevice $device): string
    {
        return $device->name();
    }
}

if (! function_exists('evdev_id')) {
    function evdev_id(EvdevDevice $device): ?DeviceId
    {
        return $device->id();
    }
}

if (! function_exists('evdev_supports')) {
    function evdev_supports(EvdevDevice $device, EventType $type, int $code): bool
    {
        return $device->supports($type, $code);
    }
}

if (! function_exists('evdev_key_down')) {
    function evdev_key_down(EvdevDevice $device, int $code): bool
    {
        return $device->keyDown($code);
    }
}

if (! function_exists('evdev_absinfo')) {
    function evdev_absinfo(EvdevDevice $device, AbsCode|int $code): ?AbsInfo
    {
        return $device->absInfo($code);
    }
}

if (! function_exists('evdev_revents')) {
    function evdev_revents(EvdevDevice $device): int
    {
        return $device->revents();
    }
}

if (! function_exists('evdev_read')) {
    /** @return list<InputEvent> */
    function evdev_read(EvdevDevice $device, int $max_events = 64): array
    {
        return $device->read($max_events);
    }
}

if (! function_exists('evdev_close')) {
    function evdev_close(EvdevDevice $device): void
    {
        $device->close();
    }
}
