<?php
namespace fenomeno\nHomeSystem\Commands\Arguments;

use fenomeno\nHomeSystem\Entity\Home;
use fenomeno\nHomeSystem\libs\CortexPE\Commando\args\StringEnumArgument;
use fenomeno\nHomeSystem\Main;
use pocketmine\command\CommandSender;

//TODO à faire
class HomeArgument extends StringEnumArgument {

    public function parse(string $argument, CommandSender $sender): string
    {
        return $argument;
    }

    public function getTypeName(): string
    {
        return "home";
    }

    public function getValue(string $string): ?Home
    {
        return Main::getInstance()->getManager()->getHomesByName($string)->first() ?? null;
    }

    public function getEnumValues(): array
    {
        return Main::getInstance()->getManager()->getHomes()->map(fn(Home $home) => $home->getName())->toArray();
    }

    public function getEnumName(): string
    {
        return "home";
    }
}