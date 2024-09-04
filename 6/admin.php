<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css"> <!-- Добавляем эту строку для подключения стилей -->
</head>
<body>
<?php

// Проверка HTTP-авторизации
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    md5($_SERVER['PHP_AUTH_PW']) != md5('123')) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

// Подключение к базе данных и выполнение запросов
$db = new PDO('mysql:host=localhost;dbname=web', 'root', '', array(PDO::ATTR_PERSISTENT => true));

// Извлечение данных пользователей
$stmt = $db->query("SELECT * FROM application");
$usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Вывод данных в виде таблицы
echo '<table border="1">';
echo '<tr><th>Имя</th><th>Телефон</th><th>Email</th><th>Год рождения</th><th>Пол</th><th>Биография</th><th>Языки программирования</th><th>Действия</th></tr>';
foreach ($usersData as $userData) {
    // Вывод данных пользователя в ячейки таблицы
    echo '<tr>';
    echo '<td>' . $userData['NAMES'] . '</td>';
    echo '<td>' . $userData['phones'] . '</td>';
    echo '<td>' . $userData['email'] . '</td>';
    echo '<td>' . $userData['dates'] . '</td>';
    echo '<td>' . $userData['gender'] . '</td>';
    echo '<td>' . $userData['biography'] . '</td>';

     // Извлечение языков программирования для данного пользователя 
     $stmt = $db->prepare("SELECT name_of_language FROM application_languages WHERE id = ?"); 
     $stmt->execute([$userData['id']]); 
     $userLanguages = $stmt->fetchAll(PDO::FETCH_COLUMN); 
     
     // Проверяем, есть ли языки и выводим их
     if ($userLanguages) { 
         echo '<td>' . implode(', ', array_map('htmlspecialchars', $userLanguages)) . '</td>'; 
         
         // Обновляем статистику по языкам
         foreach ($userLanguages as $language) {
             if (!isset($languageStats[$language])) {
                 $languageStats[$language] = 0;
             }
             $languageStats[$language]++;
         }
     } else { 
         echo '<td>Нет данных</td>'; // Если нет языков программирования 
     } 
 
     // Действия: редактирование и удаление 
     echo '<td><a href="edit_user.php?id=' . $userData['id'] . '">Редактировать</a> | <form action="delete_user.php" method="post"><input type="hidden" name="id" value="' . $userData['id'] . '"><input type="submit" value="Удалить"></form></td>'; 
     echo '</tr>'; 
 } 
 echo '</table>'; 
 
 // Вывод статистики по языкам программирования
 echo '<h3>Статистика по языкам программирования:</h3>';
 foreach ($languageStats as $language => $count) {
     echo htmlspecialchars($language) . ': ' . $count . ' пользователей<br>';
 }

?>