# File support

```php
use matrozov\yii2common\components\Storage
use matrozov\yii2common\components\LocalStorage
use matrozov\yii2common\behaviors\FileBehavior
use matrozov\yii2common\behaviors\FilesBehavior
use matrozov\yii2common\interfaces\FileTargetClassInterface
use matrozov\yii2common\traits\FileTargetClassTrait
use matrozov\yii2common\models\File
use matrozov\yii2common\migrations\file
```

Набор классов реализующий операции, хранение и связь с моделями с файлами.

## Storage и LocalStorage

Storage и LocalStorage - абстрактный компонент и его реализация для хранения
файлов локально. Реализует необходимый уровень абстракции для хранения файлов как
локально, так и извне.

```php
// config.php
return [
    'components' => [
        'storage' => [
            'class' => 'matrozov\yii2common\components\storages\LocalStorage',
            'path' => '@app/storage', // Путь по умолчанию
        ],
    ],
];

//

Yii::$app->storage->write('myFile', 'file-content');
$content = Yii::$app->storage->read('myFile');

```

## FileBehavior и FilesBehavior

FileBehavior и FilesBehavior - поведение реализующее привязку файлов к модели.
Реализует как сохранение единичного файла, так и множества файлов в рамках одного
поля. Поддерживает релейшены и проактивную загрузку метаданных файлов вместе с
основной моделью.

```php

// Test.php
/**
 * @property File $singleFile
 * @method ActiveQuery $getSingleFile
 * @see FileBehavior
 *
 * @property File[] $multipleFiles
 * @method ActiveQuery $getMultipleFiles
 * @see FilesBehavior 
 */
class TestModel extends Model {
    public function behaviors(): array
    {
        return [
            [
                'class' => FileBehavior::class,
                'attribute' => 'singleFile',
            ],
            [
                'class' => FilesBehavior::class,
                'attribute' => 'multipleFiles',
            ],
        ];           
    }
}

$test = new TestModel();
$test->singleFile = // 
```

## FileTargetClassInterface и FileTargetClassTrait

FileTargetClassInterface и FileTargetClassTrait - реализуют возможность переназначения
класса, к которому привязывается класс. Это необходимый фнукционал при использовании
множественных расширений базового класса.

```php
/**
 * @property File $file
 */
abstract class BaseModel extends Model implements FileTargetClassInterface {
    use FileTargetClassTrait;
    
    public function behaviors(): array
    {
        return [
            [
                'class' => FileBehavior::class,    
                'attribute' => 'file',
            ],
        ];
    }
}

class TestModel extends BaseModel {}

$test = new TestModel();
$test->file = 'https://example.com/image.jpg';
$test->save();
// Файл будет сохранён в реализации привязки к основной модели, а не дочерней. Это позволяет
// использовать пакетную загрузку метаданнных файлов вместе с загрузкой основной модели 

$models = BaseModel::find()->with('file')->all();
// Метаданные файлов будут пакетно загружены в рамках загрузки основных можелей
... = $models[0]->file;
```

## File

```php
class MyFile extends File {

}
```
