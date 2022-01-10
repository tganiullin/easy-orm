<?php
namespace Tganiullin\EasyOrm;

use mysqli;
use mysqli_result;

class EasyOrm{
    protected mysqli $mysqli;
    private array $where = [];
    private array $orderBy = [];
    public bool $debug = false;
    protected string $table = 'users';

    public function __construct() {
        $mysqli = new mysqli('217.182.46.178', 'root', 'pass', 'testdb');
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $this->mysqli = $mysqli;
    }

    protected function query($sql){
        if ($this->debug) echo $sql . PHP_EOL;
        return $this->mysqli->query($sql);
    }

    private function buildWhere(): ?string {
        return !empty($this->where) ? ' WHERE ' . implode(' ', $this->where) : null;
    }

    private function buildOrderBy(): ?string {
        return !empty($this->orderBy) ? ' ORDER BY ' . implode(', ', $this->orderBy) : null;
    }

    public function where($column, $mark, $value, $operator = null, $bracket = null): static {
        $open_bracket = null;
        $close_bracket = null;
        $operator = $operator ?? null;
        if(isset($bracket)){
            $open_bracket = $bracket == '(' ? '(' : null;
            $close_bracket = $bracket == ')' ? ')' : null;
        }
        $this->where[] = $open_bracket . $column . $mark . '\'' . $value . '\'' . $close_bracket . ' ' . $operator;
        return $this;
    }

    public function orderBy($column, $order = 'ASC'): static {
        $this->orderBy[] = $column . ' ' . $order;
        return $this;
    }

    public function update(array $params): mysqli_result|bool {
        foreach ($params as $column => $value) {
            $values[] = $column . ' = ' . '\'' . $value . '\'';
        }
        $values = implode(', ', $values);

        $sql = 'UPDATE ' . $this->table . ' SET ' . $values . $this->buildWhere();
        return $this->query($sql);
    }

    public function get(array $columns = null){
        $c = [];
        if(!empty($columns)) {
            foreach ($columns as $key => $column) {
                $c[] = is_int($key) ? $column : $key . ' as ' . $column;
            }
            $c = implode(', ', $c);
        }else{
            $c = '*';
        }
        $sql = 'SELECT '.$c.' FROM ' . $this->table . $this->buildWhere() . $this->buildOrderBy(); //Формируем SQL
        return $this->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(array $params){
        foreach($params as $column => $value){
            $columns[] = $column;
            $values[] = '\'' . $value . '\'';
        }
        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $sql = 'INSERT INTO '.$this->table.' ('.$columns.') VALUES ('.$values.')';
        return $this->query($sql);
    }

    public function delete(){
        $sql = 'DELETE FROM ' . $this->table . $this->buildWhere();
        return $this->query($sql);
    }
}