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
    $stmt = $pdo->prepare("SELECT * FROM completed_works WHERE master_id = ? ORDER BY work_date DESC, cost DESC");
    $stmt->execute([$master_id]);
    $works = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Ошибка при загрузке работ: ' . $e->getMessage());
}

// Подсчет статистики
$total_works = count($works);
$total_cost = 0;
$avg_cost = 0;
$monthly_stats = [];

foreach ($works as $work) {
    $total_cost += $work['cost'];
    
    // Статистика по месяцам
    $month = date('Y-m', strtotime($work['work_date']));
    if (!isset($monthly_stats[$month])) {
        $monthly_stats[$month] = [
            'count' => 0,
            'total' => 0,
            'month_name' => date('F Y', strtotime($work['work_date']))
        ];
    }
    $monthly_stats[$month]['count']++;
    $monthly_stats[$month]['total'] += $work['cost'];
}

if ($total_works > 0) {
    $avg_cost = $total_cost / $total_works;
}

// Сортируем месяцы по убыванию
krsort($monthly_stats);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные работы | СТО "АвтоДоктор"</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-back back-link">
            <i class="fas fa-arrow-left"></i> Назад к списку мастеров
        </a>

        <h1><i class="fas fa-tools"></i> Выполненные работы мастера</h1>

        <div class="info-box">
            <div class="master-info">
                <h3><i class="fas fa-user-cog"></i> Информация о мастере</h3>
                <p><strong>ФИО:</strong> <?= htmlspecialchars($master['surname'] . ' ' . $master['firstname'] . ' ' . ($master['patronymic'] ?? '')) ?></p>
                <p><strong>Специализация:</strong> <span class="badge badge-regular"><?= htmlspecialchars($master['specialization']) ?></span></p>
                <?php if (!empty($master['email'])): ?>
                    <p><strong>Контакты:</strong> <i class="fas fa-envelope"></i> <?= htmlspecialchars($master['email']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="works-stats">
                <h3><i class="fas fa-chart-line"></i> Статистика работ</h3>
                <p><strong>Всего работ:</strong> <?= $total_works ?></p>
                <p><strong>Общая сумма:</strong> <?= number_format($total_cost, 2, '.', ' ') ?> ₽</p>
                <p><strong>Средний чек:</strong> <?= number_format($avg_cost, 2, '.', ' ') ?> ₽</p>
            </div>
        </div>

        <?php if (!empty($monthly_stats)): ?>
        <div class="monthly-stats">
            <h3><i class="fas fa-calendar-check"></i> Работы по месяцам</h3>
            <div class="stats-grid">
                <?php $counter = 0; foreach ($monthly_stats as $month => $stats): ?>
                    <?php if ($counter < 3): // Показываем только 3 последних месяца ?>
                    <div class="stat-card">
                        <div class="stat-header">
                            <i class="fas fa-calendar-alt"></i>
                            <h4><?= $stats['month_name'] ?></h4>
                        </div>
                        <div class="stat-body">
                            <p><i class="fas fa-tasks"></i> Работ: <strong><?= $stats['count'] ?></strong></p>
                            <p><i class="fas fa-ruble-sign"></i> Сумма: <strong><?= number_format($stats['total'], 0, '.', ' ') ?> ₽</strong></p>
                            <?php if ($stats['count'] > 0): ?>
                                <p><i class="fas fa-calculator"></i> Среднее: <strong><?= number_format($stats['total'] / $stats['count'], 0, '.', ' ') ?> ₽</strong></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php $counter++; endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($works)): ?>
            <div class="message message-error">
                <i class="fas fa-toolbox"></i> Выполненные работы не найдены. Добавьте первую запись.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Услуга</th>
                            <th>Клиент / Автомобиль</th>
                            <th>Дата</th>
                            <th>Стоимость</th>
                            <th>Примечания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($works as $index => $work): ?>
                        <tr>
                            <td class="work-number">
                                <span class="number-circle"><?= $index + 1 ?></span>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong><?= htmlspecialchars($work['service_name']) ?></strong>
                                    <?php if (!empty($work['notes'])): ?>
                                        <div class="service-details">
                                            <small><i class="fas fa-info-circle"></i> <?= htmlspecialchars(substr($work['notes'], 0, 80)) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="client-info">
                                    <?php if (!empty($work['client_name'])): ?>
                                        <p><i class="fas fa-user"></i> <strong><?= htmlspecialchars($work['client_name']) ?></strong></p>
                                    <?php endif; ?>
                                    <?php if (!empty($work['car_model'])): ?>
                                        <p><i class="fas fa-car"></i> <?= htmlspecialchars($work['car_model']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="date-cell">
                                    <i class="fas fa-calendar"></i>
                                    <span class="date"><?= htmlspecialchars(date('d.m.Y', strtotime($work['work_date']))) ?></span>
                                    <br>
                                    <small class="weekday"><?= htmlspecialchars(date('l', strtotime($work['work_date']))) ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="price-cell">
                                    <span class="price"><?= number_format($work['cost'], 2, '.', ' ') ?> ₽</span>
                                    <?php 
                                    $cost = $work['cost'];
                                    if ($cost > 10000): ?>
                                        <span class="badge badge-premium"><i class="fas fa-crown"></i> Премиум</span>
                                    <?php elseif ($cost > 5000): ?>
                                        <span class="badge badge-standard"><i class="fas fa-star"></i> Стандарт</span>
                                    <?php else: ?>
                                        <span class="badge badge-basic"><i class="fas fa-check"></i> Базовая</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($work['notes'])): ?>
                                    <div class="notes-preview">
                                        <i class="fas fa-sticky-note"></i>
                                        <small title="<?= htmlspecialchars($work['notes']) ?>">
                                            <?= htmlspecialchars(substr($work['notes'], 0, 60)) ?>
                                            <?php if (strlen($work['notes']) > 60): ?>...<?php endif; ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <span class="no-notes"><i class="fas fa-minus"></i> Нет заметок</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="edit_work.php?id=<?= $work['id'] ?>" class="btn btn-edit" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_work.php?id=<?= $work['id'] ?>" class="btn btn-delete" title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="view_work.php?id=<?= $work['id'] ?>" class="btn btn-view" title="Подробнее">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="summary-box">
                <h3><i class="fas fa-file-invoice-dollar"></i> Финансовая сводка</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <i class="fas fa-tasks"></i>
                        <div>
                            <p class="summary-label">Всего работ</p>
                            <p class="summary-value"><?= $total_works ?></p>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-ruble-sign"></i>
                        <div>
                            <p class="summary-label">Общая сумма</p>
                            <p class="summary-value"><?= number_format($total_cost, 0, '.', ' ') ?> ₽</p>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-calculator"></i>
                        <div>
                            <p class="summary-label">Средний чек</p>
                            <p class="summary-value"><?= number_format($avg_cost, 0, '.', ' ') ?> ₽</p>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-chart-bar"></i>
                        <div>
                            <p class="summary-label">Месяцев работ</p>
                            <p class="summary-value"><?= count($monthly_stats) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="add-button-container">
            <a href="add_work.php?master_id=<?= $master_id ?>" class="btn btn-add">
                <i class="fas fa-plus-circle"></i> Добавить выполненную работу
            </a>
            <a href="schedule.php?master_id=<?= $master_id ?>" class="btn btn-schedule">
                <i class="fas fa-calendar-alt"></i> График работы
            </a>
            <a href="index.php" class="btn btn-back">
                <i class="fas fa-home"></i> На главную
            </a>
        </div>
    </div>
</body>
</html>