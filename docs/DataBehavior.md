# Data behavior

```php
use matrozov\yii2common\behaviors\DataBehavior
```

Реализует поведение хранения вложенных доп.свойств класса внутри json поля.

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
                'class' => matrozov\yii2common\behaviors\DataBehavior::class,
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
            [['field1', 'field2'], 'required'],        
        ]; 
    }  
}

$test = new TestModel();
$test->field1 = 'test';
```
