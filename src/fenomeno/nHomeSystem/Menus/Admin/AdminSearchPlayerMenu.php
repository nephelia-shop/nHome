<?php
namespace fenomeno\nHomeSystem\Menus\Admin;

use fenomeno\nHomeSystem\libs\dktapps\pmforms\CustomForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\CustomFormResponse;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\element\Input;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Menus\HomesListMenu;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\player\Player;

class AdminSearchPlayerMenu {

    private const TARGET_ARGUMENT = "target";

    public static function send(Player $player, Main $plugin) : void
    {
        $form = new CustomForm(
            title: "Recherche",
            elements: [
                new Input(AdminSearchPlayerMenu::TARGET_ARGUMENT, "Nom du joueur", $player->getName())
            ],
            onSubmit: function(Player $player, CustomFormResponse $data) use ($plugin) : void {
                $targetName = $data->getString(AdminSearchPlayerMenu::TARGET_ARGUMENT);
                $target = $player->getServer()->getPlayerExact($targetName);

                if ($target === null){
                    MessagesUtils::sendTo($player, 'messages.notFound', ['{TARGET}' => $targetName]);
                    return;
                }
                HomesListMenu::send($player, $plugin, $plugin->getManager()->getPlayerHomes($target), 1, true);
            }
        );
        $player->sendForm($form);
    }

}