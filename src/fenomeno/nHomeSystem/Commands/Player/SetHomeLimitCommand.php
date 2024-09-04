<?php
namespace fenomeno\nHomeSystem\Commands\Player;

use fenomeno\nHomeSystem\Commands\BaseHomeCommand;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\args\IntegerArgument;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\nHomeSystem\Sessions\HomeSession;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\command\CommandSender;

class SetHomeLimitCommand extends BaseHomeCommand {

    private const TARGET_ARGUMENT = "target";
    private const LIMIT_ARGUMENT = "limit";

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $targetName = (string)$args[self::TARGET_ARGUMENT];
        $limit      = (int)$args[self::LIMIT_ARGUMENT];

        $target = $sender->getServer()->getPlayerExact($targetName);
        if ($target === null){
            MessagesUtils::sendTo($sender, 'messages.notFound', ['{TARGET}' => $targetName]);
            return;
        }

        $session = HomeSession::get($target);
        $session->setLimit($limit, function () use ($limit, $target, $sender) {
            MessagesUtils::sendTo($sender, "messages.targetLimitUpdated", [
                '{TARGET}' => $target->getName(),
                '{LIMIT}'  => $limit
            ]);
            MessagesUtils::sendTo($target, "messages.limitUpdatedNotice", [
                '{LIMIT}'  => $limit
            ]);
        });

    }

    /** @throws */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument(self::TARGET_ARGUMENT));
        $this->registerArgument(1, new IntegerArgument(self::LIMIT_ARGUMENT));
    }

    public function getPermission() : string
    {
        return "nephelia.homes.sethomelimit";
    }
}