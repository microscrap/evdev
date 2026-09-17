<?php

namespace Microscrap\Bindings\Evdev\Enums;

/** EV_ABS axis codes (linux/input-event-codes.h). */
enum AbsCode: int
{
    case X     = 0x00;
    case Y     = 0x01;
    case Z     = 0x02;
    case RX    = 0x03;
    case RY    = 0x04;
    case RZ    = 0x05;
    case HAT0X = 0x10;
    case HAT0Y = 0x11;
}
