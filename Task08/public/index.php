<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>СТО - Автосервис | Управление персоналом</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-car"></i> Управление персоналом СТО "АвтоДоктор"</h1>
        
        <?php
        require_once __DIR__ . '/../db.php';
        
        try {
            $stmt = $pdo->query("SELECT * FROM masters ORDER BY surname, firstname");
            $masters = $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Ошибка при загрузке данных: ' . $e->getMessage());
        }
        ?>
        
        <?php if (empty($masters)): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-triangle"></i> Мастера не найдены. Добавьте первого мастера.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Специализация</th>
                            <th>Контакты</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($masters as $master): ?>
                        <tr>
                            <td><strong>#<?= $master['id'] ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($master['surname'] . ' ' . $master['firstname']) ?></strong>
                                <?php if (!empty($master['patronymic'])): ?>
                                    <br><span class="text-muted"><?= htmlspecialchars($master['patronymic']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-regular"><?= htmlspecialchars($master['specialization']) ?></span>
                                <?php if (!empty($master['hire_date'])): ?>
                                    <br><small>С <?= date('d.m.Y', strtotime($master['hire_date'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($master['email'])): ?>
                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($master['email']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($master['phone'])): ?>
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($master['phone']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($master['status'] === 'active'): ?>
                                    <span class="badge badge-active">Активен</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Не активен</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="edit_master.php?id=<?= $master['id'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Редактировать
                                </a>
                                <a href="delete_master.php?id=<?= $master['id'] ?>" class="btn btn-delete">
                                    <i class="fas fa-trash"></i> Удалить
                                </a>
                                <a href="schedule.php?master_id=<?= $master['id'] ?>" class="btn btn-schedule">
                                    <i class="fas fa-calendar-alt"></i> График
                                </a>
                                <a href="works.php?master_id=<?= $master['id'] ?>" class="btn btn-works">
                                    <i class="fas fa-tools"></i> Работы
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="stats-box">
                <p><i class="fas fa-chart-bar"></i> Всего мастеров: <?= count($masters) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="add-button-container">
            <a href="add_master.php" class="btn btn-add">
                <i class="fas fa-user-plus"></i> Добавить нового мастера
            </a>
        </div>
    </div>
</body>
</html>