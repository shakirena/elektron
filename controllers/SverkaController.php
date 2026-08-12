<?php

namespace app\controllers;

use app\models\Arrival;
use app\models\Barcodep;
use app\models\RestSearch;
use Yii;
use app\models\Sverka;
use app\models\Product;
use app\models\SverkaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use app\models\TypeProduct;
use app\models\SverkaLog;
/**
 * SverkaController implements the CRUD actions for Sverka model.
 */
class SverkaController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Sverka models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SverkaSearch(["id_store" =>  Yii::$app->session->get("sverka")]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $sum_fakt=Sverka::find()->select("sum(quantity) as sum_fakt")
            ->where(["id_store" =>  Yii::$app->session->get("sverka")])
            ->one();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sum_fakt' => $sum_fakt->sum_fakt,
        ]);
    }

    /**
     * Displays a single Sverka model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Sverka model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sverka();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Sverka model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    public function actionUpdateQuantity($id, $quantity)
    {
        $model = $this->findModel($id);
        $model->quantity = $quantity;
        $model->save();

    }
    /**
     * Deletes an existing Sverka model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Sverka model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sverka the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sverka::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionFind()
    {

        $searchModel = new RestSearch();
        $searchModel->id_store=Yii::$app->user->identity->id_store;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('find', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }


    public function actionInsert($id, $quantity)
    {
        $model=Sverka::find()->where(["id_product"=>$id,"id_store" =>  Yii::$app->session->get("sverka")])->one();

        if($model->quantity) {
            $model->quantity=$model->quantity+ $quantity;
            $model->save();
        }
        else {
            $sverka = new Sverka();
            $sverka->id_product = $id;
            $sverka->quantity = $quantity;
            $sverka->id_store = Yii::$app->session->get("sverka");
            $sverka->save();
        }


    }

    public function actionInsertType(array $id)
    {
        foreach ($id as $id1) {

            $model = Sverka::find()->where(["id_product" => $id1, "id_store" => Yii::$app->session->get("sverka")])->one();

            if (!$model->quantity) /*{
                $model->quantity = $model->quantity + 1;
                $model->save();
            } else */
            {
                $rest=Arrival::find()->select("sum(rest) as rest")->where(["id_product" => $id1])->one();
                if ($rest->rest) $rest=$rest->rest;
                else $rest=1;

                    $sverka = new Sverka();
                    $sverka->id_product = $id1;
                    $sverka->quantity = $rest;
                    $sverka->id_store = Yii::$app->session->get("sverka");
                    $sverka->save();

            }

        }
    }

    public function actionInsertBarcode($barcode)
    {
        $product=Barcodep::find()

            ->where(["name"=>$barcode])
            ->one();
        if ($product->id) {
            $model=Sverka::find()->where(["id_product"=>$product->id_product,"id_store" =>  Yii::$app->session->get("sverka")])->one();
            if($model->quantity) {
                $model->quantity=$model->quantity+ 1;
                $model->save();
            }
            else {
                $sverka = new Sverka();
                $sverka->id_product = $product->id_product;
                $sverka->quantity =1;
                $sverka->id_store = Yii::$app->session->get("sverka");
                $sverka->save();
            }
        }

    }

    /**
     * Sverka nəticəsini bazaya yazır.
     * @param int $zeroUnlisted 1 — bu anbarda sayılmayan (sverka siyahısında olmayan)
     *   bütün malların qalığı sıfırlanır (tam sayım rejimi). 0 — yalnız sayılan
     *   mallar yenilənir, qalanlarına toxunulmur (əvvəlki davranış).
     */
    public function actionReceived($zeroUnlisted = 0)
    {
        $storeId = Yii::$app->session->get("sverka");

        $this->backupDatabase();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($zeroUnlisted) {
                // Tam sayım: bu anbardakı BÜTÜN qalıqlar əvvəlcə sıfırlanır —
                // aşağıda yalnız sayılan (sverka-ya daxil edilən) mallar öz
                // faktiki miqdarına yenidən yazılacaq.
                Arrival::updateAll(['rest' => 0], ['id_store' => $storeId]);
            }

            $max = Arrival::find()->select('number')->max('number');
            $max = $max ? $max + 1 : 1;

            foreach (Sverka::find()->where(["id_store" => $storeId])->all() as $model) {
                $arrival1 = Arrival::find()
                    ->where(["id_product" => $model->id_product, "id_store" => $storeId])
                    ->orderBy("datetime DESC")
                    ->one();

                if ($arrival1) {
                    // Feature #27 / Story #29: snapshot qty_before ДО перезаписи.
                    $qtyBefore = (float) $arrival1->rest;

                    if (!$zeroUnlisted) {
                        // Tam sayım deyilsə, yalnız BU malın qalıqlarını sıfırlayıb
                        // tək sətirdə cəmləyirik (anbarın qalanı toxunulmaz qalır).
                        foreach (Arrival::find()->where(["id_product" => $model->id_product, "id_store" => $storeId])->all() as $arrival) {
                            $arrival->rest = 0;
                            $arrival->save();
                        }
                    }
                    $arrival1->rest = $model->quantity;
                    $arrival1->save();
                } else {
                    $qtyBefore = 0.0; // товара не было на складе
                    $arrival = new Arrival();
                    $arrival->id_product = $model->id_product;
                    $arrival->quantity = $model->quantity;
                    $arrival->rest = $model->quantity;
                    $arrival->price = 0;
                    $arrival->id_store = $storeId;
                    $arrival->number = $max;
                    $arrival->id_user = Yii::$app->user->identity->id_user;
                    $arrival->received = 1;
                    $arrival->save();
                }

                // Feature #27 / Story #29: журналируем изменение остатка перед удалением строки sverka.
                SverkaLog::logChange(
                    $model->id_product,
                    $storeId,
                    $qtyBefore,
                    (float) $model->quantity,
                    Yii::$app->user->identity->id_user
                );

                $model->delete();
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * mysqldump ilə cari bazanın ehtiyat nüsxəsini çıxarır. Xəta baş versə
     * sverka yazılışını dayandırmır — sadəcə log-a yazılır, amma cəhd
     * həmişə edilir.
     *
     * Əvvəlcə sadəcə "mysqldump" (PATH-da varsa) sınanılır. Bir çox
     * OSPanel qurğusunda mysqldump.exe PATH-a əlavə olunmur, ona görə
     * tapılmasa, OSPanel-in "modules/database/*\/bin/mysqldump.exe"
     * qovluq quruluşundan ehtimal olunan versiyalar da sınanılır.
     */
    private function backupDatabase()
    {
        $db = Yii::$app->db;
        if (!preg_match('/host=([^;]+)/', $db->dsn, $hostMatch) || !preg_match('/dbname=([^;]+)/', $db->dsn, $dbMatch)) {
            Yii::warning('Sverka backup: DSN-dən host/dbname oxuna bilmədi.', 'sverka-backup');
            return;
        }

        $backupDir = Yii::getAlias('@app/runtime/backups');
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0777, true);
        }

        $file = $backupDir . '/' . $dbMatch[1] . '_' . date('Y-m-d_H-i-s') . '.sql';

        $candidates = array_merge(
            ['mysqldump'],
            array_reverse(glob(Yii::getAlias('@app/../../modules/database/*/bin/mysqldump.exe')) ?: [])
        );

        $errFile = $file . '.err';
        $lastErrText = '';
        $lastExitCode = null;
        foreach ($candidates as $binary) {
            // stdout (əsl dump) birbaşa .sql-ə, stderr (xəbərdarlıqlar/xətalar)
            // AYRI faylа yazılır — onları qarışdırmaq .sql-i pozardı (bərpa
            // zamanı ilk sətir SQL olmayan mətn olardı).
            $cmd = sprintf(
                '%s --host=%s --user=%s --password=%s %s > %s 2> %s',
                strpos($binary, ' ') !== false ? escapeshellarg($binary) : $binary,
                escapeshellarg($hostMatch[1]),
                escapeshellarg($db->username),
                escapeshellarg($db->password),
                escapeshellarg($dbMatch[1]),
                escapeshellarg($file),
                escapeshellarg($errFile)
            );

            $output = [];
            $exitCode = null;
            exec($cmd, $output, $exitCode);

            $errText = is_file($errFile) ? file_get_contents($errFile) : '';
            @unlink($errFile);

            if ($exitCode === 0 && is_file($file) && filesize($file) > 0) {
                return;
            }

            $lastErrText = $errText;
            $lastExitCode = $exitCode;
        }

        Yii::warning("Sverka backup uğursuz oldu (exit code $lastExitCode): " . $lastErrText, 'sverka-backup');
    }

    public function actionUpdateStore($id)
    {

        Yii::$app->session->set("sverka",$id);

    }



    public function actionCheck($id){
        $model=TypeProduct::find()->where(["id_parent" => $id])->one();
        if ($model->id) { return 0;}
        else { return 1;}

    }
}
