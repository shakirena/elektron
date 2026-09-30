<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Story #33 (Feature #32):
 * Фикс sell/find модалки при повторном pjax-поиске.
 *
 * Подход: только file_get_contents() — без БД, без инстанциирования контроллера.
 * Все assertion'ы проверяют точные строки из файлов.
 */
class SellFindPjaxConfigTest extends TestCase
{
    // ------------------------------------------------------------------
    // Setup / Teardown — skip Yii app creation (тесты не требуют БД/Yii)
    // ------------------------------------------------------------------

    protected function setUp()
    {
        // Намеренно не вызываем parent::setUp() / mockApplication(),
        // так как тесты используют только file_get_contents — Yii не нужен.
    }

    protected function tearDown()
    {
        // Намеренно не вызываем parent::tearDown() / destroyApplication().
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Абсолютный путь к views/sell/find.php */
    private function findViewPath()
    {
        return dirname(__DIR__, 4) . '/views/sell/find.php';
    }

    /** Абсолютный путь к web/js/main.js */
    private function mainJsPath()
    {
        return dirname(__DIR__, 4) . '/web/js/main.js';
    }

    /** Абсолютный путь к controllers/SellController.php */
    private function sellControllerPath()
    {
        return dirname(__DIR__, 4) . '/controllers/SellController.php';
    }

    // ------------------------------------------------------------------
    // AC-4: pjax/GridView конфигурация в views/sell/find.php
    // ------------------------------------------------------------------

    /**
     * Тест 1 (AC-4): GridView имеет явный id 'grid-find', устраняет коллизию с w0.
     */
    public function testFindViewGridIdIsExplicit()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains("'id' => 'grid-find'", $content,
            'find.php должен содержать явный id GridView: \'id\' => \'grid-find\''
        );
    }

    /**
     * Тест 2 (AC-4): pjaxSettings содержит явный id 'grid-find-pjax'.
     */
    public function testFindViewPjaxContainerIdIsExplicit()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains("'id' => 'grid-find-pjax'", $content,
            'find.php должен содержать явный id pjax-контейнера: \'id\' => \'grid-find-pjax\''
        );
    }

    /**
     * Тест 3 (AC-4): enablePushState => false — URL хост-страницы не меняется.
     */
    public function testFindViewEnablePushStateFalse()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains("'enablePushState' => false", $content,
            'find.php должен содержать \'enablePushState\' => false'
        );
    }

    /**
     * Тест 4 (AC-4): enableReplaceState => false — полная блокировка history API.
     */
    public function testFindViewEnableReplaceStateFalse()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains("'enableReplaceState' => false", $content,
            'find.php должен содержать \'enableReplaceState\' => false'
        );
    }

    /**
     * Тест 5 (AC-4): timeout => 5000 — исключает fallback в window.location.href.
     */
    public function testFindViewTimeoutIs5000()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains("'timeout' => 5000", $content,
            'find.php должен содержать \'timeout\' => 5000'
        );
    }

    // ------------------------------------------------------------------
    // AC-1/AC-2: Namespaced handler в views/sell/find.php
    // ------------------------------------------------------------------

    /**
     * Тест 6 (AC-1/AC-2): Файл содержит namespace '.sellFind' —
     * предотвращает накопление обработчиков при повторных открытиях модалки.
     */
    public function testFindViewContainsSellFindNamespace()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains('.sellFind', $content,
            'find.php должен содержать namespaced handler .sellFind'
        );
    }

    /**
     * Тест 7 (AC-2): Файл содержит namespaced pjax:complete.sellFind handler.
     */
    public function testFindViewContainsPjaxCompleteNamespacedHandler()
    {
        $content = file_get_contents($this->findViewPath());
        $this->assertNotFalse($content, 'Файл views/sell/find.php должен существовать');
        $this->assertContains('pjax:complete.sellFind', $content,
            'find.php должен содержать namespaced handler pjax:complete.sellFind'
        );
    }

    // ------------------------------------------------------------------
    // AC-5: Отписка обработчика в web/js/main.js
    // ------------------------------------------------------------------

    /**
     * Тест 8 (AC-5): main.js содержит $(document).off('.sellFind') —
     * снятие namespaced обработчиков при скрытии sell-modal.
     */
    public function testMainJsContainsOffSellFind()
    {
        $content = file_get_contents($this->mainJsPath());
        $this->assertNotFalse($content, 'Файл web/js/main.js должен существовать');
        $this->assertContains("off('.sellFind')", $content,
            'main.js должен содержать $(document).off(\'.sellFind\')'
        );
    }

    /**
     * Тест 9 (AC-5): main.js содержит #sell-modal с hidden.bs.modal
     * и off('.sellFind') — оба условия в одном файле верны.
     */
    public function testMainJsSellModalHiddenHandlerWithOffSellFind()
    {
        $content = file_get_contents($this->mainJsPath());
        $this->assertNotFalse($content, 'Файл web/js/main.js должен существовать');
        $this->assertContains('#sell-modal', $content,
            'main.js должен содержать #sell-modal'
        );
        $this->assertContains('hidden.bs.modal', $content,
            'main.js должен содержать hidden.bs.modal'
        );
        $this->assertContains("off('.sellFind')", $content,
            'main.js должен содержать off(\'.sellFind\')'
        );
    }

    // ------------------------------------------------------------------
    // Guard: рендеринг find в SellController
    // ------------------------------------------------------------------

    /**
     * Тест 10 (Guard): SellController рендерит find как ajax-фрагмент
     * через renderAjax('find', ...).
     */
    public function testSellControllerRendersAjaxFind()
    {
        $content = file_get_contents($this->sellControllerPath());
        $this->assertNotFalse($content, 'Файл controllers/SellController.php должен существовать');
        $this->assertContains("renderAjax('find'", $content,
            'SellController должен содержать renderAjax(\'find\''
        );
    }
}
