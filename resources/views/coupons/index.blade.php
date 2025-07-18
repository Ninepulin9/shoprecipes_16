@extends('admin.layout')
@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <style>
        .badge {
            font-size: 0.75rem;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        .table td {
            vertical-align: middle;
        }
        .usage-progress {
            width: 100%;
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 4px;
        }
        .usage-progress-bar {
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
@endsection
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">
                                <i class="bx bxs-coupon me-2"></i>จัดการคูปอง
                            </h5>
                            <a href="{{route('couponsCreate')}}"
                                class="btn btn-sm btn-outline-success d-flex align-items-center"
                                style="font-size:14px">
                                <i class="bx bxs-plus-circle me-1"></i>เพิ่มคูปอง
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="myTable" class="display table table-striped table-hover" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">รหัสคูปอง</th>
                                            <th class="text-center">ประเภทส่วนลด</th>
                                            <th class="text-center">มูลค่าส่วนลด</th>
                                            <th class="text-center">การใช้งาน</th>
                                            <th class="text-center">จำนวนครั้งสูงสุด</th>
                                            <th class="text-center">วันหมดอายุ</th>
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
    </div>

    <!--  สำหรับดูรายละเอียดคูปอง -->
    <div class="modal fade" id="couponDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-detail me-2"></i>รายละเอียดคูปอง
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="couponDetailContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var language = '{{asset("assets/js/datatable-language.js")}}';
        
        $(document).ready(function () {
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

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#d33'
                });
            @endif

            let table = $("#myTable").DataTable({
                language: {
                    url: language,
                },
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{route('couponslistData')}}",
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataSrc: function(json) {
                        return json.data || [];
                    }
                },
                columns: [
                    { 
                        data: 'code', 
                        class: 'text-center fw-bold',
                        render: function(data, type, row) {
                            return `<span class="badge bg-primary">${data}</span>`;
                        }
                    },
                    { data: 'discount_type', class: 'text-center' },
                    { 
                        data: 'discount_value', 
                        class: 'text-center fw-bold text-success' 
                    },
                    { data: 'used_count', class: 'text-center' },
                    { data: 'usage_limit', class: 'text-center' },
                    { data: 'expired_at', class: 'text-center' },
                    { data: 'action', class: 'text-center', orderable: false },
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true
            });

            // ฟังก์ชันลบคูปอง
            $(document).on('click', '.deleteCoupons', function (e) {
                e.preventDefault();
                let couponId = $(this).data('id');
                let row = $(this).closest('tr');
                
                deleteCoupon(couponId, row, false);
            });

            function deleteCoupon(couponId, row, forceDelete = false) {
                let title = forceDelete ? 
                    'ต้องการลบคูปองที่มีการใช้งานแล้วหรือไม่?' : 
                    'ต้องการลบคูปองนี้หรือไม่?';
                    
                let text = forceDelete ? 
                    'คูปองนี้มีการใช้งานแล้ว การลบจะไม่สามารถกู้คืนได้!' : 
                    'การดำเนินการนี้ไม่สามารถกู้คืนได้!';
                    
                let confirmButtonText = forceDelete ? 
                    '<i class="bx bx-trash"></i> ยืนยันลบถาวร' : 
                    '<i class="bx bx-check"></i> ยืนยันลบ';
                    
                let confirmButtonColor = forceDelete ? '#dc3545' : '#d33';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: '<i class="bx bx-x"></i> ยกเลิก',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังลบข้อมูล...',
                            html: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showConfirmButton: false
                        });

                        $.ajax({
                            url: "{{route('couponsDelete')}}",
                            type: "post",
                            data: {
                                id: couponId,
                                force_delete: forceDelete,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.status == true) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'ลบสำเร็จ!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    }).then(() => {
                                        table.ajax.reload(null, false);
                                    });
                                } else if (response.show_force_delete) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'คูปองมีการใช้งานแล้ว',
                                        html: `
                                            <div class="alert alert-warning text-start">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bx bx-error-circle text-warning me-2"></i>
                                                    <strong>คูปองนี้มีการใช้งานแล้ว</strong>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-5"><strong>รหัสคูปอง:</strong></div>
                                                    <div class="col-7"><span class="badge bg-primary">${response.coupon_code}</span></div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-5"><strong>ใช้งานแล้ว:</strong></div>
                                                    <div class="col-7"><span class="badge bg-info">${response.used_count} ครั้ง</span></div>
                                                </div>
                                                <hr>
                                                <p class="text-danger mb-0">
                                                    <i class="bx bx-info-circle"></i> 
                                                    คุณต้องการลบคูปองนี้ถาวรหรือไม่?
                                                </p>
                                            </div>
                                        `,
                                        showCancelButton: true,
                                        confirmButtonColor: '#dc3545',
                                        cancelButtonColor: '#6c757d',
                                        confirmButtonText: '<i class="bx bx-trash"></i> ลบถาวร',
                                        cancelButtonText: '<i class="bx bx-x"></i> ยกเลิก',
                                        reverseButtons: true,
                                        customClass: {
                                            confirmButton: 'btn btn-danger',
                                            cancelButton: 'btn btn-secondary'
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            deleteCoupon(couponId, row, true);
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'ไม่สามารถลบได้!',
                                        text: response.message,
                                        confirmButtonText: 'ตกลง',
                                        confirmButtonColor: '#d33'
                                    });
                                }
                            },
                            error: function (xhr) {
                                let errorMessage = 'เกิดข้อผิดพลาดในการลบข้อมูล';
                                
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด!',
                                    text: errorMessage,
                                    confirmButtonText: 'ตกลง',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            }

            // ดูรายละเอียดคูปอง
            $(document).on('click', '.viewCoupon', function(e) {
                e.preventDefault();
                let couponId = $(this).data('id');
                
                $('#couponDetailContent').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">กำลังโหลดข้อมูล...</p>
                    </div>
                `);
                
                $('#couponDetailModal').modal('show');
                
                $.ajax({
                    url: '{{ route("getCouponDetails", ":id") }}'.replace(':id', couponId),
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let coupon = response.data;
                            let statusBadge = '';
                            let progressBar = '';
                            
                            // สถานะคูปอง
                            if (coupon.is_expired) {
                                statusBadge = '<span class="badge bg-danger"><i class="bx bx-x-circle"></i> หมดอายุ</span>';
                            } else if (coupon.is_used_up) {
                                statusBadge = '<span class="badge bg-warning"><i class="bx bx-check-circle"></i> ใช้หมดแล้ว</span>';
                            } else {
                                statusBadge = '<span class="badge bg-success"><i class="bx bx-check"></i> ใช้งานได้</span>';
                            }
                            
                            if (coupon.usage_limit) {
                                let percentage = (coupon.used_count / coupon.usage_limit) * 100;
                                let progressClass = percentage >= 90 ? 'bg-danger' : percentage >= 70 ? 'bg-warning' : 'bg-success';
                                
                                progressBar = `
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar ${progressClass}" style="width: ${percentage}%"></div>
                                    </div>
                                    <small class="text-muted">ใช้งาน ${percentage.toFixed(1)}%</small>
                                `;
                            }
                            
                            $('#couponDetailContent').html(`
                                <div class="row">
                                    <div class="col-md-8">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="30%"><strong><i class="bx bx-barcode"></i> รหัสคูปอง:</strong></td>
                                                <td><span class="badge bg-primary fs-6">${coupon.code}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bx bx-category"></i> ประเภท:</strong></td>
                                                <td>${coupon.discount_type_text}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bx bx-money"></i> มูลค่า:</strong></td>
                                                <td><span class="text-success fw-bold">${coupon.discount_value_text}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bx bx-user"></i> ใช้งานแล้ว:</strong></td>
                                                <td>
                                                    <span class="badge bg-info">${coupon.used_count} ครั้ง</span>
                                                    ${progressBar}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bx bx-limit"></i> จำกัดการใช้:</strong></td>
                                                <td>${coupon.usage_limit ? '<span class="badge bg-secondary">' + coupon.usage_limit + ' ครั้ง</span>' : '<span class="badge bg-info">ไม่จำกัด</span>'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bx bx-calendar"></i> วันหมดอายุ:</strong></td>
                                                <td>${coupon.expired_at_thai || '<span class="badge bg-info">ไม่มีวันหมดอายุ</span>'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bx bx-check-shield"></i> สถานะ:</strong></td>
                                                <td>${statusBadge}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">สถิติการใช้งาน</h5>
                                                <div class="mb-3">
                                                    <h2 class="text-primary">${coupon.used_count}</h2>
                                                    <p class="mb-0">ครั้งที่ใช้งาน</p>
                                                </div>
                                                ${coupon.usage_limit ? 
                                                    `<p class="text-muted">เหลือ <strong>${coupon.usage_limit - coupon.used_count}</strong> ครั้ง</p>` : 
                                                    '<p class="text-success">ไม่จำกัดการใช้งาน</p>'
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        } else {
                            $('#couponDetailContent').html(`
                                <div class="alert alert-danger">
                                    <i class="bx bx-error"></i> ไม่สามารถโหลดข้อมูลได้: ${response.message || 'เกิดข้อผิดพลาด'}
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#couponDetailContent').html(`
                            <div class="alert alert-danger">
                                <i class="bx bx-error"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล
                            </div>
                        `);
                    }
                });
            });
        });
    </script>
@endsection