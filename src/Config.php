<?php
namespace Tganiullin\EasyOrm;

class Config
{
    private static array $config = [];

    public static function set(array $config): void
    {
        self::$config = $config;
    }

    public static function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return self::$config;
        }

        return self::$config[$key] ?? $default;
    }

    public static function database(): array
    {
        return self::get('database', [
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'test',
            'port' => 3306,
            'charset' => 'utf8mb4'
        ]);
    }
}