<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon as ModelsCoupon;

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

                $expiredAtFormatted = '';
                if (!empty($rs->expired_at)) { // ตรวจสอบว่ามีค่า expired_at หรือไม่
                    $expiredAtFormatted = $this->DateThai($rs->expired_at);
                } else {
                    $expiredAtFormatted = 'ไม่มีวันหมดอายุ';
                }

                $info[] = [
                    'code' => $rs->code,
                    'discount_type' => $rs->discount_type,
                    'discount_value' => $rs->discount_value,
                    'used_count' => $rs->used_count,
                    'usage_limit' => $rs->usage_limit ?? 'ไม่จำกัด',
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

        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . ($input['id'] ?? 'null'),
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
        ]);


        $input = $request->input();

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

}
