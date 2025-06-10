# EasyORM

Простая и безопасная ORM библиотека для PHP с поддержкой MySQL.

## Особенности

- ✅ **Безопасность**: Защита от SQL-инъекций через prepared statements
- ✅ **Гибкая конфигурация**: Настраиваемые параметры подключения к БД
- ✅ **Fluent Interface**: Удобный синтаксис для построения запросов
- ✅ **Active Record**: Работа с моделями данных
- ✅ **Обработка ошибок**: Исключения для лучшего контроля ошибок
- ✅ **Дополнительные методы**: limit, offset, count, first, whereIn

## Установка

```bash
composer require tganiullin/easy-orm
```

## Быстрый старт

### Конфигурация

```php
use Tganiullin\EasyOrm\Config;

Config::set([
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'password',
        'database' => 'mydb',
        'port' => 3306,
        'charset' => 'utf8mb4'
    ]
]);
```

### Использование Query Builder

```php
use Tganiullin\EasyOrm\EasyOrm;

$orm = new EasyOrm();

// Получить все записи
$users = $orm->table('users')->get();

// Получить определенные столбцы
$users = $orm->table('users')->get(['id', 'name', 'email']);

// С условиями
$users = $orm->table('users')
    ->where('age', '>', 18)
    ->where('status', '=', 'active')
    ->get();

// Сложные условия со скобками
$users = $orm->table('users')
    ->where('name', '=', 'John', 'AND', '(')
    ->where('age', '>', 25, 'OR')
    ->where('city', '=', 'Moscow', 'AND', ')')
    ->get();

// С сортировкой и лимитом
$users = $orm->table('users')
    ->where('status', '=', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(10, 20) // LIMIT 10 OFFSET 20
    ->get();

// Получить первую запись
$user = $orm->table('users')
    ->where('email', '=', 'user@example.com')
    ->first();

// Подсчет записей
$count = $orm->table('users')
    ->where('status', '=', 'active')
    ->count();

// WHERE IN
$users = $orm->table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();
```

### CRUD операции

```php
// Создание записи
$result = $orm->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

// Получение ID последней вставленной записи
$lastId = $orm->getLastInsertId();

// Обновление записи
$result = $orm->table('users')
    ->where('id', '=', 1)
    ->update([
        'name' => 'Jane Doe',
        'age' => 25
    ]);

// Удаление записи
$result = $orm->table('users')
    ->where('id', '=', 1)
    ->delete();

// Количество затронутых строк
$affectedRows = $orm->getAffectedRows();
```

### Транзакции

```php
$orm->beginTransaction();

try {
    $orm->table('users')->insert([
        'name' => 'Transactional User',
        'email' => 'tx@example.com'
    ]);
    $orm->commit();
} catch (Exception $e) {
    $orm->rollback();
    throw $e;
}
```

### Использование моделей (Active Record)

```php
use Tganiullin\EasyOrm\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
}

// Получить все записи
$users = User::all();

// Найти по ID
$user = User::find(1);

// Создать новую запись
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Работа с экземпляром модели
$user = new User();
$user->name = 'Jane Doe';
$user->email = 'jane@example.com';
$user->save();

// Обновление
$user = User::find(1);
$user->name = 'Updated Name';
$user->save();

// Удаление
$user = User::find(1);
$user->delete();

// Запросы через модель
$activeUsers = User::where('status', '=', 'active')
    ->orderBy('created_at', 'DESC')
    ->get();

// Доступ к атрибутам
echo $user->name;
echo $user->email;

// Преобразование в массив
$userData = $user->toArray();
```

### Отладка

```php
$orm = new EasyOrm();
$orm->debug = true; // Включить вывод SQL запросов

$users = $orm->table('users')->get();
// Выведет: SQL: SELECT * FROM users
// Выведет: Bindings: []
```

## Безопасность

Все пользовательские данные автоматически экранируются через prepared statements:

```php
// Безопасно - защищено от SQL-инъекций
$users = $orm->table('users')
    ->where('name', '=', $_POST['name'])
    ->get();
```

## Обработка ошибок

```php
try {
    $users = $orm->table('users')->get();
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage();
}
```

## Требования

- PHP 8.0+
- MySQL 5.7+
- Расширение mysqli

## Лицензия

MIT License