<?php

use Microscrap\Bindings\Evdev\Enums\AbsCode;
use Microscrap\Bindings\Evdev\Enums\EventType;
use Microscrap\Bindings\Evdev\Ioctl;

it('encodes the evdev ioctls the way linux/input.h does', function (): void {
    expect(Ioctl::gversion())->toBe(0x80044501)
        ->and(Ioctl::gid())->toBe(0x80084502)
        ->and(Ioctl::gname(256))->toBe(0x81004506)
        ->and(Ioctl::gbit(EventType::KEY->value, 96))->toBe(0x80604521)
        ->and(Ioctl::gbit(EventType::SYN->value, 4))->toBe(0x80044520)
        ->and(Ioctl::gkey(96))->toBe(0x80604518)
        ->and(Ioctl::gabs(AbsCode::X->value))->toBe(0x80184540)
        ->and(Ioctl::gabs(AbsCode::HAT0Y->value))->toBe(0x80184551);
});
