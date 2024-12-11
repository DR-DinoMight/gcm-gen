<?php

namespace MDeloughry\GcmGen;

use function Termwind\render;

/**
 * Main class for the Git Commit Message Generator
 *
 * @package MDeloughry\GcmGen
 */
class GcmGen
{
    protected ConfigManager $configManager;
    protected GitOperations $gitOps;
    protected CommitMessageGenerator $messageGenerator;

    /**
     * Initialize the GcmGen application
     */
    public function __construct()
    {
        $this->configManager = new ConfigManager();
        $this->gitOps = new GitOperations($this->configManager->getConfig());
        $this->messageGenerator = new CommitMessageGenerator($this->configManager->getConfig());
    }

    /**
     * Run the application with provided arguments
     *
     * @param array $args Command line arguments
     */
    public function run(array $args): void
    {
        if (count($args) < 2) {
            $this->showHelp();
            return;
        }

        $command = $args[1] ?? '';
        $flag = $args[2] ?? '';

        match ($command) {
            'd', 'debug' => $this->handleDebug(),
            'g', 'generate' => $this->messageGenerator->generate(),
            'c', 'commit' => $this->messageGenerator->generate(true),
            'config' => $this->handleConfig($flag),
            'prompt' => $this->handlePrompt($flag),
            default => render("<div class='text-red'>Unknown command: $command</div>")
        };
    }

    /**
     * Handle debug command
     */
    protected function handleDebug(): void
    {
        $diff = $this->gitOps->getDiff();
        $prompt = $this->configManager->getConfig()['prompt'];
        render("<div class='text-blue'>Debug mode enabled</div>");
        render("$prompt $diff");
    }

    /**
     * Handle config command and its flags
     *
     * @param string $flag Command flag
     */
    protected function handleConfig(string $flag): void
    {
        match ($flag) {
            '-e', '--edit' => $this->configManager->editConfig(),
            default => $this->configManager->showConfig()
        };
    }

    /**
     * Handle prompt command and its flags
     *
     * @param string $flag Command flag
     */
    protected function handlePrompt(string $flag): void
    {
        match ($flag) {
            '-e', '--edit' => $this->configManager->editPrompt(),
            default => $this->showPrompt()
        };
    }

    protected function showPrompt(): void
    {
        $lines = explode("\n", $this->configManager->getPrompt());
        $formattedLines = array_map(fn($line) => '<div class="text-blue">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</div>', $lines);

        render(<<<HTML
            <div>
                <div class="px-1 bg-yellow-300 text-black">Current Prompt:</div>
                <div class="mt-1 ml-2">
                    {$this->joinLines($formattedLines)}
                </div>
            </div>
        HTML);
    }

    private function joinLines(array $lines): string
    {
        return implode("\n", $lines);
    }

    /**
     * Display help information
     */
    protected function showHelp(): void
    {
        render(<<<HTML
            <div>
                <div>
                    <div class="px-1 bg-yellow-300 text-black">Usage:</div> gcm-gen <span class="text-yellow">command</span>
                </div>
                <div class="mt-1">
                    <div class="text-yellow mb-1">Commands:</div>
                    <div class="ml-2">
                        <table>
                            <tr>
                                <td class="text-yellow pr-4">g, generate</td>
                                <td class="text-gray">Generate a commit message</td>
                            </tr>
                            <tr>
                                <td class="text-yellow pr-4">c, commit</td>
                                <td class="text-gray">Generate a commit message and commit</td>
                            </tr>
                            <tr>
                                <td class="text-yellow pr-4">config</td>
                                <td class="text-gray">Show current configuration</td>
                            </tr>
                            <tr>
                                <td class="text-yellow pr-4">config --edit</td>
                                <td class="text-gray">Open configuration in default editor</td>
                            </tr>
                            <tr>
                                <td class="text-yellow pr-4">prompt</td>
                                <td class="text-gray">Show current prompt</td>
                            </tr>
                            <tr>
                                <td class="text-yellow pr-4">prompt --edit</td>
                                <td class="text-gray">Edit prompt in default editor</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        HTML);
    }
}
