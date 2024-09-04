<?php
namespace fenomeno\nHomeSystem\Menus;

use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuForm;
use fenomeno\nHomeSystem\libs\dktapps\pmforms\MenuOption;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\player\Player;
use Ramsey\Collection\CollectionInterface;

class HomesListMenu {


    public static function send(Player $player, Main $plugin, CollectionInterface $homes, int $page = 1, bool $admin = false) : void
    {
        $homesArray = $plugin->getManager()->getHomesByPage($homes, 10, $page)->toArray();
        $form = new MenuForm(
            title: MessagesUtils::getMessage('forms.list.title', "Liste"),
            text:  MessagesUtils::getMessage('forms.list.text', "Que voulez-vous faire"),
            options: array_merge(array_map(function (Home $home){
                return new MenuOption(MessagesUtils::getMessage('forms.list.buttons.home', "{$home->getName()}\n{$home->getPlayerName()}", [
                    '{HOME}'   => $home->getName(),
                    '{PLAYER}' => $home->getPlayerName()
                ]));
            }, $homesArray), array_map(fn(string $name) => new MenuOption(MessagesUtils::getMessage('forms.list.buttons.'.$name, $name)), ['nextPage'])),
            onSubmit: function (Player $player, int $selectedOption) use ($admin, $homes, $page, $homesArray, $plugin) : void {
                if (isset($homesArray[$selectedOption])){
                    $home = $homesArray[$selectedOption];
                    HomeMenu::send($player, $home, $plugin, $admin);
                } else {
                    HomesListMenu::send($player, $plugin, $homes, $page + 1, $admin);
                }
            }
        );
        $player->sendForm($form);
    }
}