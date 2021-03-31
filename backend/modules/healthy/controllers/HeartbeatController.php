<?php
namespace backend\modules\healthy\controllers;
use yii\web\Controller;

class HeartbeatController extends Controller {
    public function actionCheck(){
        echo 'App heartbeat check.';exit(200);
    }
}