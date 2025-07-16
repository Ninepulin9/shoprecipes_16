<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Categories;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
                if ($rs->expired_at) {
                    $expiredDate = \Carbon\Carbon::parse($rs->expired_at);
                    $now = \Carbon\Carbon::now();
                    
                    if ($expiredDate->isPast()) {
                        $expiredAt = '<span class="badge bg-danger">หมดอายุแล้ว</span><br><small>' . $expiredDate->format('d/m/Y H:i') . '</small>';
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
}