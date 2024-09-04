<?php
namespace fenomeno\nHomeSystem\utils;

use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\world\World;

class PartiallyLoadedLocation extends Location
{
    private string $worldName = "";

    public function setWorldName(string $worldName): void { $this->worldName = $worldName; }
    public function getWorldName(): string { return $this->worldName; }

    public function load(): bool
    {
        if($this->world instanceof World && $this->world->isLoaded()) return true;
        if(Server::getInstance()->getWorldManager()->loadWorld($this->worldName))
        {
            $this->world = Server::getInstance()->getWorldManager()->getWorldByName($this->worldName);
            return true;
        }
        $this->world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        return false;
    }

    public static function fromLocation(Location $location): PartiallyLoadedLocation
    {
        $instance = new PartiallyLoadedLocation($location->x, $location->y, $location->z, $location->world, $location->yaw, $location->pitch);
        if($location->getWorld() instanceof World) $instance->setWorldName($location->getWorld()->getFolderName());
        return $instance;
    }
}