<?php
// Определяем корень проекта
$projectRoot = __DIR__; 
$dbPath = $projectRoot . '/data/sto_v2.db'; 

error_log("Корень проекта: $projectRoot");
error_log("Путь к БД: $dbPath");

// Создаем папку data если её нет
if (!file_exists(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0777, true);
    error_log("Папка data создана");
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON");
    
    // Проверяем существование таблиц
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        // Если таблиц нет, создаем их
        require_once __DIR__ . '/init.sql';
        $sql = file_get_contents(__DIR__ . '/init.sql');
        $pdo->exec($sql);
        error_log("Таблицы созданы из init.sql");
    }
    
} catch (PDOException $e) {
    // Для отладки выводим полную информацию
    die("Ошибка подключения к базе данных: " . $e->getMessage() . 
        "<br>Проверьте файл: " . $dbPath .
        "<br>Project Root: " . $projectRoot .
        "<br>File exists: " . (file_exists($dbPath) ? 'YES' : 'NO'));
}