# easy-orm


```php
$easyorm = new Tganiullin\EasyOrm\EasyOrm();
$easyorm->table = 'users';
```

Получить записи:
```php
$users = $easyorm->get();

//Определенные столбцы
$users = $easyorm->get(['id', 'firstname']);

//Сортировка
$users = $easyorm
            ->orderBy('age', 'ASC')
            ->get();

//Условие для выборки
$users = $easyorm
            ->where('firstname', '=', 'Тимур')
            ->get();

//Более сложная выборка
//Имя Тимур и фамилия Ганиуллин
$users = $easyorm
            ->where('firstname', '=', 'Тимур', 'AND')
            ->where('lastname', '=', 'Ганиуллин')
            ->get();

//Еще более сложная выборка со скобками
//(Имя Тимур либо фамилия Ганиуллин) и возраст 19
$users = $easyorm
            ->where('firstname', '=', 'Тимур', 'OR', '(')
            ->where('lastname', '=', 'Ганиуллин', 'AND', ')')
            ->where('age', '=', '19')
            ->get();
```

Добавить запись
```php
$easyorm
    ->insert([
        'firstname' = 'Timur',
        'lastname' = 'Ganiullin',
    ]);
```

Редактировать запись
```php
$easyorm
    ->where('id', '=', 10)
    ->update([
        'firstname' => 'Andrey',
        'age' => 20,
    ]);
```

Удалить запись
```php
$easyorm
    ->where('id', '=', 10)
    ->delete();
```