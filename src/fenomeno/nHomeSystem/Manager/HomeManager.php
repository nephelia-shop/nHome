<?php
namespace fenomeno\nHomeSystem\Manager;

use Closure;
use DateTime;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\Exceptions\HomeAlreadyExistsException;
use fenomeno\nHomeSystem\Exceptions\HomeLimitException;
use fenomeno\nHomeSystem\Main;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\player\Player;
use fenomeno\nHomeSystem\Sessions\HomeSession;
use pocketmine\scheduler\ClosureTask;
use Ramsey\Collection\Collection;
use Ramsey\Collection\CollectionInterface;

class HomeManager {

    private Collection $homes;

    public function __construct(
        protected Main $main
    ){
        $this->homes = new Collection(Home::class);
        Main::getInstance()->getHomesModel()->loadHomes()->onCompletion(
            /** @var Home[]|null $homes */
            function (?array $homes) {
                if ($homes){
                    foreach ($homes as $home){
                        $this->homes->add($home);
                    }
                }
            }, static function() : void { /* Pas grand-chose à faire si le home n'a pas été load, peut-être une question de loc */ }
        );
        $main->getScheduler()->scheduleTask(new ClosureTask(fn() => $main->getServer()->getPluginManager()->registerEvent(PlayerLoginEvent::class, fn(PlayerLoginEvent $event) => HomeSession::get($event->getPlayer()), EventPriority::MONITOR, $main)));
    }

    /**
     * @param Player $player
     * @param string $homeName
     * @param Closure|null $onSuccess
     * @param Closure|null $onFailure
     * @return void
     * @throws HomeAlreadyExistsException
     * @throws HomeLimitException
     */
    public function setHome(Player $player, string $homeName, ?Closure $onSuccess = null, ?Closure $onFailure = null) : void
    {
        if ($this->getPlayerHome($player, $homeName) !== null){
            throw new HomeAlreadyExistsException();
        }

        if ($this->getPlayerHomes($player)->count() + 1 > HomeSession::get($player)->getLimit()){
            throw new HomeLimitException();
        }

        $home = (new Home())
            ->setName($homeName)
            ->setLocation($player->getLocation())
            ->setPlayerName($player->getName())
            ->setPlayerId(HomeSession::get($player)->getId())
            ->setDateTime(new DateTime('now'));

        $this->main->getHomesModel()->addHome($home, function (Home $home) use ($onSuccess) {
            $this->homes->add($home);
            if($onSuccess){
                $onSuccess($home);
            }
        }, $onFailure);
    }

    public function getHomesByPage(CollectionInterface $homes, int $homesPerPage = 10, int $pageNum = 1): CollectionInterface
    {
        $homesArray = $homes->toArray();

        $offset = ($pageNum - 1) * $homesPerPage;

        $homesArrayPage = array_slice($homesArray, $offset, $homesPerPage);

        return new Collection(Home::class, $homesArrayPage);
    }

    public function getPlayerHomes(Player $player): CollectionInterface
    {
        return $this->homes->filter(fn(Home $home) => $home->getPlayerId() == HomeSession::get($player)->getId());
    }

    public function getPlayerHome(Player $player, string $homeName) : ?Home
    {
        foreach ($this->homes->filter(fn(Home $home) => $home->getPlayerId() == HomeSession::get($player)->getId()) as $home){
            if ($home->getName() === strtolower($homeName)){
                return $home;
            }
        }
        return null;
    }

    public function delete(Home $home) : void
    {
        $this->homes->remove($home);
        $this->main->getHomesModel()->remove($home);
    }

    public function getHomes(): Collection
    {
        return $this->homes;
    }

    public function getHomesByName(string $homeName): CollectionInterface
    {
        return $this->homes->filter(fn(Home $home) => strtolower($home->getName()) === strtolower($homeName));
    }

}