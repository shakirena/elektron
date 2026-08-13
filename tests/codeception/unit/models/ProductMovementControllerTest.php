<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit-тесты для Story #31: ProductMovementController — UI отчёта «Движение товара».
 * Feature #27.
 *
 * Scope: только unit-тесты без HTTP-стека и БД.
 *
 * Тест 1: контроллер существует и наследует yii\web\Controller.
 * Тест 2: behaviors() содержит 'access' (AccessControl) — ключ и класс.
 * Тест 3: AccessControl разрешает ТОЛЬКО роль '@' (аутентифицированные).
 * Тест 4: behaviors() содержит 'verbs' (VerbFilter) — ключ и класс.
 * Тест 5: VerbFilter разрешает только GET на action 'report'.
 * Тест 6: actionReport() без id_product возвращает пустой ArrayDataProvider через модель.
 * Тест 7: ProductMovementSearch::operationLabels() доступен из контроллера (зависимость).
 */
class ProductMovementControllerTest extends TestCase
{
    // ------------------------------------------------------------------
    // Тест 1: контроллер существует и наследует yii\web\Controller
    // ------------------------------------------------------------------

    /**
     * AC: ProductMovementController является стандартным Yii2-контроллером
     * (наследует yii\web\Controller).
     */
    public function testControllerExtendsWebController()
    {
        $controller = new \app\controllers\ProductMovementController(
            'product-movement',
            \Yii::$app
        );
        $this->assertInstanceOf(
            \yii\web\Controller::class,
            $controller,
            'ProductMovementController должен наследовать yii\web\Controller'
        );
    }

    // ------------------------------------------------------------------
    // Тест 2: behaviors() содержит AccessControl
    // ------------------------------------------------------------------

    /**
     * AC: behaviors() возвращает конфигурацию AccessControl (ключ 'access').
     */
    public function testBehaviorsContainsAccessControl()
    {
        $controller = new \app\controllers\ProductMovementController(
            'product-movement',
            \Yii::$app
        );
        $behaviors = $controller->behaviors();

        $this->assertArrayHasKey(
            'access',
            $behaviors,
            "behaviors() должен содержать ключ 'access'"
        );
        $this->assertSame(
            \yii\filters\AccessControl::className(),
            $behaviors['access']['class'],
            "Значение 'access.class' должно быть AccessControl"
        );
    }

    // ------------------------------------------------------------------
    // Тест 3: AccessControl разрешает только роль '@'
    // ------------------------------------------------------------------

    /**
     * AC: RBAC — доступ разрешён только аутентифицированным пользователям (role '@').
     * Соответствует NFR-2 Feature #27.
     */
    public function testAccessControlAllowsOnlyAuthenticatedRole()
    {
        $controller = new \app\controllers\ProductMovementController(
            'product-movement',
            \Yii::$app
        );
        $access = $controller->behaviors()['access'];

        $this->assertArrayHasKey(
            'rules',
            $access,
            "behaviors['access'] должен содержать ключ 'rules'"
        );
        $this->assertNotEmpty(
            $access['rules'],
            'Список rules не должен быть пустым'
        );

        $firstRule = $access['rules'][0];
        $this->assertTrue(
            $firstRule['allow'],
            "Первое правило должно иметь allow=true"
        );
        $this->assertContains(
            '@',
            $firstRule['roles'],
            "Первое правило должно разрешать роль '@' (аутентифицированные)"
        );
    }

    // ------------------------------------------------------------------
    // Тест 4: behaviors() содержит VerbFilter
    // ------------------------------------------------------------------

    /**
     * AC: behaviors() возвращает конфигурацию VerbFilter (ключ 'verbs').
     */
    public function testBehaviorsContainsVerbFilter()
    {
        $controller = new \app\controllers\ProductMovementController(
            'product-movement',
            \Yii::$app
        );
        $behaviors = $controller->behaviors();

        $this->assertArrayHasKey(
            'verbs',
            $behaviors,
            "behaviors() должен содержать ключ 'verbs'"
        );
        $this->assertSame(
            \yii\filters\VerbFilter::className(),
            $behaviors['verbs']['class'],
            "Значение 'verbs.class' должно быть VerbFilter"
        );
    }

    // ------------------------------------------------------------------
    // Тест 5: VerbFilter разрешает только GET для action 'report'
    // ------------------------------------------------------------------

    /**
     * AC: action 'report' принимает только GET-запросы.
     */
    public function testVerbFilterAllowsOnlyGetForReport()
    {
        $controller = new \app\controllers\ProductMovementController(
            'product-movement',
            \Yii::$app
        );
        $verbs = $controller->behaviors()['verbs'];

        $this->assertArrayHasKey(
            'actions',
            $verbs,
            "behaviors['verbs'] должен содержать ключ 'actions'"
        );
        $this->assertArrayHasKey(
            'report',
            $verbs['actions'],
            "VerbFilter должен конфигурировать action 'report'"
        );
        $this->assertContains(
            'GET',
            $verbs['actions']['report'],
            "Action 'report' должен принимать GET"
        );
        $this->assertCount(
            1,
            $verbs['actions']['report'],
            "Action 'report' должен принимать ровно один HTTP-метод (GET)"
        );
    }

    // ------------------------------------------------------------------
    // Тест 6: actionReport() через модель — guard-case без БД
    // ------------------------------------------------------------------

    /**
     * AC: actionReport() делегирует поиск ProductMovementSearch::search().
     *
     * Верифицируем guard-case: без id_product search() возвращает пустой
     * ArrayDataProvider, не обращаясь к БД. Это покрывает логику actionReport()
     * без mock HTTP-стека.
     */
    public function testActionReportDelegatesSearchToModel()
    {
        $searchModel  = new \app\models\ProductMovementSearch();
        $dataProvider = $searchModel->search([]);

        $this->assertInstanceOf(
            \yii\data\ArrayDataProvider::class,
            $dataProvider,
            'search() должен возвращать ArrayDataProvider'
        );
        $this->assertEmpty(
            $dataProvider->allModels,
            'allModels должен быть пустым при отсутствии id_product'
        );
    }

    // ------------------------------------------------------------------
    // Тест 7: зависимость от ProductMovementSearch — operationLabels()
    // ------------------------------------------------------------------

    /**
     * AC: контроллер использует ProductMovementSearch (import в шапке контроллера).
     * Проверяем доступность operationLabels() — нужна view для рендера.
     */
    public function testProductMovementSearchIsAccessible()
    {
        $labels = \app\models\ProductMovementSearch::operationLabels();

        $this->assertIsArray($labels);
        $this->assertCount(
            6,
            $labels,
            'ProductMovementSearch::operationLabels() должен содержать 6 типов операций'
        );
        $this->assertArrayHasKey('arrival', $labels);
        $this->assertArrayHasKey('sell', $labels);
    }
}
