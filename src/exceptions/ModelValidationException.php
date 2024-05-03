<?php

namespace matrozov\yii2kit\exceptions;

use Exception;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\VarDumper;

class ModelValidationException extends ErrorException
{
    public function __construct(Model $model, $message = '', $filename = __FILE__, $lineno = __LINE__, Exception $previous = null)
    {
        $delimiter = ' | ';

        if (!empty($message)) {
            $message .= $delimiter;
        }

        $message .= 'Error in ' . get_class($model) . $delimiter;

        foreach ($model->getFirstErrors() as $field => $error) {
            $value = $model->$field;

            $message .= "err: $error ($field = " . substr(VarDumper::export($value), 0, 255) . ") $delimiter";
        }

        parent::__construct($message, 0, 1, $filename, $lineno, $previous);
    }
}
