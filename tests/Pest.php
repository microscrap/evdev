<?php

/*
| Struct layout and ioctl numbers are proven here against linux/input.h.
| Byte paths are proven on the Pi with a USB pad.
*/

// Namespace-fallback overrides for posix_ppoll()/posix_read() — must load
// before any EvdevDevice call in this process. See Support/evdev-syscall-overrides.php.
require __DIR__.'/Support/evdev-syscall-overrides.php';
