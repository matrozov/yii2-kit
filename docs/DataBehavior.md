# Data behavior

```php
use matrozov\yii2kit\behaviors\DataBehavior;
```

Реализует поведение хранения вложенных доп.свойств класса внутри json поля (по умолчанию к названию вложенного поля
в attributes добавляется префикс в виде названия самого поля хранения targetAttribute с разделителем в виде символа "_":

```php
/**
 * Описывайте вложенные поля в php-doc для удобства разработки 
 * @property string $data_field1
 * @property string $data_field2
 */
class TestModel extends Model
{
    public function behaviors(): array
    {
        return [
            [
                'class' => matrozov\yii2kit\behaviors\DataBehavior::class,
                'targetAttribute' => 'data', // По умолчанию
                'attributes' => [
                    'field1',
                    'field2',                
                ],                       
            ],        
        ];       
    }
     
    // Вы можете валидировать вложенные поля как и обычные
    public function rules(): array
    {
        return [
            [['data_field1', 'data_field2'], 'required'],        
        ]; 
    }  
}

$test = new TestModel();
$test->data_field1 = 'test';
```

Пример с использованием произвольного префикса:

```php
/**
 * Описывайте вложенные поля в php-doc для удобства разработки 
 * @property string $myPrefix_field1
 * @property string $myPrefix_field2
 */
class TestModel extends Model
{
    public function behaviors(): array
    {
        return [
            [
                'class' => matrozov\yii2kit\behaviors\DataBehavior::class,
                'targetAttribute' => 'data', // По умолчанию
                'attributes' => [
                    'field1',
                    'field2',                
                ],
                'prefix' => 'myPrefix_',
            ],        
        ];       
    }
     
    // Вы можете валидировать вложенные поля как и обычные
    public function rules(): array
    {
        return [
            [['myPrefix_field1', 'myPrefix_field2'], 'required'],        
        ]; 
    }  
}

$test = new TestModel();
$test->myPrefix_field1 = 'test';
```

Пример с отключённым префиксом:

```php
/**
 * Описывайте вложенные поля в php-doc для удобства разработки 
 * @property string $field1
 * @property string $field2
 */
class TestModel extends Model {
    public function behaviors(): array
    {
        return [
            [
                'class' => matrozov\yii2kit\behaviors\DataBehavior::class,
                'targetAttribute' => 'data', // По умолчанию
                'attributes' => [
                    'field1',
                    'field2',                
                ],
                'prefix' => '',
            ],        
        ];       
    }
     
    // Вы можете валидировать вложенные поля как и обычные
    public function rules(): array
    {
        return [
            [['field1', 'field2'], 'required'],        
        ]; 
    }  
}

$test = new TestModel();
$test->field1 = 'test';
```
