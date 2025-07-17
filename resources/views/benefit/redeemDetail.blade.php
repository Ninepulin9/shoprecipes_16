@extends('admin.layout')

@section('style')
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {{-- เปลี่ยน action จาก route('redeemReward') เป็น route ที่ถูกต้อง --}}
                <form action="{{ route('redeemReward') }}" method="POST" id="redeemForm">
                    @csrf
                    {{-- ตรวจสอบว่าใช้ $info หรือ $reward ตามที่แก้ไขไปก่อนหน้า --}}
                    <input type="hidden" name="reward_id" value="{{ $info->id }}">
                    <div class="card">
                        <div class="card-header">
                            <h5>แลกรางวัล</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="{{ asset('storage/' . $info->image) }}" class="img-thumbnail"
                                    style="max-width: 250px;">
                            </div>

                            <h5 class="text-center">{{ $info->name }}</h5>
                            <p class="text-center text-muted">แต้มที่ต้องใช้:
                                <strong>{{ $info->point_required }}</strong>
                            </p>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" name="tel" id="tel" class="form-control"
                                    placeholder="กรอกเบอร์โทรศัพท์" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">แต้มคงเหลือของลูกค้า</label>
                                <input type="number" id="current_points" class="form-control" value="-" readonly>
                            </div>

                            <div id="pointStatus" class="alert d-none"></div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('redeemBenefit') }}" class="btn btn-secondary">ย้อนกลับ</a>
                            <button type="submit" class="btn btn-primary" id="redeemBtn" disabled>
                                ยืนยันแลกของรางวัล
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const telInput = document.getElementById('tel');
        const pointField = document.getElementById('current_points');
        const pointStatus = document.getElementById('pointStatus');
        const redeemBtn = document.getElementById('redeemBtn');
        const rewardId = "{{ $info->id }}";

        telInput.addEventListener('change', function () {
            const tel = telInput.value.trim();

            if (tel.length < 8) {
                pointField.value = "-";
                pointStatus.className = "alert alert-warning";
                pointStatus.textContent = "กรุณากรอกเบอร์โทรศัพท์อย่างน้อย 8 หลัก";
                pointStatus.classList.remove('d-none');
                redeemBtn.disabled = true;
                return;
            }

            fetch("{{ route('checkPoint') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ tel: tel, id: rewardId })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.status) {
                        pointField.value = "-";
                        pointStatus.className = "alert alert-danger";
                        pointStatus.textContent = data.message;
                        pointStatus.classList.remove('d-none');
                        redeemBtn.disabled = true;
                        return;
                    }

                    pointField.value = data.point;
                    if (data.enough) {
                        pointStatus.className = "alert alert-success";
                        pointStatus.textContent = "แต้มเพียงพอสำหรับแลกรางวัลนี้";
                        redeemBtn.disabled = false;
                    } else {
                        pointStatus.className = "alert alert-danger";
                        pointStatus.textContent = "แต้มไม่เพียงพอสำหรับแลกรางวัลนี้";
                        redeemBtn.disabled = true;
                    }

                    pointStatus.classList.remove('d-none');
                })
                .catch(err => {
                    console.error('Error fetching point:', err); // Log error for debugging
                    pointStatus.className = "alert alert-danger";
                    pointStatus.textContent = "เกิดข้อผิดพลาดในการตรวจสอบแต้ม";
                    pointStatus.classList.remove('d-none');
                    redeemBtn.disabled = true;
                });
        });
        document.getElementById("redeemForm").addEventListener("submit", function (e) {
            e.preventDefault(); // ป้องกันการ submit ปกติ

            const tel = telInput.value.trim();
            if (tel.length < 8) {
                alert("กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง");
                return;
            }

            fetch("{{ route('redeemReward') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    tel: tel,
                    reward_id: rewardId
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            icon: "success",
                            title: "แลกรางวัลสำเร็จ",
                            text: data.message || "ระบบได้บันทึกการแลกแล้ว",
                            confirmButtonText: "ตกลง"
                        }).then(() => {
                            window.location.href = "{{ route('redeemBenefit') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "ไม่สามารถแลกรางวัลได้",
                            text: data.message || "เกิดข้อผิดพลาด",
                        });
                    }
                })
                .catch(err => {
                    console.error('Redeem error:', err);
                    Swal.fire({
                        icon: "error",
                        title: "เกิดข้อผิดพลาด",
                        text: "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้",
                    });
                });
        });

    </script>
@endsection
