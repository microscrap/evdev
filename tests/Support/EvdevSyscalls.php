<?php

namespace Microscrap\Bindings\Evdev\Tests\Support;

/**
 * Queue-driven stand-in for the real posix_ppoll() / posix_read() the
 * process wires up (see Support/evdev-syscall-overrides.php). A test arms
 * a queue with queuePoll() / queueRead(); EvdevDevice built with a bogus fd
 * (never open()) then drives its hang-up state machine against whatever the
 * queue hands back, no kernel required.
 *
 * Unarmed (queue never set, or drained back to null via reset()), calls fall
 * through to the real global posix_ppoll() / posix_read() — so a device
 * built via EvdevDevice::open() against real hardware (the live block in
 * DeviceTest.php) is unaffected by this file being loaded.
 */
final class EvdevSyscalls
{
    /** @var list<int>|null */
    private static ?array $pollQueue = null;

    /** @var list<string|false>|null */
    private static ?array $readQueue = null;

    public static int $pollCalls = 0;

    public static int $readCalls = 0;

    public static function queuePoll(int ...$counts): void
    {
        self::$pollQueue = $counts;
    }

    public static function queueRead(string|false ...$reads): void
    {
        self::$readQueue = $reads;
    }

    public static function reset(): void
    {
        self::$pollQueue = null;
        self::$readQueue = null;
        self::$pollCalls = 0;
        self::$readCalls = 0;
    }

    public static function nextPoll(int $fd, int $timeout_ns, int $events): int
    {
        self::$pollCalls++;

        if (is_null(self::$pollQueue)) {
            return \posix_ppoll($fd, $timeout_ns, $events);
        }

        return array_shift(self::$pollQueue) ?? 0;
    }

    public static function nextRead(int $fd, int $bytes): string|false
    {
        self::$readCalls++;

        if (is_null(self::$readQueue)) {
            return \posix_read($fd, $bytes);
        }

        return array_key_exists(0, self::$readQueue) ? array_shift(self::$readQueue) : false;
    }
}
