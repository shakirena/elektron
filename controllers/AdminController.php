<?php

namespace app\controllers;

class AdminController extends \yii\web\Controller
{
    public $layout = 'admin';
    public function actionIndex()
    {
        return $this->render('index');
    }

}
