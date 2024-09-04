<?php

namespace fenomeno\nHomeSystem\Menus\Player;

use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuOption;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Menus\HomesListMenu;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\player\Player;

class DefaultHomeMenu {

    public static function send(Player $player, Main $main): void
    {
        $form = new MenuForm(
            title: MessagesUtils::getMessage('forms.default.title', "Homes"),
            text: MessagesUtils::getMessage('forms.default.text', "Que voulez-vous faire"),
            options: array_map(fn(string $name) => new MenuOption(MessagesUtils::getMessage('forms.default.buttons.'.$name, $name)), ['homes', 'sethome', 'delhome', 'quit']),
            onSubmit: function(Player $player, int $selectedOption) use ($main): void {
                if ($selectedOption === 0){
                    HomesListMenu::send($player, $main, $main->getManager()->getPlayerHomes($player));
                    return;
                }
                SetOrDelHomeMenu::send($player, $selectedOption === 1 ? SetOrDelHomeMenu::SET_OPTION : SetOrDelHomeMenu::DEL_OPTION);
            }
        );
        $player->sendForm($form);
    }
}