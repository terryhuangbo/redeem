<?php

namespace common\models;

use Yii;
use yii\base\Exception;
use common\models\Auth;
use common\models\Points;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $uid
 * @property string $nick
 * @property string $name
 * @property string $avatar
 * @property integer $mobile
 * @property string $email
 * @property integer $points
 * @property integer $user_type
 * @property integer $name_card
 * @property string $wechat_openid
 * @property integer $user_status
 * @property integer $update_at
 * @property integer $create_at
 */
class User extends \yii\db\ActiveRecord
{

    /**
     * 用户类型
     */
    const TYPE_COMMON = 1;//普通用户
    const TYPE_SELLER = 2;//销售
    const TYPE_DESIGNER = 3;//家装设计师

    const NO_DELETE = 1;//启用
    const IS_DELETE = 2;//禁用


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['points', 'user_type', 'user_status', 'update_at', 'create_at'], 'integer'],
            [['nick', 'name'], 'string', 'max' => 30],
            [['avatar', 'name_card'], 'string', 'max' => 100],
            [['email'], 'string', 'max' => 40],
            [['wechat_openid'], 'string', 'max' => 50],
            [['mobile'], 'unique', 'message' => '手机号码已经注册过了'],
            [['wechat_openid'], 'unique', 'message' => '微信号已经注册过了'],
            [['create_at', 'update_at'], 'default', 'value' => time()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户ID',
            'nick' => '用户微信昵称',
            'name' => '用户真实姓名',
            'avatar' => '用户微信头像',
            'mobile' => '用户手机号码',
            'email' => '用户邮箱',
            'name_card' => '名片',
            'points' => '积分',
            'user_type' => '用户类型（1-普通用户；2-销售；3-家装设计师）',
            'wechat_openid' => '微信Open Id',
            'user_status' => '状态（1-启用；2-禁用）',
            'update_at' => '更新时间',
            'create_at' => '创建时间',
        ];
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * 获取信息
     * @param $where array
     * @return array|boolean
     **/
    public function _get_info($where = []) {
        if (empty($where)) {
            return false;
        }

        $obj = self::findOne($where);
        if (!empty($obj)) {
            return $obj->toArray();
        }
        return false;
    }

    /**
     * 获取列表
     * @param $where array
     * @param $order string
     * @return array|boolean
     */
    public function _get_list($where = [], $order = 'created_at desc', $page = 1, $limit = 20) {
        $_obj = self::find();
        if (isset($where['sql']) || isset($where['params'])) {
            $_obj->where($where['sql'], $where['params']);
        } else if (is_array($where)) {
            $_obj->where($where);
        }

        $_obj->orderBy($order);

        if (!empty($limit)) {
            $offset = max(($page - 1), 0) * $limit;
            $_obj->offset($offset)->limit($limit);
        }
        return $_obj->asArray(true)->all();
    }

    /**
     * 获取总条数
     * @param $where array
     * @return int
     */
    public function _get_count($where = []) {
        $_obj = self::find();
        if (isset($where['sql']) || isset($where['params'])) {
            $_obj->where($where['sql'], $where['params']);
        } else {
            $_obj->where($where);
        }
        return intval($_obj->count());
    }

    /**
     * 添加记录-返回新插入的自增id
     **/
    public static function _add($data) {
        if (!empty($data) && !empty($data['username'])) {
            try {
                $_mdl = new self;

                foreach ($data as $k => $v) {
                    $_mdl->$k = $v;
                }
                if(!$_mdl->validate()) {//校验数据
                    return false;
                }
                $ret = $_mdl->insert();
                if ($ret !== false) {
                    return self::getDb()->getLastInsertID();
                }
                return false;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 保存记录
     * @param $data array
     * @return array|boolean
     */
    public function _save($data) {
        if (!empty($data)) {
            $_mdl = new self();

            try {
                foreach ($data as $k => $v) {
                    $_mdl->$k = $v;
                }

                if (!empty($data['uid'])) {//修改
                    $id = $data['uid'];
                    $ret = $_mdl->updateAll($data, ['uid' => $id]);
                } else {//增加
                    $ret = $_mdl->insert();
                }

                if ($ret !== false) {
                    return true;
                }
                return false;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 删除记录
     * @param $where array
     * @return array|boolean
     */
    public static function _delete($where) {
        if (empty($where)) {
            return false;
        }
        try {
            return (new self)->deleteAll($where);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 用户类型
     * @param $type int
     * @return array|boolean
     */
    public static function _get_user_type($type = 1){
        switch(intval($type)){
            case self::TYPE_COMMON:
                $_name = '普通用户';
                break;
            case self::TYPE_SELLER:
                $_name = '销售';
                break;
            case self::TYPE_DESIGNER:
                $_name = '家装设计师';
                break;
            default:
                $_name = '销售';
                break;
        }
        return $_name;
    }

    /**
     * 用户类型列表
     * @return array|boolean
     */
    public static function _get_user_type_list(){
        return [
            self::TYPE_COMMON => '普通用户',
            self::TYPE_SELLER => '销售',
            self::TYPE_DESIGNER => '家装设计师',
        ];
    }

    /**
     * 用户状态
     * @param $status int
     * @return array|boolean
     */
    public static function _get_user_status($status = 1){
        switch(intval($status)){
            case self::NO_DELETE:
                $_name = '启用';
                break;
            case self::IS_DELETE:
                $_name = '禁用';
                break;
            default:
                $_name = '启用';
                break;
        }
        return $_name;
    }

    /**
     * 用户状态列表
     * @return array|boolean
     */
    public static function _get_user_status_list(){
        return [
            self::NO_DELETE => '启用',
            self::IS_DELETE => '禁用',
        ];
    }

    /**
     * 新增用户记录
     *@param $param array
     * @return array|boolean
     */
    public static function _add_user($param){
        session_start();
        //验证手机号
        if(empty($param['mobile'])){
            return ['code' => -20001, 'msg' => '手机号不能为空'];
        }
        $pattern = '/^1[3|5|7|8][0-9]{9}$/';
        if(!preg_match($pattern, $param['mobile'])){
            return ['code' => -20002, 'msg' => '手机号格式不正确'];
        }
        $mobile = $param['mobile'];

        //验证验证码
        if(!isset($_SESSION[md5($mobile)])){
            return ['code' => -20003, 'msg' => '请获取验证码'];
        }
        if(empty($param['verifycode'])){
            return ['code' => -20004, 'msg' => '验证码不能为空'];
        }
        if($_SESSION[md5($mobile)] != $param['verifycode']){
            return ['code' => -20005, 'msg' => '验证码不正确'];
        }

        //验证微信公众号
        if(empty($param['wechat_openid'])){
            return ['code' => -20006, 'msg' => '微信公众号不能为空'];
        }

        $wechat_openid = $param['wechat_openid'];

        $u_mdl = new self;
        $a_mdl = new Auth();

        //验证是否已经手机认证
        $user = $u_mdl->_get_info(['mobile' => $mobile]);
        if($user){
            return ['code' => 20001, 'msg' => '已经手机认证过了，直接登录', 'data' => ['uid' => $user['uid']]];
        }

        //开启事务
        $transaction = yii::$app->db->beginTransaction();
        try {

            //用户表插入记录
            $u_mdl->mobile = $mobile;
            $u_mdl->wechat_openid = $wechat_openid;
            if(!$u_mdl->validate()){
                $error = $u_mdl->errors;
                $msg = current($error)[0];//获取错误信息
                return ['code' => -20007, 'msg' => $msg];
            }
            if(!$u_mdl->save()){
                $transaction->rollBack();
                throw new Exception('用户信息保存失败');
            }
            $uid = self::getDb()->getLastInsertID();

            //添加积分更新记录
            $ret = Points::_add_points($uid, Points::POINTS_MOBILEAUTH);
            if($ret['code'] < 0){
                $transaction->rollBack();
                throw new Exception($ret['msg']);
            }

            //认证表插入记录
            $res_a = $a_mdl->_save([
                'uid' => $uid,
                'mobile' => $mobile,
                'wechat_openid' => $wechat_openid
            ]);
            if(!$res_a){
                $transaction->rollBack();
                throw new Exception('认证信息保存失败');
            }

            //执行
            $transaction->commit();

            return ['code' => 20000, 'msg' => '保存成功！', 'data' => ['uid' => $uid]];

        } catch (Exception $e) {
            $transaction->rollBack();
            return ['code' => -20000, 'msg' => $e->getMessage()];
        }

    }

}
