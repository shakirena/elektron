<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Feature #13, Stories #14/#15/#16:
 * article_number filter in SellSearch, ArrivalSearch, RestSearch.
 *
 * Approach: no DB connections.
 *   - Rules tests: instantiate model, iterate rules(), assert article_number
 *     is present in the safe rule.
 *   - Filter tests: read the source file via file_get_contents() and assert
 *     the LIKE filter line is present. This mirrors the project style used in
 *     ActionCancelValidationTest / ProductArticleNumberTest.
 */
class ArticleNumberFilterTest extends TestCase
{
    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Find the 'safe' rule in a model's rules() array and return it.
     * Returns null when no safe rule exists.
     *
     * @param \yii\base\Model $model
     * @return array|null
     */
    private function findSafeRule(\yii\base\Model $model)
    {
        foreach ($model->rules() as $rule) {
            if (isset($rule[1]) && $rule[1] === 'safe') {
                return $rule;
            }
        }
        return null;
    }

    /**
     * Return the absolute path to a model file relative to the project root.
     *
     * @param string $filename e.g. 'SellSearch.php'
     * @return string
     */
    private function modelPath($filename)
    {
        // tests/codeception/unit/models/ → project root is 4 levels up
        return dirname(__DIR__, 4) . '/models/' . $filename;
    }

    // ------------------------------------------------------------------
    // Test 1: SellSearch — article_number присутствует в safe rules
    // ------------------------------------------------------------------

    public function testSellSearchArticleNumberInSafeRules()
    {
        $model    = new \app\models\SellSearch();
        $safeRule = $this->findSafeRule($model);

        $this->assertNotNull(
            $safeRule,
            'SellSearch::rules() must contain a safe rule'
        );
        $this->assertContains(
            'article_number',
            (array)$safeRule[0],
            'article_number must be listed in the SellSearch safe rule'
        );
    }

    // ------------------------------------------------------------------
    // Test 2: ArrivalSearch — article_number присутствует в safe rules
    // ------------------------------------------------------------------

    public function testArrivalSearchArticleNumberInSafeRules()
    {
        $model    = new \app\models\ArrivalSearch();
        $safeRule = $this->findSafeRule($model);

        $this->assertNotNull(
            $safeRule,
            'ArrivalSearch::rules() must contain a safe rule'
        );
        $this->assertContains(
            'article_number',
            (array)$safeRule[0],
            'article_number must be listed in the ArrivalSearch safe rule'
        );
    }

    // ------------------------------------------------------------------
    // Test 3: RestSearch — article_number присутствует в safe rules
    // ------------------------------------------------------------------

    public function testRestSearchArticleNumberInSafeRules()
    {
        $model    = new \app\models\RestSearch();
        $safeRule = $this->findSafeRule($model);

        $this->assertNotNull(
            $safeRule,
            'RestSearch::rules() must contain a safe rule'
        );
        $this->assertContains(
            'article_number',
            (array)$safeRule[0],
            'article_number must be listed in the RestSearch safe rule'
        );
    }

    // ------------------------------------------------------------------
    // Test 4: SellSearch — search() содержит LIKE product.article_number
    // ------------------------------------------------------------------

    public function testSellSearchContainsArticleNumberLikeFilter()
    {
        $content = file_get_contents($this->modelPath('SellSearch.php'));

        $this->assertNotFalse(
            $content,
            'SellSearch.php must be readable'
        );
        $this->assertStringContainsString(
            "product.article_number",
            $content,
            'SellSearch::search() must contain andFilterWhere LIKE on product.article_number'
        );
        // Verify the filter uses the LIKE operator pattern
        $this->assertStringContainsString(
            "'like', 'product.article_number'",
            $content,
            "SellSearch must use ['like', 'product.article_number', ...] filter"
        );
    }

    // ------------------------------------------------------------------
    // Test 5: ArrivalSearch — search() содержит LIKE product.article_number
    // ------------------------------------------------------------------

    public function testArrivalSearchContainsArticleNumberLikeFilter()
    {
        $content = file_get_contents($this->modelPath('ArrivalSearch.php'));

        $this->assertNotFalse(
            $content,
            'ArrivalSearch.php must be readable'
        );
        $this->assertStringContainsString(
            "product.article_number",
            $content,
            'ArrivalSearch::search() must contain andFilterWhere LIKE on product.article_number'
        );
        $this->assertStringContainsString(
            "'like', 'product.article_number'",
            $content,
            "ArrivalSearch must use ['like', 'product.article_number', ...] filter"
        );
    }

    // ------------------------------------------------------------------
    // Test 6: RestSearch — search() содержит LIKE product.article_number
    // ------------------------------------------------------------------

