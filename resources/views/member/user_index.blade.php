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
                    <div class="card-header d-flex justify-content-end">
                        <a href="{{route('userCreate')}}" class="btn btn-sm btn-outline-success d-flex align-items-center" style="font-size:14px">เพิ่มสมาชิก&nbsp;<i class="bx bxs-plus-circle"></i></a>
                    </div>
                    <div class="card-body">
                        <table id="myTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ชื่อสมาชิก</th>
                                    <th class="text-center">UID</th>
                                    <th class="text-center">อีเมล</th>
                                    <th class="text-center">เบอร์ติดต่อ</th>
                                    <th class="text-center">คะแนนสะสม</th>
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
<div class="modal fade" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true" id="modal-history">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ประวัติการใช้คูปอง/แต้มสะสม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="body-history"></div>
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
            ajax: {
                url: "{{route('UsermemberlistData')}}",
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },

            columns: [{
                    data: 'name',
                    class: 'text-center',
                    width: '20%'
                },
                {
                    data: 'uid',
                    class: 'text-center',
                    width: '12%'
                },
                {
                    data: 'email',
                    class: 'text-center',
                    width: '25%',
                    orderable: false
                },
                {
                    data: 'tel',
                    class: 'text-center',
                    width: '15%',
                    orderable: false
                },
                {
                    data: 'point',
                    class: 'text-center',
                    width: '10%',
                    orderable: false
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '18%',
                    orderable: false
                },
            ]
        });
    });
</script>
<script>
    $(document).on('click', '.deleteUserTable', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: "ท่านต้องการลบข้อมูลสมาชิกใช่หรือไม่?",
            icon: "question",
            showDenyButton: true,
            confirmButtonText: "ตกลง",
            denyButtonText: `ยกเลิก`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('userDelete')}}",
                    type: "post",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status == true) {
                            Swal.fire(response.message, "", "success");
                            $('#myTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).on('click', '.historyCouponBtn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: "{{ route('userHistory') }}",
            type: "post",
            data: { id: id },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                $('#modal-history').modal('show');
                $('#body-history').html(response);
            }
        });
    });
</script>
@endsection