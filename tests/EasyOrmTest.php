<?php
use PHPUnit\Framework\TestCase;
use Tganiullin\EasyOrm\EasyOrm;
use Tganiullin\EasyOrm\Config;

class EasyOrmTest extends TestCase
{
    private EasyOrm $orm;

    protected function setUp(): void
    {
        // Настройка тестовой конфигурации
        Config::set([
            'database' => [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'test_db',
                'port' => 3306,
                'charset' => 'utf8mb4'
            ]
        ]);

        $this->orm = new EasyOrm();
    }

    public function testTableMethod()
    {
        $result = $this->orm->table('users');
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testWhereMethod()
    {
        $result = $this->orm->table('users')->where('id', '=', 1);
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testOrderByMethod()
    {
        $result = $this->orm->table('users')->orderBy('name', 'ASC');
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testLimitMethod()
    {
        $result = $this->orm->table('users')->limit(10, 5);
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testInvalidOrderDirection()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid order direction. Use ASC or DESC.');
        
        $this->orm->table('users')->orderBy('name', 'INVALID');
    }

    public function testEmptyTableNameThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Table name is required');
        
        $this->orm->get();
    }

    public function testUpdateWithoutWhereThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WHERE clause is required for UPDATE operations');
        
        $this->orm->table('users')->update(['name' => 'test']);
    }

    public function testDeleteWithoutWhereThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WHERE clause is required for DELETE operations');
        
        $this->orm->table('users')->delete();
    }

    public function testInsertWithEmptyDataThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Data array cannot be empty');
        
        $this->orm->table('users')->insert([]);
    }

    public function testUpdateWithEmptyDataThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Data array cannot be empty');
        
        $this->orm->table('users')->where('id', '=', 1)->update([]);
    }

    public function testWhereInWithEmptyArray()
    {
        $result = $this->orm->table('users')->whereIn('id', []);
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testFluentInterface()
    {
        $result = $this->orm
            ->table('users')
            ->where('age', '>', 18)
            ->where('status', '=', 'active')
            ->orderBy('name', 'ASC')
            ->limit(10);
        
        $this->assertInstanceOf(EasyOrm::class, $result);
    }
}