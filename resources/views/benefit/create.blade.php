@extends('admin.layout')
@section('style')
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row d-flex justify-content-center">
                    <div class="col-10">
                        <form action="{{route('benefitSave')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    <i class="bx bx-gift me-2"></i>เพิ่มสิทธิประโยชน์แลกแต้ม
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">ชื่อสิทธิประโยชน์ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="type" class="form-label">ประเภท <span class="text-danger">*</span></label>
                                            <select class="form-control" name="type" id="type" required>
                                                <option value="">เลือกประเภท</option>
                                                <option value="discount">ส่วนลด</option>
                                                <option value="free_item">ของแถม</option>
                                                <option value="cashback">คืนเงิน</option>
                                                <option value="free_shipping">ฟรีค่าส่ง</option>
                                                <option value="point_multiplier">คูณแต้ม</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="point_required" class="form-label">แต้มที่ต้องใช้  (ขั้นต่ำ1เเต้ม) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="point_required" name="point_required" min="1" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="category" class="form-label">หมวดหมู่</label>
                                            <select class="form-control" name="category" id="category">
                                                <option value="">เลือกหมวดหมู่ (ไม่บังคับ)</option>
                                                @foreach($categories as $cat)
                                                <option value="{{$cat->name}}">{{$cat->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mt-4 pt-2">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                                <label class="form-check-label" for="is_active">
                                                    เปิดใช้งาน
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Discount Options (แสดงเมื่อเลือกประเภทส่วนลด) -->
                                    <div id="discountOptions" style="display: none;">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="discount_type" class="form-label">ประเภทส่วนลด</label>
                                                <select class="form-control" name="discount_type" id="discount_type">
                                                    <option value="percentage">เปอร์เซ็นต์ (%)</option>
                                                    <option value="fixed">จำนวนเงิน (บาท)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="discount_value" class="form-label">ค่าส่วนลด</label>
                                                <input type="number" class="form-control" id="discount_value" name="discount_value" min="0" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="min_order_amount" class="form-label">ยอดขั้นต่ำ (บาท)</label>
                                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" min="0" step="0.01">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="max_discount" class="form-label">ส่วนลดสูงสุด (บาท)</label>
                                                <input type="number" class="form-control" id="max_discount" name="max_discount" min="0" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="usage_limit" class="form-label">จำกัดการใช้งาน</label>
                                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" placeholder="ไม่จำกัด">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="expired_at" class="form-label">วันหมดอายุ</label>
                                            <input type="datetime-local" class="form-control" id="expired_at" name="expired_at">
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="description" class="form-label">รายละเอียด</label>
                                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="อธิบายสิทธิประโยชน์ เงื่อนไขการใช้งาน"></textarea>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="file" class="form-label">รูปภาพ</label>
                                            <input type="file" class="form-control" id="file" name="file" accept="image/*">
                                            <div class="form-text">ไฟล์ที่รองรับ: JPG, JPEG, PNG, GIF ขนาดไม่เกิน 2MB</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer d-flex justify-content-end">
                                    <a href="{{route('benefit')}}" class="btn btn-outline-secondary me-2">
                                        <i class="bx bx-arrow-back me-1"></i>กลับ
                                    </a>
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bx bx-save me-1"></i>บันทึก
                                    </button>
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
<script>
$(document).ready(function() {
    // แสดง/ซ่อน discount options
    $('#type').on('change', function() {
        if ($(this).val() === 'discount') {
            $('#discountOptions').show();
        } else {
            $('#discountOptions').hide();
        }
    });

    // Image Preview
    $('#file').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').show();
                $('#preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove Image Preview
    $('#removeImage').on('click', function() {
        $('#file').val('');
        $('#imagePreview').hide();
        $('#preview').attr('src', '');
    });

    // Form Validation
    $('form').on('submit', function(e) {
        var type = $('#type').val();
        var pointRequired = $('#point_required').val();
        
        if (!type) {
            e.preventDefault();
            Swal.fire('กรุณาเลือกประเภทสิทธิประโยชน์', '', 'warning');
            return false;
        }
        
        if (!pointRequired || pointRequired < 1) {
            e.preventDefault();
            Swal.fire('กรุณาใส่แต้มที่ต้องใช้ (ขั้นต่ำ 1 แต้ม)', '', 'warning');
            return false;
        }

        // Validate discount options if type is discount
        if (type === 'discount') {
            var discountValue = $('#discount_value').val();
            if (!discountValue || discountValue <= 0) {
                e.preventDefault();
                Swal.fire('กรุณาใส่ค่าส่วนลดที่ถูกต้อง', '', 'warning');
                return false;
            }
        }
    });
});
</script>
@endsection