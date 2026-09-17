<?php

namespace Microscrap\Bindings\Evdev\DataObjects;

/** struct input_id (linux/input.h): 4 x __u16. */
final readonly class DeviceId
{
    public function __construct(
        public int $bustype,
        public int $vendor,
        public int $product,
        public int $version,
    ) {}

    public static function fromBytes(string $bytes): self
    {
        $fields = unpack('vbustype/vvendor/vproduct/vversion', $bytes);

        return new self($fields['bustype'], $fields['vendor'], $fields['product'], $fields['version']);
    }
}
