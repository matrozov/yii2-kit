# Model validation exception

```php
use matrozov\yii2common\exceptions\ModelValidationException
```

Вид исключения для обработки внутренних ошибок валидации модели.
Реализует вывод поля ошибки и его значения.

```php
class TestModel extends Model {}

$test = new TestModel();

if (!$test->save()) {
    throw new ModelValidationException($test, 'optional error message');
}
```
