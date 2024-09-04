<?php
namespace fenomeno\nHomeSystem\Entity;

use Closure;
use fenomeno\nHomeSystem\Events\PlayerHomeEvent;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use fenomeno\nHomeSystem\utils\PartiallyLoadedLocation;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\sound\EndermanTeleportSound;

class Home implements \IteratorAggregate {

    public const DATETIME_SQL_FORMAT = "Y-m-d H:i:s";

    private int       $id;
    private string    $name;
    private Location  $location;
    private int       $player_id;
    private string    $player_name;
    private \DateTime $dateTime;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Home
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): Home
    {
        $this->name = $name;

        return $this;
    }

    public function setLocation(Location $location): Home
    {
        $this->location = $location;

        return $this;
    }

    public function setDateTime(\DateTime $dateTime): Home
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function setPlayerId(int $player_id): Home
    {
        $this->player_id = $player_id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getPlayerId(): int
    {
        return $this->player_id;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function getPlayerName(): string
    {
        return $this->player_name;
    }

    public function setPlayerName(string $player_name): Home
    {
        $this->player_name = $player_name;

        return $this;
    }

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator([
            'name'        => $this->name,
            'x'           => $this->location->x,
            'y'           => $this->location->y,
            'z'           => $this->location->z,
            'world'       => $this->location->world->getFolderName(),
            'yaw'         => $this->location->yaw,
            'pitch'       => $this->location->pitch,
            'player_name' => $this->player_name,
            'player_id'   =>   $this->player_id,
        ]);
    }

    public static function make(array $data) : Home
    {
        $loc = new PartiallyLoadedLocation(
            (float)$data['x'], (float)$data['y'], (float)$data['z'], null, (float)$data['yaw'], (float)$data['pitch']
        );
        $loc->setWorldName((string)$data['world']);
        $loc->load();
        return (new Home())
            ->setId((int)$data['id'])
            ->setName((string)$data['name'])
            ->setLocation($loc)
            ->setPlayerName((string)$data['player_name'])
            ->setPlayerId((int)$data['player_id'])
            ->setDateTime(\DateTime::createFromFormat(Home::DATETIME_SQL_FORMAT, $data['date']));
    }

    public function teleport(Player $player, Closure $onTeleport, Closure $onFail) : void
    {
        $ev = new PlayerHomeEvent($this, $player);
        $ev->call();
        if ($ev->isCancelled()) {
            $onFail($this, $player);
            return;
        }
        try {
            $player->teleport($this->location);
            MessagesUtils::sendTo($player, "messages.teleported", ["{HOME}" => $this->getName()], "§aTéléporté");
            if (Main::getInstance()->getHomeConfig()->sound){
                $player->broadcastSound(new EndermanTeleportSound());
            }
            $onTeleport($this, $player);
        } catch (\Exception $e) {
            //NOTE: Si le monde déconne
            $player->sendMessage("§cTéléportation annulée: " . $e->getMessage());
            $ev->cancel();
        }
    }

}