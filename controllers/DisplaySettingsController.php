<?php

namespace app\controllers;

use Yii;
use app\models\DisplaySettingsForm;
use yii\web\Controller;

/**
 * Idarə: satış və mal qəbulu panellərində hansı sahələrin göstəriləcəyi.
 */
class DisplaySettingsController extends Controller
{
    public $layout = 'admin';

    public function actionIndex()
    {
        $model = new DisplaySettingsForm();
        $model->loadCurrent();

        if ($model->load(Yii::$app->request->post()) && $model->saveConfig()) {
            Yii::$app->session->setFlash('success', 'Görünüş tənzimləmələri yadda saxlanıldı.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
