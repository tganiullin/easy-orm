<?php
namespace Tganiullin\EasyOrm;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function query(): EasyOrm
    {
        $orm = new EasyOrm(Config::database());
        return $orm->table(static::getTable());
    }

    public static function all(array $columns = ['*']): array
    {
        return static::query()->get($columns);
    }

    public static function find($id): ?static
    {
        $result = static::query()
            ->where(static::$primaryKey, '=', $id)
            ->first();

        if ($result) {
            $model = new static($result);
            $model->exists = true;
            $model->original = $result;
            return $model;
        }

        return null;
    }

    public static function where(string $column, string $operator, $value, string $boolean = 'AND'): EasyOrm
    {
        return static::query()->where($column, $operator, $value, $boolean);
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    protected function insert(): bool
    {
        $result = static::query()->insert($this->attributes);
        
        if ($result) {
            $this->exists = true;
            $this->original = $this->attributes;
            
            // Получаем ID последней вставленной записи
            $orm = new EasyOrm(Config::database());
            $lastId = $orm->getLastInsertId();
            if ($lastId > 0) {
                $this->attributes[static::$primaryKey] = $lastId;
            }
        }

        return $result;
    }

    protected function update(): bool
    {
        $changes = $this->getDirty();
        
        if (empty($changes)) {
            return true; // Нет изменений
        }

        $result = static::query()
            ->where(static::$primaryKey, '=', $this->getKey())
            ->update($changes);

        if ($result) {
            $this->original = $this->attributes;
        }

        return $result;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $result = static::query()
            ->where(static::$primaryKey, '=', $this->getKey())
            ->delete();

        if ($result) {
            $this->exists = false;
        }

        return $result;
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function setAttribute(string $key, $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getKey()
    {
        return $this->getAttribute(static::$primaryKey);
    }

    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __get(string $name)
    {
        return $this->getAttribute($name);
    }

    public function __set(string $name, $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    protected static function getTable(): string
    {
        if (!empty(static::$table)) {
            return static::$table;
        }

        // Автоматическое определение имени таблицы из имени класса
        $className = (new \ReflectionClass(static::class))->getShortName();
        return strtolower($className) . 's';
    }
}