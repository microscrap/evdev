<?php

namespace Microscrap\Bindings\Evdev\DataObjects;

/** struct input_absinfo (linux/input.h): 6 x __s32. */
final readonly class AbsInfo
{
    public function __construct(
        public int $value,
        public int $minimum,
        public int $maximum,
        public int $fuzz,
        public int $flat,
        public int $resolution,
    ) {}

    public static function fromBytes(string $bytes): self
    {
        $fields = unpack('lvalue/lminimum/lmaximum/lfuzz/lflat/lresolution', $bytes);

        return new self(
            $fields['value'],
            $fields['minimum'],
            $fields['maximum'],
            $fields['fuzz'],
            $fields['flat'],
            $fields['resolution'],
        );
    }
}
