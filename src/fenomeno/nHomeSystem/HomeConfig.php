<?php
namespace fenomeno\nHomeSystem;

/**
 * @property-read bool $sound
 * @property-read int  $limit
 */
class HomeConfig {

    public const DEFAULT_SETTINGS = [
        'sound'         => true,
        'default-limit' => 5
    ];

    private array $config;

    public function __construct(array $settings)
    {
        $this->config = [
            'sound' => (bool)($settings['sound'] ?? true),
            'limit' => (int)($settings['default-limit'] ?? 5)
        ];
    }

    public function __get(string $name)
    {
        return $this->config[$name] ?? null;
    }

}