# GCM-Gen (Git Commit Message Generator)

GCM-Gen is a CLI tool that generates meaningful commit messages by analysing your git changes. It uses AI to understand your code changes and generate appropriate commit messages. Supports both ChatGPT and Ollama for AI processing.

## Features

- 🤖 AI-powered commit message generation (ChatGPT or Ollama)
- ⚡ Quick and easy to use
- 🛠️ Configurable prompts and settings
- 📝 Direct git commit integration with branch-based commit types
- 🎨 Beautiful terminal UI

## Choosing an LLM Provider

GCM-Gen supports two AI providers, each with their own advantages:

### ChatGPT (Recommended for Best Results)
- ✅ Superior commit message quality
- ✅ Faster processing
- ✅ Lower system requirements
- ❌ Requires API key
- ❌ Sends code diffs to OpenAI servers
- ❌ Usage costs (pay per API call)

### Ollama (Recommended for Privacy/Offline Use)
- ✅ Completely free to use
- ✅ Runs 100% locally
- ✅ No data leaves your machine
- ❌ Requires significant system resources
- ❌ Slower processing time
- ❌ May produce less consistent results

Choose ChatGPT if you want the best quality commit messages and don't mind the API costs. Choose Ollama if you need to keep your code private or want a free, offline solution.

## Requirements

### Core Requirements
- PHP 8.1 or higher
- Composer
- Git

### AI Provider Requirements

#### Option 1: ChatGPT
1. OpenAI API Key
   - Sign up at [OpenAI Platform](https://platform.openai.com/signup)
   - Create an API key at [API Keys](https://platform.openai.com/api-keys)
   - Add the API key to your configuration file

#### Option 2: Ollama
1. Install Ollama
   - Visit [Ollama.ai](https://ollama.ai) to download and install
   - Follow the installation instructions for your operating system
2. Start the Ollama service:
   ```bash
   ollama serve
   ```
3. Pull the required model:
   ```bash
   ollama pull llama3.2
   ```

## Installation

### Global Installation

```bash
composer global require mdeloughry/gcm-gen
```

Make sure your global Composer bin directory is in your system's PATH.

### Project Installation

```bash
composer global require mdeloughry/gcm-gen
```

## Usage

```bash
# Generate a commit message
gcm-gen g

# Generate and commit in one go
gcm-gen c

# Show current configuration
gcm-gen config

# Edit configuration
gcm-gen config --edit
```

## Configuration

On first run, GCM-Gen will create a configuration file at `~/.config/gcm-gen.config.php`. You can edit this file directly or use:

```bash
gcm-gen config --edit
```

### Configuration Options

The configuration file supports both ChatGPT and Ollama providers. Here's a complete example:

```php
return [
    'provider' => 'chatgpt', // or 'ollama'
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
        'model' => 'llama3.2', // Default model
    ],
    'chatgpt' => [
        'model' => 'gpt-4', // Default model
        'api_key' => 'your-api-key-here',
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
        bugfix/hotfix/* → 🐛
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
```

#### Configuration Breakdown

- `provider`: Choose between 'chatgpt' or 'ollama'
- `ignore_files`: List of files and directories to exclude from the git diff analysis
- `ollama`: Ollama-specific settings
  - `model`: The Ollama model to use (default: llama3.2)
- `chatgpt`: ChatGPT-specific settings
  - `model`: The GPT model to use (default: gpt-4)
  - `api_key`: Your OpenAI API key
- `prompt`: The template used to generate commit messages, supporting:
  - Automatic emoji type based on branch name
  - Ticket number extraction from branch names
  - Conventional commit message formatting

## Development Setup

1. Clone the repository:
```bash
git clone https://github.com/mdeloughry/gcm-gen.git
cd gcm-gen
```

2. Install dependencies:
```bash
composer install
```

3. Set up your environment:
- Copy `.env.example` to `.env`
- Add your OpenAI API key to `.env`

4. Run tests:
```bash
composer test
```

### Development Requirements

- PHP 8.1 or higher
- Composer
- OpenAI API key or Ollama installed locally
- Git


## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

If you encounter any problems or have suggestions, please [open an issue](https://github.com/DR-DinoMight/gcm-gen/issues/new).

