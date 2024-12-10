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
        $this->configPath = $_SERVER['HOME'] . '/.config/gcm-gen.config.php';
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
        if (!file_exists($this->configPath)) {
            $this->createConfig();
            return;
        }

        $this->config = require $this->configPath;
    }

    protected function createConfig(): void
    {
        if (!is_dir(dirname($this->configPath))) {
            mkdir(dirname($this->configPath), 0755, true);
        }

        $defaultConfig = [
            'provider' => 'chatgpt',
            'ignore_files' => [
                'vendor',
                'node_modules',
                'package-lock.json',
                'yarn.lock',
                'composer.lock',
                'dist',
                'build',
                'public',
                'storage',
            ],
            'ollama' => [
                'model' => 'llama3.2',
            ],
            'chatgpt' => [
                'model' => 'gpt-4',
                'api_key' => '',
            ],
            'prompt' => <<<EOL
Generate a git commit message based on the following rules:

1. First line:
    - Use imperative mood (Add not Added)
    - Max 50 characters
    - Format: [type] - [ticket] [description]
    - [ticket] is optional and can be built from the branch name look for the pattern '/(?:#(\d+)|([A-Z]+-\d+))/i'
    - [type] from branch patterns using emojis only (no branch pattern match):
        feature/* → ✨
        [bugfix,hotfix]/* → 🐛
        release/* → 🔖
        default → 🤖

2. Optional body (if changes are complex):
    - Leave one blank line after subject
    - Create a new line at 72 characters
    - Explain the type of change in the first line
    - Explain what and why, not how
    - Add BREAKING CHANGE: for breaking changes
EOL,
        ];

        $configContent = "<?php\n\nreturn " . var_export($defaultConfig, true) . ";\n";
        file_put_contents($this->configPath, $configContent);

        $this->config = $defaultConfig;
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
