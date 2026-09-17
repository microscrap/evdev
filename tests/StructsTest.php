<?php

use Microscrap\Bindings\Evdev\DataObjects\AbsInfo;
use Microscrap\Bindings\Evdev\DataObjects\DeviceId;
use Microscrap\Bindings\Evdev\DataObjects\InputEvent;
use Microscrap\Bindings\Evdev\Enums\EventType;
use Microscrap\Bindings\Evdev\Enums\KeyCode;

/** One input_event as a 64-bit kernel writes it. */
function eventBytes(int $type, int $code, int $value, int $sec = 1, int $usec = 2): string
{
    return pack('q', $sec).pack('q', $usec).pack('v', $type).pack('v', $code).pack('l', $value);
}

it('sizes input_event by the machine word', function (): void {
    expect(InputEvent::size())->toBe(PHP_INT_SIZE === 8 ? 24 : 16);
});

it('unpacks one event, signed value and all', function (): void {
    $event = InputEvent::fromBytes(eventBytes(EventType::ABS->value, 0x01, -32768, 17, 250_000));

    expect([$event->sec, $event->usec, $event->type, $event->code, $event->value])->toBe([17, 250_000, 3, 1, -32768]);
})->skip(PHP_INT_SIZE !== 8, '64-bit layout');

it('splits a read of several events and drops a torn tail', function (): void {
    $bytes = eventBytes(EventType::KEY->value, KeyCode::BTN_SOUTH->value, 1).eventBytes(EventType::SYN->value, 0, 0).'torn';

    $events = InputEvent::manyFromBytes($bytes);

    expect($events)->toHaveCount(2)->and($events[0]->code)->toBe(0x130)->and($events[1]->type)->toBe(0);
})->skip(PHP_INT_SIZE !== 8, '64-bit layout');

it('unpacks input_absinfo and input_id', function (): void {
    $abs = AbsInfo::fromBytes(pack('l6', 128, 0, 255, 0, 15, 0));
    $id = DeviceId::fromBytes(pack('v4', 0x03, 0x045E, 0x028E, 0x0114));

    expect([$abs->value, $abs->minimum, $abs->maximum, $abs->flat])->toBe([128, 0, 255, 15])
        ->and([$id->bustype, $id->vendor, $id->product, $id->version])->toBe([3, 0x045E, 0x028E, 0x0114]);
});
