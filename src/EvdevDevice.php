<?php

namespace Microscrap\Bindings\Evdev;

use Microscrap\Bindings\Evdev\DataObjects\AbsInfo;
use Microscrap\Bindings\Evdev\DataObjects\DeviceId;
use Microscrap\Bindings\Evdev\DataObjects\InputEvent;
use Microscrap\Bindings\Evdev\Enums\AbsCode;
use Microscrap\Bindings\Evdev\Enums\EventType;
use Microscrap\Bindings\Evdev\Enums\OpenFlag;
use Microscrap\Bindings\Evdev\Enums\PollFlag;

/**
 * One /dev/input/event* node. Deliberately not final: the gamepad adapter's
 * tests subclass this to script read() / supports() / absInfo() without a
 * kernel.
 *
 * posix_ppoll() (ext-posi's System::ppoll) reports readiness as a ready
 * count (0 idle, >0 ready, <0 on error) — it does not return the revents
 * bitmask. revents() folds that into POLLIN, and a hang-up (posix_read()
 * returning false) latches POLLHUP for every revents() call after it. See
 * .okf/traps/input-group-and-nonblock.md.
 */
class EvdevDevice
{
    private bool $hungUp = false;

    public function __construct(
        public readonly int $fd,
        public readonly string $path,
    ) {}

    public static function open(string $path): ?static
    {
        $fd = posix_open($path, OpenFlag::O_RDONLY->value | OpenFlag::O_NONBLOCK->value, 0);

        return $fd < 0 ? null : new static($fd, $path);
    }

    public function name(): string
    {
        $bytes = $this->fetch(Ioctl::gname(256), 256);

        if (is_null($bytes)) {
            return '';
        }

        $nul = strpos($bytes, "\0");

        return $nul === false ? $bytes : substr($bytes, 0, $nul);
    }

    public function id(): ?DeviceId
    {
        $bytes = $this->fetch(Ioctl::gid(), 8);

        return is_null($bytes) ? null : DeviceId::fromBytes($bytes);
    }

    public function supports(EventType $type, int $code): bool
    {
        if (($code >> 3) >= 96) {
            return false;
        }

        $bits = $this->fetch(Ioctl::gbit($type->value, 96), 96);

        return ! is_null($bits) && (ord($bits[$code >> 3]) & (1 << ($code & 7))) !== 0;
    }

    /** Whether the key is held right now (EVIOCGKEY); false when the kernel does not answer. */
    public function keyDown(int $code): bool
    {
        if (($code >> 3) >= 96) {
            return false;
        }

        $bits = $this->fetch(Ioctl::gkey(96), 96);

        return ! is_null($bits) && (ord($bits[$code >> 3]) & (1 << ($code & 7))) !== 0;
    }

    public function absInfo(AbsCode|int $code): ?AbsInfo
    {
        $abs_code = $code instanceof AbsCode ? $code->value : $code;
        $bytes = $this->fetch(Ioctl::gabs($abs_code), 24);

        return is_null($bytes) ? null : AbsInfo::fromBytes($bytes);
    }

    public function revents(): int
    {
        if ($this->hungUp) {
            return PollFlag::POLLHUP->value;
        }

        $ready = posix_ppoll($this->fd, 0, PollFlag::POLLIN->value);

        return $ready > 0 ? PollFlag::POLLIN->value : 0;
    }

    /** @return list<InputEvent> up to $max_events of what is queued right now, oldest first; [] when idle. Never waits. */
    public function read(int $max_events = 64): array
    {
        if (! ($this->revents() & PollFlag::POLLIN->value)) {
            return [];
        }

        $bytes = posix_read($this->fd, InputEvent::size() * $max_events);

        if ($bytes === false) {
            $this->hungUp = true;

            return [];
        }

        if ($bytes === '') {
            return [];
        }

        return InputEvent::manyFromBytes($bytes);
    }

    public function close(): void
    {
        posix_close($this->fd);
    }

    private function fetch(int $cmd, int $len): ?string
    {
        $buffer = str_repeat("\0", $len);
        $ret = ioctl($this->fd, $cmd, ['data' => $buffer], $buffer);

        return ($ret < 0 || ! is_string($buffer) || strlen($buffer) < $len) ? null : $buffer;
    }
}
