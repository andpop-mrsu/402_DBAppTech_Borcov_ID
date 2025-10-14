<?php
namespace ZerolLka\GuessNumber;

class View
{
    public static function showWelcome(): void
    {
        echo "==============================\n";
        echo " Добро пожаловать в игру 'Угадай число'!\n";
        echo "==============================\n\n";
    }

    public static function showWin(int $attempts): void
    {
        echo "🎉 Поздравляем! Вы угадали число за $attempts попыток!\n";
    }

    public static function showLose(int $number): void
    {
        echo "😔 К сожалению, вы не угадали. Загаданное число: $number\n";
    }

    public static function showGames(array $games, string $title = "История игр"): void
    {
        if (empty($games)) {
            echo "Пока нет сыгранных партий.\n";
            return;
        }

        echo "===== $title =====\n";
        foreach ($games as $g) {
            echo "{$g['id']}. {$g['date']} | {$g['player_name']} | ";
            echo "макс: {$g['max_number']} | загадано: {$g['secret_number']} | ";
            echo "результат: {$g['result']} | попыток: {$g['attempts_count']}\n";
        }
    }

    public static function showStats(array $stats): void
    {
        if (empty($stats)) {
            echo "Пока нет статистики.\n";
            return;
        }

        echo "===== Статистика по игрокам =====\n";
        echo "Игрок           | Игр | Побед | Поражений | % побед\n";
        echo "-----------------------------------------------\n";
        
        foreach ($stats as $player) {
            $winRate = $player['total_games'] > 0 
                ? round(($player['wins'] / $player['total_games']) * 100, 1)
                : 0;
            
            printf("%-15s | %3d | %5d | %9d | %6.1f%%\n",
                $player['player_name'],
                $player['total_games'],
                $player['wins'],
                $player['losses'],
                $winRate
            );
        }
    }

    public static function showReplay(array $game): void
    {
        echo "===== Повтор игры #{$game['id']} =====\n";
        echo "Игрок: {$game['player_name']}\n";
        echo "Дата: {$game['date']}\n";
        echo "Максимальное число: {$game['max_number']}\n";
        echo "Загаданное число: {$game['secret_number']}\n";
        echo "Результат: {$game['result']}\n";
        echo "Количество попыток: {$game['attempts_count']}\n\n";
        
        echo "Ход игры:\n";
        echo "Попытка | Число   | Ответ\n";
        echo "--------|---------|------------\n";
        
        foreach ($game['attempts'] as $attempt) {
            printf(" %6d | %7d | %s\n",
                $attempt['attempt_number'],
                $attempt['guessed_number'],
                $attempt['computer_response']
            );
        }
    }
}