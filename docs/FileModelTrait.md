# Fine model trait

```php
use matrozov\yii2kit\traits\FindModelTrait;
```

Треит реализующий стандартизированный поиск и исключение в случае отсутствия записи

```php
class TestModel extends Model {
    use FindModelTrait;
}

TestModel::findModel(['id' => 1]);
```
