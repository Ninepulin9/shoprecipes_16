@extends('admin.layout')
@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <style>
        /* ปรับสีปุ่ม toggle status */
        .btn-outline-success.toggleStatus {
            color: #28a745;
            border-color: #28a745;
        }

        .btn-outline-success.toggleStatus:hover {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-outline-danger.toggleStatus {
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-outline-danger.toggleStatus:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        /* เพิ่มเอฟเฟกต์ transition */
        .toggleStatus {
            transition: all 0.3s ease;
        }

        /* ปรับไอคอน toggle */
        .toggleStatus .bx-toggle-right {
            color: #28a745;
        }

        .toggleStatus .bx-toggle-left {
            color: #dc3545;
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
                            <h5 class="mb-0">แลกแต้ม</h5>
                        </div>
                        <div class="card-body">
                            <table id="redeemTable" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">ชื่อเมนู/สิทธิประโยชน์</th>
                                        <th class="text-center">หมวดหมู่</th>
                                        <th class="text-center">Point ที่ใช้แลก</th>
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
            // ตรวจสอบว่า DataTable มีอยู่แล้วหรือไม่
            if ($.fn.DataTable.isDataTable('#redeemTable')) {
                $('#redeemTable').DataTable().destroy();
            }

            $("#redeemTable").DataTable({
                language: {
                    url: language,
                },
                processing: true,
                ajax: {
                    url: "{{route('redeemListData')}}",
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    error: function (xhr, error, thrown) {
                        console.log('Error: ', error);
                        console.log('Thrown: ', thrown);
                        console.log('Response: ', xhr.responseText);
                    }
                },
                columns: [
                    {
                        data: 'name',
                        class: 'text-center',
                        width: '20%'
                    },
                    {
                        data: 'categories',
                        class: 'text-center',
                        width: '12%'
                    },
                    {
                        data: 'point_required',
                        class: 'text-center',
                        width: '12%',
                        orderable: false
                    },
                    {
                        data: 'usage_info',
                        class: 'text-center',
                        width: '15%',
                        orderable: false
                    },
                    {
                        data: 'action',
                        class: 'text-center',
                        width: '15%',
                        orderable: false
                    }
                ]
            });
        });
    </script>
@endsection
