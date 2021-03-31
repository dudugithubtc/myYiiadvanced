<?php

namespace openapi\modules\wanshang\models;

use Yii;

class WanshangPushCount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wanshang_push_count';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_product');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date','count','create_time','update_time','type'], 'required'],
            [['create_time','update_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'count' => 'Count',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time'
        ];
    }


    /**
     *  记录动信通-推送文章数据条数
     * @param int $count
     * @param int $type
     * @return bool
     */
    public static function saveResultData($count = 0, $type = 1)
    {
        date_default_timezone_set("PRC");

        $day = date('Y-m-d');

        $logs = WanshangPushCount::find()->where(['date' => $day])->andWhere(['type' => $type])->one();
        if(!$logs){
            $logs = new WanshangPushCount();
            $data['create_time'] = date("Y-m-d H:i:s");
            $data['update_time'] = date("Y-m-d H:i:s");
            $data['date'] = $day;
            $data['count'] = $count;
            $data['type'] = $type;

        } else {
            $data['count'] = $count + $logs->count;
            $data['update_time'] = date("Y-m-d H:i:s");
            $data['type'] = $type;
        }

        $logs->setAttributes($data);
        if($logs->save()){
            return true;
        }
        return false;
    }
}
