<?php

namespace MDeloughry\GcmGen;

use function Termwind\render;

class ClipboardManager
{
    /**
     * Display text content to the terminal
     */
    private function displayTextContentToTerminal(string $text): void
    {
        render('<div class="bg-yellow-500 text-white p-2">Output:</div>');
        render('<div class="text-yellow p-2">' . $text . '</div>');
    }

    /**
     * Check if a command exists
     */
    private function commandExists(string $command): bool
    {
        $whereIsCommand = PHP_OS === 'WINNT' ? 'where' : 'which';
        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnCode = proc_close($process);
            return $returnCode === 0;
        }

        return false;
    }

    private function tryPossibleLinuxClipboardCopy(string $text): bool
    {
        if ($this->commandExists('xclip')) {
            $process = proc_open('xclip -selection clipboard', [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);
        } elseif ($this->commandExists('xsel')) {
            $process = proc_open('xsel --clipboard --input', [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);
        } else {
            render('<div class="bg-red-500 text-white p-2">Neither xclip nor xsel is installed</div>');
            $this->displayTextContentToTerminal($text);
            return false;
        }

        if (is_resource($process)) {
            fwrite($pipes[0], $text);
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return true;
        }

        return false;
    }

    /**
     * Copy text to the clipboard
     */
    public static function copyToClipboard(string $text): void
    {
        $os = strtolower(PHP_OS);

        try {
            switch ($os) {
                case 'darwin':
                    $process = proc_open('pbcopy', [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ], $pipes);

                    if (is_resource($process)) {
                        fwrite($pipes[0], $text);
                        fclose($pipes[0]);
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        proc_close($process);
                    }
                    break;

                case 'linux':
                    self::tryPossibleLinuxClipboardCopy($text);
                    break;

                case 'win32':
                case 'windows':
                case 'winnt':
                    // For Windows, use a temporary file approach
                    $tempFile = tempnam(sys_get_temp_dir(), 'clip');
                    file_put_contents($tempFile, $text);
                    exec("type \"$tempFile\" | clip");
                    unlink($tempFile);
                    break;

                default:
                    render(
                        '<div class="bg-red-500 text-white p-2">Unsupported OS: ' . $os . '</div>'
                    );
                    echo $text;
            }
        } catch (\Exception $e) {
            render('<div class="bg-red-500 text-white p-2">Failed to copy to clipboard: ' . $e->getMessage() . '</div>');
            self::displayTextContentToTerminal($text);
        }
    }
}
