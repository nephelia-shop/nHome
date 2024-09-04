<?php
namespace fenomeno\nHomeSystem\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class PlayerSethomeEvent extends HomeEvent implements Cancellable {
    use CancellableTrait;
}