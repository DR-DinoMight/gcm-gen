<?php

namespace MDeloughry\GcmGen;

use function Termwind\render;

/**
 * Handles Git-related operations
 */
class GitOperations
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the git diff with specified parameters
     */
    public function getDiff(int $maxLength = 10000): ?string
    {
        $ignoreFiles = $this->getIgnoreFiles();
        $diff = shell_exec("git diff --diff-filter=ACMR --no-color --cached . $ignoreFiles");

        if ($diff === null) {
            render(<<<HTML
                <div class="text-red font-bold">
                    Error: No staged changes found. Please stage your changes using 'git add' first.
                </div>
            HTML);
            exit(1);
        }

        return $this->processDiff($diff, $maxLength);
    }

    /**
     * Get current branch name
     */
    public function getCurrentBranch(): string
    {
        return trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?? '');
    }

    /**
     * Commit changes with the given message
     */
    public function commit(string $message): void
    {
        $escapedMessage = escapeshellarg($message);
        shell_exec("git commit -m $escapedMessage");
    }

    protected function getIgnoreFiles(): string
    {
        return implode(' ', array_map(
            fn($file) => "':(exclude)$file'",
            $this->config['ignore_files']
        ));
    }

    protected function processDiff(string $diff, int $maxLength): string
    {
        if (preg_match('/Binary files .* differ/', $diff)) {
            $diff = preg_replace('/^Binary files .* differ$/m', '[Binary file diff omitted]', $diff);
        }

        $filteredDiff = preg_replace('/^index .*$/m', '', $diff);

        if (strlen($filteredDiff) > $maxLength) {
            $filteredDiff = substr($filteredDiff, 0, $maxLength) . "\n...diff truncated...";
        }

        return trim($filteredDiff);
    }
}