    public function testRestSearchContainsArticleNumberLikeFilter()
    {
        $content = file_get_contents($this->modelPath('RestSearch.php'));

        $this->assertNotFalse(
            $content,
            'RestSearch.php must be readable'
        );
        $this->assertStringContainsString(
            "product.article_number",
            $content,
            'RestSearch::search() must contain andFilterWhere LIKE on product.article_number'
        );
        $this->assertStringContainsString(
            "'like', 'product.article_number'",
            $content,
            "RestSearch must use ['like', 'product.article_number', ...] filter"
        );
    }

    // ------------------------------------------------------------------
    // Test 7: views/sell/report.php — attribute article_number присутствует
    // ------------------------------------------------------------------

    public function testSellReportViewContainsArticleNumberAttribute()
    {
        $viewPath = dirname(__DIR__, 4) . '/views/sell/report.php';
        $content  = file_get_contents($viewPath);

        $this->assertNotFalse($content, 'views/sell/report.php must be readable');
        $this->assertStringContainsString(
            "'attribute' => 'article_number'",
            $content,
            "sell/report.php GridView column must declare 'attribute' => 'article_number' for filter"
        );
        $this->assertStringContainsString(
            "'value'  => 'idProduct.article_number'",
            $content,
            "sell/report.php column must read value from idProduct.article_number relation"
        );
    }

    // ------------------------------------------------------------------
    // Test 8: views/arrival/report.php — attribute article_number присутствует
    // ------------------------------------------------------------------

    public function testArrivalReportViewContainsArticleNumberAttribute()
    {
        $viewPath = dirname(__DIR__, 4) . '/views/arrival/report.php';
        $content  = file_get_contents($viewPath);

        $this->assertNotFalse($content, 'views/arrival/report.php must be readable');
        $this->assertStringContainsString(
            "'attribute' => 'article_number'",
            $content,
            "arrival/report.php GridView column must declare 'attribute' => 'article_number' for filter"
        );
        $this->assertStringContainsString(
            "'value'  => 'idProduct.article_number'",
            $content,
            "arrival/report.php column must read value from idProduct.article_number relation"
        );
    }

    // ------------------------------------------------------------------
    // Test 9: views/arrival/rest.php — колонка article_number присутствует
    // ------------------------------------------------------------------

    public function testRestViewContainsArticleNumberColumnAndAttribute()
    {
        $viewPath = dirname(__DIR__, 4) . '/views/arrival/rest.php';
        $content  = file_get_contents($viewPath);

        $this->assertNotFalse($content, 'views/arrival/rest.php must be readable');
        $this->assertStringContainsString(
            "'attribute' => 'article_number'",
            $content,
            "arrival/rest.php GridView column must declare 'attribute' => 'article_number'"
        );
        $this->assertStringContainsString(
            "'value'  => 'idProduct.article_number'",
            $content,
            "arrival/rest.php column must read value from idProduct.article_number"
        );
        $this->assertStringContainsString(
            "'label'  => 'Artikul nomresi'",
            $content,
            "arrival/rest.php column must have label 'Artikul nomresi'"
        );
    }

    // ------------------------------------------------------------------
    // Test 10: article_number safe rule is not in integer/number validators
    //          (guard: must not be miscategorised as numeric)
    // ------------------------------------------------------------------

    public function testSellSearchArticleNumberIsNotInNumericRule()
    {
        $model = new \app\models\SellSearch();

        foreach ($model->rules() as $rule) {
            $validator  = $rule[1] ?? null;
            $attributes = (array)($rule[0] ?? []);

            if (in_array($validator, ['integer', 'number'], true)) {
                $this->assertNotContains(
                    'article_number',
                    $attributes,
                    'article_number must NOT appear in integer/number rules in SellSearch'
                );
            }
        }
        // If we reach here all integer/number rules were checked
        $this->assertTrue(true);
    }

    public function testArrivalSearchArticleNumberIsNotInNumericRule()
    {
        $model = new \app\models\ArrivalSearch();

        foreach ($model->rules() as $rule) {
            $validator  = $rule[1] ?? null;
            $attributes = (array)($rule[0] ?? []);

            if (in_array($validator, ['integer', 'number'], true)) {
                $this->assertNotContains(
                    'article_number',
                    $attributes,
                    'article_number must NOT appear in integer/number rules in ArrivalSearch'
                );
            }
        }
        $this->assertTrue(true);
    }

    public function testRestSearchArticleNumberIsNotInNumericRule()
    {
        $model = new \app\models\RestSearch();

        foreach ($model->rules() as $rule) {
            $validator  = $rule[1] ?? null;
            $attributes = (array)($rule[0] ?? []);

            if (in_array($validator, ['integer', 'number'], true)) {
                $this->assertNotContains(
                    'article_number',
                    $attributes,
                    'article_number must NOT appear in integer/number rules in RestSearch'
                );
            }
        }
        $this->assertTrue(true);
    }
}
