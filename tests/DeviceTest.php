<?php

use Microscrap\Bindings\Evdev\DataObjects\InputEvent;
use Microscrap\Bindings\Evdev\Enums\EventType;
use Microscrap\Bindings\Evdev\Enums\PollFlag;
use Microscrap\Bindings\Evdev\EvdevDevice;
use Microscrap\Bindings\Evdev\Tests\Support\EvdevSyscalls;

beforeEach(fn () => EvdevSyscalls::reset());
afterEach(fn () => EvdevSyscalls::reset());

it('answers null for a device path that does not exist', function (): void {
    expect(EvdevDevice::open('/nonexistent/event99'))->toBeNull();
})->skip(! extension_loaded('posi'), 'needs ext-posi');

it('is idle when ppoll reports nothing queued, and never reads', function (): void {
    EvdevSyscalls::queuePoll(0);

    $device = new EvdevDevice(-1, '/dev/input/eventX');

    expect($device->revents())->toBe(0)
        ->and($device->read())->toBe([])
        ->and(EvdevSyscalls::$readCalls)->toBe(0);
});

it('reads and unpacks queued events when ppoll reports ready', function (): void {
    $bytes = pack('q', 1).pack('q', 2).pack('v', EventType::KEY->value).pack('v', 0x130).pack('l', 1);

    EvdevSyscalls::queuePoll(1);
    EvdevSyscalls::queueRead($bytes);

    $device = new EvdevDevice(-1, '/dev/input/eventX');
    $events = $device->read();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(InputEvent::class)
        ->and($events[0]->code)->toBe(0x130)
        ->and($events[0]->value)->toBe(1);
})->skip(PHP_INT_SIZE !== 8, '64-bit layout');

it('latches POLLHUP once posix_read reports a hang-up, and stays hung up', function (): void {
    // Three poll slots queued: only the first should ever be consumed once
    // the hang-up latches, because revents() must stop calling posix_ppoll
    // at all once hung up. If the latch is removed, the second and third
    // revents() calls below fall through to posix_ppoll again, consume the
    // remaining queued 1s, and answer POLLIN instead of POLLHUP — failing
    // this test.
    EvdevSyscalls::queuePoll(1, 1, 1);
    EvdevSyscalls::queueRead(false);

    $device = new EvdevDevice(-1, '/dev/input/eventX');

    expect($device->read())->toBe([])
        ->and($device->revents())->toBe(PollFlag::POLLHUP->value)
        ->and($device->revents())->toBe(PollFlag::POLLHUP->value)
        ->and($device->read())->toBe([])
        ->and(EvdevSyscalls::$pollCalls)->toBe(1)
        ->and(EvdevSyscalls::$readCalls)->toBe(1);
});

it('is not hung up on an empty read', function (): void {
    EvdevSyscalls::queuePoll(1, 1);
    EvdevSyscalls::queueRead('');

    $device = new EvdevDevice(-1, '/dev/input/eventX');

    expect($device->read())->toBe([])
        ->and($device->revents())->toBe(PollFlag::POLLIN->value);
});

it('reads a live pad without blocking', function (): void {
    $device = EvdevDevice::open('/dev/input/event0');

    expect($device)->not->toBeNull();

    $start = hrtime(true);
    $name = $device->name();
    $events = $device->read();
    $elapsed_ms = (hrtime(true) - $start) / 1_000_000;

    expect($name)->toBeString()->not->toBe('')
        ->and($events)->toBeArray()
        ->and($elapsed_ms)->toBeLessThan(50);

    $device->close();
})->skip(! is_readable('/dev/input/event0'), 'needs /dev/input/event0');
