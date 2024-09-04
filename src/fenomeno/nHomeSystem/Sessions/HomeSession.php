<?php
namespace fenomeno\nHomeSystem\Sessions;

use fenomeno\nHomeSystem\Main;
use pocketmine\player\Player;
use WeakMap;

class HomeSession {
    private static WeakMap $map;

    public static function get(Player $player) : HomeSession
    {
        if (! isset(self::$map)){
            self::$map = new WeakMap();
        }

        return self::$map[$player] ??= self::loadSessionData($player);
    }

    private static function loadSessionData(Player $player) : HomeSession
    {
        return (new self())->loadData($player);
    }

    private function loadData(Player $player) : HomeSession
    {
        Main::getInstance()->getHomesModel()->loadPlayer($player)->onCompletion(function (array $data) use ($player) {
            //load après l'authentification
            $this->id = (int)$data['id'];
            $this->limit = (int)$data['home_limit'];
        }, fn() => $player->kick("§cVotre connexion est instable, veuillez vous reconnecter"));

        return $this;
    }


    protected int $id    = -1;
    private   int $limit;
    //private Collection $homes;

    public function __construct()
    {
        //faut build un orm pour libasynql ou pmmp
        //$this->homes = new Collection(Home::class);
        $this->limit = Main::getInstance()->getHomeConfig()->limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit, ?callable $onUpdate = null): HomeSession
    {
        $this->limit = $limit; // pas besoin de check du cache
        Main::getInstance()->getHomesModel()->updateLimit($this, $limit, $onUpdate);

        return $this;
    }

    public function getId() : int
    {
        return $this->id;
    }

}