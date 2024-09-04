<?php
namespace fenomeno\nHomeSystem\Commands\Player;

use fenomeno\nHomeSystem\Commands\BaseHomeCommand;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Sessions\HomeSession;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class HomesCommand extends BaseHomeCommand {

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);
        $session = HomeSession::get($sender);

        $homes = Main::getInstance()->getManager()->getPlayerHomes($sender);

        if ($homes->isEmpty()){
            MessagesUtils::sendTo($sender, "messages.noHome", [
                "{COUNT}" => 0,
                "{LIMIT}" => $session->getLimit()
            ]);
            return;
        }

        MessagesUtils::sendTo($sender, "messages.homesList", [
            "{COUNT}" => $homes->count(),
            "{LIMIT}" => $session->getLimit(),
            "{HOMES}" => implode(", ", $homes->map(fn(Home $home) => $home->getName())->toArray())
        ]);
    }

    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function getPermission() : string
    {
        return "nephelia.homes.homes";
    }
}