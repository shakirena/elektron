<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Story #7: article_number field validation in Product model.
 *
 * Verifies that the article_number attribute is:
 *   - accepted as a string of up to 100 characters (happy path)
 *   - nullable (no DB or application error when absent)
 *   - not required
 *   - rejected when longer than 100 characters
 *   - labelled correctly ('Artikul nomresi')
 *
 * These tests exercise ONLY model validation rules — no DB calls, no save().
 * All external dependencies (TypeProduct exist-check, DB) are bypassed by
 * calling $model->validate(['article_number']) with a scenario that skips
 * unrelated rules, or by asserting purely on the string validator output.
 */
class ProductArticleNumberTest extends TestCase
{
    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Build a partially-populated Product instance.
     * We do NOT call save() — only validate().
     *
     * @return \app\models\Product
     */
    private function makeProduct()
    {
        $model = new \app\models\Product();
        return $model;
    }

    /**
     * Validate only the article_number attribute to isolate it from
     * unrelated rules (required id_type/name, exist validators, etc.).
     *
     * @param \app\models\Product $model
     * @return bool
     */
    private function validateArticleNumber(\app\models\Product $model)
    {
        return $model->validate(['article_number']);
    }

    // ---------------------------------------------------------------
    // Test 1 — Happy path: valid string ≤ 100 characters is accepted
    // ---------------------------------------------------------------

    public function testValidArticleNumberPasses()
    {
        $model = $this->makeProduct();
        $model->article_number = 'ART-2026-001';

        $result = $this->validateArticleNumber($model);

        $this->assertTrue($result, 'A valid article_number should pass validation');
        $this->assertFalse(
            $model->hasErrors('article_number'),
            'No errors expected for a short, valid article_number'
        );
    }

    // ---------------------------------------------------------------
    // Test 2 — Nullable: null is a valid value (column is NULL in DB)
    // ---------------------------------------------------------------

    public function testNullArticleNumberIsValid()
    {
        $model = $this->makeProduct();
        $model->article_number = null;

        $result = $this->validateArticleNumber($model);

        $this->assertTrue($result, 'null article_number must be valid (column is nullable)');
        $this->assertFalse(
            $model->hasErrors('article_number'),
            'null must not produce validation errors'
        );
    }

    // ---------------------------------------------------------------
    // Test 3 — Empty string: not required, so empty string is valid
    // ---------------------------------------------------------------

    public function testEmptyStringArticleNumberIsValid()
    {
        $model = $this->makeProduct();
        $model->article_number = '';

        $result = $this->validateArticleNumber($model);

        $this->assertTrue($result, 'Empty string must pass — article_number is not required');
        $this->assertFalse(
            $model->hasErrors('article_number'),
            'Empty string must not produce validation errors'
        );
    }

    // ---------------------------------------------------------------
    // Test 4 — Boundary: exactly 100 characters is accepted
    // ---------------------------------------------------------------

    public function testExactly100CharsIsValid()
    {
        $model = $this->makeProduct();
        $model->article_number = str_repeat('A', 100);

        $result = $this->validateArticleNumber($model);

        $this->assertTrue($result, 'Exactly 100 characters must be valid (max=100)');
        $this->assertFalse(
            $model->hasErrors('article_number'),
            'No errors expected for a 100-char string'
        );
    }

    // ---------------------------------------------------------------
    // Test 5 — Too long: 101 characters must be rejected
    // ---------------------------------------------------------------

    public function testOver100CharsIsInvalid()
    {
        $model = $this->makeProduct();
        $model->article_number = str_repeat('X', 101);

        $result = $this->validateArticleNumber($model);

        $this->assertFalse($result, '101-character article_number must fail validation');
        $this->assertTrue(
            $model->hasErrors('article_number'),
            'Errors must be reported when article_number exceeds 100 characters'
        );
    }

    // ---------------------------------------------------------------
    // Test 6 — Label: attributeLabels() must return 'Artikul nomresi'
    // ---------------------------------------------------------------

    public function testAttributeLabelIsArticulNomresi()
    {
        $model = $this->makeProduct();
        $labels = $model->attributeLabels();

        $this->assertArrayHasKey(
            'article_number',
            $labels,
            'attributeLabels() must contain article_number key'
        );
        $this->assertSame(
            'Artikul nomresi',
            $labels['article_number'],
            "The label for article_number must be 'Artikul nomresi'"
        );
    }

    // ---------------------------------------------------------------
    // Test 7 — Not required: article_number must not be in required rules
    // ---------------------------------------------------------------

    public function testArticleNumberIsNotRequired()
    {
        $model = $this->makeProduct();

        $this->assertFalse(
            $model->isAttributeRequired('article_number'),
            'article_number must NOT be a required attribute'
        );
    }

    // ---------------------------------------------------------------
    // Test 8 — Rule presence: string rule with max=100 must exist
    // ---------------------------------------------------------------

    public function testStringRuleWithMax100Exists()
    {
        $model = $this->makeProduct();
        $found = false;

        foreach ($model->rules() as $rule) {
            // Each rule is [attributes, validator, ...options]
            $attributes = (array)$rule[0];
            $validator  = $rule[1] ?? null;
            $maxOption  = $rule['max'] ?? null;

            if (
                in_array('article_number', $attributes, true) &&
                $validator === 'string' &&
                $maxOption === 100
            ) {
                $found = true;
                break;
            }
        }

        $this->assertTrue(
            $found,
            "rules() must contain a string rule with max=>100 for article_number"
        );
    }
}
