<?php
namespace fenomeno\nHomeSystem;

use fenomeno\nHomeSystem\Commands\Admin\AdminHomeCommand;
use fenomeno\nHomeSystem\Commands\Admin\SetHomeLimitCommand;
use fenomeno\nHomeSystem\Commands\Player\DelhomeCommand;
use fenomeno\nHomeSystem\Commands\Player\HomeCommand;
use fenomeno\nHomeSystem\Commands\Player\HomesCommand;
use fenomeno\nHomeSystem\Commands\Player\SethomeCommand;
use fenomeno\nHomeSystem\libs\Pimple\Container;
use fenomeno\nHomeSystem\Manager\HomeManager;
use fenomeno\nHomeSystem\Model\HomesModel;
use fenomeno\nHomeSystem\utils\MessagesUtils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase {
    use SingletonTrait;

    private HomesModel  $homesModel;
    private HomeManager $manager;
    private HomeConfig $homeConfig;
    private array $homeCommands = [];
    private Container $container;

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->saveDefaultConfig();

        $this->homeConfig = new HomeConfig((array)$this->getConfig()->get('settings', HomeConfig::DEFAULT_SETTINGS));
        MessagesUtils::startup($this);
    }

    protected function onEnable(): void
    {
        $this->container = new Container();
        $this->homesModel = new HomesModel($this);
        $this->container[HomesModel::MODEL] = fn($c) => $this->homesModel;

        $this->manager    = new HomeManager($this);

        $this->getServer()->getCommandMap()->registerAll('nepheliashop:homes', $this->homeCommands = [
            new HomeCommand($this, "home"),
            new HomesCommand($this, "homes"),
            new SetHomeLimitCommand($this, "sethomelimit"),
            new DelhomeCommand($this, "delhome"),
            new SethomeCommand($this, "sethome"),
            new AdminHomeCommand($this, "adminhome", "Ouvrir le admin home")
        ]);
    }

    public function getManager(): HomeManager
    {
        return $this->manager;
    }

    /**
     * //TODO utilisation des containers pour plus de model
     *
     * @return HomesModel
     */
    public function getHomesModel(): HomesModel
    {
        return $this->homesModel;
    }

    public function getHomeConfig(): HomeConfig
    {
        return $this->homeConfig;
    }

    /** @return HomeCommand[] */
    public function getHomeCommands(): array
    {
        return $this->homeCommands;
    }

    protected function onDisable(): void
    {
        $this->homesModel->close();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

}