<?php
namespace fenomeno\nHomeSystem\Events;

use fenomeno\nHomeSystem\Entity\Home;
use pocketmine\event\Event;
use pocketmine\player\Player;

//C'est mieux de mettre le player, en soi, chaque event est dû au joueur ?
abstract class HomeEvent extends Event {

    public function __construct(
        protected Home   $home,
        protected Player $player
    ){ /* TODO */}

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getHome(): Home
    {
        return $this->home;
    }

}