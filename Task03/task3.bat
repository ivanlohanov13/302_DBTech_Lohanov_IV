#!/bin/bash
@echo off
chcp 65001 > nul

echo "Создание базы данных movies_rating.db и заполнение данными..."
sqlite movies_rating.db < db_init.sql


echo ======================================================================
echo 1. Составить список фильмов, имеющих хотя бы одну оценку. Список фильмов отсортировать по году выпуска и по названиям. В списке оставить первые 10 фильмов.
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT DISTINCT m.id, m.title, m.year FROM movies m JOIN ratings r ON m.id = r.movie_id WHERE m.year IS NOT NULL ORDER BY m.year, m.title LIMIT 10;"
echo.

echo ======================================================================
echo 2. Вывести список всех пользователей, фамилии (не имена!) которых начинаются на букву 'A'. Полученный список отсортировать по дате регистрации. В списке оставить первых 5 пользователей.
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT id, name, email, gender, register_date, occupation FROM users WHERE SUBSTR(name, INSTR(name, ' ') + 1) LIKE 'A%%' ORDER BY register_date LIMIT 5;"
echo.

echo ======================================================================
echo 3. Написать запрос, возвращающий информацию о рейтингах в более читаемом формате: имя и фамилия эксперта, название фильма, год выпуска, оценка и дата оценки в формате ГГГГ-ММ-ДД.
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT u.name AS user_name, m.title AS movie_title, m.year, r.rating, strftime('%%Y-%%m-%%d', datetime(r.timestamp, 'unixepoch')) AS rating_date FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id ORDER BY u.name, m.title, r.rating LIMIT 50;"
echo.

echo ======================================================================
echo 4. Вывести список фильмов с указанием тегов, которые были им присвоены пользователями. Сортировать по году выпуска, затем по названию фильма, затем по тегу. В списке оставить первые 40 записей.
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT m.year, m.title, t.tag, u.name AS user_name FROM tags t JOIN movies m ON t.movie_id = m.id JOIN users u ON t.user_id = u.id WHERE m.year IS NOT NULL ORDER BY m.year, m.title, t.tag LIMIT 40;"
echo.

echo ======================================================================
echo 5. Вывести список самых свежих фильмов. В список должны войти все фильмы последнего года выпуска, имеющиеся в базе данных.
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT id, title, year, genres FROM movies WHERE year = (SELECT MAX(year) FROM movies) ORDER BY title;"
echo.

echo ======================================================================
echo 6. Найти все комедии, выпущенные после 2000 года, которые понравились мужчинам (оценка не ниже 4.5).
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT m.title, m.year, COUNT(*) as high_ratings_count FROM movies m JOIN ratings r ON m.id = r.movie_id JOIN users u ON r.user_id = u.id WHERE ('|' || m.genres || '|') GLOB '*|Comedy|*' AND m.year > 2000 AND u.gender = 'male' AND r.rating >= 4.5 GROUP BY m.id, m.title, m.year ORDER BY m.year, m.title;"
echo.

echo ======================================================================
echo 7. Провести анализ занятий (профессий) пользователей - вывести количество пользователей для каждого рода занятий.
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT occupation, COUNT(*) AS user_count FROM users GROUP BY occupation ORDER BY user_count DESC;"
echo.

echo ======================================================================
echo Самые распространенные профессии:
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT occupation, COUNT(*) AS user_count FROM users GROUP BY occupation HAVING COUNT(*) = (SELECT MAX(user_count) FROM (SELECT COUNT(*) AS user_count FROM users GROUP BY occupation));"
echo.

echo ======================================================================
echo Самые редкие профессии:
echo ----------------------------------------------------------------------
sqlite movies_rating.db -box -echo "SELECT occupation, COUNT(*) AS user_count FROM users GROUP BY occupation HAVING COUNT(*) = (SELECT MIN(user_count) FROM (SELECT COUNT(*) AS user_count FROM users GROUP BY occupation));"
echo.
