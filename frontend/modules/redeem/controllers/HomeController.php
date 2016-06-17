<?php

namespace frontend\modules\redeem\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use app\base\BaseController;
use common\api\VsoApi;
use common\models\User;
use common\models\Goods;


class HomeController extends BaseController
{

    public $layout = 'layout';
    public $enableCsrfValidation = false;

    /**
     * 用户列表
     * @return type
     */
    public function actionIndex()
    {
        $g_mdl = new Goods();

        //判断用户是否手机认证
        $_goods_list = $g_mdl->_get_list(['>' , 'gid', 0], 'gid DESC', 1, 20);
        $_data = [
            'user' => $this->user,
            'goods_list' => $_goods_list,
        ];
        return $this->render('index', $_data);
    }

    /**
     * 用户列表
     * @return type
     */
    public function actionListView()
    {
        return $this->render('list');
    }

    /**
     * 关于我们
     * @return type
     */
    public function actionAbout()
    {
        return $this->render('about');
    }





}
