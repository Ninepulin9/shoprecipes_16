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
                        <form action="{{ route('couponSave') }}" method="post" id="couponForm">
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
                                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                                   id="code" name="code" value="{{ old('code') }}" required>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="discount_type" class="form-label">ประเภทส่วนลด :</label>
                                            <select class="form-select @error('discount_type') is-invalid @enderror" 
                                                    id="discount_type" name="discount_type" required>
                                                <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>เปอร์เซ็นต์ (%)</option>
                                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>จำนวนเงินคงที่</option>
                                                <option value="point" {{ old('discount_type') == 'point' ? 'selected' : '' }}>เพิ่ม Point</option>
                                            </select>
                                            @error('discount_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="discount_value" class="form-label" id="discount_value_label">มูลค่าส่วนลด :</label>
                                            <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                                   id="discount_value" name="discount_value" value="{{ old('discount_value') }}" required>
                                            <div class="form-text" id="discount_value_help">ใส่จำนวนตามประเภทที่เลือก</div>
                                            @error('discount_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="usage_limit" class="form-label">จำนวนครั้งสูงสุดในการใช้ (เว้นว่าง = ไม่จำกัด) :</label>
                                            <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" 
                                                   id="usage_limit" name="usage_limit" value="{{ old('usage_limit') }}">
                                            @error('usage_limit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="expired_at" class="form-label">วันหมดอายุ (เว้นว่าง = ไม่มีวันหมดอายุ) :</label>
                                            <input type="date" class="form-control @error('expired_at') is-invalid @enderror" 
                                                   id="expired_at" name="expired_at" value="{{ old('expired_at') }}">
                                            @error('expired_at')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
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
                                    <button type="submit" class="btn btn-outline-primary" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;" id="submitSpinner"></span>
                                        บันทึก
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // แสดง SweetAlert สำหรับ Success Message
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    @endif

    // แสดง SweetAlert สำหรับ Error Message
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด!',
            text: '{{ session('error') }}',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#d33'
        });
    @endif

    // แสดง SweetAlert สำหรับ Validation Errors
    @if($errors->any())
        let errorMessages = [];
        @foreach($errors->all() as $error)
            errorMessages.push('{{ $error }}');
        @endforeach
        
        Swal.fire({
            icon: 'error',
            title: 'ข้อมูลไม่ถูกต้อง!',
            html: '<ul style="text-align: left; margin: 0; padding-left: 20px;">' + 
                  errorMessages.map(msg => '<li>' + msg + '</li>').join('') + 
                  '</ul>',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#d33'
        });
    @endif

    // ตรวจสอบรหัสคูปองซ้ำ (Real-time validation)
    $('#code').on('blur', function() {
        let couponCode = $(this).val().trim();
        if (couponCode.length > 0) {
            $.ajax({
                url: '{{ route("checkCouponCode") }}', // สร้าง route นี้
                method: 'POST',
                data: {
                    code: couponCode,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (!response.available) {
                        $('#code').addClass('is-invalid');
                        if (!$('#code').next('.invalid-feedback').length) {
                            $('#code').after('<div class="invalid-feedback">รหัสคูปองนี้มีอยู่แล้วในระบบ</div>');
                        }
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'คำเตือน!',
                            text: 'รหัสคูปองนี้มีอยู่แล้วในระบบ กรุณาใช้รหัสอื่น',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#f39c12'
                        });
                    } else {
                        $('#code').removeClass('is-invalid');
                        $('#code').next('.invalid-feedback').remove();
                    }
                }
            });
        }
    });

    // ตัวเลือกประเภทส่วนลด
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

    // Form submission with loading
    $('#couponForm').on('submit', function(e) {
        e.preventDefault();
        
        // ตรวจสอบว่ามี validation error หรือไม่
        if ($('.is-invalid').length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'ข้อมูลไม่ถูกต้อง!',
                text: 'กรุณาแก้ไขข้อมูลที่ไม่ถูกต้องก่อนบันทึก',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#d33'
            });
            return false;
        }

        // แสดง loading
        $('#submitBtn').prop('disabled', true);
        $('#submitSpinner').show();
        
        // ส่งข้อมูลแบบ AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#submitBtn').prop('disabled', false);
                $('#submitSpinner').hide();
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: response.message || 'บันทึกคูปองเรียบร้อยแล้ว',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // รีเซ็ตฟอร์ม หรือ redirect
                        $('#couponForm')[0].reset();
                        $('#discount_type').trigger('change');
                        // window.location.href = '{{ route("coupons") }}'; // ถ้าต้องการ redirect
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: response.message || 'ไม่สามารถบันทึกข้อมูลได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            error: function(xhr) {
                $('#submitBtn').prop('disabled', false);
                $('#submitSpinner').hide();
                
                let errorMessage = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                
                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    let errorList = [];
                    
                    $.each(errors, function(field, messages) {
                        $.each(messages, function(index, message) {
                            errorList.push(message);
                        });
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อมูลไม่ถูกต้อง!',
                        html: '<ul style="text-align: left; margin: 0; padding-left: 20px;">' + 
                              errorList.map(msg => '<li>' + msg + '</li>').join('') + 
                              '</ul>',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#d33'
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: errorMessage,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#d33'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: errorMessage,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        });
    });

    // ป้องกันการกด Enter ใน input
    $('#couponForm input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#submitBtn').click();
        }
    });
});
</script>
@endsection