<?php
namespace fenomeno\nHomeSystem\Commands\subCommands;

use fenomeno\nHomeSystem\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\nHomeSystem\Main;
use pocketmine\command\CommandSender;

class HomeHelpCommand extends BaseSubCommand {

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($this->parent->getName() === "home"){
            $main = $this->getOwningPlugin();
            assert($main instanceof Main);
            $commands = $main->getHomeCommands();
            foreach ($commands as $command){
                if(! $sender->hasPermission($command->getPermission())){
                    continue;
                }
                $sender->sendMessage("§7{$command->getUsage()} : §f{$command->getDescription()}");
            }
        } else {
            $sender->sendMessage("§7{$this->parent->getUsage()} : §f{$this->parent->getDescription()}");
        }
        $sender->sendMessage("§aNEPHELIASHOP");
    }

    protected function prepare(): void
    {

    }

}