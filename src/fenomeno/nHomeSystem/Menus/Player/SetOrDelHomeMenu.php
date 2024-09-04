<?php
namespace fenomeno\nHomeSystem\Menus\Player;

use fenomeno\nHomeSystem\libs\dktapps\pmforms\CustomForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\CustomFormResponse;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\element\Input;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\player\Player;

class SetOrDelHomeMenu {

    public const SET_OPTION     = "sethome";
    public const DEL_OPTION     = "delhome";
    private const HOME_ARGUMENT = "home";

    public static function send(Player $player, string $option = self::SET_OPTION) : void
    {
        $form = new CustomForm(
            title: MessagesUtils::getMessage('forms.addOrDel.title', 'Homes'),
            elements: [
                new Input(SetOrDelHomeMenu::HOME_ARGUMENT, MessagesUtils::getMessage('forms.addOrDel.input', 'Nom du home'), "base")
            ],
            onSubmit: function(Player $player, CustomFormResponse $data) use ($option): void {
                $homeName = $data->getString(SetOrDelHomeMenu::HOME_ARGUMENT);
                $player->getServer()->getCommandMap()->dispatch($player, "$option $homeName");
            }
        );
        $player->sendForm($form);
    }

}