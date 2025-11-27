## Проверка окружения перед любыми операциями
1. Убедиться что контейнер базы запущен: `docker ps`
   Собрать если нет контейнера: `docker compose up -d db`

2. Проверить наличие нужной базы (по умолчанию `postgres`) внутри контейнера:
   `docker exec postgres psql -U root -lqt | grep postgres`

3. При отсутствии базы создать её:
   `docker exec postgres createdb -U root postgres`

## Создание новой миграции
1. php spark make:migration NameMigration

2. Добавить `up()` и `down()`.

## Накатывание миграций в контейнерную БД
1. Выполнить проверку окружения из раздела выше.

2. Запустить миграции в локальном PHP (установить pgsql/pdo_pgsql/intl):
   `php spark migrate`

## Откат миграций
1. Выполнить откат: `php spark migrate:rollback`

2. Для полного сброса можно использовать: `php spark migrate:refresh`.

## Просмотр данных в базе контейнера
1. Открыть psql в контейнере:
  `docker exec -it postgres psql -U root -d postgres`

2. Вывод содержимого таблицы files:
  `docker exec postgres psql -U root -d postgres -c "SELECT * FROM files LIMIT 10;"`

