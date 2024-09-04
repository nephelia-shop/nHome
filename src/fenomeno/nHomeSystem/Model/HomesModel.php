<?php
namespace fenomeno\nHomeSystem\Model;

use Closure;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\poggit\libasynql\DataConnector;
use fenomeno\nHomeSystem\libs\poggit\libasynql\libasynql;
use fenomeno\nHomeSystem\libs\poggit\libasynql\SqlError;
use fenomeno\nHomeSystem\libs\SOFe\AwaitGenerator\Await;
use fenomeno\nHomeSystem\Main;
use Generator;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use fenomeno\nHomeSystem\Sessions\HomeSession;

class HomesModel {

    private DataConnector $database;

    public function __construct(
        protected Main $main
    ) {
        $main->saveDefaultConfig();
        $this->database = libasynql::create($main, $main->getConfig()->get("database"), [
            "sqlite" => "sql/sqlite.sql",
        ]);
        $this->database->executeGeneric(HomeQueries::INIT_PLAYERS_QUERY);
        $this->database->executeGeneric(HomeQueries::INIT_HOMES_QUERY);
    }

    public function loadHomes() : Promise
    {
        $promise = new PromiseResolver();
        $this->database->executeSelect(HomeQueries::GETALL_HOMES_QUERY, [], function (array $rows) use ($promise) {
            if (! empty($rows)){
                $homes = [];
                foreach ($rows as $row){
                    $homes[] = Home::make($row);

                }
                $promise->resolve($homes);
            } else {
                $promise->resolve(null);
            }
        });
        return $promise->getPromise();
    }

    public function close() : void
    {
        if (isset($this->database)){
            $this->database->close();
        }
    }

    public function loadPlayer(Player $player) : Promise
    {
        $promise = new PromiseResolver();
        $this->database->executeSelect(HomeQueries::GET_PLAYER_QUERY, ['name' => $player->getName()], function (array $rows) use ($promise, $player) {
            if (count($rows) === 0) {
                $this->database->executeInsert(HomeQueries::AUTH_PLAYER_QUERY, [
                    "name"  => $player->getName(),
                    "limit" => Main::getInstance()->getHomeConfig()->limit
                ], function ($insertId) use ($promise, $player) {
                    $data = [
                        'id' => $insertId,
                        'home_limit' => Main::getInstance()->getHomeConfig()->limit
                    ];
                    $promise->resolve($data);
                });
            } else {
                $promise->resolve($rows[0]);
            }
        });
        return $promise->getPromise();
    }

    public function updateLimit(HomeSession $session, int $limit, ?callable $onUpdate = null) : void
    {
        Await::f2c(function () use ($limit, $session): Generator{
            yield from $this->database->asyncChange(HomeQueries::UPDATE_LIMIT_QUERY, [
                'id'    => $session->getId(),
                'limit' => $limit,
            ]);
        }, $onUpdate);
    }

    /** @throws */
    public function addHome(Home $home, ?Closure $onSuccess = null, ?Closure $onFailure = null) : void
    {
        $this->database->executeInsert(HomeQueries::ADD_HOME_QUERY, $home->getIterator()->getArrayCopy(), function (int $insertId, int $affectedRows) use ($onSuccess, $home) {
            $home->setId($insertId);
            $onSuccess($home);
        }, function(SqlError $error) use ($onFailure) {
            if ($onFailure){
                $onFailure($error);
            }
        });

    }

    public function remove(Home $home) : void
    {
        Await::f2c(fn() => yield from $this->database->asyncGeneric(HomeQueries::DELETE_HOME_QUERY, ['id' => $home->getId()]));
    }

}