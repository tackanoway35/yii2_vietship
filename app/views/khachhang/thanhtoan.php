<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\easyii\models\Admin;
use app\modules\khachhang\models\Khachhang;
use yii\bootstrap\Modal;
use richardfan\widget\JSRegister;
use \app\modules\goidichvu\models\Goidichvu;

$this->title = "Thanh toán với khách hàng";

?>

<style>
    .font14 {
        font-size: 14px;
        font-weight: 600;
    }
    .money {
        font-size: 16px;
        background-color: #f39c12;
        height: 20px;
        border-radius: 0.25em;
        color: #FFF;
        padding-left: 8px;
        padding-right: 8px;
    }
    .td14 {
        font-size: 14px;
    }
    .table thead tr, .table tbody tr td {
        vertical-align: baseline !important
    }
</style>

<div class="ui-content-body">
    <div class="ui-container" style="padding: 10px; background-color: #fff">
        <div class="row">
            <div class="col-md-12 col-xs-12 col-sm-12">
                <?= $this->render('_menu') ?>
            </div>
        </div>

        <?php
            if (isset($models)):
        ?>
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <section class="panel">
                        <header class="panel-heading panel-border">
                            Thanh toán với khách hàng
                            <span class="tools pull-right">
                                <a class="collapse-box fa fa-chevron-down" href="javascript:;"></a>
                            </span>
                        </header>
                        <div class="panel-body">
                            <div class='col-md-12 col-sm-12 col-xs-12'>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Mã KH</th>
                                            <th>Khách hàng</th>
                                            <th>Thông tin thanh toán</th>
                                            <th>Số dư</th>
                                            <th>Số nợ</th>
                                            <th>Tiền thu hộ</th>
                                            <th>Tiền trả khách</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($models as $model):?>
                                            <?php if (count($model['donhang']) > 0):?>
                                                <?php $donhang = $model['donhang'];?>
                                                <tr>
                                                    <!--Mã khách hàng-->
                                                    <td class='font14'>
                                                        <?= $model['kh_id']?>
                                                    </td>
                                                    <!--Khách hàng-->
                                                    <td class='font14'>
                                                        <?php
                                                            $ten = $model['ten_hien_thi'];
                                                            $dia_chi = $model['dia_chi'];
                                                            $so_dt = $model['so_dien_thoai'];
                                                            $email = $model['email'];
                                                            echo $ten;
                                                            echo '<br>';
                                                            echo $dia_chi;
                                                            echo '<br>';
                                                            echo $so_dt;
                                                            echo '<br>';
                                                            echo $email;
                                                        ?>
                                                    </td>
                                                    <!--Thông tin thanh toán-->
                                                    <td class='font14' style='text-align: center'>
                                                        <?php
                                                            $httt = $model['hinhthucthanhtoan']['hinh_thuc_thanh_toan'];
                                                            if ($httt === 'Tiền mặt'):
                                                                echo 'Tiền mặt';
                                                        ?>
                                                        <?php else:
                                                            $ttnh = json_decode($model['hinhthucthanhtoan']['thong_tin_ngan_hang'], true);
                                                            echo 'Chuyển khoản';
                                                        ?>
                                                        <?php
                                                            Modal::begin([
                                                                'header'=> '<h3 style="text-align : center;">Thông tin tài khoản của '.$model['ten_hien_thi'].'</h3>',
                                                                'id'    => 'kh'.$model['kh_id'],
                                                                'size'  => 'modal-lg',
                                                            ])                                                
                                                        ?>
                                                            <div class='row'>
                                                                <?php foreach($ttnh as $item):?>
                                                                    <div class='col-md-6 col-sm-12 col-xs-12'>
                                                                        <div class="panel panel-success">
                                                                            <header class="panel-heading">
                                                                                <?= $item['ten_ngan_hang']?>
                                                                            </header>
                                                                            <div class="panel-body">
                                                                                <div class='row'>
                                                                                    <div class='col-md-6 col-sm-6 col-xs-6'>
                                                                                        Chi nhánh:
                                                                                    </div>
                                                                                    <div class='col-md-6 col-sm-6 col-xs-6'>
                                                                                        <?= $item['chi_nhanh']?>
                                                                                    </div>
                                                                                </div>

                                                                                <div class='row'>
                                                                                    <div class='col-md-6 col-sm-6 col-xs-6'>
                                                                                        Chủ tài khoản:
                                                                                    </div>
                                                                                    <div class='col-md-6 col-sm-6 col-xs-6'>
                                                                                        <?= $item['chu_tai_khoan']?>
                                                                                    </div>
                                                                                </div>

                                                                                <div class='row'>
                                                                                    <div class='col-md-6 col-sm-6 col-xs-6'>
                                                                                        Số tài khoản:
                                                                                    </div>
                                                                                    <div class='col-md-6 col-sm-6 col-xs-6'>
                                                                                        <?= $item['so_tai_khoan']?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach;?>
                                                            </div>
                                                        <?php
                                                            Modal::end();
                                                        ?>
                                                            <button
                                                                data-toggle='modal'
                                                                data-target='#kh<?= $model['kh_id']?>'
                                                                type="button"
                                                                style='margin-top: 3px;'
                                                                class='btn btn-sm btn-success btn-block'
                                                            >
                                                            <i class='glyphicon glyphicon-th-list' style="vertical-align: baseline !important"></i>
                                                            Chi tiết
                                                            </button>
                                                        <?php endif;?>
                                                    </td>
                                                    <!--Số dư-->
                                                    <td class='font14' style='text-align: center'>
                                                        <?= $model['sodu'] > 0 ? $model['sodu'].' VNĐ' : 0?>
                                                    </td>
                                                    <!--Số nợ-->
                                                    <td class='font14' style='text-align: center'>
                                                        <?php
                                                            $so_du = 0;
                                                            $so_no = 0;
                                                            foreach($donhang as $model_dh) {
                                                                $so_no += (int)$model_dh['so_no'];
                                                            }
                                                        ?>
                                                        <?= $so_no > 0 ? number_format($so_no, 0, '', ',').' VNĐ' : 0?>
                                                    </td>
                                                    <!--Tiền thu hộ-->
                                                    <td class='font14' style='text-align: center'>
                                                        <?php
                                                            $tong_tien_thu_ho = 0;
                                                            Modal::begin([
                                                                'header'=> '<h3 style="text-align : center;">Chi tiết tiền thu hộ của '.$model['ten_hien_thi'].'</h3>',
                                                                'id'    => 'tienthuho'.$model['kh_id'],
                                                                'size'  => 'modal-lg',
                                                            ])
                                                        ?>
                                                            <div class='row'>
                                                                <div class='col-md-12 col-sm-12 col-xs-12'>
                                                                    <table class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Mã đơn</th>
                                                                                <th>Người nhận</th>
                                                                                <th>Gói dịch vụ</th>
                                                                                <th>Hình thức thanh toán</th>
                                                                                <th>Cước v/c</th>
                                                                                <th>Tiền thu hộ</th>
                                                                                <th>Ngày tạo</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach($donhang as $dh):?>
                                                                                <tr>
                                                                                    <td>
                                                                                        <?= $dh['ma_don_hang']?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?php
                                                                                            $arr_nguoi_nhan = json_decode($dh['nguoi_nhan'], true);
                                                                                            $nn_ten = $arr_nguoi_nhan['ten'];
                                                                                            $nn_so_dien_thoai = $arr_nguoi_nhan['so_dien_thoai'];
                                                                                            $nn_dia_chi_giao_hang = $arr_nguoi_nhan['dia_chi_giao_hang'];
                                                                                            echo $nn_ten.'<br>';
                                                                                            echo $nn_dia_chi_giao_hang.'<br>';
                                                                                            echo $nn_so_dien_thoai;
                                                                                        ?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?=
                                                                                            Goidichvu::find()->where(['gdv_id' => $dh['gdv_id']])->one()['ten_goi_dich_vu'];
                                                                                        ?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?= $dh['hinh_thuc_thanh_toan']?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?= $dh['tong_tien'] > 0 ? number_format((float)($dh['tong_tien']), 0, '', ',').' VNĐ' : 0?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?php
                                                                                            $trang_thai = $dh['trang_thai'];
                                                                                            if ($trang_thai == 'Đã hoàn') {
                                                                                                echo 0;
                                                                                            } else {
                                                                                                echo number_format((float)($dh['tien_thu_ho']), 0, '', ',').' VNĐ';
                                                                                            }
                                                                                        ?>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?= date('H:i d/m/Y', $dh['time'])?>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php endforeach;?>        
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        <?php
                                                            Modal::end();
                                                        ?>
                                                        <?php
                                                            foreach($donhang as $dh) {
                                                                $httt = $dh['hinh_thuc_thanh_toan'];
                                                                $trang_thai = $dh['trang_thai'];
                                                                switch($httt) {
                                                                    case 'Người gửi thanh toán':
                                                                    case 'Người nhận thanh toán':
                                                                    case 'Thanh toán sau':
                                                                        if ($trang_thai == 'Đã hoàn') {
                                                                            $tien_thu_ho = 0;
                                                                        } else {
                                                                            $tien_thu_ho = $dh['tien_thu_ho'] > 0 ? $dh['tien_thu_ho'] : 0;
                                                                        }
                                                                        $tong_tien_thu_ho += $tien_thu_ho;  
                                                                    break;
                                                                    case 'Thanh toán sau COD':
                                                                        if ($trang_thai == 'Đã hoàn') {
                                                                            $tien_thu_ho = 0;
                                                                            $tong_tien_thu_ho += $tien_thu_ho;
                                                                        } else {
                                                                            $tien_thu_ho = $dh['tien_thu_ho'] > 0 ? $dh['tien_thu_ho'] : 0;
                                                                            $tong_tien = $dh['tong_tien'] > 0 ? $dh['tong_tien'] : 0;
                                                                            $tien_thu_ho_phai_tra = $tien_thu_ho - $tong_tien;
                                                                            $tong_tien_thu_ho += $tien_thu_ho_phai_tra;
                                                                        }
                                                                    break;
                                                                }    
                                                            }
                                                            echo $tong_tien_thu_ho > 0 ? number_format($tong_tien_thu_ho, 0, '', ',').' VNĐ' : 0
                                                        ?>
                                                        <button
                                                            data-toggle='modal'
                                                            data-target='#tienthuho<?= $model['kh_id']?>'
                                                            type="button"
                                                            style='margin-top: 3px;'
                                                            class='btn btn-sm btn-primary btn-block'
                                                        >
                                                        <i class='glyphicon glyphicon-th-list' style="vertical-align: baseline !important"></i>
                                                            Chi tiết
                                                    </button>
                                                    </td>
                                                    <!--Tiền trả khách-->
                                                    <td class='font14' style='text-align: center'>
                                                        <?php
                                                            $tien_tra_khach = $tong_tien_thu_ho - $so_no + $so_du;
                                                            echo number_format($tien_tra_khach, 0, '', ',').' VNĐ';
                                                        ?>
                                                        <?= Html::beginForm(Url::to(['/admin/khachhang/a/thanhtoan']), 'post', ['class' => 'form-inline']) ?>
                                                            <input type='hidden' name='kh_id' value='<?= $model['kh_id']?>'/>
                                                            <input type='hidden' name='donhang' value='<?= json_encode($model['donhang'], JSON_UNESCAPED_UNICODE)?>'/>
                                                            <input type='hidden' name='chuyen_tra_khach' value='<?= $tien_tra_khach?>' />
                                                            <button
                                                                type="submit"
                                                                style='margin-top: 3px;'
                                                                class='btn btn-sm btn-danger btn-block'
                                                            >
                                                            <i class='glyphicon glyphicon-copy' style="vertical-align: baseline !important"></i>
                                                                Thanh toán
                                                            </button>
                                                        <?= Html::endForm() ?>
                                                    </td>
                                                </tr>
                                            <?php endif;?>
                                        <?php endforeach;?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        <?php
            endif;
        ?>
    </div>
</div>