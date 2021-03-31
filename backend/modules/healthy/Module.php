<?php

namespace backend\modules\healthy;

/**
 * workorder module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'backend\modules\healthy\controllers';

    public $defaultRoute = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}