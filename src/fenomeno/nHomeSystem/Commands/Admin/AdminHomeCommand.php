<?php
namespace fenomeno\nHomeSystem\Commands\Admin;

use fenomeno\nHomeSystem\Commands\BaseHomeCommand;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Menus\Admin\AdminHomeMenu;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class AdminHomeCommand extends BaseHomeCommand {

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);
        $plugin = $this->getOwningPlugin();
        assert($plugin instanceof Main);
        AdminHomeMenu::send($sender, $plugin);
    }

    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function getPermission() : string
    {
        return "nephelia.homes.adminhome";
    }
}