<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\models\MonthlySverka;
use app\models\MonthlySverkaReport;

/**
 * MonthlySverkaController — страница «Aylıq sverka».
 *
 * URL: /monthly-sverka/index
 * Сравнивает текущий «Stok + Portfel + Kassa − Borc» с сохранённым снимком
 * и показывает, какая часть изменения не объясняется прибылью.
 * Доступ: только администраторы (id_role 2, 6) — на странице прибыль.
 */
class MonthlySverkaController extends Controller
{
    public $layout = 'admin';

    const ADMIN_ROLES = [2, 6];

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                    'snapshot' => ['POST'],
                    'manual' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return in_array((int) Yii::$app->user->identity->id_role, self::ADMIN_ROLES, true);
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int|null $base id снимка для сравнения (по умолчанию — последний)
     * @param string|null $from      начало периода доходов/расходов (Y-m-d)
     * @param string|null $to        конец периода (Y-m-d; без времени — весь день включительно)
     * @param string|null $from_time необязательное время начала (H:i)
     * @param string|null $to_time   необязательное время конца (H:i)
     */
    public function actionIndex($base = null, $from = null, $to = null, $from_time = null, $to_time = null)
    {
        $from = self::joinDateTime($from, $from_time);
        $to = self::joinDateTime($to, $to_time);
        $snapshots = MonthlySverka::find()->orderBy(['datetime' => SORT_DESC])->all();
        $baseModel = null;
        if ($base) {
            $baseModel = MonthlySverka::findOne((int) $base);
        } elseif ($snapshots) {
            $baseModel = $snapshots[0];
        }

        $now = MonthlySverkaReport::current();
        $flows = null; $compare = null; $backdated = [];
        if ($baseModel) {
            $flows = MonthlySverkaReport::flows($baseModel->datetime);
            $compare = MonthlySverkaReport::compare($baseModel->attributes, $now, $flows);
            $backdated = MonthlySverkaReport::backdated($baseModel);
        }

        // Период для раздела «Gəlir və xərc»: явно заданный, иначе — от снимка, иначе — с 1-го числа прошлого месяца
        $periodFrom = self::parseDate($from, false);
        $periodTo = self::parseDate($to, true);
        if ($periodFrom === null) {
            $periodFrom = $baseModel ? $baseModel->datetime : date('Y-m-01 00:00:00', strtotime('first day of last month'));
        }
        $periodFlows = ($flows && $from === null && $to === null) ? $flows : MonthlySverkaReport::flows($periodFrom, $periodTo);

        $manual = new MonthlySverka(['datetime' => date('Y-m-d H:i:s')]);

        return $this->render('index', [
            'periodFrom' => $periodFrom,
            'periodTo' => $periodTo,
            'periodFlows' => $periodFlows,
            'snapshots' => $snapshots,
            'baseModel' => $baseModel,
            'now' => $now,
            'flows' => $flows,
            'compare' => $compare,
            'backdated' => $backdated,
            'manual' => $manual,
        ]);
    }

    /**
     * Дата из формы → 'Y-m-d H:i:s'. Для конца периода дата без времени
     * включает весь день (граница — начало следующего дня, сравнение строгое).
     * @return string|null
     */
    private static function joinDateTime($date, $time)
    {
        $date = trim((string) $date);
        $time = trim((string) $time);
        if ($date === '') {
            return null;
        }
        return $time === '' ? $date : $date . ' ' . $time;
    }

    private static function parseDate($value, $isEnd)
    {
        $value = trim(str_replace('T', ' ', (string) $value));
        if ($value === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $isEnd ? date('Y-m-d 00:00:00', strtotime($value . ' +1 day')) : $value . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $value)) {
            return date('Y-m-d H:i:s', strtotime($value));
        }
        return null;
    }

    /**
     * Сохраняет текущее состояние как снимок (закрытие месяца).
     */
    public function actionSnapshot()
    {
        $now = MonthlySverkaReport::current();
        $model = new MonthlySverka();
        $model->setAttributes([
            'datetime' => date('Y-m-d H:i:s'),
            'stok' => $now['stok'],
            'stok_draft' => $now['stok_draft'],
            'portfel' => $now['portfel'],
            'portfel_minus' => $now['portfel_minus'],
            'kassa' => $now['kassa'],
            'borc' => $now['borc'],
            'is_manual' => 0,
            'note' => Yii::$app->request->post('note'),
        ] + $now['max_ids']);
        $model->id_user = Yii::$app->user->identity->id_user;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Cari vəziyyət yadda saxlanıldı.');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['index']);
    }

    /**
     * Снимок, введённый вручную (например, цифры прошлого месяца из Excel).
     */
    public function actionManual()
    {
        $model = new MonthlySverka();
        $model->load(Yii::$app->request->post(), 'MonthlySverka');
        $model->is_manual = 1;
        $model->id_user = Yii::$app->user->identity->id_user;
        if ($model->datetime && strlen($model->datetime) === 16) {
            $model->datetime .= ':00'; // из <input type="datetime-local">
        }
        $model->datetime = str_replace('T', ' ', (string) $model->datetime);

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Snapshot əlavə olundu.');
            return $this->redirect(['index', 'base' => $model->id]);
        }
        Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        $model = MonthlySverka::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'Snapshot silindi.');
        return $this->redirect(['index']);
    }
}
