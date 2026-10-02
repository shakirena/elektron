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
     * @param string|null $from начало периода доходов/расходов (Y-m-d, с 00:00:00)
     * @param string|null $to   конец периода (Y-m-d, по 23:59:59 включительно)
     */
    public function actionIndex($base = null, $from = null, $to = null)
    {
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

        // Период «Gəlir və xərc» — только даты: с 00:00:00 первого дня по 23:59:59 последнего.
        // По умолчанию: со дня снимка (или с 1-го числа прошлого месяца) по сегодня.
        $fromDate = self::validDate($from)
            ?: ($baseModel ? substr($baseModel->datetime, 0, 10) : date('Y-m-d', strtotime('first day of last month')));
        $toDate = self::validDate($to) ?: date('Y-m-d');
        $periodFlows = MonthlySverkaReport::flows($fromDate . ' 00:00:00', $toDate . ' 23:59:59');

        $manual = new MonthlySverka(['datetime' => date('Y-m-d H:i:s')]);

        return $this->render('index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
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
     * @return string|null дата 'Y-m-d' или null, если значение пустое/некорректное
     */
    private static function validDate($value)
    {
        $value = trim((string) $value);
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
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
