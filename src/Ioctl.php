<?php

namespace Microscrap\Bindings\Evdev;

/**
 * evdev ioctl numbers, encoded the way linux/input.h / asm-generic/ioctl.h
 * does: _IOC(dir, type, nr, size) = dir<<30 | size<<16 | type<<8 | nr.
 * Every evdev read ioctl this package uses is _IOC_READ (2) on type 'E' (0x45).
 */
final class Ioctl
{
    public static function read(int $nr, int $size): int
    {
        return (2 << 30) | ($size << 16) | (0x45 << 8) | $nr;
    }

    public static function gversion(): int
    {
        return self::read(0x01, 4);
    }

    public static function gid(): int
    {
        return self::read(0x02, 8);
    }

    public static function gname(int $len): int
    {
        return self::read(0x06, $len);
    }

    /** EVIOCGKEY: the keys held right now, one bit per EV_KEY code. */
    public static function gkey(int $len): int
    {
        return self::read(0x18, $len);
    }

    public static function gbit(int $event_type, int $len): int
    {
        return self::read(0x20 + $event_type, $len);
    }

    public static function gabs(int $abs_code): int
    {
        return self::read(0x40 + $abs_code, 24);
    }
}
