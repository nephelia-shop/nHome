<?php
namespace fenomeno\nHomeSystem\Model;

use fenomeno\nHomeSystem\libs\poggit\libasynql\DataConnector;
use fenomeno\nHomeSystem\libs\poggit\libasynql\libasynql;
use fenomeno\nHomeSystem\Main;

abstract class Model {

    protected DataConnector $database;

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

}