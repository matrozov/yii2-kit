<?php

namespace matrozov\yii2kit\exceptions;

use Exception;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class ModelValidationException extends ErrorException
{
    /**
     * @param Model $model
     * @param $message
     * @param $filename
     * @param $lineno
     * @param Exception|null $previous
     * @throws Exception
     */
    public function __construct(Model $model, $message = '', $filename = __FILE__, $lineno = __LINE__, Exception $previous = null)
    {
        $delimiter = ' | ';

        $result = [];

        if (!empty($message)) {
            $result[] = $message;
        }

        $result[] = 'Error in ' . get_class($model);

        foreach ($model->getFirstErrors() as $field => $error) {
            // Compatibility with https://github.com/matrozov/yii2-multiple-field
            $field = str_replace(['[', ']'], ['.', ''], $field);

            $value = ArrayHelper::getValue($model, $field);

            $result[] = "err: $error ($field = " . substr(VarDumper::export($value), 0, 255) . ')';
        }

        parent::__construct(implode($delimiter, $result), 0, 1, $filename, $lineno, $previous);
    }
}
