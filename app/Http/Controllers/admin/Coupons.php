<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon as ModelsCoupon;
use Carbon\Carbon;

class Coupons extends Controller
{
    public function coupons()
    {
        $data['function_key'] = __FUNCTION__;
        return view('coupons.index', $data);
    }

    public function couponsEdit($id)
    {
        $function_key = 'coupons';
        $info = ModelsCoupon::find($id);

        return view('coupons.edit', compact('info', 'function_key'));
    }
    
    public function couponsListData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];

        $coupons = ModelsCoupon::get();
        if ($coupons->count() > 0) {
            $info = [];
            foreach ($coupons as $rs) {
                $action = '<a href="' . route('couponsEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteCoupons" title="ลบ"><i class="bx bxs-trash"></i></button>';

                // จัดการวันหมดอายุ
                $expiredAtFormatted = '';
                if (!empty($rs->expired_at)) {
                    $expiredDate = Carbon::parse($rs->expired_at);
                    $now = Carbon::now();
                    
                    if ($expiredDate->isPast()) {
                        $expiredAtFormatted = '<span class="badge bg-danger">หมดอายุ</span><br><small>' . $this->DateThai($rs->expired_at) . '</small>';
                    } elseif ($expiredDate->diffInDays($now) <= 7) {
                        $expiredAtFormatted = '<span class="badge bg-warning">ใกล้หมดอายุ</span><br><small>' . $this->DateThai($rs->expired_at) . '</small>';
                    } else {
                        $expiredAtFormatted = '<span class="badge bg-success">ยังใช้ได้</span><br><small>' . $this->DateThai($rs->expired_at) . '</small>';
                    }
                } else {
                    $expiredAtFormatted = '<span class="badge bg-info">ไม่หมดอายุ</span>';
                }

                // จัดการจำนวนครั้งสูงสุด
                $usageLimitFormatted = '';
                if ($rs->usage_limit) {
                    $remaining = $rs->usage_limit - $rs->used_count;
                    $percentage = ($rs->used_count / $rs->usage_limit) * 100;
                    
                    if ($percentage >= 90) {
                        $badgeClass = 'bg-danger';
                    } elseif ($percentage >= 70) {
                        $badgeClass = 'bg-warning';
                    } else {
                        $badgeClass = 'bg-success';
                    }
                    
                    $usageLimitFormatted = '<div class="text-center">';
                    $usageLimitFormatted .= '<div><span class="badge ' . $badgeClass . '">เหลือ ' . $remaining . ' ครั้ง</span></div>';
                    $usageLimitFormatted .= '<div><small>ใช้แล้ว: ' . number_format($rs->used_count) . ' ครั้ง</small></div>';
                    $usageLimitFormatted .= '</div>';
                } else {
                    $usageLimitFormatted = '<div class="text-center">';
                    $usageLimitFormatted .= '<div><span class="badge bg-info">ไม่จำกัด</span></div>';
                    $usageLimitFormatted .= '<div><small>ใช้แล้ว: ' . number_format($rs->used_count) . ' ครั้ง</small></div>';
                    $usageLimitFormatted .= '</div>';
                }

                $info[] = [
                    'code' => $rs->code,
                    'discount_type' => $this->getDiscountTypeText($rs->discount_type),
                    'discount_value' => $this->getDiscountValueText($rs->discount_type, $rs->discount_value),
                    'used_count' => number_format($rs->used_count) . ' ครั้ง',
                    'usage_limit' => $usageLimitFormatted,
                    'expired_at' => $expiredAtFormatted,
                    'action' => $action
                ];
            }

            $data = [
                'data' => $info,
                'status' => true,
                'message' => 'success'
            ];
        }

        return response()->json($data);
    }
    
    public function couponsCreate()
    {
        $data['function_key'] = 'coupons';
        return view('coupons.create', $data);
    }
    
    public function couponSave(Request $request)
    {
        $input = $request->input();

        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . ($input['id'] ?? 'null'),
            'discount_type' => 'required|in:percent,fixed,point',
            'discount_value' => 'required|numeric|min:0',
        ]);

        if (!isset($input['id'])) {
            // ➡️ CREATE COUPON
            $coupon = new ModelsCoupon();
            $coupon->code = $input['code'];
            $coupon->discount_type = $input['discount_type'];
            $coupon->discount_value = $input['discount_value'];
            $coupon->usage_limit = $input['usage_limit'] ?? null;
            $coupon->used_count = 0;
            $coupon->expired_at = $input['expired_at'] ?? null;

            if ($coupon->save()) {
                return redirect()->route('coupons')->with('success', 'บันทึกรายการคูปองเรียบร้อยแล้ว');
            }

        } else {
            // ➡️ UPDATE COUPON
            $coupon = ModelsCoupon::find($input['id']);
            if (!$coupon) {
                return redirect()->route('coupons')->with('error', 'ไม่พบข้อมูลคูปองที่ต้องการแก้ไข');
            }

            $coupon->code = $input['code'];
            $coupon->discount_type = $input['discount_type'];
            $coupon->discount_value = $input['discount_value'];
            $coupon->usage_limit = $input['usage_limit'] ?? null;
            $coupon->expired_at = $input['expired_at'] ?? null;

            if ($coupon->save()) {
                return redirect()->route('coupons')->with('success', 'อัปเดตคูปองเรียบร้อยแล้ว');
            }
        }

        return redirect()->route('coupons')->with('error', 'ไม่สามารถบันทึกข้อมูลคูปองได้');
    }

    public function couponsDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = ModelsCoupon::find($id);
            if ($delete->delete()) {
                $data = [
                    'status' => true,
                    'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                ];
            }
        }

        return response()->json($data);
    }

    function DateThai($strDate)
    {
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("n", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear";
    }

    private function getDiscountTypeText($type)
    {
        switch ($type) {
            case 'percent':
                return 'เปอร์เซ็นต์';
            case 'fixed':
                return 'จำนวนเงิน';
            case 'point':
                return 'เพิ่ม Point';
            default:
                return $type;
        }
    }

    private function getDiscountValueText($type, $value)
    {
        switch ($type) {
            case 'percent':
                return $value . '%';
            case 'fixed':
                return number_format($value) . ' บาท';
            case 'point':
                return number_format($value) . ' Point';
            default:
                return $value;
        }
    }
}