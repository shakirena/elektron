<?php

namespace app\controllers;

use Yii;
use app\models\CompanySettingsForm;
use yii\web\Controller;

/**
 * Управление реквизитами компании, которые печатаются на чеках.
 */
class CompanyController extends Controller
{
    public $layout = 'admin';

    public function actionIndex()
    {
        $model = new CompanySettingsForm();
        $model->loadCurrent();

        if ($model->load(Yii::$app->request->post()) && $model->saveConfig()) {
            Yii::$app->session->setFlash('success', 'Şirkət məlumatları yadda saxlanıldı. Dəyişikliklər bütün çeklərdə tətbiq olunacaq.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
