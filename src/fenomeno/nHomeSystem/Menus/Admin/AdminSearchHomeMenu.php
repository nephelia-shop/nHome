<?php

namespace fenomeno\nHomeSystem\Menus\Admin;

use fenomeno\nHomeSystem\libs\dktapps\pmforms\CustomForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\CustomFormResponse;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\element\Input;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Menus\HomesListMenu;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\player\Player;

class AdminSearchHomeMenu {

    private const HOME_ARGUMENT = "home";

    public static function send(Player $player, Main $plugin) : void
    {
        $form = new CustomForm(
            title: "Recherche",
            elements: [
                new Input(AdminSearchHomeMenu::HOME_ARGUMENT, "Nom du home", "base")
            ],
            onSubmit: function(Player $player, CustomFormResponse $data) use ($plugin) : void {
                $homeName = $data->getString(AdminSearchHomeMenu::HOME_ARGUMENT);
                $homes = $plugin->getManager()->getHomesByName($homeName);

                if ($homes->isEmpty()){
                    MessagesUtils::sendTo($player, "messages.noHome", [], "no home");
                    return;
                }

                HomesListMenu::send($player, $plugin, $homes, 1, true);
            }
        );
        $player->sendForm($form);
    }

}