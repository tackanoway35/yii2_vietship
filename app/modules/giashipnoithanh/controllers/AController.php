<?php
namespace app\modules\giashipnoithanh\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;

use yii\easyii\components\Controller;
use app\modules\giashipnoithanh\models\Giashipnoithanh;
use \app\modules\duongpho\models\Duongpho;
use \app\modules\goidichvu\models\Goidichvu;

class AController extends Controller
{
    public function actionIndex()
    {
        $data = new ActiveDataProvider([
            'query' => Giashipnoithanh::find(),
            'pagination' => [
                'pageSize' => 0
            ]
        ]);

        return $this->render('index', [
            'data' => $data
        ]);
    }

    public function actionCreate()
    {
        $model = new Giashipnoithanh;

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->save()){
                    $this->flash('success', 'Tạo giá ship nội thành thành công!');
                    return $this->redirect(['/admin/'.$this->module->id]);
                }
                else{
                    $this->flash('error', 'Tạo giá ship nội thành thất bại! Hãy kiểm tra lại thông tin vừa khởi tạo!');
                    return $this->refresh();
                }
            }
        }
        else {
            return $this->render('create', [
                'model' => $model
            ]);
        }
    }

    public function actionEdit($id)
    {
        $model = Giashipnoithanh::findOne($id);

        if($model === null){
            $this->flash('error', "Không tìm thấy giá ship nội thành nào");
            return $this->redirect(['/admin/'.$this->module->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->save()){
                    $this->flash('success', "Cập nhật giá ship nội thành thành công");
                }
                else{
                    $this->flash('error', "Cập nhật giá ship nội thành thất bại. Hãy kiểm tra lại thông tin!");
                }
                return $this->refresh();
            }
        }
        else {
            return $this->render('edit', [
                'model' => $model
            ]);
        }
    }

    public function actionDelete($id)
    {
        if(($model = Giashipnoithanh::findOne($id))){
            $model->delete();
        } else {
            $this->error = "Không tìm thấy giá ship nội thành nào";
        }
        return $this->formatResponse("Xóa giá ship nội thành thành công");
    }
    
    public function actionCalculatePrice()
    {
        $model = new Giashipnoithanh();
        
        if($model->load(Yii::$app->request->post()))
        {
            $dataForm = Yii::$app->request->post()[$model->formName()];
            $noi_lay_id = $dataForm['noi_lay_id'];
            $noi_giao_id = $dataForm['noi_giao_id'];
            $gdv_id = $dataForm['gdv_id'];
            // Tìm khu vực lấy và khu vực giao
            $kvl_id = Duongpho::find()->where(['dp_id' => $noi_lay_id])->one()['kv_id'];
            $kvg_id = Duongpho::find()->where(['dp_id' => $noi_giao_id])->one()['kv_id'];

            // Tính thời gian lấy và giao hàng
            $thoigianship = $this->thongBaoThoiGianShip($gdv_id);

            //Tìm đơn giá
            $dongia = Giashipnoithanh::find()->where([
                'kvl_id' => $kvl_id,
                'kvg_id' => $kvg_id,
                'gdv_id' => $gdv_id
            ])->one()['don_gia'];
            if($dongia)
            {
                $result = [
                    'dongia' => $dongia,
                    'thoigianship' => $thoigianship
                ];
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            return -1;
        }
        return $this->render('calculateprice', ['model' => $model]);
    }

    //Xử lý thông báo giờ lấy hàng và giờ giao hàng ajax thông qua gói dịch vụ
    public function thongBaoThoiGianShip($gdv_id)
    {
        $gdv = Goidichvu::find()->where(['gdv_id' => $gdv_id])->one()['ten_goi_dich_vu'];
        $currentTimeStamp = time();
        
        $currentHour = (int)date('H', $currentTimeStamp);
        $currentMinute = (int)date('i', $currentTimeStamp);
        $dayOfWeek = (int)date('w', $currentTimeStamp); //0 : Sunday, 6 : Saturday
        
        //Quy đổi hết ra phút và so sánh
        $mocGio = 11*60 + 30;
        $gioHienTai = $currentHour*60 + $currentMinute;
        
        if($gdv == 'Chuyển nhanh')
        {
            if($dayOfWeek != 0) //Không làm ngày chủ nhật
            {
                if($gioHienTai <= $mocGio) //<= 11h30
                {
                    $thoigianship = '-- Thời gian lấy: 8h30 - 12h hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>';
                    $thoigianship .= '-- Thời gian giao: 13h30 - 17h hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>.';
                }
                else //>11h30
                {
                    $thoigianship = '-- Thời gian lấy: 13h30 - 17h30 hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>';
                    if($dayOfWeek == 6) //Lấy vào chiều thứ 7 và giao vào sáng thứ 2 cộng 2 ngày
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h thứ 2 ('.$ngay_giao.').<br>';
                    }else
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h hôm sau ('.$ngay_giao.').<br>';
                    }
                }
            } else {
                $thoigianship = 'Không làm việc vào chủ nhật';
            }
        }
        elseif($gdv == 'Tiết kiệm')
        {
            if($dayOfWeek != 0)
            {
                if($gioHienTai <= $mocGio) //<= 11h30
                {
                    $thoigianship = '-- Thời gian lấy: 8h30 - 17h30 hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>';
                    if($dayOfWeek == 6) //Thứ 7 lấy vào sáng hôm nay và giao sáng thứ 2
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h thứ 2 ('.$ngay_giao.').<br>';
                    }else
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h hôm sau ('.$ngay_giao.').<br>';
                    }
                }
                else //>11h30
                {
                    if($dayOfWeek == 6) //Chiều thứ 7 nhận đơn sáng thứ 2 lấy và giao chiều thứ 2
                    {
                        $ngay_lay = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $ngay_giao = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $thoigianship = '-- Thời gian lấy: 8h30 - 12 thứ 2 ('.$ngay_lay.').<br>';
                        $thoigianship .= '-- Thời gian giao: 13h30 - 17h30 thứ 2 ('.$ngay_giao.').<br>';
                    }else
                    {
                        $ngay_lay = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $ngay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $thoigianship = '-- Thời gian lấy: 8h30 - 12 hôm sau ('.$ngay_lay.').<br>';
                        $thoigianship .= '-- Thời gian giao: 13h30 - 17h30 hôm sau ('.$ngay_giao.').<br>';
                    }
                }
            } else {
                $thoigianship = 'Không làm việc vào chủ nhật';
            }
        }
        elseif($gdv == 'Hỏa tốc')
        {
            if($dayOfWeek != 0)
            {
                //8h30 - 11h30 : 480 - 690
                //13h30 - 16h30 : 810 - 990
                //TH1 : $gioHienTai < 480 -> status = 0 (Ngoài giờ hành chính) giao từ 8h - 10h hôm nay
                if($gioHienTai < 480)
                {
                    $ngay_lay_giao = date('d/m/Y', $currentTimeStamp);
                    $thoigianship = '-- Thời gian lấy và giao: 8h - 10h hôm nay ('.$ngay_lay_giao.').<br>';
                }
                //TH2 : 690 - 810 -> status = 1 (Ngoài giờ hành chính) giao 13h30 - 15h30 hôm nay
                elseif($gioHienTai > 690 && $gioHienTai < 810)
                {
                    $ngay_lay_giao = date('d/m/Y', $currentTimeStamp);
                    $thoigianship = '-- Thời gian lấy và giao: 13h30 - 15h30 hôm nay ('.$ngay_lay_giao.').<br>';
                }
                //TH3 : >990 -> status = 2 (Ngoài giờ hành chính) giao 8h - 10h hôm sau
                elseif($gioHienTai > 990)
                {
                    $ngay_lay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                    $thoigianship = '-- Thời gian lấy và giao: 8h - 10h hôm sau ('.$ngay_lay_giao.').<br>';
                }
                //TH4 : 480 <=  <= 690 -> status = 3 (Trong giờ hành chính buổi sáng) giao sau 2 tiếng
                elseif(($gioHienTai >= 480 && $gioHienTai <= 690) || ($gioHienTai >= 810 && $gioHienTai <= 990))
                {
                    $ngay_lay_giao = date('d/m/Y', $currentTimeStamp);
                    $lay = $currentHour.'h'.$currentMinute;
                    $giao = ($currentHour+2).'h'.$currentMinute;
                    $strLayGiao = $lay.' - '.$giao;
                    $thoigianship = '-- Thời gian lấy và giao: '.$strLayGiao.' hôm nay ('.$ngay_lay_giao.').<br>';
                }
            } else {
                $thoigianship = 'Không làm việc vào chủ nhật';
            }
        }
        
        return $thoigianship;
    }
}