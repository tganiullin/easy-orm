<?php
use PHPUnit\Framework\TestCase;
use Tganiullin\EasyOrm\EasyOrm;
use Tganiullin\EasyOrm\Config;

class EasyOrmTest extends TestCase
{
    private EasyOrm $orm;

    protected function setUp(): void
    {
        // Skip database connection for unit tests
        // We'll test the query building logic without actual database operations
    }

    public function testConfigurationClass()
    {
        // Test Config class functionality
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

        $config = Config::get('database');
        $this->assertIsArray($config);
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals('test_db', $config['database']);
    }

    public function testConfigGetWithDefault()
    {
        $default = ['default' => 'value'];
        $result = Config::get('nonexistent', $default);
        $this->assertEquals($default, $result);
    }

    public function testInvalidOrderDirection()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid order direction. Use ASC or DESC.');
        
        // Create a mock ORM that doesn't connect to database
        $orm = $this->createMockOrm();
        $orm->table('users')->orderBy('name', 'INVALID');
    }

    public function testEmptyTableNameThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Table name is required');
        
        $orm = $this->createMockOrm();
        $orm->get();
    }

    public function testUpdateWithoutWhereThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WHERE clause is required for UPDATE operations');
        
        $orm = $this->createMockOrm();
        $orm->table('users')->update(['name' => 'test']);
    }

    public function testDeleteWithoutWhereThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WHERE clause is required for DELETE operations');
        
        $orm = $this->createMockOrm();
        $orm->table('users')->delete();
    }

    public function testInsertWithEmptyDataThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Data array cannot be empty');
        
        $orm = $this->createMockOrm();
        $orm->table('users')->insert([]);
    }

    public function testUpdateWithEmptyDataThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Data array cannot be empty');
        
        $orm = $this->createMockOrm();
        $orm->table('users')->where('id', '=', 1)->update([]);
    }

    public function testFluentInterface()
    {
        $orm = $this->createMockOrm();
        $result = $orm
            ->table('users')
            ->where('age', '>', 18)
            ->where('status', '=', 'active')
            ->orderBy('name', 'ASC')
            ->limit(10);
        
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testTableMethod()
    {
        $orm = $this->createMockOrm();
        $result = $orm->table('users');
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testWhereMethod()
    {
        $orm = $this->createMockOrm();
        $result = $orm->table('users')->where('id', '=', 1);
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testOrderByMethod()
    {
        $orm = $this->createMockOrm();
        $result = $orm->table('users')->orderBy('name', 'ASC');
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testLimitMethod()
    {
        $orm = $this->createMockOrm();
        $result = $orm->table('users')->limit(10, 5);
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testWhereInWithEmptyArray()
    {
        $orm = $this->createMockOrm();
        $result = $orm->table('users')->whereIn('id', []);
        $this->assertInstanceOf(EasyOrm::class, $result);
    }

    public function testOrderByValidDirections()
    {
        $orm = $this->createMockOrm();
        
        // Test ASC
        $result1 = $orm->table('users')->orderBy('name', 'ASC');
        $this->assertInstanceOf(EasyOrm::class, $result1);
        
        // Test DESC
        $result2 = $orm->table('users')->orderBy('name', 'DESC');
        $this->assertInstanceOf(EasyOrm::class, $result2);
        
        // Test lowercase
        $result3 = $orm->table('users')->orderBy('name', 'asc');
        $this->assertInstanceOf(EasyOrm::class, $result3);
    }

    /**
     * Create a mock ORM instance that doesn't connect to database
     */
    private function createMockOrm(): EasyOrm
    {
        // Create a partial mock that skips the constructor
        $orm = $this->getMockBuilder(EasyOrm::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['query'])
                    ->getMock();

        // Mock the query method to avoid database calls
        $orm->method('query')->willReturn(true);

        return $orm;
    }
}