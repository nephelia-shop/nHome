<?php
namespace fenomeno\nHomeSystem\Manager;

use Closure;
use DateTime;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\Events\PlayerDelhomeEvent;
use fenomeno\nHomeSystem\Events\PlayerSethomeEvent;
use fenomeno\nHomeSystem\Exceptions\HomeAlreadyExistsException;
use fenomeno\nHomeSystem\Exceptions\HomeLimitException;
use fenomeno\nHomeSystem\Exceptions\HomeNotExistsException;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Model\HomesModel;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\player\Player;
use fenomeno\nHomeSystem\Sessions\HomeSession;
use pocketmine\scheduler\ClosureTask;
use Ramsey\Collection\Collection;
use Ramsey\Collection\CollectionInterface;

class HomeManager {

    private Collection $homes;
    private HomesModel $model;

    public function __construct(Main $main){
        $this->homes = new Collection(Home::class);
        $container   = $main->getContainer();
        $this->model = $container[HomesModel::MODEL];
        $this->model->loadHomes()->onCompletion(
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
     * Ajouter un home au joueur
     *
     * @param Player $player
     * @param string $homeName
     * @param Closure|null $onSuccess
     * @param Closure|null $onFailure
     * @return void
     * @throws HomeAlreadyExistsException
     * @throws HomeLimitException
     * @throws \Exception
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

        $ev = new PlayerSethomeEvent($home, $player);
        $ev->call();
        if (!$ev->isCancelled()){
            $this->homes->add($home);
            $this->model->addHome($home, function (Home $home) use ($onSuccess) {
                if($onSuccess){
                    $onSuccess($home);
                }
            }, $onFailure);
        }
    }

    /**
     * Avoir une liste de home avec pagination
     *
     * @param CollectionInterface $homes
     * @param int $homesPerPage
     * @param int $pageNum
     * @return CollectionInterface
     */
    public function getHomesByPage(CollectionInterface $homes, int $homesPerPage = 10, int $pageNum = 1): CollectionInterface
    {
        $homesArray = $homes->toArray();

        $offset = ($pageNum - 1) * $homesPerPage;

        $homesArrayPage = array_slice($homesArray, $offset, $homesPerPage);

        return new Collection(Home::class, $homesArrayPage);
    }

    /**
     * Avoir la liste des homes d'un joueur
     *
     * @param Player $player
     * @return CollectionInterface
     */
    public function getPlayerHomes(Player $player): CollectionInterface
    {
        return $this->homes->filter(fn(Home $home) => $home->getPlayerId() == HomeSession::get($player)->getId());
    }

    /**
     * Avoir un home d'un joueur avec un nom
     *
     * @param Player $player
     * @param string $homeName
     * @return Home|null
     */
    public function getPlayerHome(Player $player, string $homeName) : ?Home
    {
        foreach ($this->homes->filter(fn(Home $home) => $home->getPlayerId() == HomeSession::get($player)->getId()) as $home){
            if ($home->getName() === strtolower($homeName)){
                return $home;
            }
        }
        return null;
    }

    /**
     * Avoir un home avec id
     * @warning SENSIBLE
     *
     * @param int $id
     * @return Home
     * @throws HomeNotExistsException
     */
    public function getHome(int $id) : Home
    {
        /** La collection implements ArrayAccess */
        if (! isset($this->homes[$id])){
            throw new HomeNotExistsException();
        }

        return $this->homes[$id];
    }

    /**
     * Supprimer un home d'un joueur
     *
     * @param Player $player
     * @param Home $home
     * @param Closure|null $onSuccess
     * @param Closure|null $onError
     * @return void
     */
    public function delete(Player $player, Home $home, ?Closure $onSuccess = null, ?Closure $onError = null) : void
    {
        $ev = new PlayerDelhomeEvent($home, $player);
        $ev->call();
        if(!$ev->isCancelled()){
            $this->homes->remove($home);
            $this->model->remove($home, $onSuccess, $onError);
        }
    }

    /**
     * Avoir la liste de tous les homes en jeu
     *
     * @return Collection
     */
    public function getHomes(): Collection
    {
        return $this->homes;
    }

    /**
     * Avoir une liste de home avec le même nom
     *
     * @param string $homeName
     * @return CollectionInterface
     */
    public function getHomesByName(string $homeName): CollectionInterface
    {
        return $this->homes->filter(fn(Home $home) => strtolower($home->getName()) === strtolower($homeName));
    }

}