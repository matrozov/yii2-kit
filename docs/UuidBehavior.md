# Uuid behavior

```php
use matrozov\yii2kit\behaviors\UuidBehavior;
```

Поведение реализующее автозаполнения поля id (по умолчанию) автогенерацией UUIDv4.

```php
class TestModel extends Model
{
    public string $id = '';

    public function behaviors(): array
    {
        return [
            [
                'class' => matrozov\yii2kit\behaviors\UuidBehavior::class,
                'attributes' => ['id'], // По умолчанию
            ],
        ];
    }
}
```
