<?php
namespace fenomeno\nHomeSystem\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;

class PlayerUpdateLimitEvent extends Event implements Cancellable{
    use CancellableTrait;

    public function __construct(
        protected Player $player,
        protected int    $oldLimit,
        private   int    $newLimit,
    )
    {
    }

    public function getNewLimit(): int
    {
        return $this->newLimit;
    }

    public function setNewLimit(int $newLimit): void
    {
        $this->newLimit = $newLimit;
    }

    public function getOldLimit(): int
    {
        return $this->oldLimit;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

}