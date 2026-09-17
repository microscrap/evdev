<?php

/**
 * EvdevDevice calls posix_ppoll()/posix_read() unqualified from inside
 * `namespace Microscrap\Bindings\Evdev`. PHP resolves an unqualified
 * function call by looking for that name in the *current* namespace first,
 * falling back to the global namespace only if no such function exists
 * there. Declaring same-named functions in that namespace here intercepts
 * every call EvdevDevice makes, in every test in this process, without
 * touching the real global posix_ppoll()/posix_read() microscrap/posix
 * defines (still reachable, and used, via the \-qualified fallback inside
 * EvdevSyscalls when no queue is armed).
 *
 * Loaded once from tests/Pest.php, before any test runs — required for the
 * override to be in place before EvdevDevice's calls are ever resolved in
 * this process.
 */

namespace Microscrap\Bindings\Evdev;

use Microscrap\Bindings\Evdev\Tests\Support\EvdevSyscalls;

function posix_ppoll(int $fd, int $timeout_ns, int $events): int
{
    return EvdevSyscalls::nextPoll($fd, $timeout_ns, $events);
}

function posix_read(int $fd, int $bytes): string|false
{
    return EvdevSyscalls::nextRead($fd, $bytes);
}
