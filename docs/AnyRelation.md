# AnyRelation

```php
use matrozov\yii2kit\traits\AnyRelationTrait
```
Реализует набор методов hasAnyOne/hasAnyMany для возможности организации отношения (по аналогии с hasOne/hasAny)
со сложным условием.

Решение поддерживает ограниченную подгрузку данных связанных сущностей через with(), что позволяет оптимизировать
получение связанных данных по аналогии с аналогичным решением в ActiveQuery.

Так как AnyRelationQuery не является в чисто виде ActiveQuery, а
только имитацией его, то для таких соотношений невозможно использовать кастомизацию запроса или использование
AnyRelationQuery в виде обычного Query для организации запроса к базе данных.

Рассмотрим простейший пример отношения двух моделей друг к другу. Да, этот пример можно было бы записать и в виде
привычного hasMany отношения. Он приведён только для целей демонстрации синтаксиса. В рассматриваемом примере мы
производим линковку к родительскому полю "id". Переданный вторым параметром коллбек, который получает на вход список
родительских моделей. Мы самостоятельно должны извлечь необходимые нам данные из родительских модели для нахождения
дочерних сущностей. В данном примере мы извлекли значения поля "id" и применили дополнительную фильтрацию по стране.
Метод должен возвращать индексный массив, где ключами выступают значения родительского поля (в нашем случае "id"), а
значением - массив (для hasAnyMany) или значение (для hasAnyOne) данного отношения.

```php
/**
 * @property int $id
 * @property int $parent_id
 * @property string $label
 * @property string $country
 */
class TestRelatedModel extends \yii\db\ActiveRecord
{
    
}

/**
 * @property int $id
 * 
 * @property-read TestRelatedModel $ruRelation
 * @see self::getRuRelation() 
 */
class TestModel extends \yii\db\ActiveRecord
{
    use \matrozov\yii2kit\traits\AnyRelationTrait;
    
    /**
     * @return \matrozov\yii2kit\components\AnyRelationQuery
     */
    public function getRuRelation(): \matrozov\yii2kit\components\AnyRelationQuery
    {
        return $this->hasAnyOne('id', function (array $parentModels) {
            /** @var self[] $parentModels */
            
            return TestRelatedModel::find()
                ->where([
                    'parent_id' => \yii\helpers\ArrayHelper::getColumn($parentModels, 'id'),
                    'country'   => 'ru',
                ])
                ->indexBy('parent_id')
                ->all();
        });
    }
}

$models = TestModel()
    ->find()
    ->with('ruRelation')
    ->all();

var_dump($models[0]->ruRelation);
```

Данные отношения можно использовать не только для получения связанных моделей, но и для получения скалярных значений или 
созданрия связей с внешними системами:

```php
/**
 * @property string $id
 * @property string $currency
 * 
 * @property-read string $rate
 * @see self::getRate()
 */
class TestModel extends \yii\db\ActiveRecord
{
    use \matrozov\yii2kit\traits\AnyRelationTrait;
    
    public function getRate(): \matrozov\yii2kit\components\AnyRelationQuery
    {
        return $this->hasAnyOne('currency', function (array $parentModels) {
            $currencies = ArrayHelhep::getColumn($parentModels, 'currency');
            $currencies = array_unique($currencies);
        
            $request = (new \yii\httpclient\Client())->createRequest();
            
            $request->method = 'get';
            $request->url    = 'http://example.com/get_currency_rates/$currency=' . implode(',', $currencies);
            $request->format = \yii\httpclient\Client::FORMAT_JSON;
            
            $response = $request->send();
            
            if (!$response->isOk) {
                return [];            
            }
            
            return ArrayHelper::map($response->data, 'currency', 'value');
        });
    }
}

$models = TestModel::find()
    ->with(['rate'])
    ->all();

var_dump($models[0]->rate);
```

В данном примере мы для получения данных по связи будем использовать внешний сервис, который по списку валют
возвращает их ставки. При этом связывание мы будем производить по идентификатору валюты.
