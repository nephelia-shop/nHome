<?php
namespace fenomeno\nHomeSystem\Commands;

use fenomeno\nHomeSystem\Commands\subCommands\HomeHelpCommand;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\BaseCommand;
use fenomeno\nHomeSystem\Main;
use pocketmine\lang\Translatable;

abstract class BaseHomeCommand extends BaseCommand {

    public function __construct(Main $plugin, string $name, Translatable|string $description = "", array $aliases = [])
    {
        parent::__construct(
            $plugin,
            $name,
            (string)$plugin->getConfig()->getNested('commands.'.$name.'.description', $description),
            (array)$plugin->getConfig()->getNested('commands.'.$name.'.aliases', $aliases),
        );
        $this->setUsage((string)$plugin->getConfig()->getNested('commands.'.$name.'.usage', ""));
        $this->setPermission($this->getPermission());
        $this->registerSubCommand(new HomeHelpCommand("help", "En savoir plus sur la commande {$this->getName()}"));
    }

}