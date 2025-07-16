@extends('admin.layout')
@section('style')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">จัดการสิทธิประโยชน์แลกแต้ม</h5>
                        <a href="{{route('benefitCreate')}}" class="btn btn-sm btn-outline-success d-flex align-items-center" style="font-size:14px">
                            เพิ่มสิทธิประโยชน์&nbsp;<i class="bx bxs-plus-circle"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <table id="benefitTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ชื่อเมนู/สิทธิประโยชน์</th>
                                    <th class="text-center">หมวดหมู่</th>
                                    <th class="text-center">Point ที่ใช้แลก</th>
                                    <th class="text-center">วันหมดอายุ</th>
                                    <th class="text-center">การใช้งาน</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var language = '{{asset("assets/js/datatable-language.js")}}';
    
    $(document).ready(function() {
        // ตรวจสอบว่า DataTable มีอยู่แล้วหรือไม่
        if ($.fn.DataTable.isDataTable('#benefitTable')) {
            $('#benefitTable').DataTable().destroy();
        }
        
        $("#benefitTable").DataTable({
            language: {
                url: language,
            },
            processing: true,
            ajax: {
                url: "{{route('benefitListData')}}",
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                error: function(xhr, error, thrown) {
                    console.log('Error: ', error);
                    console.log('Thrown: ', thrown);
                    console.log('Response: ', xhr.responseText);
                }
            },
            columns: [
                {
                    data: 'name',
                    class: 'text-center',
                    width: '25%'
                },
                {
                    data: 'categories',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'point_required',
                    class: 'text-center',
                    width: '15%',
                    orderable: false
                },
                {
                    data: 'expired_at',
                    class: 'text-center',
                    width: '15%',
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

        // ลบสิทธิประโยชน์
        $(document).on('click', '.deleteBenefit', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            Swal.fire({
                title: "ต้องการลบสิทธิประโยชน์นี้หรือไม่?",
                icon: "question",
                showDenyButton: true,
                confirmButtonText: "ตกลง",
                denyButtonText: "ยกเลิก"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('benefitDelete')}}",
                        type: "post",
                        data: { id: id },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status == true) {
                                Swal.fire(response.message, "", "success");
                                $('#benefitTable').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire(response.message, "", "error");
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection