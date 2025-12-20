<?php
require_once __DIR__ . '/../db.php';

$master_id = $_GET['master_id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM masters WHERE id = ?");
    $stmt->execute([$master_id]);
    $master = $stmt->fetch();

    if (!$master):
        header('Location: index.php');
        exit;
    endif;
} catch (PDOException $e) {
    die('Ошибка при загрузке данных: ' . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT * FROM work_schedule WHERE master_id = ? ORDER BY
        CASE day_of_week
            WHEN 'Понедельник' THEN 1
            WHEN 'Вторник' THEN 2
            WHEN 'Среда' THEN 3
            WHEN 'Четверг' THEN 4
            WHEN 'Пятница' THEN 5
            WHEN 'Суббота' THEN 6
            WHEN 'Воскресенье' THEN 7
        END, start_time");
    $stmt->execute([$master_id]);
    $schedule = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Ошибка при загрузке графика: ' . $e->getMessage());
}

// Подсчет общего количества часов
$total_hours = 0;
foreach ($schedule as $item) {
    $start = new DateTime($item['start_time']);
    $end = new DateTime($item['end_time']);
    $interval = $start->diff($end);
    $total_hours += $interval->h;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График работы мастера | СТО "АвтоДоктор"</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-back back-link">
            <i class="fas fa-arrow-left"></i> Назад к списку мастеров
        </a>

        <h1><i class="fas fa-calendar-alt"></i> График работы мастера</h1>

        <div class="info-box">
            <div class="master-info">
                <h3><i class="fas fa-user-cog"></i> Информация о мастере</h3>
                <p><strong>ФИО:</strong> <?= htmlspecialchars($master['surname'] . ' ' . $master['firstname'] . ' ' . ($master['patronymic'] ?? '')) ?></p>
                <p><strong>Специализация:</strong> <span class="badge badge-regular"><?= htmlspecialchars($master['specialization']) ?></span></p>
                <?php if (!empty($master['email'])): ?>
                    <p><strong>Email:</strong> <i class="fas fa-envelope"></i> <?= htmlspecialchars($master['email']) ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($schedule)): ?>
            <div class="schedule-stats">
                <h3><i class="fas fa-chart-pie"></i> Статистика графика</h3>
                <p><strong>Всего рабочих дней:</strong> <?= count($schedule) ?></p>
                <p><strong>Общее количество часов:</strong> <?= $total_hours ?> ч.</p>
                <p><strong>Средняя смена:</strong> <?= count($schedule) > 0 ? round($total_hours / count($schedule), 1) : 0 ?> ч.</p>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($schedule)): ?>
            <div class="message message-error">
                <i class="fas fa-calendar-times"></i> График работы не настроен. Добавьте рабочие дни.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>День недели</th>
                            <th>Время работы</th>
                            <th>Тип смены</th>
                            <th>Продолжительность</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedule as $item): ?>
                        <?php
                        // Вычисление продолжительности смены
                        $start = new DateTime($item['start_time']);
                        $end = new DateTime($item['end_time']);
                        $interval = $start->diff($end);
                        $hours = $interval->h;
                        $minutes = $interval->i;
                        
                        // Определение типа смены
                        $work_type = $item['work_type'] ?? 'regular';
                        $badge_class = 'badge-regular';
                        $type_text = 'Обычная';
                        $type_icon = 'fas fa-clock';
                        
                        switch ($work_type) {
                            case 'extended':
                                $badge_class = 'badge-extended';
                                $type_text = 'Удлиненная';
                                $type_icon = 'fas fa-clock';
                                break;
                            case 'early':
                                $badge_class = 'badge-early';
                                $type_text = 'Ранняя';
                                $type_icon = 'fas fa-sun';
                                break;
                            case 'evening':
                                $badge_class = 'badge-evening';
                                $type_text = 'Вечерняя';
                                $type_icon = 'fas fa-moon';
                                break;
                            case 'weekend':
                                $badge_class = 'badge-weekend';
                                $type_text = 'Выходной';
                                $type_icon = 'fas fa-calendar-day';
                                break;
                            case 'night':
                                $badge_class = 'badge-night';
                                $type_text = 'Ночная';
                                $type_icon = 'fas fa-bed';
                                break;
                            default:
                                $badge_class = 'badge-regular';
                                $type_text = 'Обычная';
                                $type_icon = 'fas fa-clock';
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="day-cell">
                                    <i class="fas fa-calendar-day"></i>
                                    <strong><?= htmlspecialchars($item['day_of_week']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="time-cell">
                                    <i class="fas fa-play-circle"></i> <?= htmlspecialchars($item['start_time']) ?>
                                    <i class="fas fa-arrow-right time-arrow"></i>
                                    <i class="fas fa-stop-circle"></i> <?= htmlspecialchars($item['end_time']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $badge_class ?>">
                                    <i class="<?= $type_icon ?>"></i> <?= $type_text ?>
                                </span>
                            </td>
                            <td>
                                <div class="duration-cell">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span class="duration"><?= $hours ?> ч <?= $minutes > 0 ? $minutes . ' мин' : '' ?></span>
                                </div>
                            </td>
                            <td class="actions">
                                <a href="edit_schedule.php?id=<?= $item['id'] ?>" class="btn btn-edit" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_schedule.php?id=<?= $item['id'] ?>" class="btn btn-delete" title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="summary-box">
                <h3><i class="fas fa-list-check"></i> Итоги недели</h3>
                <p>Рабочих дней: <strong><?= count($schedule) ?></strong> | Общее время: <strong><?= $total_hours ?> часов</strong></p>
                <?php if ($total_hours > 40): ?>
                    <p class="warning"><i class="fas fa-exclamation-triangle"></i> Превышена стандартная рабочая неделя (40 часов)</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="add-button-container">
            <a href="add_schedule.php?master_id=<?= $master_id ?>" class="btn btn-add">
                <i class="fas fa-plus-circle"></i> Добавить рабочий день
            </a>
            <a href="index.php" class="btn btn-back">
                <i class="fas fa-home"></i> На главную
            </a>
        </div>
    </div>
</body>
</html>