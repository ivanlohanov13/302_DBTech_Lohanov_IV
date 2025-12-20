<?php
$baseDir = __DIR__;
$dbPath = $baseDir . '/data/sto_v2.db';
$dataDir = $baseDir . '/data';
// Создаем папку data если её нет
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
    echo "Папка data создана в: $dataDir\n";
}
echo "Путь к БД: $dbPath\n";
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");

    echo "Создаю таблицы..\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS masters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        surname TEXT NOT NULL,
        firstname TEXT NOT NULL,
        patronymic TEXT,
        specialization TEXT NOT NULL,
        email TEXT,
        phone TEXT,
        hire_date DATE DEFAULT CURRENT_DATE,
        status TEXT DEFAULT 'active'
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS work_schedule (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        master_id INTEGER NOT NULL,
        day_of_week TEXT NOT NULL,
        start_time TEXT NOT NULL,
        end_time TEXT NOT NULL,
        work_type TEXT DEFAULT 'regular',
        FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS completed_works (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        master_id INTEGER NOT NULL,
        service_name TEXT NOT NULL,
        client_name TEXT,
        car_model TEXT,
        work_date DATE NOT NULL,
        cost REAL NOT NULL,
        notes TEXT,
        FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE CASCADE
    )");

    // Проверяем, есть ли уже данные
    $stmt = $pdo->query("SELECT COUNT(*) FROM masters");
    $count = $stmt->fetchColumn();

    // Если таблица пустая, добавляем новые тестовые данные
    if ($count == 0) {
        echo "Добавляю тестовые данные..\n";
        
        // Добавляем мастеров с расширенными данными
        $pdo->exec("INSERT INTO masters (surname, firstname, patronymic, specialization, email, phone, hire_date) VALUES
            ('Смирнов', 'Андрей', 'Викторович', 'Диагност', 'smirnov@sto.ru', '+7(900)123-45-67', '2023-01-15'),
            ('Кузнецов', 'Максим', 'Олегович', 'Электроник', 'kuznetsov@sto.ru', '+7(900)234-56-78', '2023-03-20'),
            ('Попов', 'Игорь', 'Сергеевич', 'Маляр', 'popov@sto.ru', '+7(900)345-67-89', '2024-01-10'),
            ('Васильев', 'Роман', 'Александрович', 'Моторист', 'vasilev@sto.ru', '+7(900)456-78-90', '2022-11-05')
        ");

        // Добавляем график работы с типами смен
        $pdo->exec("INSERT INTO work_schedule (master_id, day_of_week, start_time, end_time, work_type) VALUES
            (1, 'Понедельник', '08:00', '17:00', 'regular'),
            (1, 'Вторник', '08:00', '17:00', 'regular'),
            (1, 'Среда', '08:00', '17:00', 'regular'),
            (2, 'Понедельник', '09:00', '18:00', 'regular'),
            (2, 'Среда', '09:00', '18:00', 'regular'),
            (2, 'Пятница', '10:00', '19:00', 'extended'),
            (3, 'Вторник', '07:00', '16:00', 'early'),
            (3, 'Четверг', '07:00', '16:00', 'early'),
            (4, 'Понедельник', '12:00', '21:00', 'evening'),
            (4, 'Суббота', '09:00', '18:00', 'weekend')
        ");

        // Добавляем выполненные работы с дополнительной информацией
        $pdo->exec("INSERT INTO completed_works (master_id, service_name, client_name, car_model, work_date, cost, notes) VALUES
            (1, 'Компьютерная диагностика', 'Иванов А.С.', 'Toyota Camry', '2024-12-01', 3000.00, 'Обнаружена ошибка датчика кислорода'),
            (1, 'Проверка систем безопасности', 'Петрова М.И.', 'Honda Civic', '2024-12-03', 2500.00, 'Все системы в норме'),
            (2, 'Ремонт аудиосистемы', 'Сидоров П.К.', 'Kia Rio', '2024-12-02', 8000.00, 'Замена динамиков'),
            (3, 'Локальная покраска', 'Козлов Д.В.', 'Lada Vesta', '2024-12-04', 12000.00, 'Покраска заднего бампера'),
            (4, 'Замена ГРМ', 'Николаев С.М.', 'Ford Focus', '2024-12-05', 18000.00, 'С заменой роликов и помпы'),
            (4, 'Капитальный ремонт двигателя', 'Федоров А.П.', 'Volkswagen Passat', '2024-12-06', 45000.00, 'С гарантией 1 год')
        ");

        echo "Новая база данных успешно создана и заполнена данными!\n";
    } else {
        echo "База данных уже существует и содержит $count мастеров.\n";
    }

    // Проверяем таблицы
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Созданные таблицы: " . implode(', ', $tables) . "\n";

} catch (PDOException $e) {
    die("Ошибка при создании базы данных: " . $e->getMessage() . "\n");
}

?>