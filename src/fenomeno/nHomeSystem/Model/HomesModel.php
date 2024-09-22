<?php
namespace fenomeno\nHomeSystem\Model;

use Closure;
use Exception;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\poggit\libasynql\SqlError;
use fenomeno\nHomeSystem\libs\SOFe\AwaitGenerator\Await;
use fenomeno\nHomeSystem\Main;
use Generator;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use fenomeno\nHomeSystem\Sessions\HomeSession;

class HomesModel extends Model {

    public const MODEL = 'home_model';

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

    public function remove(Home $home, ?Closure $onSuccess = null, ?Closure $onError = null) : void
    {
        Await::f2c(function () use ($onError, $onSuccess, $home) {
            try {
                $result = yield from $this->database->asyncGeneric(HomeQueries::DELETE_HOME_QUERY, ['id' => $home->getId()]);

                if($onSuccess){
                    $onSuccess($result);
                }
            } catch (Exception $e){
                if($onError){
                    $onError($e);
                }
            }
        });
    }

    /**
     * @param Home $home
     * @param Closure|null $onSuccess
     * @param Closure|null $onFailure
     * @return void
     * @throws Exception
     */
    public function addHome(Home $home, ?Closure $onSuccess = null, ?Closure $onFailure = null) : void
    {
        $this->database->executeInsert(HomeQueries::ADD_HOME_QUERY, $home->getIterator()->getArrayCopy(), function (int $insertId) use ($onSuccess, $home) {
            $home->setId($insertId);
            $onSuccess($home);
        }, function(SqlError $error) use ($onFailure) {
            if ($onFailure){
                $onFailure($error);
            }
        });

    }

    public function updateLimit(HomeSession $session, int $limit, ?Closure $onUpdate = null, ?Closure $onFail = null) : void
    {
        Await::f2c(function () use ($onUpdate, $onFail, $limit, $session): Generator{
            try {
                $result = yield from $this->database->asyncChange(HomeQueries::UPDATE_LIMIT_QUERY, [
                    'id'    => $session->getId(),
                    'limit' => $limit,
                ]);
                if ($onUpdate){
                    $onUpdate($result);
                }
            } catch (Exception $e){
                if($onFail){
                    $onFail($e);
                }
            }
        }, $onUpdate);
    }

}