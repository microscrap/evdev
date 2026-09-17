<?php

namespace Microscrap\Bindings\Evdev\Enums;

/** poll(2) event/revent bits this package uses. */
enum PollFlag: int
{
    case POLLIN  = 0x01;
    case POLLERR = 0x08;
    case POLLHUP = 0x10;
}
