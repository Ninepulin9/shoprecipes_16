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
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="discount_value" class="form-label">มูลค่าส่วนลด :</label>
                                            <input type="number" class="form-control" id="discount_value" name="discount_value" required>
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
