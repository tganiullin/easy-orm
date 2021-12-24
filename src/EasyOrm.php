<?php
namespace Tganiullin\EasyOrm;

use mysqli;

class EasyOrm{
    private mysqli $mysqli;
    public bool $debug = false;
    protected string $table;

    public function __construct() {
        $mysqli = new mysqli('localhost', 'root', 'GH1281w!', 'ekaterina_zhuravleva');
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $this->mysqli = $mysqli;
    }

    public function insertId() {
        return $this->mysqli->insert_id;
    }

    /**
     * Построить where
     * @param array $params
     * @return string|null
     */
    private function where(array $params) {
        if (!empty($params)) {
            foreach ($params as $key => $param) {
                $open_bracket = null;
                $close_bracket = null;

                $many = isset($param[3]) ? $param[3] : null;
                if (isset($param[4])) {
                    $bracket = $param[4];
                    $open_bracket = $bracket == '(' ? '(' : null;
                    $close_bracket = $bracket == ')' ? ')' : null;
                }
                $where[] = $open_bracket . $param[0] . $param[1] . '\'' . $param[2] . '\'' . $close_bracket . ' ' . $many;
            }
            $where = ' WHERE ' . implode(' ', $where);
        } else {
            $where = null;
        }
        return $where;
    }

    protected function query($sql) {
        if ($this->debug) echo $sql . PHP_EOL;
        return $this->mysqli->query($sql);
    }

    protected function select(array $where = []) {
        $where = $this->where($where);
        $sql = 'SELECT * FROM ' . $this->table . $where; //Формируем SQL
        return $this->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    protected function insert(array $params) {
        foreach ($params as $column => $value) {
            $columns[] = $column;
            $values[] = '\'' . $value . '\'';
        }

        $columns = implode(', ', $columns);
        $values = implode(', ', $values);

        $sql = 'INSERT INTO ' . $this->table . ' (' . $columns . ') VALUES (' . $values . ')';
        return $this->query($sql);
    }

    protected function update(array $where, array $params) {
        foreach ($params as $column => $value) {
            $values[] = $column . ' = ' . '\'' . $value . '\'';
        }
        $values = implode(', ', $values);
        $where = $this->where($where);
        $sql = 'UPDATE ' . $this->table . ' SET ' . $values . $where;
        return $this->query($sql);
    }

    protected function delete(array $where) {
        $where = $this->where($where);
        $sql = 'DELETE FROM ' . $this->table . $where;
        return $this->query($sql);
    }
}