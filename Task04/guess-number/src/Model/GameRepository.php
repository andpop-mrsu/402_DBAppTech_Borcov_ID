<?php
namespace ZerolLka\GuessNumber\Model;

use PDO;

class GameRepository
{
    private PDO $pdo;

    public function __construct()
    {
        // Создаем директорию data если не существует
        $dataDir = __DIR__ . '/../../data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        // Создаем или подключаемся к базе SQLite
        $this->pdo = new PDO('sqlite:' . $dataDir . '/games.sqlite');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем таблицы, если не существуют
        $this->createTables();
    }

    private function createTables(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                date TEXT NOT NULL,
                player_name TEXT NOT NULL,
                max_number INTEGER NOT NULL,
                secret_number INTEGER NOT NULL,
                result TEXT NOT NULL,
                attempts_count INTEGER NOT NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                game_id INTEGER NOT NULL,
                attempt_number INTEGER NOT NULL,
                guessed_number INTEGER NOT NULL,
                computer_response TEXT NOT NULL,
                FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
            )
        ");
    }

    // Сохраняем игру с попытками
    public function saveGameWithAttempts(
        string $playerName, 
        int $maxNumber, 
        int $secretNumber, 
        int $attemptsCount, 
        string $result,
        array $attemptsData
    ): int {
        $this->pdo->beginTransaction();

        try {
            // Сохраняем основную информацию об игре
            $stmt = $this->pdo->prepare("
                INSERT INTO games (date, player_name, max_number, secret_number, result, attempts_count)
                VALUES (:date, :player_name, :max_number, :secret_number, :result, :attempts_count)
            ");

            $stmt->execute([
                ':date' => date('Y-m-d H:i:s'),
                ':player_name' => $playerName,
                ':max_number' => $maxNumber,
                ':secret_number' => $secretNumber,
                ':result' => $result,
                ':attempts_count' => $attemptsCount
            ]);

            $gameId = $this->pdo->lastInsertId();

            // Сохраняем попытки
            $attemptStmt = $this->pdo->prepare("
                INSERT INTO attempts (game_id, attempt_number, guessed_number, computer_response)
                VALUES (:game_id, :attempt_number, :guessed_number, :computer_response)
            ");

            foreach ($attemptsData as $attempt) {
                $attemptStmt->execute([
                    ':game_id' => $gameId,
                    ':attempt_number' => $attempt['number'],
                    ':guessed_number' => $attempt['guess'],
                    ':computer_response' => $attempt['response']
                ]);
            }

            $this->pdo->commit();
            return $gameId;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Получаем список всех игр
    public function listGames(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, date, player_name, max_number, secret_number, result, attempts_count 
            FROM games 
            ORDER BY id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получаем игры с победами
    public function getWonGames(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, date, player_name, max_number, secret_number, result, attempts_count 
            FROM games 
            WHERE result = 'Победа' 
            ORDER BY id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получаем игры с проигрышами
    public function getLostGames(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, date, player_name, max_number, secret_number, result, attempts_count 
            FROM games 
            WHERE result = 'Проигрыш' 
            ORDER BY id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получаем статистику по игрокам
    public function getPlayerStats(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                player_name,
                COUNT(*) as total_games,
                SUM(CASE WHEN result = 'Победа' THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN result = 'Проигрыш' THEN 1 ELSE 0 END) as losses
            FROM games 
            GROUP BY player_name
            ORDER BY wins DESC, total_games DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получаем конкретную игру по id
    public function getGame(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM games WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        return $game ?: null;
    }

    // Получаем попытки для конкретной игры
    public function getGameAttempts(int $gameId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT attempt_number, guessed_number, computer_response 
            FROM attempts 
            WHERE game_id = :game_id 
            ORDER BY attempt_number ASC
        ");
        $stmt->execute([':game_id' => $gameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получаем полную информацию об игре с попытками
    public function getGameWithAttempts(int $id): ?array
    {
        $game = $this->getGame($id);
        if (!$game) {
            return null;
        }

        $game['attempts'] = $this->getGameAttempts($id);
        return $game;
    }
}