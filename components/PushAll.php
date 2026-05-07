<?php
/**
 * ������ � �������� PushAll
 *
 * @author     Vladyslav Platonov <vlad@plat-x.com>
 * @copyright  2013-2015 Plat-X
 * @version    1.0
 * @link       http://plat-x.com/
 */


namespace app\components;

use yii\base\Model;
use yii\web\HttpException;


/**
 *  ��� ������� PushAll.ru
 *
 * @id integer ID ������������ ��� ��������, �����������
 * @key string ���� ��� ��������, �����������
 * @type string ��� push-����������� (TYPE_SELF, TYPE_BROADCAST, TYPE_UNICAST),
 *                  �� ��������� - TYPE_SELF
 * @title string ��������� �����������, �����������
 * @text string ����� �����������, �����������
 * @icon string ������ �� ������, �� �����������
 * @url string ������, �� ������� ������������ ������� ���
 *              ����� �� �����������, �� �����������
 * @hidden ��������� ����������� (HIDDEN_FALSE, HIDDEN_HISTORY, HIDDEN_BAND),
 *          �� ��������� - HIDDEN_FALSE
 * @encode ��������� ���������, �� ��������� - UTF-8
 * @priority integer ��������� ���������
 *                      (PRIORITY_DEFAULT, PRIORITY_NOT_IMPORTANT, PRIORITY_IMPORTANT),
 *                      �� ��������� - PRIORITY_DEFAULT
 */
class PushAll extends Model
{
    /**
     * ����
     */
    const TYPE_SELF = 'self';
    const TYPE_BROADCAST = 'broadcast';
    const TYPE_UNICAST = 'unicast';

    /**
     * ������� �����������
     */
    const HIDDEN_FALSE = 0;
    const HIDDEN_HISTORY = 1;
    const HIDDEN_BAND = 2;

    /**
     * ����������
     */
    const PRIORITY_DEFAULT = 0;
    const PRIORITY_NOT_IMPORTANT = -1;
    const PRIORITY_IMPORTANT = 1;

    /**
     * ��� �����������
     *
     * @var string
     */
    public $type = self::TYPE_SELF;

    /**
     * ����� ��������
     *
     * @var integer
     */
    public $id;

    /**
     * ���� ��������
     *
     * @var string
     */
    public $key;

    /**
     * ��������� �����������
     *
     * @var string
     */
    public $title;

    /**
     * ����� �����������
     *
     * @var string
     */
    public $text;

    /**
     * URL �������� (�� ������ 512��)
     *
     * @var string
     */
    public $icon;

    /**
     * ������ ��� ��������
     *
     * @var string
     */
    public $url;

    /**
     * ������� �����������
     *
     * @var int
     */
    public $hidden = self::HIDDEN_FALSE;

    /**
     * ���������
     *
     * @var string
     */
    public $encode = 'UTF-8';

    /**
     * ��������� �����������
     *
     * @var int
     */
    public $priority = self::PRIORITY_DEFAULT;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['title', 'text', 'id', 'key'], 'required'],
            [['id', 'priority', 'hidden'], 'integer'],
            [['url', 'encode', 'icon', 'title', 'text', 'key', 'type'], 'string'],
        ];
    }

    /**
     * @throws \yii\base\Exception
     */
    public function beforeValidate()
    {
        if (empty($this->id)) {
            throw new HttpException('���������� �������� ID ��� PushAll!');
        }

        if (empty($this->key)) {
            throw new HttpException('���������� �������� key ��� PushAll!');
        }

        return parent::beforeValidate();
    }

    /**
     * @return mixed
     */
    public function send()
    {
        if ($this->validate()) {
            $params = [
                "type" => $this->type,
                "id" => $this->id,
                "key" => $this->key,
                "text" => $this->text,
                "title" => $this->title,
                "hidden" => $this->hidden,
                "priority" => $this->priority,
            ];

            if (!empty($this->url)) {
                $params['url'] = $this->url;
            }

            if (!empty($this->encode)) {
                $params['encode'] = $this->encode;
            }

            if (!empty($this->icon)) {
                $params['icon'] = $this->icon;
            }

            curl_setopt_array($ch = curl_init(), array(
                CURLOPT_URL => "https://pushall.ru/api.php",
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_SAFE_UPLOAD => true,
                CURLOPT_RETURNTRANSFER => true
            ));

            $return = curl_exec($ch); //�������� ����� ��� ������

            curl_close($ch);

            return $return;
        }

        return $this->errors;
    }
}