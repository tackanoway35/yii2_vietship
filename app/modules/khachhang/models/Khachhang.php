<?php
namespace app\modules\khachhang\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\behaviors\Taggable;
use yii\easyii\models\Photo;
use yii\helpers\StringHelper;
use yii\easyii\models\Hinhthucthanhtoan;
use yii\easyii\models\Diachilayhang;
use app\modules\goikhachhang\models\Goikhachhang;
use \app\modules\donhang\models\Donhang;
use yii\easyii\models\Setting;

class Khachhang extends \yii\easyii\components\ActiveRecord implements \yii\web\IdentityInterface
{
    public $arr_tinh_nang_an = [];
    
    public static function tableName()
    {
        return 'khach_hang';
    }

    public function rules()
    {
        return [
            [['ten_dang_nhap', 'email'], 'unique', 'message' => '{attribute} đã tồn tại'],
            [['ten_dang_nhap', 'email', 'so_dien_thoai', 'dia_chi', 'ten_hien_thi'], 'required', 'message' => "Bạn chưa nhập {attribute}"],
            [
                [
                    'ten_dang_nhap', 'ten_hien_thi', 'email',
                    'so_dien_thoai', 'dia_chi', 'website', 'facebook', 'tinh_nang_an'
                ], 'string', 'message' => "{attribute} phải là kiểu chuỗi"
            ],
            [['mat_khau', 'ten_shop', 'forgot_password_code'], 'safe'],
            ['email', 'email', 'message' => "Không đúng định dạng email"],
            [['time'], 'integer'],
            [['gkh_id', 'sodu', 'sono', 'cho_thanh_toan', 'don_hang_da_xuat_hoa_don', 'setting'], 'safe'],
            ['time', 'default', 'value' => time()],
            ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')],
            ['slug', 'default', 'value' => null],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ten_dang_nhap' => 'Tên đăng nhập',
            'mat_khau' => "Mật khẩu",
            'ten_hien_thi' => "Tên cá nhân/cửa hàng / công ty (Tên hiển thị)",
            'so_dien_thoai' => "Số điện thoại",
            'dia_chi' => "Địa chỉ",
            'tinh_nang_an' => "Tính năng ẩn",
            'ten_shop' => "Tên shop",
            'gkh_id' => "Gói khách hàng"
        ];
    }

    public function behaviors()
    {
        return [
            'seoBehavior' => SeoBehavior::className(),
            'taggabble' => Taggable::className(),
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'ten_dang_nhap',
                'ensureUnique' => true
            ],
        ];
    }

    public static function tinhSoDuNo($kh_id, $hinh_thuc_thanh_toan, $tien_ship, $tien_thu_ho) {
        $model = static::findOne($kh_id);
        switch($hinh_thuc_thanh_toan) {
            case 'Người gửi thanh toán':
            case 'Người nhận thanh toán':
                $model->sodu = $model->sodu + $tien_thu_ho;
            break;
            case 'Thanh toán sau COD':
                $model->sodu = $model->sodu + ( $tien_thu_ho - $tien_ship );
            break;
            case 'Thanh toán sau':
                $model->sono = $model->sono + $tien_ship;
            break;
        }

        if ($model->save(false)) {
            return true;
        }
        return false;
    }

    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['item_id' => 'khachhang_id'])->where(['class' => self::className()])->sort();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // if ($this->isNewRecord) {
            //     $this->auth_key = $this->generateAuthKey();
            //     $this->mat_khau = $this->hashPassword($this->mat_khau);
            // } else {
            //     $this->mat_khau = $this->mat_khau != '' ? $this->hashPassword($this->mat_khau) : $this->oldAttributes['mat_khau'];
            // }
            return true;
        } else {
            return false;
        }
    }

    public static function findIdentity($id)
    {
        $result = null;
        try {
            $result = $id == self::$rootUser['kh_id']
                ? static::createRootUser()
                : static::findOne($id);
        } catch (\yii\base\InvalidConfigException $e) {
        }
        return $result;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        if ($username === self::$rootUser['ten_dang_nhap']) {
            return static::createRootUser();
        }
        return static::findOne(['ten_dang_nhap' => $username]);
    }

    public function getId()
    {
        return $this->kh_id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return $this->mat_khau === $this->hashPassword($password);
    }

    private function hashPassword($password)
    {
        return sha1($password . $this->getAuthKey() . Setting::get('password_salt'));
    }

    private function generateAuthKey()
    {
        return Yii::$app->security->generateRandomString();
    }

    public static function createRootUser()
    {
        return new static(array_merge(self::$rootUser, [
            'mat_khau' => Setting::get('root_password'),
            'auth_key' => Setting::get('root_auth_key')
        ]));
    }

    public function isRoot()
    {
        return $this->ten_dang_nhap === self::$rootUser['ten_dang_nhap'];
    }
    
    public function getDiachilayhang()
    {
        return $this->hasMany(Diachilayhang::className(), ['kh_id' => 'kh_id']);
    }
    
    public function getHinhthucthanhtoan()
    {
        return $this->hasOne(Hinhthucthanhtoan::className(), ['kh_id' => 'kh_id']);
    }

    public function getDonhang() {
        return $this->hasMany(Donhang::className(), ['kh_id' => 'kh_id']);
    }

    public static function checkLogin($email, $pw) {
        $model = self::find()
        ->where(['ten_dang_nhap' => $email])
        ->andWhere(['mat_khau' => $pw])
        ->one();
        if (count($model) > 0) {
            return true;
        }
        return false;
    }

    public static function checkSettingProfile($user) {
        // Nếu chưa thêm thông tin thì vào trang thông tin
        $kh_id = $user['kh_id'];
        $model_kh = Khachhang::find()->where(['kh_id' => $kh_id])->one();
        $setting = (int)$model_kh['setting'];
        if ($setting === 0) {
            return false;
        }
        return true;
    }
}