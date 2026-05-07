<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\helpers\Url;
class SiteController extends Controller
{
    public $layout = 'start';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new LoginForm();
		
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $post=Yii::$app->request->post("LoginForm");
			if (Yii::$app->user->identity->id_role==1) {
				Yii::$app->session->set("store",Yii::$app->user->identity->id_store);
			}
			else  Yii::$app->session->set("store",$post['store']);

            Yii::$app->session->set("sverka",1);
            Yii::$app->session->set('id_client',1);
            Yii::$app->session->set('client',"Müştəri");
			 Yii::$app->session->set('show',1);
			if (Yii::$app->user->identity->id_role==4)
				return $this->redirect(Url::to(['transfer/index2']));
			else return $this->redirect(Url::to(['sell/index']));
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }


    /**
     * Login action.
     *
     * @return string
     */

    public function actionLogin()
    {

        return $this->redirect('index');
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }



    public function actionProverka()
    {
        return $this->render('about');
    }



}
