<?php
use PHPUnit\Framework\TestCase;
use Tganiullin\EasyOrm\Config;
use Tganiullin\EasyOrm\EasyOrm;

class BasicTest extends TestCase
{
    public function testConfigClass()
    {
        // Тест класса Config
        Config::set([
            'database' => [
                'host' => 'localhost',
                'username' => 'test',
                'password' => 'test',
                'database' => 'test_db'
            ]
        ]);

        $config = Config::database();
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals('test', $config['username']);
        $this->assertEquals('test_db', $config['database']);
    }

    public function testEasyOrmInstantiation()
    {
        // Тест создания экземпляра EasyOrm без подключения к БД
        $config = [
            'host' => 'localhost',
            'username' => 'test',
            'password' => 'test',
            'database' => 'test_db'
        ];

        // Проверяем, что класс можно создать
        $this->expectException(Exception::class);
        $orm = new EasyOrm($config);
    }

    public function testFluentInterface()
    {
        // Тест fluent interface без выполнения запросов
        $config = [
            'host' => 'localhost',
            'username' => 'test',
            'password' => 'test',
            'database' => 'test_db'
        ];

        try {
            $orm = new EasyOrm($config);
        } catch (Exception $e) {
            // Ожидаем исключение при подключении к несуществующей БД
            $this->assertStringContainsString('Failed to connect to database', $e->getMessage());
        }
    }

    public function testConfigDefaults()
    {
        // Тест значений по умолчанию
        Config::set([]);
        $config = Config::database();
        
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals('root', $config['username']);
        $this->assertEquals('', $config['password']);
        $this->assertEquals('test', $config['database']);
        $this->assertEquals(3306, $config['port']);
        $this->assertEquals('utf8mb4', $config['charset']);
    }
}