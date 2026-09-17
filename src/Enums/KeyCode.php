<?php

namespace Microscrap\Bindings\Evdev\Enums;

/**
 * Gamepad-relevant EV_KEY codes (linux/input-event-codes.h).
 * BTN_GAMEPAD is an alias of BTN_SOUTH in the kernel header; an enum cannot
 * repeat a backing value, so callers test BTN_SOUTH for that case.
 */
enum KeyCode: int
{
    case BTN_JOYSTICK   = 0x120;
    case BTN_SOUTH      = 0x130;
    case BTN_EAST       = 0x131;
    case BTN_C          = 0x132;
    case BTN_NORTH      = 0x133;
    case BTN_WEST       = 0x134;
    case BTN_Z          = 0x135;
    case BTN_TL         = 0x136;
    case BTN_TR         = 0x137;
    case BTN_TL2        = 0x138;
    case BTN_TR2        = 0x139;
    case BTN_SELECT     = 0x13A;
    case BTN_START      = 0x13B;
    case BTN_MODE       = 0x13C;
    case BTN_THUMBL     = 0x13D;
    case BTN_THUMBR     = 0x13E;
    case BTN_DPAD_UP    = 0x220;
    case BTN_DPAD_DOWN  = 0x221;
    case BTN_DPAD_LEFT  = 0x222;
    case BTN_DPAD_RIGHT = 0x223;
}
