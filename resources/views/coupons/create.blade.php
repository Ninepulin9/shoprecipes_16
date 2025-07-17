@extends('admin.layout')
@section('style')
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row d-flex justify-content-center">
                    <div class="col-8">
                        <form action="{{ route('couponSave') }}" method="post">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    เพิ่มคูปอง
                                    <hr>
                                </div>
                                <div class="card-body">

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="code" class="form-label">รหัสคูปอง :</label>
                                            <input type="text" class="form-control" id="code" name="code" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="discount_type" class="form-label">ประเภทส่วนลด :</label>
                                            <select class="form-select" id="discount_type" name="discount_type" required>
                                                <option value="percent">เปอร์เซ็นต์ (%)</option>
                                                <option value="fixed">จำนวนเงินคงที่</option>
                                                <option value="point">เพิ่ม Point</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="discount_value" class="form-label" id="discount_value_label">มูลค่าส่วนลด :</label>
                                            <input type="number" class="form-control" id="discount_value" name="discount_value" required>
                                            <div class="form-text" id="discount_value_help">ใส่จำนวนตามประเภทที่เลือก</div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="usage_limit" class="form-label">จำนวนครั้งสูงสุดในการใช้ (เว้นว่าง = ไม่จำกัด) :</label>
                                            <input type="number" class="form-control" id="usage_limit" name="usage_limit">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="expired_at" class="form-label">วันหมดอายุ (เว้นว่าง = ไม่มีวันหมดอายุ) :</label>
                                            <input type="date" class="form-control" id="expired_at" name="expired_at">
                                        </div>
                                    </div>

                                    <!-- ข้อมูลเพิ่มเติมสำหรับเปอร์เซ็นต์ -->
                                    <div class="row g-3 mb-3" id="percent_info" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="alert alert-info" style="background-color: #e6f7ff; border-color: #91d5ff; color: #1890ff;">
                                                <h6 class="alert-heading" style="color: #1890ff;">คูปองเปอร์เซ็นต์ %</h6>
                                                <p class="mb-0" style="color: #1890ff;">ตัวอย่างการใช้งาน: ใส่ 10 = ลูกค้าจะได้รับส่วนลด 10%</p>
                                               
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ข้อมูลเพิ่มเติมสำหรับจำนวนเงิน -->
                                    <div class="row g-3 mb-3" id="fixed_info" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="alert alert-info" style="background-color: #e6f7ff; border-color: #91d5ff; color: #1890ff;">
                                                <h6 class="alert-heading" style="color: #1890ff;">คูปองจำนวนเงิน</h6>
                                                <p class="mb-0" style="color: #1890ff;">ตัวอย่างการใช้งาน: ใส่ 50 = ลูกค้าจะได้รับส่วนลด 50 บาท</p>
                                         
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ข้อมูลเพิ่มเติมสำหรับ Point -->
                                    <div class="row g-3 mb-3" id="point_info" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="alert alert-info" style="background-color: #e6f7ff; border-color: #91d5ff; color: #1890ff;">
                                                <h6 class="alert-heading" style="color: #1890ff;">คูปอง Point</h6>
                                                <p class="mb-0" style="color: #1890ff;">ตัวอย่างการใช้งาน: ใส่ 100 = ลูกค้าจะได้รับ 100 Point เพิ่ม</p>
                                                
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-primary">บันทึก</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#discount_type').on('change', function() {
        var selectedType = $(this).val();
        var label = $('#discount_value_label');
        var help = $('#discount_value_help');
        var percentInfo = $('#percent_info');
        var fixedInfo = $('#fixed_info');
        var pointInfo = $('#point_info');
        
        percentInfo.hide();
        fixedInfo.hide();
        pointInfo.hide();
        
        switch(selectedType) {
            case 'percent':
                label.text('เปอร์เซ็นต์ส่วนลด :');
                help.text('');
                percentInfo.show();
                break;
            case 'fixed':
                label.text('จำนวนเงินส่วนลด :');
                help.text('');
                fixedInfo.show();
                break;
            case 'point':
                label.text('จำนวน Point ที่ให้ :');
                help.text('');
                pointInfo.show();
                break;
        }
    });
    
    $('#discount_type').trigger('change');
});
</script>
@endsection