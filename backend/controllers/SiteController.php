<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

use yii\helpers\VarDumper;
use common\widgets\AppUtil;
use app\models\Country;

// 上传
use app\models\UploadForm;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller
{   

    // private $url = 'http://databus.gsdata.cn:8888/api/service';
    // private $app_key = '00bc9149097f204ee6a646e62d588a2b'; // me
    // private $app_secret = '914b85c6641f234503402c8695ed2d85'; // me

    private $url = 'http://openapi.com/api/service';
    // private $url = 'http://backend.my/site/accept';
    // private $url = 'http://127.0.0.1/crcanalysis/www/index.php';
    private $app_key = '2fa6e02de688057df273a7351616e057';
    private $app_secret = '3c39b04654ac2b9c0ffde079b70faef1';

    private $redis; //redis缓存

    public function init()
    {
        parent::init();
        date_default_timezone_set("PRC");
        $this->redis = Yii::$app->redis;
        // $this->token = 'Bearer eyJhbGciOiJIUzUxMi9J.eyJzdWIiOiLljJfkuqzlnLDpnIflsYDlpKflsY_pobnnm64iLCJpc3MiOiJlY2hpc2FuIiwiZXhwIjo0NzIyMzkyNzc1LCJpYXQiOjE1Njg3OTI3NzUsInJvbCI6IlJPTEVfVVNFUiJ9.48sucuyWCs3ByiIzlVcBzxX_rc4_E9bagqh4qAB5-LwYKo6b5WlkdWSRLNMpVreiugbXtr5YkeVCO2O6VPI1Bg';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        // 'actions' => ['login', 'index', 'testapi', 'adddata', 'upload'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionTest () 
    {
        $app_key = "21b129508e90b9c28b5291621b32fa7e"; //会员id为101用户的app_key
        $app_secret = "1c80a85f352cb594bd07f2b436a6c252"; //会员id为101用户的app_secret
        $url = 'http://databus.gsdata.cn:8888/api/service';
        $router = '/account/toutiao/search';
        $params = ['page'=>1, 'toutiao_province'=>'安徽'];
        $sign = AppUtil::createSign($app_secret, $params);
        $header = AppUtil::createHeader($app_key, $sign, $router);
        $res_format = AppUtil::fetch($url, $header, [], $params);
        $res_format = json_decode($res_format, true);

        echo '<pre>';
        var_dump($res_format);
        exit;
    }


    public function actionSay($message = 'This is say message.')
    {
        return $this->render('say', ['message' => $message]);
    }


    public function actionAccept()
    {
        return json_encode($_GET);
        exit;
        // AppUtil::dump(Yii::$app->request->get());
        $request = Yii::$app->request;
        $get = $request->get();
        echo '<pre>';
        var_dump($get);
        exit;

        $toutiao_province = isset($get['toutiao_province']) && !empty($get['toutiao_province']) ? htmlspecialchars($get['toutiao_province']) : '安徽';

        $params = ['page'=>1, 'toutiao_province'=>$toutiao_province];
        $router = '/account/toutiao/search';

        $sign = self::createSign($this->app_secret, $params);

        $access_token = self::createAccessToken($this->app_key, $sign, $router);
        $header = ["access-token:".$access_token];
        $res = self::doCurl($this->url, $header, $params);
        $res = json_decode($res, 1);

        AppUtil::dump($res);
        echo '<pre>';
        var_dump($res);
        exit;
    }

    public function actionTestapi()
    {
        // phpinfo();exit;
        // echo 1;exit;
        AppUtil::dump(Yii::$app->request->get());
        $request = Yii::$app->request;
        $get = $request->get();
        // echo '<pre>';
        // var_dump($get);
        // exit;

        $toutiao_province = isset($get['toutiao_province']) && !empty($get['toutiao_province']) ? htmlspecialchars($get['toutiao_province']) : '安徽';
        $page = isset($get['page']) && !empty($get['page']) ? intval($get['page']) : 1;

        $params = ['page'=>$page, 'toutiao_province'=>$toutiao_province];
        $router = '/account/toutiao/search';

        $sign = self::createSign($this->app_secret, $params);

        $access_token = self::createAccessToken($this->app_key, $sign, $router);
        $header = ["access-token:".$access_token];
        $res = self::doCurl($this->url, $header, $params);
        // $hdrs = array(
        //     'http' =>array('header' =>$header)
        // );
        // $res = file_get_contents('http://backend.my/index.php?r=site/accept&page=1&toutiao_province=%E5%AE%89%E5%BE%BD', false, stream_context_create($hdrs));
        $res = json_decode($res, 1);

        echo 'response:';
        AppUtil::dump($res);
        echo '<pre>';
        var_dump($res);
        exit;
    }

    // 生成签名sign
    public function createSign ($app_secret, $params='') 
    {   
        $text = '';
        if(!empty($params) && is_array($params)) {
            ksort($params);
            foreach ($params as $key => $value) {
                if(in_array($key, ['app_key','app_secret','sign'])) {
                    continue; //$params数组中只允许有请求的业务参数
                }
                $text .= $key . $value;
            }
        }

        return md5($app_secret."_".$text."_".$app_secret);
    }

    // 生成签名sign
    public function createAccessToken ($app_key,$sign,$router) 
    {   
        return base64_encode("{$app_key}:{$sign}:{$router}");
    }

    /**
     * @param $url  请求的url
     * @param array $params    传递get参数
     * @param array $post_data  传递post参数
     * @param int $time_out     响应超时时间
     * @return mixed
     */
    public static function doCurl($url, $header=[], $params=[], $post_data=[], $time_out=100)
    {
        $ch = curl_init();
        if (is_array($params)) {
            $query = http_build_query($params);
        } else {
            $query = $params;
        }
        if (strpos($url, '?') !== false) {
            $url .= "&" . $query;
        } else {
            $url .= "?" . $query;
        }
        // file_get_contents($url);
        // echo $url;
        // var_dump($header);
        // exit;
        if ($post_data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);//建立连接时候的超时设置
        curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);//接收信息时的超时设置
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        try{
            $data = curl_exec($ch);
        }catch (Exception $e){
            throw new Exception("网络请求出现错误");
        }
        // echo curl_errno($ch);exit;
        if(curl_errno($ch))
        {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $data;
    }

    function PostCurl($url, $header,$params = array())
    {
        $params['pcs'] = 3;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $output = curl_exec($ch);
        curl_close($ch);     
        return json_decode($output,true);
    }

    public function actionAdddata () {

        //--------------------------------------------------------------

        // 查询生成器
        // $query = new \yii\db\Query();
        // $res = $query->select('*')
        // ->from('country')
        // ->where(['like', 'name', 'C'])
        // ->limit(10)
        // ->all();

        //--------------------------------------------------------------

        // $db = Yii::$app->db;
        // $sql = 'SELECT * from country where name like "%C%"';
        // $res = $db->createCommand($sql)->queryAll();

        //--------------------------------------------------------------
        
        // 直接出查询结果
        // $res = Yii::$app->db->createCommand("SELECT count([[id]]) FROM {{country}}")->queryScalar();
        // $res = Yii::$app->db->createCommand("SELECT sum(population) FROM country")->queryScalar();

        //--------------------------------------------------------------
        
        // 事务
        // Yii::$app->db->transaction(function($db) {
        //     $sql1 = 'select * from country where code like "C%" AND population < 1000000000';
        //     $sql2 = 'update country set population = population * 1000';
        //     $r = $db->createCommand($sql1)->queryOne();
        //     // AppUtil::dump($r);
        //     if(!empty($r)) {
        //         $sql2 .= " where id = {$r['id']}";
        //         // echo $sql2;exit;
        //         $res = $db->createCommand($sql2)->execute();
        //     }
        // });

        //--------------------------------------------------------------


        echo '<pre>';
        var_dump($res);
        exit;

        // 获取 country 表的所有行并以 name 排序
        $countries = Country::find()->orderBy('name')->all();

        // 获取主键为 “US” 的行
        $country = Country::findOne('US');

        // 输出 “United States”
        echo $country->name;

        // 修改 name 为 “U.S.A.” 并在数据库中保存更改
        $country->name = 'U.S.A.';
        $country->save();
    }

    /**
     * 单文件上传
     *
     * @return string
     */
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            // echo '<pre>';var_dump($model);
            // exit;
            if ($model->upload()) {
                // 文件上传成功
                return json_encode(['code'=>0, 'msg'=>'success']);
            } else {
                var_dump($model->getFirstErrors());exit;
                return json_encode(['code'=>1, 'msg'=>'error']);
            }
        }

        return $this->render('upload', ['model' => $model]);
    }

    /**
     * 单多件上传
     *
     * @return string
     */
    public function actionUploads()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            $model->files = UploadedFile::getInstances($model, 'files');
            if ($model->uploads()) {
                // 文件上传成功
                return json_encode(['code'=>0, 'msg'=>'success']);
            } else {
                return json_encode(['code'=>2, 'msg'=>'error']);
            }

            /*$file = UploadedFile::getInstances($model, 'file');
            $path = 'uploads/'.date("YmdH",time()).'/';
            if ($file && $model->validate()) {
                if(!file_exists($path)){
                    mkdir($path, 0777);
                }
                foreach ($file as $key=>$fl) {
                    $fl->saveAs($path .$key.time() .$fl->baseName. '.' . $fl->extension);
                }
                Yii::$app->session->setFlash('success','上传成功！');
                return $this->redirect('index');
            }*/
        }

        return $this->render('uploads', ['model' => $model]);
    }



    //--------------------------------------------------------------------------
    


    // api begin-----------------------------------------------------------------
    /**
     * 获取榜单数据接口
     * @return string
     */
    public function actionTestt()
    {
        $keywords = AppUtil::loadParam("keywords"); //关键词
        $start_time = AppUtil::loadParam("start_time"); //开始时间 Y-m-d H:i:s
        $end_time = AppUtil::loadParam("end_time"); //结束时间

        // 从数组中随机取出一个关键词，防止请求有缓存
        if(file_exists(Yii::$app->basePath. DIRECTORY_SEPARATOR. 'config'. DIRECTORY_SEPARATOR. 'keywords.php')) {
            require_once Yii::$app->basePath. DIRECTORY_SEPARATOR. 'config'. DIRECTORY_SEPARATOR. 'keywords.php';
            $keywords_key = array_rand($keywords_arr);
            $keywords = $keywords_arr[$keywords_key];
        }

        if (empty($end_time)) {
            return json_encode(['errcode' => 500, 'errmsg' => "end_time不能为空"], JSON_UNESCAPED_UNICODE);
        }
        if ($start_time >= $end_time) {
            return json_encode(['errcode' => 500, 'errmsg' => "开始时间不能大于结束时间"], JSON_UNESCAPED_UNICODE);
        }
        if (strtotime($end_time) > time()-60*5) {
            return json_encode(['errcode' => 500, 'errmsg' => "结束时间不能大于当前时间五分钟"], JSON_UNESCAPED_UNICODE);
        }
        if ($start_time <= date('Y-m-d H:i:s',strtotime('-3 months'))) {
            return json_encode(['errcode' => 500, 'errmsg' => '开始时间不能超过当前时间3个月'], JSON_UNESCAPED_UNICODE);
        }

        // 查询redis中是否有当前查询条件的数据
        $redis_key = $keywords. $start_time. $end_time;
        if(isset($redis_key) && !empty($redis_key) && $this->redis->exists($redis_key)) {
            $response = $this->redis->get($redis_key);
            $data = json_decode($response, 1);
            return json_encode(['errcode' => 200, 'errmsg' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
        }

        $begin_timestamp = DateTime::getMillisecond(); //执行开始时间

        //关键词
        $keywords = self::filterKeywords($keywords);
        if (count($keywords) > 20) {
            return json_encode(['errcode' => 500, 'errmsg' => "关键词数量不能大于20"], JSON_UNESCAPED_UNICODE);
        }
        $keywords_fq = [];
        foreach ($keywords as $k1 => $v1) {
            $keywords_arr2 = explode("+", $v1);
            foreach($keywords_arr2 as $k2=>$v2) {
                $keywords_fq['bool']['should'][] = [
                    ["match_phrase"=>["news_title"=>$v2]]
                ];
            }
        }

        //es查询条件
        $esParams = [
            "from"=> 0,
            "size"=> 1,
            "sort"=>[["news_posttime" => ["order" => "desc"]]],
            "query"=>[
                "bool" => [
                    "filter" => [
                        "range" => [
                            "news_posttime" => [
                                "gte" => $start_time,
                                "lte" => $end_time
                            ]
                        ]
                    ],
                    "must" => [
                        $keywords_fq
                    ]
                ]
            ],
            "aggs" => [
                "platform" => [
                    "terms" => [
                        "field" => "platform",
                        "size" => 50
                    ]
                ]
            ]
        ]; 
        // echo json_encode($esParams);exit;
        // AppUtil::dump($esParams);

        $begin_timestamp_curl = DateTime::getMillisecond(); //curl执行开始时间

        $url = "http://es-cn-nif20cyw10004km98.elasticsearch.aliyuncs.com:9200/*/_search?pretty";

        //检索es数据
        $response = $this->esfetch($url, [], [], $esParams);
        // AppUtil::dump(json_decode($response, 1));exit;

        $end_timestamp_curl = DateTime::getMillisecond(); //curl执行结束时间

        if (empty($response)) {
            return json_encode(['errcode' => 500, 'errmsg' => "数据不存在，请稍后再试"], JSON_UNESCAPED_UNICODE);
        }

        $data = [];
        $response = json_decode($response, 1);
        if (isset($response['aggregations']['platform']['buckets']) && !empty($response['aggregations']['platform']['buckets'])) {
            foreach ($response['aggregations']['platform']['buckets'] as $val) {
                $data[] = [
                    $val['key'] => $val['doc_count']
                ];
            }
        }

        // 设置查询数据到redis
        $this->redis->set($redis_key, json_encode($data));

        $end_timestamp = DateTime::getMillisecond(); //执行结束时间

        //写入接口日志
        // WanshangLog::saveResultData(1, json_encode($data), 5, $end_timestamp - $begin_timestamp);

        return json_encode(['errcode' => 200, 'errmsg' => 'success','data' => $data], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 发送网络请求
     * @param $esParams
     * @return mixed
     */
    public static function esfetch($url, $params = [], $header= [], $post_data = [], $time_out = 1)
    {

        try {
            $user = 'php_search';
            $password = 'ZChjo6yPuEVyTzdq';
            $userpwd = $user.':'.$password;

            $ch = curl_init();
            if (is_array($params)) {
                $query = http_build_query($params);
            } else {
                $query = $params;
            }
            if (strpos($url, '?') !== false) {
                $url .= "&" . $query;
            } else {
                $url .= "?" . $query;
            }
            if ($post_data) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            }

            // echo $url;exit;
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_USERPWD , $userpwd);
            if($header) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type:application/json"));
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);//建立连接时候的超时设置
            curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);//接收信息时的超时设置
            // curl_setopt($ch, CURLOPT_REFERER, self::getApiHost());
            $res = curl_exec($ch);

            if(curl_errno($ch)) {
                $errmsg = curl_error($ch);
                $log_dir = 'log'.DIRECTORY_SEPARATOR.'error'. DIRECTORY_SEPARATOR. date('Ymd');
                $log_file = date('H'). '.log';
                $log_dir = dirname(Yii::$app->basePath). DIRECTORY_SEPARATOR. $log_dir;
                if(!file_exists($log_dir)){
                    mkdir($log_dir, 0777, true);
                }
                $log = $log_dir. DIRECTORY_SEPARATOR. $log_file;
                error_log($errmsg. PHP_EOL, 3, $log);
            }

            curl_close($ch);

        }catch (HttpException $e) {
            curl_close($ch);

            if ($e->getMessage()) {
                $errmsg = $e->getMessage();
                $log_dir = 'log'.DIRECTORY_SEPARATOR.'exception'. DIRECTORY_SEPARATOR. date('Ymd');
                $log_file = date('H'). '.log';
                $log_dir = dirname(Yii::$app->basePath). DIRECTORY_SEPARATOR. $log_dir;
                if(!file_exists($log_dir)){
                    mkdir($log_dir, 0777, true);
                }
                $log = $log_dir. DIRECTORY_SEPARATOR. $log_file;
                error_log($errmsg. PHP_EOL, 3, $log);
            }

            // $errcode = ErrorUtil::ERROR_NO_RESPONSE;
            // $errmsg = ErrorUtil::getErrorMsg($errcode. '---'. $e->getMessage());
            // throw new UnprocessableEntityHttpException($errmsg, $errcode);

        }
        return $res;
    }

    /**
     * 对keywords参数进行处理
     * @param $keywords
     * @return mixed|string
     */
    public static function filterKeywords($keywords)
    {
        $keywords = AppUtil::strFilter($keywords);
        $keywords = preg_replace("/,|，|、/",",",$keywords);
        $keywords = explode(",",$keywords);
        $keywords = array_values(array_unique($keywords));
        $keywords = AppUtil::arrFilter($keywords);
        return $keywords;
    }
    // api end-----------------------------------------------------------------

}
