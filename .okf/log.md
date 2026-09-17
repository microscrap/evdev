# Log

## 2026-09-17 (resync)

* **Update**: [Structs and ioctls](architecture/structs-and-ioctls.md) — `gkey()` / `keyDown()` (`EVIOCGKEY`); `SynCode` enum.
* **Update**: [Input group and non-blocking reads](traps/input-group-and-nonblock.md) — `read()` returns at most `$max_events`; `SYN_DROPPED` resync.

## 2026-09-17

* **Fix**: [Structs and ioctls](architecture/structs-and-ioctls.md) — `spi-device` source path is `../spi/src/Device.php`.
* **Update**: [Input group and non-blocking reads](traps/input-group-and-nonblock.md) — any `posix_read()` failure latches hang-up, not only `ENODEV`; `ppoll < 0` reads as idle.
* **Creation**: Initial OKF v0.2 bundle for `microscrap/evdev` **0.8.0** (new package; HumanInput slice, Task 10). Shape mirrored from `microscrap/uart` / `microscrap/spi`.
* **Creation**: Orientation — [Package (0.8)](orientation/package.md).
* **Creation**: Architecture — [Structs and ioctls](architecture/structs-and-ioctls.md).
* **Creation**: Traps — [Input group and non-blocking reads](traps/input-group-and-nonblock.md).
* **Note**: `posix_ppoll()` (ext-posi `System::ppoll`) was assumed by the task brief to return the ready (revents) bitmask. Verified against `php-io-extensions/posi/src/system-api.c` (`ppoll_file` / `posix_ppoll`): the C layer captures the kernel's `revents` into a local variable it never returns to PHP — `posix_ppoll` returns a **ready count** (`<=0` on error/idle, `1` when ready), not a mask. `EvdevDevice::revents()` folds that into `PollFlag::POLLIN` when the count is `>0`, `0` otherwise, and latches `PollFlag::POLLHUP` for every subsequent call once `posix_read()` has returned `false` once. All concepts left `status: draft` pending human verification.
