<?php
namespace ZerolLka\GuessNumber;

use ZerolLka\GuessNumber\Model\GameRepository;
use function cli\prompt;

class Controller
{
    public static function run(array $argv)
    {
        $repo = new GameRepository();
        
        // Правильный анализ аргументов командной строки
        $options = getopt('lw:s:r:', ['list', 'wins', 'losses', 'stats', 'replay:']);
        
        // Список всех игр
        if (isset($options['l']) || isset($options['list'])) {
            $games = $repo->listGames();
            View::showGames($games, "Все игры");
            return;
        }

        // Список выигранных игр
        if (isset($options['w']) || isset($options['wins'])) {
            $games = $repo->getWonGames();
            View::showGames($games, "Выигранные игры");
            return;
        }

        // Список проигранных игр
        if (isset($options['losses'])) {
            $games = $repo->getLostGames();
            View::showGames($games, "Проигранные игры");
            return;
        }

        // Статистика по игрокам
        if (isset($options['s']) || isset($options['stats'])) {
            $stats = $repo->getPlayerStats();
            View::showStats($stats);
            return;
        }

        // Повтор игры
        if (isset($options['r']) || isset($options['replay'])) {
            $gameId = $options['r'] ?? $options['replay'] ?? null;
            if ($gameId) {
                $game = $repo->getGameWithAttempts((int)$gameId);
                if ($game) {
                    View::showReplay($game);
                } else {
                    echo "Игра с ID $gameId не найдена.\n";
                }
            } else {
                echo "Укажите ID игры для повтора: --replay ID\n";
            }
            return;
        }

        // Новая игра
        self::startNewGame($repo);
    }

    private static function startNewGame(GameRepository $repo): void
    {
        View::showWelcome();

        // Ввод имени игрока
        $player = prompt("Введите имя игрока");

        // Ввод максимального числа для угадывания
        $max = (int)prompt("Введите максимальное число (например, 100)");

        // Ввод максимального количества попыток
        $maxAttempts = (int)prompt("Введите максимальное количество попыток");

        $secret = rand(1, $max);
        $attempts = 0;
        $attemptsData = [];

        echo "\nИгра началась! Угадайте число от 1 до $max. У вас $maxAttempts попыток.\n\n";

        while ($attempts < $maxAttempts) {
            $guess = (int)prompt("Попытка " . ($attempts + 1) . ". Введите число (1-$max)");
            $attempts++;

            if ($guess === $secret) {
                View::showWin($attempts);
                $attemptsData[] = [
                    'number' => $attempts,
                    'guess' => $guess,
                    'response' => 'Угадал'
                ];
                $repo->saveGameWithAttempts($player, $max, $secret, $attempts, "Победа", $attemptsData);
                return;
            } elseif ($guess < $secret) {
                echo "➡️  Загаданное число БОЛЬШЕ.\n\n";
                $attemptsData[] = [
                    'number' => $attempts,
                    'guess' => $guess,
                    'response' => 'Больше'
                ];
            } else {
                echo "⬅️  Загаданное число МЕНЬШЕ.\n\n";
                $attemptsData[] = [
                    'number' => $attempts,
                    'guess' => $guess,
                    'response' => 'Меньше'
                ];
            }
        }

        // Если попытки закончились
        View::showLose($secret);
        $repo->saveGameWithAttempts($player, $max, $secret, $attempts, "Проигрыш", $attemptsData);
    }
}