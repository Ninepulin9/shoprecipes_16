@extends('admin.layout')
@section('style')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    svg {
        width: 100%;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6>รายการออเดอร์ทั้งหมด</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <table id="myTable" class="display table-responsive">
                            <thead>
                                <tr>
                                    <th class="text-center">สั่งหน้าร้าน</th>
                                    <th class="text-center">เลขโต้ะ</th>
                                    <th class="text-center">ยอดราคา</th>
                                    <th class="text-left">หมายเหตุ</th>
                                    <th class="text-left">วันที่สั่ง</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 order-2">
                <div class="card">
                    <div class="card-header">
                        <h6>รายการชำระเงินแล้ว</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <table id="myTable2" class="display table-responsive">
                            <thead>
                                <tr>
                                    <th class="text-center">เลขที่ใบเสร็จ</th>
                                    <th class="text-center">โต้ะ</th>
                                    <th class="text-center">ยอดรวมทั้งหมด</th>
                                    <th class="text-center">วันที่ชำระ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-detail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดออเดอร์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="body-html">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-detail-pay">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดออเดอร์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="body-html-pay">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-pay">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ชำระเงิน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body d-flex justify-content-center">
                        <div class="row">
                            <div class="col-12 text-center">
                                <h5>ยอดชำระ</h5>
                                <h1 class="text-success" id="totalPay"></h1>
                            </div>
                            <div class="col-12 d-flex justify-content-center mb-3" id="qr_code">
                            </div>
                            <div class="col-12 text-center mb-1" id="discounted"></div>
                            <div class="col-8 mb-2">
                                <input type="text" id="member_search" class="form-control" placeholder="เบอร์โทรหรือ UID">
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-secondary" id="check_member">ตรวจสอบ</button>
                            </div>
                            <div class="col-12 mb-2" id="member_info" style="display:none;"></div>
                            <div class="col-12 mb-2" id="coupon_box" style="display:none;">
                                <select id="coupon_select" class="form-control"></select>
                            </div>
                        </div>
                        <input type="hidden" id="table_id">
                        <input type="hidden" id="member_id">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirm_pay">ยืนยันชำระเงิน</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-rider">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เลือกไรเดอร์ที่ต้องการจัดส่ง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label">ไรเดอร์ : </label>
                            <select class="form-control" name="rider_id" id="rider_id">
                                <option value="" disabled selected>กรุณาเลือกไรเดอร์</option>
                                @foreach($rider as $rs)
                                <option value="{{$rs->id}}">{{$rs->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="order_id_rider">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirm_rider">ยืนยันการจัดส่ง</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-tax-full">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ออกใบกำกับภาษี</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="tax-full">
                <div class="modal-body">
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">ชื่อลูกค้า : </label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label for="tel" class="form-label">เบอร์โทรศัพท์ : </label>
                                <input type="text" name="tel" id="tel" class="form-control" required onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10">
                            </div>
                            <div class="col-md-12">
                                <label for="tax_id" class="form-label">เลขประจำตัวผู้เสียภาษี : </label>
                                <input type="text" name="tax_id" id="tax_id" class="form-control" required onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            </div>
                            <div class="col-md-12">
                                <label for="address" class="form-label">ที่อยู่ : </label>
                                <textarea rows="4" class="form-control" name="address" id="address" required></textarea>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="pay_id">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="open-tex-full">ออกใบเสร็จ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modalRecipes">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดสูตรอาหาร</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="body-html-recipes">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script>
    var language = '{{asset("assets/js/datatable-language.js")}}';
    $(document).ready(function() {
        $("#myTable").DataTable({
            language: {
                url: language,
            },
            processing: true,
            scrollX: true,
            order: [
                [4, 'desc']
            ],
            ajax: {
                url: "{{route('ListOrder')}}",
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },

            columns: [{
                    data: 'flag_order',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'table_id',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'total',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'remark',
                    class: 'text-left',
                    width: '15%'
                },
                {
                    data: 'created',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'status',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '15%',
                    orderable: false
                },
            ]
        });
        $("#myTable2").DataTable({
            language: {
                url: language,
            },
            processing: true,
            scrollX: true,
            order: [
                [0, 'desc']
            ],
            ajax: {
                url: "{{route('ListOrderPay')}}",
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },

            columns: [{
                    data: 'payment_number',
                    class: 'text-center',
                    width: '20%'
                },
                {
                    data: 'table_id',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'total',
                    class: 'text-center',
                    width: '20%'
                },
                {
                    data: 'created',
                    class: 'text-center',
                    width: '20%'
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '30%',
                    orderable: false
                },
            ]
        });
    });
</script>
<script>
    $(document).on('click', '.modalShow', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            type: "post",
            url: "{{ route('listOrderDetail') }}",
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#modal-detail').modal('show');
                $('#body-html').html(response);
            }
        });
    });

    $(document).on('click', '.modalShowPay', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            type: "post",
            url: "{{ route('listOrderDetailPay') }}",
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#modal-detail-pay').modal('show');
                $('#body-html-pay').html(response);
            }
        });
    });

    $(document).on('click', '.modalPay', function(e) {
        var total = $(this).data('total');
        var id = $(this).data('id');
        Swal.showLoading();
        $.ajax({
            type: "post",
            url: "{{ route('generateQr') }}",
            data: {
                total: total
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.close();
                $('#modal-pay').modal('show');
                $('#totalPay').html(total + ' บาท');
                $('#qr_code').html(response);
                $('#table_id').val(id);
                $('#member_search').val('');
                $('#member_id').val('');
                $('#member_info').hide().text('');
                $('#coupon_box').hide();
            }
        });
    });

    $('#check_member').click(function () {
        $.ajax({
            url: "{{ route('admin.checkUser') }}",
            type: "post",
            data: {
                keyword: $('#member_search').val(),
                table_id: $('#table_id').val()
            },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (res.status) {
                    $('#member_id').val(res.user.id);
                    $('#member_info').show().text(res.user.name + ' (' + res.user.email + ') คะแนน ' + res.user.point);
                    if (res.coupon_used) {
                        $('#member_info').append(' ใช้คูปอง ' + res.coupon_used);
                        $('#coupon_box').hide();
                    } else {
                        $('#coupon_select').empty();
                        $('#coupon_select').append('<option value="">ไม่ใช้คูปอง</option>');
                        res.coupons.forEach(function(c){
                            $('#coupon_select').append('<option value="'+c.code+'">'+c.code+'</option>');
                        });
                        $('#coupon_box').show();
                    }
                } else {
                    $('#member_id').val('');
                    $('#member_info').show().text(res.message);
                    $('#coupon_box').hide();
                }
            }
        });
    });

  $('#coupon_select').change(function(){
    var code = $(this).val();
    if(!code){
        $('#discounted').text('');
        return;
    }
    $.ajax({
        type:"post",
        url:"{{ route('checkCoupon') }}",
        data:{code:code, subtotal: parseFloat($('#totalPay').text().replace(' บาท',''))},
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},
        success:function(res){
            if(res.status){
                if(res.coupon_type === 'point') {
                    $('#discounted').html('<span class="text-success">🎁 โบนัส ' + res.bonus_points + ' Point</span>');
                } else {
                    $('#discounted').text(res.final_total + ' บาทหลังส่วนลด');
                }
            }else{
                $('#discounted').text('');
            }
        }
    });
});
    $(document).on('click', '.modalRider', function(e) {
        var total = $(this).data('total');
        var id = $(this).data('id');
        Swal.showLoading();
        $('#order_id_rider').val(id);
        $('#modal-rider').modal('show');
        Swal.close();
    });

    $('#confirm_pay').click(function(e) {
        e.preventDefault();
        var id = $('#table_id').val();
        var userId = $('#member_id').val();
        var couponCode = $('#coupon_select').val();
        $.ajax({
            url: "{{route('confirm_pay')}}",
            type: "post",
            data: {
                id: id,
                user_id: userId,
                coupon_code: couponCode
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#modal-pay').modal('hide')
                if (response.status == true) {
                    Swal.fire(response.message, "", "success");
                    $('#myTable').DataTable().ajax.reload(null, false);
                    $('#myTable2').DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire(response.message, "", "error");
                }
            }
        });
    });

    $('#confirm_rider').click(function(e) {
        e.preventDefault();
        var id = $('#order_id_rider').val();
        var rider_id = $('#rider_id').val();
        $.ajax({
            url: "{{route('confirm_rider')}}",
            type: "post",
            data: {
                id: id,
                rider_id: rider_id
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#modal-rider').modal('hide')
                if (response.status == true) {
                    Swal.fire(response.message, "", "success");
                    $('#myTable').DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire(response.message, "", "error");
                }
            }
        });
    });

    $(document).on('click', '.modalTax', function(e) {
        var id = $(this).data('id');
        $('#modal-tax-full').modal('show');
        $('#pay_id').val(id);
    });

    $('#modal-tax-full').on('hidden.bs.modal', function() {
        $('#pay_id').val('');
        $('input').val('');
        $('textarea').val('');
    })

    $('#modal-pay').on('hidden.bs.modal', function() {
        $('#table_id').val('');
    })

    $(document).on('submit', '#tax-full', function(e) {
        e.preventDefault();
        var pay_id = $('#pay_id').val();
        var name = $('#name').val();
        var tel = $('#tel').val();
        var tax_id = $('#tax_id').val();
        var address = $('#address').val();
        window.open('<?= url('admin/order/printReceiptfull') ?>/' + pay_id + '?name=' + name + '&tel=' + tel + '&tax_id=' + tax_id + '&address=' + address, '_blank');
    });

    $(document).on('click', '.cancelOrderSwal', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.fire({
            title: "ต้องการยกเลิกออเดอร์นี้ใช่หรือไม่",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            denyButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('cancelOrder') }}",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status == true) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                            Swal.fire(response.message, "", "success");
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.cancelMenuSwal', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.fire({
            title: "ต้องการยกเลิกเมนูนี้ใช่หรือไม่",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            denyButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('cancelMenu') }}",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status == true) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                            Swal.fire(response.message, "", "success");
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.update-status', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.fire({
            title: "<h5>ท่านต้องการอัพเดทสถานะรายการนี้ใช่หรือไม่</h5>",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            denyButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('updatestatus') }}",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status == true) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                            Swal.fire(response.message, "", "success");
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });
    $(document).on('click', '.updatestatusOrder', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.fire({
            title: "<h5>ท่านต้องการอัพเดทสถานะรายการนี้ใช่หรือไม่</h5>",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            denyButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('updatestatusOrder') }}",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status == true) {
                            $('#myTable').DataTable().ajax.reload(null, false);
                            Swal.fire(response.message, "", "success");
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });
    $(document).on('click', '.OpenRecipes', function(e) {
        var id = $(this).data('id');
        $('#modal-detail').modal('hide');
        Swal.showLoading();
        $.ajax({
            type: "post",
            url: "{{ route('OpenRecipes') }}",
            data: {
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.close();
                $('#modalRecipes').modal('show');
                $('#body-html-recipes').html(response);
            }
        });
    });
</script>
@endsection