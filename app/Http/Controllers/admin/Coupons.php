<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon as ModelsCoupon;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

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

        if (!$info) {
            return redirect()->route('coupons')->with('error', 'ไม่พบข้อมูลคูปองที่ต้องการแก้ไข');
        }

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
                $action = '<div class="btn-group" role="group" aria-label="Actions">
                    <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-info viewCoupon" title="ดูรายละเอียด">
                        <i class="bx bx-show"></i>
                    </button>
                    <a href="' . route('couponsEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                        <i class="bx bx-edit-alt"></i>
                    </a>
                    <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteCoupons" title="ลบ">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>';

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

    
    public function checkCouponCode(Request $request)
    {
        $code = $request->input('code');
        $id = $request->input('id'); 

        if (empty($code)) {
            return response()->json(['available' => true]);
        }

        $query = ModelsCoupon::where('code', $code);
        
        if ($id) {
            $query->where('id', '!=', $id);
        }

        $exists = $query->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'รหัสคูปองนี้มีอยู่แล้วในระบบ' : 'รหัสคูปองสามารถใช้ได้'
        ]);
    }
    
    public function couponSave(Request $request)
    {
        try {
            $input = $request->input();

            $messages = [
                'code.required' => 'กรุณากรอกรหัสคูปอง',
                'code.unique' => 'รหัสคูปองนี้มีอยู่แล้วในระบบ กรุณาใช้รหัสอื่น',
                'code.string' => 'รหัสคูปองต้องเป็นตัวอักษร',
                'discount_type.required' => 'กรุณาเลือกประเภทส่วนลด',
                'discount_type.in' => 'ประเภทส่วนลดไม่ถูกต้อง',
                'discount_value.required' => 'กรุณากรอกมูลค่าส่วนลด',
                'discount_value.numeric' => 'มูลค่าส่วนลดต้องเป็นตัวเลข',
                'discount_value.min' => 'มูลค่าส่วนลดต้องมากกว่าหรือเท่ากับ 0',
                'discount_value.max' => 'เปอร์เซ็นต์ส่วนลดต้องไม่เกิน 100%',
                'usage_limit.integer' => 'จำนวนครั้งสูงสุดต้องเป็นตัวเลขจำนวนเต็ม',
                'usage_limit.min' => 'จำนวนครั้งสูงสุดต้องมากกว่า 0',
                'expired_at.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
                'expired_at.after' => 'วันหมดอายุต้องเป็นวันในอนาคต'
            ];

            $rules = [
                'code' => 'required|string|max:50|unique:coupons,code,' . ($input['id'] ?? 'null'),
                'discount_type' => 'required|in:percent,fixed,point',
                'discount_value' => 'required|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'expired_at' => 'nullable|date|after:today'
            ];

            if ($request->discount_type === 'percent') {
                $rules['discount_value'] = 'required|numeric|min:0|max:100';
            }

            $request->validate($rules, $messages);

            if (!isset($input['id'])) {
                //  CREATE COUPON
                $coupon = new ModelsCoupon();
                $coupon->code = strtoupper(trim($input['code'])); // แปลงเป็นตัวพิมพ์ใหญ่
                $coupon->discount_type = $input['discount_type'];
                $coupon->discount_value = $input['discount_value'];
                $coupon->usage_limit = !empty($input['usage_limit']) ? $input['usage_limit'] : null;
                $coupon->used_count = 0;
                $coupon->expired_at = !empty($input['expired_at']) ? $input['expired_at'] : null;

                if ($coupon->save()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'บันทึกรายการคูปองเรียบร้อยแล้ว',
                            'data' => $coupon
                        ]);
                    }
                    return redirect()->route('coupons')->with('success', 'บันทึกรายการคูปองเรียบร้อยแล้ว');
                }

            } else {
                //  UPDATE COUPON
                $coupon = ModelsCoupon::find($input['id']);
                if (!$coupon) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'ไม่พบข้อมูลคูปองที่ต้องการแก้ไข'
                        ], 404);
                    }
                    return redirect()->route('coupons')->with('error', 'ไม่พบข้อมูลคูปองที่ต้องการแก้ไข');
                }

                $coupon->code = strtoupper(trim($input['code']));
                $coupon->discount_type = $input['discount_type'];
                $coupon->discount_value = $input['discount_value'];
                $coupon->usage_limit = !empty($input['usage_limit']) ? $input['usage_limit'] : null;
                $coupon->expired_at = !empty($input['expired_at']) ? $input['expired_at'] : null;

                if ($coupon->save()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'อัปเดตคูปองเรียบร้อยแล้ว',
                            'data' => $coupon
                        ]);
                    }
                    return redirect()->route('coupons')->with('success', 'อัปเดตคูปองเรียบร้อยแล้ว');
                }
            }

            // กรณีบันทึกไม่สำเร็จ
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกข้อมูลคูปองได้'
                ], 500);
            }
            return redirect()->route('coupons')->with('error', 'ไม่สามารถบันทึกข้อมูลคูปองได้');

        } catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Coupon Save Error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง'
                ], 500);
            }
            return redirect()->route('coupons')->with('error', 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง');
        }
    }

    /**
     * Soft Delete คูปอง
     */
    public function couponsDelete(Request $request)
    {
        try {
            \Log::info('Coupon Soft Delete Request:', $request->all());
            
            $data = [
                'status' => false,
                'message' => 'ปิดการใช้งานไม่สำเร็จ',
            ];

            $id = $request->input('id');
            $permanent_delete = $request->input('permanent_delete', false);
            
            \Log::info('Soft Delete Coupon ID: ' . $id . ', Permanent: ' . ($permanent_delete ? 'true' : 'false'));
            
            if (!$id) {
                $data['message'] = 'ไม่พบรหัสคูปองที่ต้องการลบ';
                return response()->json($data);
            }

            $coupon = ModelsCoupon::find($id);
            if (!$coupon) {
                $data['message'] = 'ไม่พบข้อมูลคูปองที่ต้องการลบ';
                return response()->json($data);
            }

            \Log::info('Found Coupon: ' . $coupon->code . ', Used Count: ' . $coupon->used_count);

            if ($permanent_delete) {
                DB::beginTransaction();
                
                try {
                    // ลบข้อมูลการใช้งานก่อน 
                    if ($coupon->used_count > 0) {
                        // ลบจาก coupon_usage_logs
                        DB::table('coupon_usage_logs')->where('coupon_id', $id)->delete();
                        \Log::info('Deleted coupon usage logs for coupon ID: ' . $id);
                        
                        // ลบจาก order_coupons
                        if (DB::getSchemaBuilder()->hasTable('order_coupons')) {
                            DB::table('order_coupons')->where('coupon_id', $id)->delete();
                            \Log::info('Deleted order coupons for coupon ID: ' . $id);
                        }
                    }
                    
                    // ลบคูปองถาวร
                    $coupon->forceDelete(); 
                    
                    DB::commit();
                    
                    $data = [
                        'status' => true,
                        'message' => 'ลบคูปองและประวัติการใช้งานถาวรเรียบร้อยแล้ว'
                    ];
                    
                    \Log::info('Coupon permanently deleted: ' . $coupon->code);
                    
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Transaction failed: ' . $e->getMessage());
                    throw $e;
                }
                
            } else {
                // Soft Delete 
                $deleteResult = $coupon->delete(); 
                \Log::info('Soft Delete Result: ' . ($deleteResult ? 'success' : 'failed'));
                
                if ($deleteResult) {
                    $message = $coupon->used_count > 0 ? 
                        'ปิดการใช้งานคูปองเรียบร้อยแล้ว (ยังคงประวัติการใช้งาน)' : 
                        'ปิดการใช้งานคูปองเรียบร้อยแล้ว';
                        
                    $data = [
                        'status' => true,
                        'message' => $message,
                    ];
                    
                    \Log::info('Coupon soft deleted successfully: ' . $coupon->code);
                } else {
                    \Log::error('Failed to soft delete coupon: ' . $coupon->code);
                    $data['message'] = 'ไม่สามารถปิดการใช้งานคูปองได้';
                }
            }

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Coupon Delete Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return response()->json([
                    'status' => false,
                    'message' => 'ไม่สามารถลบคูปองได้เนื่องจากมีข้อมูลการใช้งานที่เกี่ยวข้อง'
                ], 500);
            }
            
            return response()->json([
                'status' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function couponsDeleted()
    {
        $data['function_key'] = 'coupons';
        return view('coupons.deleted', $data);
    }

  
    public function couponsDeletedListData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];

        $coupons = ModelsCoupon::onlyTrashed()->get();
        
        if ($coupons->count() > 0) {
            $info = [];
            foreach ($coupons as $rs) {
                $action = '<div class="btn-group" role="group" aria-label="Actions">
                    <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-info viewCoupon" title="ดูรายละเอียด">
                        <i class="bx bx-show"></i>
                    </button>
                    <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-success restoreCoupon" title="กู้คืน">
                        <i class="bx bx-undo"></i>
                    </button>
                    <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger forceDeleteCoupon" title="ลบถาวร">
                        <i class="bx bxs-trash"></i>
                    </button>
                </div>';

                $info[] = [
                    'code' => $rs->code,
                    'discount_type' => $this->getDiscountTypeText($rs->discount_type),
                    'discount_value' => $this->getDiscountValueText($rs->discount_type, $rs->discount_value),
                    'used_count' => number_format($rs->used_count) . ' ครั้ง',
                    'deleted_at' => '<span class="badge bg-warning">ปิดใช้งาน</span><br><small>' . $this->DateThai($rs->deleted_at) . '</small>',
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

   
    public function couponsRestore(Request $request)
    {
        try {
            $id = $request->input('id');
            
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ไม่พบรหัสคูปองที่ต้องการกู้คืน'
                ]);
            }

            $coupon = ModelsCoupon::onlyTrashed()->find($id);
            if (!$coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'ไม่พบข้อมูลคูปองที่ต้องการกู้คืน'
                ]);
            }

            if ($coupon->restore()) {
                \Log::info('Coupon restored: ' . $coupon->code);
                
                return response()->json([
                    'status' => true,
                    'message' => 'กู้คืนคูปอง ' . $coupon->code . ' เรียบร้อยแล้ว'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'ไม่สามารถกู้คืนคูปองได้'
            ]);

        } catch (\Exception $e) {
            \Log::error('Coupon Restore Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'เกิดข้อผิดพลาดในการกู้คืนข้อมูล'
            ], 500);
        }
    }

    /**
     * แสดงรายละเอียดคูปอง 
     */
    public function getCouponDetails($id)
    {
        try {
            $coupon = ModelsCoupon::withTrashed()->find($id);
            
            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลคูปอง'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_type_text' => $this->getDiscountTypeText($coupon->discount_type),
                    'discount_value' => $coupon->discount_value,
                    'discount_value_text' => $this->getDiscountValueText($coupon->discount_type, $coupon->discount_value),
                    'usage_limit' => $coupon->usage_limit,
                    'used_count' => $coupon->used_count,
                    'expired_at' => $coupon->expired_at,
                    'expired_at_thai' => $coupon->expired_at ? $this->DateThai($coupon->expired_at) : null,
                    'deleted_at' => $coupon->deleted_at,
                    'deleted_at_thai' => $coupon->deleted_at ? $this->DateThai($coupon->deleted_at) : null,
                    'is_deleted' => $coupon->trashed(),
                    'is_expired' => $coupon->expired_at ? Carbon::parse($coupon->expired_at)->isPast() : false,
                    'is_used_up' => $coupon->usage_limit ? $coupon->used_count >= $coupon->usage_limit : false
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Coupon Details Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'
            ], 500);
        }
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