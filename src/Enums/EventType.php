<?php

namespace Microscrap\Bindings\Evdev\Enums;

/** struct input_event type field (linux/input-event-codes.h). */
enum EventType: int
{
    case SYN = 0x00;
    case KEY = 0x01;
    case REL = 0x02;
    case ABS = 0x03;
    case MSC = 0x04;
    case SW  = 0x05;
    case LED = 0x11;
    case SND = 0x12;
    case REP = 0x14;
    case FF  = 0x15;
}
