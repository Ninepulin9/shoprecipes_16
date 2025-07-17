@extends('layouts.luxury-nav')

@section('title', 'หน้ารายละเอียด')

@section('content')
    <?php
    
    use App\Models\Config;
    
    $config = Config::first();
    ?>
    <style>
        .title-buy {
            font-size: 30px;
            font-weight: bold;
            color: <?=$config->color_font !='' ? $config->color_font : '#ffffff' ?>;
        }

        .title-list-buy {
            font-size: 25px;
            font-weight: bold;
        }

        .btn-plus {
            background: none;
            border: none;
            color: rgb(0, 156, 0);
            cursor: pointer;
            padding: 0;
            font-size: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-plus:hover {
            color: rgb(185, 185, 185);
        }

        .btn-delete {
            background: none;
            border: none;
            color: rgb(192, 0, 0);
            cursor: pointer;
            padding: 0;
            font-size: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            color: rgb(185, 185, 185);
        }

        .btn-aprove {
            background: linear-gradient(360deg, var(--primary-color), var(--sub-color));
            border-radius: 20px;
            border: 0px solid #0d9700;
            padding: 5px 0px;
            font-weight: bold;
            text-decoration: none;
            color: rgb(255, 255, 255);
            transition: background 0.3s ease;
        }

        .btn-aprove:hover {
            background: linear-gradient(360deg, var(--sub-color), var(--primary-color));
            cursor: pointer;
        }

        .btn-edit {
            background: transparent;
            color: rgb(206, 0, 0);
            border: none;
            font-size: 12px;
            text-decoration: underline;
            padding: 0;
            margin-top: -8px;
            cursor: pointer;
        }
    </style>

    <div class="container">
        <div class="d-flex flex-column justify-content-center gap-2">
            <div class="title-buy">
                คำสั่งซื้อ
            </div>
            <div class="bg-white px-2 pt-3 shadow-lg d-flex flex-column aling-items-center justify-content-center"
                style="border-radius: 10px;">
                <div class="title-list-buy">
                    รายการอาหารที่สั่ง
                </div>
                <div id="order-summary" class="mt-2"></div>
                <div class="input-group mt-3">
                    <input type="text" id="coupon" class="form-control" placeholder="กรอกเลขคูปอง">
                    <button class="btn btn-outline-primary" type="button" id="check-coupon-btn">ตรวจสอบ</button>
                    <!-- ✅ เพิ่มปุ่มยกเลิกคูปอง -->
                    <button class="btn btn-outline-danger" type="button" id="cancel-coupon-btn" style="display:none;">ยกเลิก</button>
                </div>
                <div id="coupon-message" class="text-danger small mt-1"></div>
                <!-- ✅ เพิ่มข้อความแสดงคูปองที่ใช้ -->
                <div id="applied-coupon" class="alert alert-success mt-2" style="display:none;">
                    <strong>ใช้คูปอง:</strong> <span id="coupon-code-display"></span>
                    <span id="coupon-benefit"></span>
                </div>
                <div class="fw-bold fs-5 mt-5 " style="border-top:2px solid #7e7e7e; margin-bottom:-10px;">
                    ยอดชำระ
                </div>
                <div class="fw-bold text-center" style="font-size:45px; ">
                    <span id="total-price" style="color: #0d9700"></span><span class="text-dark ms-2">บาท</span>
                </div>
                <div id="discounted-box" class="fw-bold text-center" style="font-size:45px; display:none;">
                    <span id="discounted-price" style="color: #0d9700"></span><span class="text-dark ms-2">บาทหลังส่วนลด</span>
                </div>
            </div>

            <a href="javascript:void(0);" class="btn-aprove mt-3" id="confirm-order-btn"
                style="display: none;">ยืนยันคำสั่งซื้อ</a>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('order-summary');
            const totalPriceEl = document.getElementById('total-price');
            const checkCouponBtn = document.getElementById('check-coupon-btn');
            const cancelCouponBtn = document.getElementById('cancel-coupon-btn');
            const couponInput = document.getElementById('coupon');
            const couponMsg = document.getElementById('coupon-message');
            const discountedBox = document.getElementById('discounted-box');
            const discountedPriceEl = document.getElementById('discounted-price');
            const appliedCouponBox = document.getElementById('applied-coupon');
            const couponCodeDisplay = document.getElementById('coupon-code-display');
            const couponBenefit = document.getElementById('coupon-benefit');
            
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let appliedCoupon = null; // ✅ เก็บข้อมูลคูปองที่ใช้
            let originalTotal = 0;

            function renderOrderList() {
                container.innerHTML = '';
                let total = 0;
                
                if (cart.length === 0) {
                    const noItemsMessage = document.createElement('div');
                    noItemsMessage.textContent = "ท่านยังไม่ได้เลือกสินค้า";
                    container.appendChild(noItemsMessage);
                } else {
                    const mergedItems = {};
                    cart.forEach(item => {
                        if (!mergedItems[item.name]) {
                            mergedItems[item.name] = [];
                        }
                        mergedItems[item.name].push(item);
                    });

                    for (const name in mergedItems) {
                        const groupedItems = mergedItems[name];
                        let totalPrice = 0;

                        groupedItems.forEach(item => {
                            totalPrice += item.total_price;

                            const optionsText = (item.options && item.options.length) ?
                                item.options.map(opt => opt.label).join(', ') :
                                '-';

                            const row = document.createElement('div');
                            row.className = 'row justify-content-between align-items-start fs-6 mb-2 text-start px-1';

                            const leftCol = document.createElement('div');
                            leftCol.className = 'col-9 d-flex flex-column justify-content-start lh-sm';

                            const title = document.createElement('div');
                            title.className = 'card-title m-0';
                            title.textContent = item.name + " x" + item.amount;

                            const optionTextEl = document.createElement('div');
                            optionTextEl.className = 'text-muted';
                            optionTextEl.style.fontSize = '12px';
                            optionTextEl.textContent = optionsText;

                            const note = document.createElement('div');
                            note.className = 'text-muted';
                            note.style.fontSize = '12px';
                            note.textContent = 'หมายเหตุ : ' + item.note;

                            leftCol.appendChild(title);
                            leftCol.appendChild(optionTextEl);
                            if(item.note){
                                leftCol.appendChild(note);
                            }

                            const rightCol = document.createElement('div');
                            rightCol.className = 'col-2 d-flex flex-column align-items-end';

                            const priceText = document.createElement('div');
                            priceText.textContent = item.total_price.toLocaleString();

                            const editBtn = document.createElement('a');
                            editBtn.className = 'btn-edit';
                            editBtn.textContent = 'แก้ไข';
                            editBtn.href = `/detail/${item.category_id}#select-${item.id}&uuid=${item.uuid}`;

                            rightCol.appendChild(priceText);
                            rightCol.appendChild(editBtn);

                            row.appendChild(leftCol);
                            row.appendChild(rightCol);
                            container.appendChild(row);
                        });

                        total += totalPrice;
                    }
                }

                originalTotal = total;
                totalPriceEl.textContent = total.toLocaleString();
                
                // ✅ อัพเดทการแสดงราคาตามคูปอง
                updatePriceDisplay();
            }

            // ✅ ฟังก์ชันอัพเดทการแสดงราคา
            function updatePriceDisplay() {
                if (appliedCoupon) {
                    if (appliedCoupon.coupon_type === 'point') {
                        // คูปอง Point: ไม่ลดราคา
                        discountedBox.style.display = 'none';
                        totalPriceEl.textContent = originalTotal.toLocaleString();
                    } else {
                        // คูปองส่วนลด: แสดงราคาหลังส่วนลด
                        discountedPriceEl.textContent = parseFloat(appliedCoupon.final_total).toLocaleString();
                        discountedBox.style.display = 'block';
                    }
                } else {
                    discountedBox.style.display = 'none';
                    totalPriceEl.textContent = originalTotal.toLocaleString();
                }
            }

            // ✅ ฟังก์ชันแสดงคูปองที่ใช้
            function showAppliedCoupon(couponData) {
                couponCodeDisplay.textContent = couponInput.value;
                
                if (couponData.coupon_type === 'point') {
                    couponBenefit.textContent = ` (โบนัส ${couponData.bonus_points} Point)`;
                } else {
                    couponBenefit.textContent = ` (ส่วนลด ${couponData.discount} บาท)`;
                }
                
                appliedCouponBox.style.display = 'block';
                checkCouponBtn.style.display = 'none';
                cancelCouponBtn.style.display = 'inline-block';
                couponInput.disabled = true;
            }

            // ✅ ฟังก์ชันยกเลิกคูปอง
            function cancelCoupon() {
                appliedCoupon = null;
                appliedCouponBox.style.display = 'none';
                checkCouponBtn.style.display = 'inline-block';
                cancelCouponBtn.style.display = 'none';
                couponInput.disabled = false;
                couponInput.value = '';
                couponMsg.textContent = '';
                updatePriceDisplay();
            }

            function toggleConfirmButton(cart) {
                const confirmButton = document.getElementById('confirm-order-btn');
                if (cart.length > 0) {
                    confirmButton.style.display = 'inline-block';
                } else {
                    confirmButton.style.display = 'none';
                }
            }

            renderOrderList();
            toggleConfirmButton(cart);

            checkCouponBtn.addEventListener('click', function () {
                if (appliedCoupon) {
                    couponMsg.textContent = 'คุณใช้คูปองไปแล้ว กรุณายกเลิกคูปองเก่าก่อน';
                    couponMsg.className = 'text-danger small mt-1';
                    return;
                }

                $.ajax({
                    type: "post",
                    url: "{{ route('checkCoupon') }}",
                    data: {
                        code: couponInput.value,
                        subtotal: originalTotal
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.status) {
                            appliedCoupon = response;
                            showAppliedCoupon(response);
                            updatePriceDisplay();
                            
                            couponMsg.className = 'text-success small mt-1';
                            couponMsg.textContent = response.message;
                        } else {
                            couponMsg.className = 'text-danger small mt-1';
                            couponMsg.textContent = response.message;
                        }
                    }
                });
            });

            cancelCouponBtn.addEventListener('click', function() {
                cancelCoupon();
            });

            const confirmButton = document.getElementById('confirm-order-btn');
            confirmButton.addEventListener('click', function(event) {
                event.preventDefault();
                if (Object.keys(cart).length > 0) {
                    const couponCode = appliedCoupon ? couponInput.value : null;
                    
                    $.ajax({
                        type: "post",
                        url: "{{ route('SendOrder') }}",
                        data: {
                            cart: cart,
                            remark: '',
                            coupon: couponCode
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.status == true) {
                                Swal.fire(response.message, "", "success");
                                localStorage.removeItem('cart');
                                cart = []; 
                                toggleConfirmButton(cart); 
                                setTimeout(() => {
                                    location.reload();
                                }, 3000);
                            } else {
                                Swal.fire(response.message, "", "error");
                                toggleConfirmButton(cart);
                            }
                        }
                    });
                }
            });
        });
    </script>

@endsection