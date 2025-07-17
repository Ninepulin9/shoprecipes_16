<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Categories;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\RewardRedeemLog;
use Illuminate\Support\Facades\DB; // เพิ่มการใช้งาน DB Facade

class BenefitController extends Controller
{
    public function benefit()
    {
        $data['function_key'] = 'benefit';
        return view('benefit.index', $data);
    }

    public function benefitListData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];

        $benefits = Benefit::orderBy('created_at', 'desc')->get();

        if (count($benefits) > 0) {
            $info = [];
            foreach ($benefits as $rs) {
                // จัดการวันหมดอายุ
                $expiredAt = '-';
                $isExpired = false; // เพิ่มตัวแปรสำหรับตรวจสอบสถานะหมดอายุ

                if ($rs->expired_at) {
                    $expiredDate = \Carbon\Carbon::parse($rs->expired_at);
                    $now = \Carbon\Carbon::now();

                    if ($expiredDate->isPast()) {
                        $expiredAt = '<span class="badge bg-danger">หมดอายุแล้ว</span><br><small>' . $expiredDate->format('d/m/Y H:i') . '</small>';
                        $isExpired = true; // ตั้งค่าเป็นหมดอายุแล้ว
                    } elseif ($expiredDate->diffInDays($now) <= 7) {
                        $expiredAt = '<span class="badge bg-warning">ใกล้หมดอายุ</span><br><small>' . $expiredDate->format('d/m/Y H:i') . '</small>';
                    } else {
                        $expiredAt = '<span class="badge bg-success">ยังใช้ได้</span><br><small>' . $expiredDate->format('d/m/Y H:i') . '</small>';
                    }
                }

                // จัดการข้อมูลการใช้งาน
                $usageInfo = '<div class="text-center">';
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

                    $usageInfo .= '<div><strong>ใช้แล้ว:</strong> ' . number_format($rs->used_count) . ' ครั้ง</div>';
                    $usageInfo .= '<div><span class="badge ' . $badgeClass . '">เหลือ ' . $remaining . ' ครั้ง</span></div>';
                    $usageInfo .= '<div class="progress mt-1" style="height: 5px;">';
                    $usageInfo .= '<div class="progress-bar ' . str_replace('bg-', 'bg-', $badgeClass) . '" style="width: ' . $percentage . '%"></div>';
                    $usageInfo .= '</div>';
                } else {
                    $usageInfo .= '<div><span class="badge bg-info">ไม่จำกัด</span></div>';
                    $usageInfo .= '<div><strong>ใช้แล้ว:</strong> ' . number_format($rs->used_count) . ' ครั้ง</div>';
                }
                $usageInfo .= '</div>';

                // สถานะ (แบบง่าย - แสดงเฉพาะเปิด/ปิดการใช้งาน)
                if ($rs->is_active) {
                    $status = '<span class="badge bg-success">เปิดใช้งาน</span>';
                } else {
                    $status = '<span class="badge bg-danger">ปิดใช้งาน</span>';
                }

                $action = '<a href="' . route('benefitEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteBenefit" title="ลบ"><i class="bx bxs-trash"></i></button>';

                $info[] = [
                    'name' => $rs->name ?? '-',
                    'categories' => $rs->category ?? '-',
                    'point_required' => number_format($rs->point_required ?? 0) . ' แต้ม',
                    'expired_at' => $expiredAt,
                    'usage_info' => $usageInfo,
                    'status' => $status,
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

    public function benefitCreate()
    {
        $data['function_key'] = 'benefit';
        $data['categories'] = Categories::orderBy('name', 'asc')->get();
        return view('benefit.create', $data);
    }

    public function benefitSave(Request $request)
    {
        $input = $request->input();

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'point_required' => 'required|integer|min:1',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if (!isset($input['id'])) {
            // สร้างใหม่
            $benefit = new Benefit();
            $benefit->name = $input['name'];
            $benefit->description = $input['description'] ?? '';
            $benefit->type = $input['type'];
            $benefit->point_required = $input['point_required'];
            $benefit->category = $input['category'] ?? null;
            $benefit->discount_type = $input['discount_type'] ?? null;
            $benefit->discount_value = $input['discount_value'] ?? 0;
            $benefit->min_order_amount = $input['min_order_amount'] ?? 0;
            $benefit->max_discount = $input['max_discount'] ?? null;
            $benefit->usage_limit = $input['usage_limit'] ?? null;
            $benefit->used_count = 0;
            $benefit->is_active = isset($input['is_active']) ? 1 : 0;
            $benefit->start_date = $input['start_date'] ?? null;
            $benefit->end_date = $input['end_date'] ?? null;
            $benefit->expired_at = $input['expired_at'] ?? null;
            $benefit->applicable_categories = $input['applicable_categories'] ?? null;

            if ($benefit->save()) {
                // จัดการไฟล์รูปภาพ
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('benefit_images', $filename, 'public');

                    $benefit->image = $path;
                    $benefit->save();
                }

                return redirect()->route('benefit')->with('success', 'เพิ่มสิทธิประโยชน์เรียบร้อยแล้ว');
            }
        } else {
            // แก้ไข
            $benefit = Benefit::find($input['id']);
            if ($benefit) {
                $benefit->name = $input['name'];
                $benefit->description = $input['description'] ?? '';
                $benefit->type = $input['type'];
                $benefit->point_required = $input['point_required'];
                $benefit->category = $input['category'] ?? null;
                $benefit->discount_type = $input['discount_type'] ?? null;
                $benefit->discount_value = $input['discount_value'] ?? 0;
                $benefit->min_order_amount = $input['min_order_amount'] ?? 0;
                $benefit->max_discount = $input['max_discount'] ?? null;
                $benefit->usage_limit = $input['usage_limit'] ?? null;
                $benefit->is_active = isset($input['is_active']) ? 1 : 0;
                $benefit->start_date = $input['start_date'] ?? null;
                $benefit->end_date = $input['end_date'] ?? null;
                $benefit->expired_at = $input['expired_at'] ?? null;
                $benefit->applicable_categories = $input['applicable_categories'] ?? null;

                if ($benefit->save()) {
                    // จัดการไฟล์รูปภาพ
                    if ($request->hasFile('file')) {
                        // ลบรูปเก่า
                        if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
                            Storage::disk('public')->delete($benefit->image);
                        }

                        $file = $request->file('file');
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('benefit_images', $filename, 'public');

                        $benefit->image = $path;
                        $benefit->save();
                    }

                    return redirect()->route('benefit')->with('success', 'แก้ไขสิทธิประโยชน์เรียบร้อยแล้ว');
                }
            }
        }

        return redirect()->route('benefit')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }

    public function benefitEdit($id)
    {
        $function_key = 'benefit';
        $info = Benefit::find($id);
        $categories = Categories::orderBy('name', 'asc')->get();

        if (!$info) {
            return redirect()->route('benefit')->with('error', 'ไม่พบข้อมูลสิทธิประโยชน์');
        }

        return view('benefit.edit', compact('info', 'function_key', 'categories'));
    }

    public function benefitDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];

        $id = $request->input('id');
        if ($id) {
            $benefit = Benefit::find($id);
            if ($benefit) {
                // ลบรูปภาพ
                if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
                    Storage::disk('public')->delete($benefit->image);
                }

                if ($benefit->delete()) {
                    $data = [
                        'status' => true,
                        'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                    ];
                }
            }
        }

        return response()->json($data);
    }

    public function benefitToggleStatus(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'เปลี่ยนสถานะไม่สำเร็จ',
        ];

        $id = $request->input('id');
        if ($id) {
            $benefit = Benefit::find($id);
            if ($benefit) {
                $benefit->is_active = !$benefit->is_active;
                if ($benefit->save()) {
                    $status = $benefit->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
                    $data = [
                        'status' => true,
                        'message' => 'เปลี่ยนสถานะเป็น ' . $status . ' เรียบร้อยแล้ว',
                    ];
                }
            }
        }

        return response()->json($data);
    }

    public function benefitDetail($id)
    {
        $benefit = Benefit::find($id);

        if (!$benefit) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบข้อมูลสิทธิประโยชน์'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $benefit
        ]);
    }

    public function benefitCheckAvailable(Request $request)
    {
        $userId = $request->input('user_id');
        $benefitId = $request->input('benefit_id');
        $orderAmount = $request->input('order_amount', 0);

        $benefit = Benefit::find($benefitId);

        if (!$benefit) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบสิทธิประโยชน์'
            ]);
        }

        // ตรวจสอบว่าใช้งานได้หรือไม่
        if (!$benefit->isValid()) {
            return response()->json([
                'status' => false,
                'message' => 'สิทธิประโยชน์นี้ไม่สามารถใช้งานได้'
            ]);
        }

        // ตรวจสอบยอดขั้นต่ำ
        if (!$benefit->isValidForOrder($orderAmount)) {
            return response()->json([
                'status' => false,
                'message' => 'ยอดสั่งซื้อไม่ถึงขั้นต่ำที่กำหนด'
            ]);
        }

        // คำนวณส่วนลด
        $discount = $benefit->calculateDiscount($orderAmount);

        return response()->json([
            'status' => true,
            'data' => [
                'benefit' => $benefit,
                'discount' => $discount,
                'final_amount' => $orderAmount - $discount
            ]
        ]);
    }

    public function benefitRestore($id)
    {
        $benefit = Benefit::withTrashed()->find($id);

        if (!$benefit) {
            return redirect()->route('benefit')->with('error', 'ไม่พบข้อมูลสิทธิประโยชน์');
        }

        if ($benefit->restore()) {
            return redirect()->route('benefit')->with('success', 'กู้คืนสิทธิประโยชน์เรียบร้อยแล้ว');
        }

        return redirect()->route('benefit')->with('error', 'ไม่สามารถกู้คืนข้อมูลได้');
    }

    public function benefitTrashed()
    {
        $data['function_key'] = 'benefit';
        $data['benefits'] = Benefit::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return view('benefit.trashed', $data);
    }

    public function benefitForceDelete($id)
    {
        $benefit = Benefit::withTrashed()->find($id);

        if (!$benefit) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบข้อมูลสิทธิประโยชน์'
            ]);
        }

        // ลบรูปภาพ
        if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
            Storage::disk('public')->delete($benefit->image);
        }

        if ($benefit->forceDelete()) {
            return response()->json([
                'status' => true,
                'message' => 'ลบข้อมูลถาวรเรียบร้อยแล้ว'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'ไม่สามารถลบข้อมูลได้'
        ]);
    }

    // Redeem Benefit
    public function redeemBenefit()
    {
        $data['function_key'] = 'redeem_benefit';
        return view('benefit.redeem', $data);
    }

    public function redeemListData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];

        $redeem = Benefit::orderBy('created_at', 'desc')->get();

        if (count($redeem) > 0) {
            $info = [];
            foreach ($redeem as $rs) {
                // --- ส่วนจัดการข้อมูลการใช้งาน (เหมือนเดิม) ---
                $usageInfo = '<div class="text-center">';
                if ($rs->usage_limit !== null && $rs->usage_limit > 0) { // ปรับเงื่อนไขให้ชัดเจนขึ้น
                    $remaining = $rs->usage_limit - $rs->used_count;
                    $percentage = ($rs->used_count / $rs->usage_limit) * 100;

                    if ($percentage >= 90) {
                        $badgeClass = 'bg-danger';
                    } elseif ($percentage >= 70) {
                        $badgeClass = 'bg-warning';
                    } else {
                        $badgeClass = 'bg-success';
                    }

                    $usageInfo .= '<div><strong>ใช้แล้ว:</strong> ' . number_format($rs->used_count) . ' ครั้ง</div>';
                    $usageInfo .= '<div><span class="badge ' . $badgeClass . '">เหลือ ' . $remaining . ' ครั้ง</span></div>';
                    $usageInfo .= '<div class="progress mt-1" style="height: 5px;">';
                    $usageInfo .= '<div class="progress-bar ' . str_replace('bg-', 'bg-', $badgeClass) . '" style="width: ' . $percentage . '%"></div>';
                    $usageInfo .= '</div>';
                } else {
                    $usageInfo .= '<div><span class="badge bg-info">ไม่จำกัด</span></div>';
                    $usageInfo .= '<div><strong>ใช้แล้ว:</strong> ' . number_format($rs->used_count) . ' ครั้ง</div>';
                }
                $usageInfo .= '</div>';

                // 🚀 จุดที่แก้ไข: รวมเงื่อนไข is_active, usage_limit และ expired_at
                $isAvailable = $rs->is_active && ($rs->usage_limit === null || $rs->used_count < $rs->usage_limit);
                $isExpired = false;
                $expiredText = '';

                if ($rs->expired_at) {
                    $expiredDate = Carbon::parse($rs->expired_at);
                    if ($expiredDate->isPast()) {
                        $isExpired = true;
                        $expiredText = '<br><span class="badge bg-danger">หมดอายุแล้ว</span>';
                    }
                }

                if ($isAvailable && !$isExpired) {
                    // กรณี: กดปุ่มได้ (is_active=1 และ โควต้ายังไม่เต็ม และยังไม่หมดอายุ)
                    $status = '<span class="badge bg-success">เปิดใช้งาน</span>';
                    $action = '<a href="' . route('redeemDetails', $rs->id) . '" class="btn btn-sm btn-outline-success" title="แลกของรางวัล"><i class="bx bx-gift"> แลกของรางวัล</i></a>';
                } else {
                    // กรณี: กดปุ่มไม่ได้ (is_active=0 หรือ โควต้าเต็มแล้ว หรือหมดอายุแล้ว)
                    $buttonTitle = 'ปิดการใช้งาน'; // ข้อความเริ่มต้น
                    if ($isExpired) {
                        $buttonTitle = 'หมดอายุแล้ว';
                        $status = '<span class="badge bg-danger">หมดอายุแล้ว</span>';
                    } elseif (!$rs->is_active) {
                        $status = '<span class="badge bg-danger">ปิดใช้งาน</span>';
                    } elseif ($rs->is_active && ($rs->usage_limit !== null && $rs->used_count >= $rs->usage_limit)) {
                        // ถ้าของรางวัลยัง Active อยู่ แต่กดไม่ได้ แสดงว่า "สิทธิ์เต็มแล้ว"
                        $status = '<span class="badge bg-success">เปิดใช้งาน</span>';
                        $buttonTitle = 'สิทธิ์เต็มแล้ว';
                    }

                    $action = '<button type="button" class="btn btn-sm btn-outline-secondary disabled" title="' . $buttonTitle . '"><i class="bx bx-gift"> แลกของรางวัล</i></button>';
                }

                $info[] = [
                    'name' => $rs->name ?? '-' . $expiredText, // เพิ่มข้อความหมดอายุ
                    'categories' => $rs->category ?? '-',
                    'point_required' => number_format($rs->point_required ?? 0) . ' แต้ม',
                    'usage_info' => $usageInfo,
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

    public function redeemDetails($id)
    {
        $function_key = 'redeem_benefit';
        $info = Benefit::find($id);
        $categories = Categories::orderBy('name', 'asc')->get();

        if (!$info) {
            return redirect()->route('benefit')->with('error', 'ไม่พบข้อมูลสินค้าที่จะแลกรับ');
        }

        // ตรวจสอบวันหมดอายุเมื่อเข้าหน้ารายละเอียด
        if ($info->expired_at && Carbon::parse($info->expired_at)->isPast()) {
            return redirect()->route('redeemBenefit')->with('error', 'ของรางวัลนี้หมดอายุแล้ว ไม่สามารถแลกได้');
        }


        return view('benefit.redeemDetail', compact('info', 'function_key', 'categories'));
    }

    public function checkPoint(Request $request)
    {
        $phone = $request->input('tel');
        $reward_id = $request->input('id');
        // ตรวจสอบว่ามีการกรอกเบอร์มา
        if (!$phone) {
            return response()->json([
                'status' => false,
                'message' => 'กรุณากรอกเบอร์ลูกค้า'
            ]);
        }

        // ดึงข้อมูลลูกค้า
        $user = User::where('tel', $phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบบัญชีลูกค้าที่ใช้เบอร์นี้'
            ]);
        }

        // ดึงข้อมูล reward
        $reward = Benefit::find($reward_id);

        if (!$reward) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบของรางวัลนี้'
            ]);
        }

        // 🚀 เพิ่มการตรวจสอบวันหมดอายุ
        if ($reward->expired_at && Carbon::parse($reward->expired_at)->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'ของรางวัลนี้หมดอายุแล้ว ไม่สามารถแลกได้'
            ]);
        }

        // ตรวจสอบแต้มลูกค้า
        $userPoint = $user->point ?? 0;
        $requiredPoint = $reward->point_required ?? 0;

        if ($userPoint >= $requiredPoint) {
            return response()->json([
                'status' => true,
                'message' => 'แต้มเพียงพอ',
                'point' => $userPoint,
                'enough' => true
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'แต้มไม่เพียงพอ',
                'point' => $userPoint,
                'enough' => false
            ]);
        }
    }

    public function redeemReward(Request $request)
    {
        $phone = $request->input('tel');
        $reward_id = $request->input('reward_id');

        if (!$phone || !$reward_id) {
            return response()->json([
                'status' => false,
                'message' => 'ข้อมูลไม่ครบถ้วน'
            ]);
        }

        // ดึงข้อมูลลูกค้า (สมมติว่าคุณมี Model ชื่อ User)
        $user = User::where('tel', $phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบบัญชีลูกค้าที่ใช้เบอร์นี้'
            ]);
        }

        // ดึงข้อมูล reward
        $reward = Benefit::find($reward_id);

        if (!$reward) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบของรางวัลนี้'
            ]);
        }

        // 🚀 เพิ่มการตรวจสอบวันหมดอายุ
        if ($reward->expired_at && Carbon::parse($reward->expired_at)->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'ของรางวัลนี้หมดอายุแล้ว ไม่สามารถแลกได้'
            ]);
        }

        // ตรวจสอบแต้มลูกค้า
        $userPoint = $user->point ?? 0;
        $requiredPoint = $reward->point_required ?? 0;

        if ($userPoint < $requiredPoint) {
            return response()->json([
                'status' => false,
                'message' => 'แต้มของคุณไม่เพียงพอ'
            ]);
        }

        // ตรวจสอบ usage limit (ถ้ามี)
        if ($reward->usage_limit && $reward->used_count >= $reward->usage_limit) {
            return response()->json([
                'status' => false,
                'message' => 'ของรางวัลนี้ถูกแลกหมดแล้ว'
            ]);
        }

        // เริ่ม Transaction
        DB::beginTransaction(); // แก้ไขเป็น DB::beginTransaction()
        try {
            // หักแต้มลูกค้า
            $user->point -= $requiredPoint;
            $user->save();

            // เพิ่มจำนวน used_count ของ reward
            $reward->used_count += 1;
            $reward->save();

            // สร้างประวัติการแลกของรางวัล (ถ้ามี RewardRedeemLog Model)
            RewardRedeemLog::create([
                'user_id' => $user->id,
                'benefit_id' => $reward->id,
                'point_used' => $requiredPoint,
                'redeemed_at' => now()
            ]);

            DB::commit(); // แก้ไขเป็น DB::commit()

            return response()->json([
                'status' => true,
                'message' => 'แลกของรางวัลสำเร็จ'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // แก้ไขเป็น DB::rollBack()
            return response()->json([
                'status' => false,
                'message' => 'เกิดข้อผิดพลาดในการแลกของรางวัล: ' . $e->getMessage()
            ]);
        }
    }
}
