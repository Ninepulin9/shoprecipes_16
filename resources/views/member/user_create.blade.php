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
                        <form action="{{route('userSave')}}" method="post" enctype="multipart/form-data" id="userForm">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    เพิ่มสมาชิก
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">หมวดหมู่ : </label>
                                            <select class="form-control" name="categories_id" id="categories_id" required>
                                                <option value="" selected disabled>เลือก</option>
                                                @foreach($categories as $rs)
                                                <option value="{{$rs->id}}">{{$rs->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">ชื่อ : </label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="email" class="form-label">อีเมล : </label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="tel" class="form-label">เบอร์ติดต่อ : </label>
                                            <input type="text" class="form-control" id="tel" name="tel" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10" required>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Real-time validation เมื่อพิมพ์ email
    $('#email').on('blur', function() {
        var email = $(this).val();
        if (email) {
            checkEmailExists(email);
        }
    });

    // Real-time validation เมื่อพิมพ์ เบอร์
    $('#tel').on('blur', function() {
        var tel = $(this).val();
        if (tel) {
            checkTelExists(tel);
        }
    });

    // Form submission validation
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        var email = $('#email').val();
        var tel = $('#tel').val();
        
        // ตรวจสอบทั้ง email และ tel พร้อมกัน
        Promise.all([
            checkEmailExists(email),
            checkTelExists(tel)
        ]).then(function(results) {
            var emailExists = results[0];
            var telExists = results[1];
            
            if (emailExists && telExists) {
                Swal.fire({
                    title: 'ข้อมูลซ้ำ!',
                    text: 'อีเมลและเบอร์ติดต่อนี้ถูกใช้งานแล้ว',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            } else if (emailExists) {
                Swal.fire({
                    title: 'อีเมลซ้ำ!',
                    text: 'อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            } else if (telExists) {
                Swal.fire({
                    title: 'เบอร์ซ้ำ!',
                    text: 'เบอร์ติดต่อนี้ถูกใช้งานแล้ว กรุณาใช้เบอร์อื่น',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            } else {
                $('#userForm')[0].submit();
            }
        });
    });
//ตรวจเมล
    function checkEmailExists(email) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: "{{route('checkEmailExists')}}",
                type: "POST",
                data: {
                    email: email
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    resolve(response.exists);
                },
                error: function() {
                    resolve(false);
                }
            });
        });
    }
//ตรวจเบอ
    function checkTelExists(tel) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: "{{route('checkTelExists')}}",
                type: "POST",
                data: {
                    tel: tel
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    resolve(response.exists);
                },
                error: function() {
                    resolve(false);
                }
            });
        });
    }
});
</script>
@endsection