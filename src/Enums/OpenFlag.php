<?php

namespace Microscrap\Bindings\Evdev\Enums;

/** Linux open(2) flags this package uses. This package is Linux-only. */
enum OpenFlag: int
{
    case O_RDONLY   = 0;
    case O_NONBLOCK = 0x800;
}
