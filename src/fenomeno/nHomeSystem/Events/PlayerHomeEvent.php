<?php
namespace fenomeno\nHomeSystem\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class PlayerHomeEvent extends HomeEvent implements Cancellable {
    use CancellableTrait;

}