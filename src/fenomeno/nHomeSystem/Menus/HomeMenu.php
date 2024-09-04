<?php
namespace fenomeno\nHomeSystem\Menus;

use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuOption;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\sound\EndermanTeleportSound;

class HomeMenu {

    public static function send(Player $player, Home $home, Main $plugin, bool $admin = false) : void
    {
        $homeArgs = [
            '{HOME}'      => $home->getName(),
            '{HOME_ID}'   => $home->getId(),
            '{PLAYER}'    => $home->getPlayerName(),
            '{PLAYER_ID}' => $home->getPlayerId(),
            '{DATE}'      => $home->getDateTime()->format(Home::DATETIME_SQL_FORMAT)
        ];
        $type = $admin ? "admin" : "player";
        $form = new MenuForm(
            title: $home->getName(),
            text: MessagesUtils::getMessage('forms.home.text.' . $type, "", $homeArgs),
            options: [
                new MenuOption(MessagesUtils::getMessage('forms.home.buttons.teleport', "Se téléporter")),
                new MenuOption(MessagesUtils::getMessage('forms.home.buttons.delete', "Supprimer")),
                new MenuOption(MessagesUtils::getMessage('forms.home.buttons.quit', "Quitter")),
            ],
            onSubmit: function(Player $player, int $selectedOption) use ($admin, $plugin, $home) : void {
                switch ($selectedOption){
                    case 0:
                        try {
                            $home->teleport($player, function (Home $home, Player $player) {
                                MessagesUtils::sendTo($player, "messages.teleported", ["{HOME}" => $home->getName()], "§aTéléporté");
                                if (Main::getInstance()->getHomeConfig()->sound){
                                    $player->broadcastSound(new EndermanTeleportSound());
                                }
                            }, function (Home $home, Player $player) {
                                $player->sendMessage("§cLa téléportation au {$home->getName()} a échoué");
                                //FIXME: ceci ne devrait jamais arriver, peut-être que le home s'est corrompu ?
                            });
                        } catch (AssumptionFailedError){
                            MessagesUtils::sendTo($player, "messages.positionCorrompu");
                        }
                        return;
                    case 1:
                        $plugin->getManager()->delete($home);
                        MessagesUtils::sendTo($player, "messages.homeDeleted", ["{HOME}" => $home->getName()]);
                        break;
                }
            }
        );
        $player->sendForm($form);
    }

}