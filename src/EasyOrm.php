<?php
namespace Tganiullin\EasyOrm;

use mysqli;
use mysqli_result;
use Exception;

class EasyOrm
{
    protected mysqli $mysqli;
    private array $where = [];
    private array $orderBy = [];
    private array $bindings = [];
    private int $limit = 0;
    private int $offset = 0;
    public bool $debug = false;
    protected string $table = '';

    public function __construct(array $config = [])
    {
        $defaultConfig = Config::database();
        $config = array_merge($defaultConfig, $config);

        try {
            $this->mysqli = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );

            if ($this->mysqli->connect_error) {
                throw new Exception('Database connection failed: ' . $this->mysqli->connect_error);
            }

            $this->mysqli->set_charset($config['charset']);
        } catch (Exception $e) {
            throw new Exception('Failed to connect to database: ' . $e->getMessage());
        }
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    protected function query(string $sql, array $bindings = []): mysqli_result|bool
    {
        if ($this->debug) {
            echo "SQL: $sql" . PHP_EOL;
            echo "Bindings: " . json_encode($bindings) . PHP_EOL;
        }

        if (!empty($bindings)) {
            $stmt = $this->mysqli->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->mysqli->error);
            }

            if (!empty($bindings)) {
                $types = str_repeat('s', count($bindings));
                $stmt->bind_param($types, ...$bindings);
            }

            $result = $stmt->execute();
            if (!$result) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }

            $queryResult = $stmt->get_result();
            $stmt->close();
            
            return $queryResult ?: $result;
        }

        $result = $this->mysqli->query($sql);
        if (!$result) {
            throw new Exception('Query failed: ' . $this->mysqli->error);
        }

        return $result;
    }

    private function buildWhere(): array
    {
        if (empty($this->where)) {
            return ['', []];
        }

        $whereClause = ' WHERE ' . implode(' ', array_column($this->where, 'clause'));
        $bindings = [];
        
        foreach ($this->where as $condition) {
            if (isset($condition['binding'])) {
                $bindings[] = $condition['binding'];
            }
        }

        return [$whereClause, $bindings];
    }

    private function buildOrderBy(): string
    {
        return !empty($this->orderBy) ? ' ORDER BY ' . implode(', ', $this->orderBy) : '';
    }

    private function buildLimit(): string
    {
        if ($this->limit > 0) {
            $limitClause = ' LIMIT ' . $this->limit;
            if ($this->offset > 0) {
                $limitClause .= ' OFFSET ' . $this->offset;
            }
            return $limitClause;
        }
        return '';
    }

    public function where(string $column, string $operator, $value, string $boolean = 'AND', string $bracket = ''): static
    {
        $placeholder = '?';
        $clause = '';

        if (str_contains($bracket, '(')) {
            $clause .= '(';
        }

        if (!empty($this->where) && !str_contains($bracket, '(')) {
            $clause .= " $boolean ";
        }

        $clause .= "$column $operator $placeholder";

        if (str_contains($bracket, ')')) {
            $clause .= ')';
        }

        $this->where[] = [
            'clause' => $clause,
            'binding' => $value
        ];

        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): static
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $clause = '';

        if (!empty($this->where)) {
            $clause .= " $boolean ";
        }

        $clause .= "$column IN ($placeholders)";

        $this->where[] = [
            'clause' => $clause,
            'binding' => null
        ];

        foreach ($values as $value) {
            $this->bindings[] = $value;
        }

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new Exception('Invalid order direction. Use ASC or DESC.');
        }

        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): static
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function get(array $columns = ['*']): array
    {
        if (empty($this->table)) {
            throw new Exception('Table name is required');
        }

        $columnsList = implode(', ', $columns);
        [$whereClause, $whereBindings] = $this->buildWhere();
        $orderByClause = $this->buildOrderBy();
        $limitClause = $this->buildLimit();

        $sql = "SELECT $columnsList FROM {$this->table}$whereClause$orderByClause$limitClause";
        
        $allBindings = array_merge($whereBindings, $this->bindings);
        $result = $this->query($sql, $allBindings);

        $this->resetQuery();

        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    public function first(array $columns = ['*']): ?array
    {
        $results = $this->limit(1)->get($columns);
        return $results[0] ?? null;
    }

    public function count(): int
    {
        if (empty($this->table)) {
            throw new Exception('Table name is required');
        }

        [$whereClause, $whereBindings] = $this->buildWhere();
        $sql = "SELECT COUNT(*) as count FROM {$this->table}$whereClause";
        
        $allBindings = array_merge($whereBindings, $this->bindings);
        $result = $this->query($sql, $allBindings);

        $this->resetQuery();

        if ($result instanceof mysqli_result) {
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        }

        return 0;
    }

    public function insert(array $data): bool
    {
        if (empty($this->table)) {
            throw new Exception('Table name is required');
        }

        if (empty($data)) {
            throw new Exception('Data array cannot be empty');
        }

        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        $values = array_values($data);

        $columnsList = implode(', ', $columns);
        $sql = "INSERT INTO {$this->table} ($columnsList) VALUES ($placeholders)";

        $result = $this->query($sql, $values);
        $this->resetQuery();

        return $result !== false;
    }

    public function update(array $data): bool
    {
        if (empty($this->table)) {
            throw new Exception('Table name is required');
        }

        if (empty($data)) {
            throw new Exception('Data array cannot be empty');
        }

        if (empty($this->where)) {
            throw new Exception('WHERE clause is required for UPDATE operations');
        }

        $setParts = [];
        $setValues = [];

        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $setValues[] = $value;
        }

        [$whereClause, $whereBindings] = $this->buildWhere();
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$this->table} SET $setClause$whereClause";

        $allBindings = array_merge($setValues, $whereBindings, $this->bindings);
        $result = $this->query($sql, $allBindings);

        $this->resetQuery();

        return $result !== false;
    }

    public function delete(): bool
    {
        if (empty($this->table)) {
            throw new Exception('Table name is required');
        }

        if (empty($this->where)) {
            throw new Exception('WHERE clause is required for DELETE operations');
        }

        [$whereClause, $whereBindings] = $this->buildWhere();
        $sql = "DELETE FROM {$this->table}$whereClause";

        $allBindings = array_merge($whereBindings, $this->bindings);
        $result = $this->query($sql, $allBindings);

        $this->resetQuery();

        return $result !== false;
    }

    public function getLastInsertId(): int
    {
        return $this->mysqli->insert_id;
    }

    public function getAffectedRows(): int
    {
        return $this->mysqli->affected_rows;
    }

    public function beginTransaction(): bool
    {
        return $this->mysqli->begin_transaction();
    }

    public function commit(): bool
    {
        return $this->mysqli->commit();
    }

    public function rollback(): bool
    {
        return $this->mysqli->rollback();
    }

    private function resetQuery(): void
    {
        $this->where = [];
        $this->orderBy = [];
        $this->bindings = [];
        $this->limit = 0;
        $this->offset = 0;
    }

    public function __destruct()
    {
        if (isset($this->mysqli)) {
            $this->mysqli->close();
        }
    }
}