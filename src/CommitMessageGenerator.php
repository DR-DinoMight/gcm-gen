<?php

namespace MDeloughry\GcmGen;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use function Termwind\render;

/**
 * Handles commit message generation using different providers
 */
class CommitMessageGenerator
{
    protected array $config;
    protected GitOperations $gitOps;
    protected Client $httpClient;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->gitOps = new GitOperations($config);
        $this->httpClient = new Client();
    }

    /**
     * Generate a commit message and optionally commit changes
     */
    public function generate(bool $commit = false): void
    {
        $diff = $this->gitOps->getDiff();
        if (empty($diff)) {
            render("<div class='text-yellow font-bold'>Warning: No changes detected in staged files.</div>");
            return;
        }

        $provider = $this->config['provider'];
        $model = $this->config[$provider]['model'];
        $prompt = $this->config['prompt'];

        $message = match ($provider) {
            'ollama' => $this->generateWithOllama($model, $prompt, $diff),
            'chatgpt' => $this->generateWithChatGPT($model, $prompt, $diff),
        };

        //clean up message to remove `
        $message = str_replace('`', '', $message);
        if ($commit) {
            $this->gitOps->commit($message);
        }

        $this->printCommitMessage($message);

        // Copy to clipboard
        render('<div class="bg-green-500 text-white p-1 mt-2">Copying to clipboard...</div>');
        ClipboardManager::copyToClipboard($message);
    }

    protected function generateWithOllama(string $model, string $prompt, string $diff): string
    {
        try {
            // Start loading animation in a separate process
            $loadingPid = pcntl_fork();

            if ($loadingPid === 0) {
                // Child process - loading animation
                $this->showLoadingAnimation($model);
                exit;
            }

            $response = $this->httpClient->post('http://localhost:11434/api/chat', [
                'json' => [
                    'model' => $model,
                    'stream' => false,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $prompt
                        ],
                        [
                            'role' => 'user',
                            'content' => "Here is the current branch name: " . $this->gitOps->getCurrentBranch()
                        ],
                        [
                            'role' => 'user',
                            'content' => "Here is the `git diff` output: {$diff}"
                        ]
                    ]
                ]
            ]);

            // Stop loading animation
            if ($loadingPid > 0) {
                posix_kill($loadingPid, SIGTERM);
                pcntl_wait($status);
                echo "\033[2K"; // Clear the last loading frame
            }

            $result = json_decode($response->getBody(), true);
            return $result['message']['content'] ?? 'Error: Unable to parse response';
        } catch (GuzzleException $e) {
            if ($loadingPid > 0) {
                posix_kill($loadingPid, SIGTERM);
                pcntl_wait($status);
                echo "\033[2K";
            }

            render("<div class='text-red font-bold'>Error calling Ollama API: {$e->getMessage()}</div>");
            exit(1);
        }
    }

    private function showLoadingAnimation(string $model): void
    {
        $frames = ['⠋', '⠙', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
        while (true) {
            foreach ($frames as $frame) {
                render(<<<HTML
                    <div class="flex space-x-1">
                        <span class="text-green">{$frame}</span>
                        <span class="text-gray">Generating commit message with {$model}...</span>
                    </div>
                HTML);
                usleep(80000); // 80ms delay
                echo "\033[1A\033[2K"; // Move cursor up and clear line
            }
        }
    }

    protected function generateWithChatGPT(string $model, string $prompt, string $diff): string
    {
        try {
            $response = $this->httpClient->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['chatgpt']['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user', 'content' => $diff],
                    ],
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['choices'][0]['message']['content'];
        } catch (GuzzleException $e) {
            render(<<<HTML
                <div class="text-red font-bold">
                    Error calling ChatGPT API: {$e->getMessage()}
                </div>
            HTML);
            exit(1);
        }
    }

    protected function printCommitMessage(string $message): void
    {
        render("<pre>{$message}</pre>");
    }

    public function setGitOps(GitOperations $gitOps): void
    {
        $this->gitOps = $gitOps;
    }

    public function setHttpClient(Client $client): void
    {
        $this->httpClient = $client;
    }
}
