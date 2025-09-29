<?php
namespace ZerolLka\GuessNumber\Controller;

use ZerolLka\GuessNumber\View\View;

function startGame(array $args = []) {
    if (empty($args)) {
        $mode = 'new';
    } else {
        $mode = strtolower($args[0]);
    }

    switch ($mode) {
        case '--new':
        case '-n':
        case 'new':
            View::renderStartScreen();
            playGame();
            break;
        case '--list':
        case '-l':
        case 'list':
            $sub = $args[1] ?? '';
            View::renderMessage("Listing games ($sub) — saving to DB is not implemented yet.");
            break;
        case '--top':
            View::renderMessage("Top players — saving to DB is not implemented yet.");
            break;
        case '--replay':
        case '-r':
            $id = $args[1] ?? '';
            View::renderMessage("Replay game $id — saving to DB is not implemented yet.");
            break;
        case '--help':
        case '-h':
        default:
            View::renderHelp();
            break;
    }
}

function playGame() {
    // Запрос максимального числа
    $maxNumberInput = \cli\prompt("Enter the maximum number (n)");
    if (!is_numeric($maxNumberInput) || $maxNumberInput < 1) {
        \cli\line("Invalid number. Using default 100.");
        $maxNumber = 100;
    } else {
        $maxNumber = (int)$maxNumberInput;
    }

    // Запрос максимального количества попыток
    $maxAttemptsInput = \cli\prompt("Enter the maximum number of attempts");
    if (!is_numeric($maxAttemptsInput) || $maxAttemptsInput < 1) {
        \cli\line("Invalid number. Using default 10 attempts.");
        $maxAttempts = 10;
    } else {
        $maxAttempts = (int)$maxAttemptsInput;
    }

    $target = rand(1, $maxNumber);

    $attempts = 0;
    $guessed = false;

    while ($attempts < $maxAttempts) {
        $input = \cli\prompt("Enter your guess (1-$maxNumber)");
        if (!is_numeric($input)) {
            \cli\line("Please enter a valid number.");
            continue;
        }

        $guess = (int)$input;
        $attempts++;

        if ($guess === $target) {
            \cli\line("Congratulations! You guessed the number in $attempts attempts.");
            $guessed = true;
            break;
        } elseif ($guess < $target) {
            \cli\line("Higher!");
        } else {
            \cli\line("Lower!");
        }
    }

    if (!$guessed) {
        \cli\line("Sorry, you didn't guess the number. It was $target.");
    }

    \cli\line("Note: Saving game to DB is not implemented yet.");
}
