<?php

namespace openapi\modules\wanshang\models;

use Yii;

class WanshangLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wanshang_api_log';
    }

    public static function getDb()
    {
        return \Yii::$app->get('db_product');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['response_params','code','cost','update_time', 'create_time','type'], 'required'],
            [['create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'response_params' => 'Response Params',
            'code' => 'Code',
            'cost' => 'Cost',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }


    /**
     * 记录万商项目-接口调用日志
     * @param int $code 1.成功 0.失败
     * @param string $response_params 返回参数
     * @param int $type 媒体类型
     * @param int $cost 请求耗时
     * @return bool
     */
    public static function saveResultData($code, $response_params, $type, $cost = 0)
    {
        date_default_timezone_set("PRC");

        $fuchi_log = new WanshangLog();
        $data['response_params'] = $response_params;
        $data['code'] = $code;
        $data['type'] = $type;
        $data['cost'] = intval($cost);
        $data['update_time'] = date("Y-m-d H:i:s");
        $data['create_time'] = date("Y-m-d H:i:s");
        $fuchi_log->setAttributes($data);
        if($fuchi_log->save()){
            return true;
        }
        return false;

    }
}
