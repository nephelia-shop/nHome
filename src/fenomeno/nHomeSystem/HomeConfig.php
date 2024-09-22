<?php
namespace fenomeno\nHomeSystem;

/**
 * TODO : passer en statique
 *
 * @property-read bool   $sound
 * @property-read int    $limit
 * @property-read array  $permissionsLimit
 */
class HomeConfig {
    
    private const SOUND             = 'sound';
    private const PERMISSIONS_LIMIT = 'permissionsLimit';
    private const DEFAULT_LIMIT     = 'default-limit';

    public const DEFAULT_SETTINGS = [
        'sound'            => true,
        'default-limit'    => 5,
        'permissionsLimit' => []
    ];

    private array $config;

    public function __construct(array $settings)
    {
        $this->config = [
            'sound'            => (bool)($settings[HomeConfig::SOUND] ?? HomeConfig::DEFAULT_SETTINGS[HomeConfig::SOUND]),
            'limit'            => (int)($settings[HomeConfig::DEFAULT_LIMIT] ?? HomeConfig::DEFAULT_SETTINGS[HomeConfig::DEFAULT_LIMIT]),
            'permissionsLimit' => (array)($settings[HomeConfig::PERMISSIONS_LIMIT] ?? HomeConfig::DEFAULT_SETTINGS[HomeConfig::PERMISSIONS_LIMIT])
        ];
    }

    public function __get(string $name)
    {
        return $this->config[$name] ?? null;
    }

}