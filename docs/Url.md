# Url helper

```php
use matrozov\yii2common\helpers\Url
```

Хелпер реализующий доп.функции работы с URL

## Парсинг DataUri

```php
$dataUri = 'data:...';

URL::parseDataUri($dataUri, $type, $attributes, $data);
```
