<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tganiullin\EasyOrm\Config;
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

// Определение модели User
class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
    
    protected array $fillable = ['name', 'email', 'age', 'city', 'status'];
}

// Определение модели Post
class Post extends Model
{
    protected static string $table = 'posts';
    protected static string $primaryKey = 'id';
    
    protected array $fillable = ['title', 'content', 'user_id', 'status'];
}

echo "=== Model Examples ===\n";

try {
    // Получить всех пользователей
    $users = User::all();
    echo "Всего пользователей: " . count($users) . "\n";
    
    // Найти пользователя по ID
    $user = User::find(1);
    if ($user) {
        echo "Найден пользователь: " . $user->name . "\n";
    }
    
    // Создать нового пользователя
    $newUser = new User([
        'name' => 'Новый пользователь',
        'email' => 'new@example.com',
        'age' => 25,
        'city' => 'Москва'
    ]);
    
    // Сохранить пользователя (если БД доступна)
    // $newUser->save();
    
    // Поиск с условиями
    $activeUsers = User::where('status', '=', 'active')->get();
    echo "Активных пользователей: " . count($activeUsers) . "\n";
    
    // Поиск первого пользователя
    $firstUser = User::where('age', '>', 18)->first();
    if ($firstUser) {
        echo "Первый взрослый пользователь: " . $firstUser->name . "\n";
    }
    
    // Подсчет записей
    $count = User::where('city', '=', 'Москва')->count();
    echo "Пользователей из Москвы: " . $count . "\n";
    
    // Обновление пользователя
    if ($user) {
        $user->age = 26;
        // $user->save(); // Сохранить изменения
    }
    
    // Удаление пользователя
    // $user->delete();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Примечание: Для полноценной работы требуется настроенная база данных MySQL\n";
}

echo "\n=== Демонстрация завершена ===\n";
echo "Для полноценного тестирования настройте подключение к MySQL в Config::set()\n";