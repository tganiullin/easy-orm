<?php
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testExampleFileExists()
    {
        $this->assertFileExists('examples/basic_usage.php');
    }

    public function testExampleSyntax()
    {
        $output = shell_exec('php -l examples/basic_usage.php 2>&1');
        $this->assertStringContainsString('No syntax errors detected', $output);
    }

    public function testModelExample()
    {
        $this->assertFileExists('examples/model_example.php');
        
        if (file_exists('examples/model_example.php')) {
            $output = shell_exec('php -l examples/model_example.php 2>&1');
            $this->assertStringContainsString('No syntax errors detected', $output);
        }
    }

    public function testDatabaseMigrationExists()
    {
        $this->assertFileExists('database/migrations/create_users_table.sql');
    }

    public function testReadmeExists()
    {
        $this->assertFileExists('README.md');
        $content = file_get_contents('README.md');
        $this->assertStringContainsString('EasyORM', $content);
        $this->assertStringContainsString('composer require', $content);
    }

    public function testAnalysisExists()
    {
        $this->assertFileExists('ANALYSIS.md');
        $content = file_get_contents('ANALYSIS.md');
        $this->assertStringContainsString('Анализ и доработка', $content);
    }
}