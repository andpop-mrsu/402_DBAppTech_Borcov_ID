<?php
namespace ZerolLka\GuessNumber\View;

class View {
    public static function renderStartScreen() {
        if (function_exists('\cli\line')) {
            \cli\line("=== Угадай число (GuessNumber) ===");
            \cli\line("Starting a new game...");
        } else {
            echo "=== Угадай число (GuessNumber) ===\n";
            echo "Starting a new game...\n";
        }
    }

    public static function renderMessage(string $msg) {
        if (function_exists('\cli\line')) {
            \cli\line($msg);
        } else {
            echo $msg . "\n";
        }
    }

    public static function renderHelp() {
        $help = <<<HELP
Usage: guess-number [options]

Options:
  -n, --new        Start a new game (default)
  -l, --list       List games (optionally: win / loose)
  --top            Show top players
  -r, --replay ID  Replay a game by ID
  -h, --help       Show this help
HELP;
        self::renderMessage($help);
    }
}
