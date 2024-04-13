# File helper

```php
use matrozov\yii2common\helpers\FileHelper;
```

Хелпер реализующий доп.функции работы с файлами

## Парсинг DataUri

```php
$name           = 'myFile.txt';
$content        = 'test';
$magicFile      = null;
$checkExtension = true;

FileHelper::getMimeTypeByContent($name, $content, $magicFile, $checkExtension);
```
