<?php

namespace MDeloughry\GcmGen;

use function Termwind\render;

/**
 * Manages configuration for the Git Commit Message Generator
 */
class ConfigManager
{
    protected string $configPath;
    protected array $config;

    public function __construct()
    {
        $this->configPath = $this->getConfigPath();
        if (!file_exists($this->configPath)) {
            $this->createConfig();
        }
        $this->loadConfig();
    }

    /**
     * Get the current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Display current configuration
     */
    public function showConfig(): void
    {
        render(<<<HTML
            <div>
                <div>
                    <span class="text-black bg-yellow-300 px-1 font-bold">Config Path:</span>
                    <span class="text-blue">{$this->configPath}</span>
                </div>
            </div>
        HTML);
        render(<<<'HTML'
            <div class="mt-1 text-black bg-yellow-300 px-1 font-bold">Current Configuration:</div>
        HTML);
        print_r($this->config);
    }

    protected function getConfigPath(): string
    {
        $homedir = getenv('HOME') ?: $_SERVER['HOME'];
        return $homedir . '/.config/gcm-gen.config.php';
    }

    protected function loadConfig(): void
    {
        $this->config = require_once $this->configPath;
    }

    protected function createConfig(): void
    {
        // ... [Keep existing createConfig implementation] ...
    }

    public function editConfig(): void
    {
        $configPath = $this->getConfigPath();
        $editor = getenv('EDITOR') ?: 'nano';

        // Ensure we're running in an interactive terminal
        passthru("$editor $configPath", $returnCode);

        if ($returnCode !== 0) {
            render("<div class='text-red'>Failed to open editor. Please make sure your EDITOR environment variable is set correctly.</div>");
        }
    }
}
