<?php

namespace MDeloughry\GcmGen;

use function Termwind\render;

/**
 * Manages configuration for the Git Commit Message Generator
 */
class ConfigManager
{
    protected string $configPath;
    protected string $promptPath;
    protected array $config = [];

    public function __construct()
    {
        $this->configPath = $_SERVER['HOME'] . '/.config/gcm-gen/config.json';
        $this->promptPath = $_SERVER['HOME'] . '/.config/gcm-gen/prompt.md';
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
     * Get the current prompt
     */
    public function getPrompt(): string
    {
        return trim($this->config['prompt']);
    }

    /**
     * Display current configuration
     */
    public function showConfig(): void
    {
        render(<<<HTML
            <div>
                <table>
                    <tr>
                        <td class="text-black bg-yellow-300 px-1 font-bold pr-2">Config Path:</td>
                        <td class="text-blue">{$this->configPath}</td>
                    </tr>
                    <tr>
                        <td class="text-black bg-yellow-300 px-1 font-bold pr-2">Prompt Path:</td>
                        <td class="text-blue">{$this->promptPath}</td>
                    </tr>
                </table>
            </div>
        HTML);
        render(<<<'HTML'
            <div class="mt-1 text-black bg-yellow-300 px-1 font-bold">Current Configuration:</div>
        HTML);
        echo json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function loadConfig(): void
    {
        $this->ensureConfigDirectory();

        if (!file_exists($this->configPath)) {
            $this->createConfig();
        }

        if (!file_exists($this->promptPath)) {
            $this->createPrompt();
        }

        $jsonContent = file_get_contents($this->configPath);
        if ($jsonContent === false) {
            render("<div class='text-red'>Error: Failed to read config file</div>");
            exit(1);
        }

        $config = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
            render("<div class='text-red'>Error: Invalid config format</div>");
            exit(1);
        }

        $promptContent = file_get_contents($this->promptPath);
        if ($promptContent === false) {
            render("<div class='text-red'>Error: Failed to read prompt file</div>");
            exit(1);
        }

        $config['prompt'] = $promptContent;
        $this->config = $config;
    }

    protected function ensureConfigDirectory(): void
    {
        $configDir = dirname($this->configPath);
        if (!is_dir($configDir) && !mkdir($configDir, 0755, true)) {
            render("<div class='text-red'>Error: Failed to create config directory</div>");
            exit(1);
        }
    }

    protected function createConfig(): void
    {
        $starterConfigPath = dirname(__DIR__) . '/resources/starter-config.json';
        if (!file_exists($starterConfigPath)) {
            render("<div class='text-red'>Error: Starter config file not found</div>");
            exit(1);
        }

        if (!copy($starterConfigPath, $this->configPath)) {
            render("<div class='text-red'>Error: Failed to create config file</div>");
            exit(1);
        }
    }

    protected function createPrompt(): void
    {
        $starterPromptPath = dirname(__DIR__) . '/resources/prompt.md';
        if (!file_exists($starterPromptPath)) {
            render("<div class='text-red'>Error: Starter prompt file not found</div>");
            exit(1);
        }

        if (!copy($starterPromptPath, $this->promptPath)) {
            render("<div class='text-red'>Error: Failed to create prompt file</div>");
            exit(1);
        }
    }

    public function editConfig(): void
    {
        $editor = getenv('EDITOR') ?: 'nano';
        passthru("$editor $this->configPath", $returnCode);

        if ($returnCode !== 0) {
            render("<div class='text-red'>Failed to open editor. Please make sure your EDITOR environment variable is set correctly.</div>");
        }
    }

    public function editPrompt(): void
    {
        $editor = getenv('EDITOR') ?: 'nano';
        passthru("$editor $this->promptPath", $returnCode);

        if ($returnCode !== 0) {
            render("<div class='text-red'>Failed to open editor. Please make sure your EDITOR environment variable is set correctly.</div>");
        }
    }
}
