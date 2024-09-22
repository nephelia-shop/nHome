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
        return (new self($player))->loadData();
    }

    /**
     * //TODO ENLEVER SINGLETON
     *
     * @return HomeSession
     */
    private function loadData() : HomeSession
    {
        Main::getInstance()->getHomesModel()->loadPlayer($this->player)->onCompletion(function (array $data) {
            $this->id = (int)$data['id'];
            $this->limit = (int)$data['home_limit'];
        }, fn() => $this->player->kick("§cVotre connexion est instable, veuillez vous reconnecter"));

        return $this;
    }


    protected int $id    = -1;
    private   int $limit;
    //private Collection $homes;

    public function __construct(
        protected Player $player
    ){
        //faut build un orm pour libasynql ou pmmp
        //$this->homes = new Collection(Home::class);
        $this->limit = Main::getInstance()->getHomeConfig()->limit;
    }

    public function getLimit(): int
    {
        if (! $this->player->isConnected()){
            return $this->limit;
        }

        $currentLimit = $this->limit;
        foreach (Main::getInstance()->getHomeConfig()->permissionsLimit as $perm => $limit){
            if ($this->player->hasPermission($perm) && $currentLimit < $limit){
                $currentLimit = $limit;
            } else {
                break;
            }
        }

        return $currentLimit;
    }

    public function setLimit(int $limit, ?\Closure $onUpdate = null): HomeSession
    {
        Main::getInstance()->getHomesModel()->updateLimit($this, $limit, $onUpdate);
        $this->limit = $limit; // pas besoin de check du cache

        return $this;
    }

    public function getId() : int
    {
        return $this->id;
    }

}