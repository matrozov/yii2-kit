# Json safe behavior

```php
use matrozov\yii2kit\behaviors\JsonSafeBehavior;
```

Поведение реализующее валидацию и преобразование строковых значений внутри массива
для хранения в виде json.

Валидация происходит по схеме базы данных при указании, что тип поле является json.

```php
class TestModel extends Model {
    public function behaviors(): array
    {
        return [
            [
                'class' => matrozov\yii2kit\behaviors\JsonSafeBehavior::class,
            ],
        ];       
    }
}
```
