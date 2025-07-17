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
                            <input type="hidden" name="id" value="{{ $info->id }}">
                            <div class="card">
                                <div class="card-header">
                                    แก้ไขคูปอง
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="code" class="form-label">รหัสคูปอง :</label>
                                            <input type="text" class="form-control" id="code" name="code" value="{{ $info->code }}" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="discount_type" class="form-label">ประเภทส่วนลด :</label>
                                            <select class="form-select" id="discount_type" name="discount_type" required>
                                                <option value="percent" {{ $info->discount_type == 'percent' ? 'selected' : '' }}>เปอร์เซ็นต์ (%)</option>
                                                <option value="fixed" {{ $info->discount_type == 'fixed' ? 'selected' : '' }}>จำนวนเงิน</option>
                                                <option value="point" {{ $info->discount_type == 'point' ? 'selected' : '' }}>โบนัสแต้ม (Point)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="discount_value" class="form-label" id="discount_value_label">
                                                @if($info->discount_type == 'point')
                                                    จำนวนแต้มโบนัส :
                                                @elseif($info->discount_type == 'percent')
                                                    เปอร์เซ็นต์ส่วนลด :
                                                @else
                                                    จำนวนเงินส่วนลด :
                                                @endif
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="discount_value" name="discount_value" value="{{ $info->discount_value }}" required>
                                                <span class="input-group-text" id="discount_unit">
                                                    @if($info->discount_type == 'point')
                                                        แต้ม
                                                    @elseif($info->discount_type == 'percent')
                                                        %
                                                    @else
                                                        บาท
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ส่วนเงื่อนไขเพิ่มเติม (ซ่อนเมื่อเป็น point) -->
                                    <div id="additional_conditions" style="display: {{ $info->discount_type == 'point' ? 'none' : 'block' }};">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="min_order_amount" class="form-label">ยอดขั้นต่ำในการใช้คูปอง :</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" value="{{ $info->min_order_amount ?? 0 }}" min="0">
                                                    <span class="input-group-text">บาท</span>
                                                </div>
                                                <small class="text-muted">ใส่ 0 หรือเว้นว่าง = ไม่จำกัดยอดขั้นต่ำ</small>
                                            </div>
                                            <div class="col-md-6" id="max_discount_section" style="display: {{ $info->discount_type == 'percent' ? 'block' : 'none' }};">
                                                <label for="max_discount" class="form-label">ส่วนลดสูงสุด (สำหรับ %) :</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="max_discount" name="max_discount" value="{{ $info->max_discount }}" min="0">
                                                    <span class="input-group-text">บาท</span>
                                                </div>
                                                <small class="text-muted">เว้นว่าง = ไม่จำกัดส่วนลดสูงสุด</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="usage_limit" class="form-label">จำนวนครั้งสูงสุดในการใช้ :</label>
                                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="{{ $info->usage_limit }}" min="1">
                                            <small class="text-muted">เว้นว่าง = ไม่จำกัดจำนวนครั้ง</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="expired_at" class="form-label">วันหมดอายุ :</label>
                                            <input type="date" class="form-control" id="expired_at" name="expired_at" value="{{ $info->expired_at ? \Carbon\Carbon::parse($info->expired_at)->format('Y-m-d') : '' }}">
                                            <small class="text-muted">เว้นว่าง = ไม่มีวันหมดอายุ</small>
                                        </div>
                                    </div>

                                    <!-- แสดงตัวอย่างการใช้งาน -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <div class="alert alert-info" id="example_usage">
                                                <strong><i class="bx bx-info-circle"></i> ตัวอย่างการใช้งาน:</strong>
                                                <span id="example_text">
                                                    @if($info->discount_type == 'point')
                                                        ลูกค้าจะได้รับแต้มโบนัส {{ $info->discount_value }} แต้ม เมื่อใช้คูปองนี้
                                                    @elseif($info->discount_type == 'percent')
                                                        ลูกค้าจะได้รับส่วนลด {{ $info->discount_value }}% จากยอดสั่งซื้อ
                                                        @if($info->max_discount)
                                                            (สูงสุด {{ number_format($info->max_discount) }} บาท)
                                                        @endif
                                                    @else
                                                        ลูกค้าจะได้รับส่วนลด {{ number_format($info->discount_value) }} บาท จากยอดสั่งซื้อ
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bx bx-save"></i> บันทึก
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountType = document.getElementById('discount_type');
    const discountValueLabel = document.getElementById('discount_value_label');
    const discountUnit = document.getElementById('discount_unit');
    const additionalConditions = document.getElementById('additional_conditions');
    const maxDiscountSection = document.getElementById('max_discount_section');
    const exampleText = document.getElementById('example_text');
    const discountValue = document.getElementById('discount_value');
    const maxDiscount = document.getElementById('max_discount');
    const minOrderAmount = document.getElementById('min_order_amount');

    function updateFormBasedOnType() {
        const selectedType = discountType.value;
        
        // อัพเดท label และ unit
        if (selectedType === 'point') {
            discountValueLabel.textContent = 'จำนวนแต้มโบนัส :';
            discountUnit.textContent = 'แต้ม';
            additionalConditions.style.display = 'none';
        } else if (selectedType === 'percent') {
            discountValueLabel.textContent = 'เปอร์เซ็นต์ส่วนลด :';
            discountUnit.textContent = '%';
            additionalConditions.style.display = 'block';
            maxDiscountSection.style.display = 'block';
        } else {
            discountValueLabel.textContent = 'จำนวนเงินส่วนลด :';
            discountUnit.textContent = 'บาท';
            additionalConditions.style.display = 'block';
            maxDiscountSection.style.display = 'none';
        }
        
        updateExample();
    }

    function updateExample() {
        const selectedType = discountType.value;
        const value = parseFloat(discountValue.value) || 0;
        const maxDiscountValue = parseFloat(maxDiscount.value) || 0;
        const minOrder = parseFloat(minOrderAmount.value) || 0;
        
        let exampleString = '';
        
        if (selectedType === 'point') {
            exampleString = `ลูกค้าจะได้รับแต้มโบนัส ${value.toLocaleString()} แต้ม เมื่อใช้คูปองนี้`;
        } else if (selectedType === 'percent') {
            exampleString = `ลูกค้าจะได้รับส่วนลด ${value}% จากยอดสั่งซื้อ`;
            if (maxDiscountValue > 0) {
                exampleString += ` (สูงสุด ${maxDiscountValue.toLocaleString()} บาท)`;
            }
        } else {
            exampleString = `ลูกค้าจะได้รับส่วนลด ${value.toLocaleString()} บาท จากยอดสั่งซื้อ`;
        }
        
        if (selectedType !== 'point' && minOrder > 0) {
            exampleString += ` เมื่อสั่งซื้อขั้นต่ำ ${minOrder.toLocaleString()} บาท`;
        }
        
        exampleText.textContent = exampleString;
    }

    // Event listeners
    discountType.addEventListener('change', updateFormBasedOnType);
    discountValue.addEventListener('input', updateExample);
    maxDiscount.addEventListener('input', updateExample);
    minOrderAmount.addEventListener('input', updateExample);

    // เรียกใช้ครั้งแรก
    updateFormBasedOnType();
});
</script>
@endsection