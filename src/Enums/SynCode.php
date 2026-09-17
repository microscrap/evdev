<?php

namespace Microscrap\Bindings\Evdev\Enums;

/** EV_SYN codes (linux/input-event-codes.h). */
enum SynCode: int
{
    case REPORT    = 0;
    case CONFIG    = 1;
    case MT_REPORT = 2;
    case DROPPED   = 3;
}
