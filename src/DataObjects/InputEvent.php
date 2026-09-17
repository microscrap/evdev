<?php

namespace Microscrap\Bindings\Evdev\DataObjects;

/**
 * struct input_event (linux/input.h): a timeval (two longs sized to the
 * machine word) followed by __u16 type, __u16 code, __s32 value.
 */
final readonly class InputEvent
{
    public function __construct(
        public int $sec,
        public int $usec,
        public int $type,
        public int $code,
        public int $value,
    ) {}

    public static function size(): int
    {
        return PHP_INT_SIZE === 8 ? 24 : 16;
    }

    public static function fromBytes(string $bytes): self
    {
        $fields = PHP_INT_SIZE === 8
            ? unpack('qsec/qusec/vtype/vcode/lvalue', $bytes)
            : unpack('lsec/lusec/vtype/vcode/lvalue', $bytes);

        return new self($fields['sec'], $fields['usec'], $fields['type'], $fields['code'], $fields['value']);
    }

    /** @return list<self> */
    public static function manyFromBytes(string $bytes): array
    {
        $size = self::size();
        $events = [];

        foreach (str_split($bytes, $size) as $chunk) {
            if (strlen($chunk) === $size) {
                $events[] = self::fromBytes($chunk);
            }
        }

        return $events;
    }
}
