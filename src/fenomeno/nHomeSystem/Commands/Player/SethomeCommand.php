<?php
namespace fenomeno\nHomeSystem\Commands\Player;

use Exception;
use fenomeno\nHomeSystem\Commands\BaseHomeCommand;
use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\Exceptions\HomeAlreadyExistsException;
use fenomeno\nHomeSystem\Exceptions\HomeLimitException;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\nHomeSystem\libs\poggit\libasynql\SqlError;
use fenomeno\nHomeSystem\Main;
use fenomeno\nHomeSystem\Sessions\HomeSession;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SethomeCommand extends BaseHomeCommand {

    private const HOME_ARGUMENT = "home";

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);

        $session = HomeSession::get($sender);
        $homeName = (string)$args[self::HOME_ARGUMENT];
        $count = Main::getInstance()->getManager()->getPlayerHomes($sender)->count();

        if ($count + 1 > $session->getLimit()){
            MessagesUtils::sendTo($sender, "messages.limitWarning", [], "§cVous avez atteint la limite");
            return;
        }

        try {
            Main::getInstance()->getManager()->setHome($sender, $homeName, function (Home $home) use ($sender) {
                MessagesUtils::sendTo($sender, "messages.sethome", [
                    "{HOME}" => $home->getName()
                ]);
            }, function (SqlError $error) use ($homeName) {
                $errorMessage = "Erreur lors de la création du home '$homeName': " . PHP_EOL
                    . "Code: {$error->getCode()}" . PHP_EOL
                    . "Message: {$error->getMessage()}" . PHP_EOL
                    . "SQL Query: {$error->getQuery()}" . PHP_EOL
                    . "Trace: {$error->getTraceAsString()}";
                $this->getOwningPlugin()->getLogger()->error($errorMessage);
            });
        } catch (HomeLimitException){
            MessagesUtils::sendTo($sender, "messages.limitWarning", [], "§cVous avez atteint la limite");
        } catch(HomeAlreadyExistsException){
            MessagesUtils::sendTo($sender, "messages.homeAlreadyExists", [], "§cLe home existe déjà");
        } catch (Exception $e) {
            $sender->sendMessage("§cErreur lors du sethome : " . $e->getMessage());
        }

    }

    /** @throws */
    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0, new RawStringArgument(self::HOME_ARGUMENT));
    }

    public function getPermission() : string
    {
        return "nephelia.homes.sethome";
    }
}