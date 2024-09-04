<?php

namespace fenomeno\nHomeSystem\Menus\Admin;

use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuOption;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Menus\HomesListMenu;
use pocketmine\player\Player;

class AdminHomeMenu
{

    public static function send(Player $player, Main $plugin) : void
    {
        $form = new MenuForm(
            title: "AdminHome",
            text: "Que voulez-vous faire",
            options: [
                new MenuOption("Liste des homes"),
                new MenuOption("Homes d'un joueur"),
                new MenuOption("Recherche d'un Home"),
                new MenuOption("Quitter")
            ],
            onSubmit: function (Player $player, int $selectedOption) use ($plugin) : void {
                switch ($selectedOption){
                    case 0:
                        HomesListMenu::send($player, $plugin, $plugin->getManager()->getHomes(), 1, true);
                        break;
                    case 1:
                        AdminSearchPlayerMenu::send($player, $plugin);
                        break;
                    case 2:
                        AdminSearchHomeMenu::send($player, $plugin);
                        break;
                }
            }
        );
        $player->sendForm($form);
    }
}