<?php
namespace fenomeno\nHomeSystem\Commands\Player;

use fenomeno\nHomeSystem\Commands\BaseHomeCommand;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Menus\Player\DefaultHomeMenu;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\sound\EndermanTeleportSound;

class HomeCommand extends BaseHomeCommand {

    protected const HOME_ARGUMENT = "home";

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $main = $this->getOwningPlugin();
        assert($sender instanceof Player);
        assert($main instanceof Main);

        if(isset($args[self::HOME_ARGUMENT])){
            $homeName = (string)$args[self::HOME_ARGUMENT];
            $home = $main->getManager()->getPlayerHome($sender, $homeName);

            if ($home === null){
                MessagesUtils::sendTo($sender, "messages.noHomeWithArg", ["{HOME}" => $homeName], "§cPas de home $homeName");
                return;
            }

            try {
                $home->teleport($sender, function (Home $home, Player $player) {
                    MessagesUtils::sendTo($player, "messages.teleported", ["{HOME}" => $home->getName()], "§aTéléporté");
                    if (Main::getInstance()->getHomeConfig()->sound){
                        $player->broadcastSound(new EndermanTeleportSound());
                    }
                }, function (Home $home, Player $player){
                    $player->sendMessage("§cLa téléportation au {$home->getName()} a échoué");
                    //FIXME: ceci ne devrait jamais arriver, peut-être que le home s'est corrompu ?
                });
            } catch (AssumptionFailedError){
                MessagesUtils::sendTo($sender, "messages.positionCorrompu");
            }
            return;
        }

        DefaultHomeMenu::send($sender, $main);
    }

    /** @throws */
    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));

        $this->registerArgument(0, new RawStringArgument(self::HOME_ARGUMENT, true));
    }

    public function getPermission() : string {
        return "nephelia.homes.home";
    }
}