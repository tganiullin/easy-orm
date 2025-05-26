<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tganiullin\EasyOrm\Config;
use Tganiullin\EasyOrm\EasyOrm;
use Tganiullin\EasyOrm\Model;

// Настройка конфигурации
Config::set([
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'password',
        'database' => 'test_db',
        'port' => 3306,
        'charset' => 'utf8mb4'
    ]
]);

// Пример использования Query Builder
echo "=== Query Builder Examples ===\n";

try {
    $orm = new EasyOrm();
    
    // Получить всех пользователей
    $users = $orm->table('users')->get();
    echo "Всего пользователей: " . count($users) . "\n";
    
    // Получить пользователей старше 18 лет
    $adults = $orm->table('users')
        ->where('age', '>', 18)
        ->orderBy('age', 'DESC')
        ->get();
    echo "Пользователей старше 18: " . count($adults) . "\n";
    
    // Создать нового пользователя
    $result = $orm->table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 25
    ]);
    
    if ($result) {
        echo "Пользователь создан с ID: " . $orm->getLastInsertId() . "\n";
    }
    
    // Обновить пользователя
    $updated = $orm->table('users')
        ->where('email', '=', 'john@example.com')
        ->update(['age' => 26]);
    
    if ($updated) {
        echo "Пользователь обновлен\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

// Пример модели
class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
}

echo "\n=== Model Examples ===\n";

try {
    // Получить всех пользователей через модель
    $users = User::all();
    echo "Всего пользователей (через модель): " . count($users) . "\n";
    
    // Найти пользователя по ID
    $user = User::find(1);
    if ($user) {
        echo "Найден пользователь: " . $user->name . "\n";
    }
    
    // Создать нового пользователя через модель
    $newUser = User::create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'age' => 30
    ]);
    echo "Создан пользователь: " . $newUser->name . " с ID: " . $newUser->id . "\n";
    
    // Обновить пользователя
    $newUser->age = 31;
    $newUser->save();
    echo "Возраст пользователя обновлен\n";
    
    // Запрос через модель
    $activeUsers = User::where('age', '>', 20)
        ->orderBy('name', 'ASC')
        ->get();
    echo "Пользователей старше 20: " . count($activeUsers) . "\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== Advanced Examples ===\n";

try {
    $orm = new EasyOrm();
    
    // Сложный запрос с группировкой условий
    $users = $orm->table('users')
        ->where('age', '>', 18, 'AND', '(')
        ->where('city', '=', 'Moscow', 'OR')
        ->where('city', '=', 'SPB', 'AND', ')')
        ->where('status', '=', 'active')
        ->limit(10)
        ->get();
    
    echo "Результат сложного запроса: " . count($users) . " записей\n";
    
    // WHERE IN
    $specificUsers = $orm->table('users')
        ->whereIn('id', [1, 2, 3, 4, 5])
        ->get();
    
    echo "Пользователи с ID 1-5: " . count($specificUsers) . " записей\n";
    
    // Подсчет записей
    $count = $orm->table('users')
        ->where('status', '=', 'active')
        ->count();
    
    echo "Активных пользователей: " . $count . "\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}