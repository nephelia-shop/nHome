<?php
namespace fenomeno\nHomeSystem\Commands\Player;

use fenomeno\nHomeSystem\Commands\BaseHomeCommand;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class DelhomeCommand extends BaseHomeCommand {

    protected const HOME_ARGUMENT = "home";

    //faut plus de debug, car ça crashe pas
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $main = $this->getOwningPlugin();
        assert($sender instanceof Player);
        assert($main instanceof Main);

        $homeName = (string)$args[self::HOME_ARGUMENT];
        $home = $main->getManager()->getPlayerHome($sender, $homeName);

        if ($home === null){
            MessagesUtils::sendTo($sender, "messages.noHomeWithArg", ["{HOME}" => $homeName], "§cPas de home $homeName");
            return;
        }

        $main->getManager()->delete($sender, $home);
        MessagesUtils::sendTo($sender, "messages.homeDeleted", ["{HOME}" => $home->getName()]);

    }

    /** @throws */
    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));

        $this->registerArgument(0, new RawStringArgument(self::HOME_ARGUMENT));
    }

    public function getPermission()
    {
        return "nephelia.homes.delhome";
    }
}