<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\admin\Category;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\LogStock;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\MenuStock;
use App\Models\MenuTypeOption;
use App\Models\Orders;
use App\Models\OrdersDetails;
use App\Models\OrdersOption;
use App\Models\Promotion;
use App\Models\Stock;
use App\Models\Table;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Session;
use App\Models\CouponUsageLog;

class Main extends Controller
{
    public function index(Request $request)
    {
        $table_id = $request->input('table');
        if ($table_id) {
            $table = Table::where('table_number', $table_id)->first();
            session(['table_id' => $table_id]);
        }
        $promotion = Promotion::where('is_status', 1)->get();
        $category = Categories::has('menu')->with('files')->get();
        return view('users.main_page', compact('category', 'promotion'));
    }

    public function detail($id)
    {
        $item = [];
        $menu = Menu::where('categories_id', $id)->with('files')->orderBy('created_at', 'asc')->get();
        foreach ($menu as $key => $rs) {
            $item[$key] = [
                'id' => $rs->id,
                'category_id' => $rs->categories_id,
                'name' => $rs->name,
                'detail' => $rs->detail,
                'base_price' => $rs->base_price,
                'files' => $rs['files']
            ];
            $typeOption = MenuTypeOption::where('menu_id', $rs->id)->get();
            if (count($typeOption) > 0) {
                foreach ($typeOption as $typeOptions) {
                    $optionItem = [];
                    $option = MenuOption::where('menu_type_option_id', $typeOptions->id)->get();
                    foreach ($option as $options) {
                        $optionItem[] = (object) [
                            'id' => $options->id,
                            'name' => $options->type,
                            'price' => $options->price
                        ];
                    }
                    $item[$key]['option'][$typeOptions->name] = [
                        'is_selected' => $typeOptions->is_selected,
                        'amout' => $typeOptions->amout,
                        'items' => $optionItem
                    ];
                }
            } else {
                $item[$key]['option'] = [];
            }
        }
        $menu = $item;
        return view('users.detail_page', compact('menu'));
    }

    public function order()
    {
        return view('users.list_page');
    }

    // ✅ แก้ไข SendOrder method ใน Main Controller
public function SendOrder(Request $request)
{
    $data = [
        'status' => false,
        'message' => 'สั่งออเดอร์ไม่สำเร็จ',
    ];
    $orderData = $request->input('cart');
    $remark = $request->input('remark');
    $coupon = $request->input('coupon');
    $item = array();
    $menu_id = array();
    $categories_id = array();
    $total = 0;
    
    foreach ($orderData as $key => $order) {
        $item[$key] = [
            'menu_id' => $order['id'],
            'quantity' => $order['amount'],
            'price' => $order['total_price'], // ✅ ราคารวมของ item นี้แล้ว
            'note' => $order['note'] ?? '',
        ];
        if (!empty($order['options'])) {
            foreach ($order['options'] as $rs) {
                $item[$key]['option'][] = $rs['id'];
            }
        } else {
            $item[$key]['option'] = [];
        }
        $total = $total + $order['total_price']; // ✅ บวกยอดรวมทั้งหมด
        $menu_id[] = $order['id'];
    }
    
    $menu_id = array_unique($menu_id);
    foreach ($menu_id as $rs) {
        $menu = Menu::find($rs);
        $categories_id[] = $menu->categories_member_id;
    }
    $categories_id = array_unique($categories_id);

    if (!empty($item)) {
        $discount = 0;
        
        // ตรวจสอบการใช้คูปองซ้ำก่อนคำนวณส่วนลด
        $couponModel = null;
        if ($coupon) {
             $tableId = session('table_id');
            // หากโต๊ะนี้เคยใช้คูปองแล้ว ให้แจ้งเตือนและยกเลิกการใช้คูปอง
            if ($tableId && Orders::where('table_id', $tableId)->whereNotNull('coupon_code')->exists()) {
                return response()->json(['status' => false, 'message' => 'คูปองถูกใช้ไปแล้ว']);
            }
            $couponModel = Coupon::where('code', $coupon)->first();
            if ($couponModel && $couponModel->isValid()) {
                if (CouponUsageLog::where('user_id', Session::get('user')->id ?? 0)
                        ->where('coupon_code', $couponModel->code)->exists()) {
                    return response()->json(['status' => false, 'message' => 'คูปองนี้ถูกใช้ไปแล้ว']);
                }
                $discount = $couponModel->calculateDiscount($total);
                $couponModel->incrementUsage();
            }
        }
        
        // ✅ สร้างออเดอร์หลักแค่ครั้งเดียว
        $order = new Orders();
        $order->table_id = session('table_id') ?? '1';
        $order->total = $total - $discount; // ✅ ยอดรวม - ส่วนลด (ครั้งเดียว)
        $order->remark = $remark;
        $order->status = 1;
        if ($couponModel) {
            $order->coupon_code = $couponModel->code;
            $order->discount_amount = $discount;
        }
        
        if ($order->save()) {
            // ✅ บันทึกรายละเอียดแต่ละ item (ไม่คำนวณส่วนลดซ้ำ)
            foreach ($item as $rs) {
                $orderdetail = new OrdersDetails();
                $orderdetail->order_id = $order->id;
                $orderdetail->menu_id = $rs['menu_id'];
                $orderdetail->quantity = $rs['quantity'];
                $orderdetail->price = $rs['price']; // ✅ ราคาต้นฉบับ ไม่หักส่วนลด
                $orderdetail->remark = $rs['note'];
                
                if ($orderdetail->save()) {
                    foreach ($rs['option'] as $key => $option) {
                        $orderOption = new OrdersOption();
                        $orderOption->order_detail_id = $orderdetail->id;
                        $orderOption->option_id = $option;
                        $orderOption->save();
                        
                        // จัดการ stock
                        $menuStock = MenuStock::where('menu_option_id', $option)->get();
                        if ($menuStock->isNotEmpty()) {
                            foreach ($menuStock as $stock_rs) {
                                $stock = Stock::find($stock_rs->stock_id);
                                $stock->amount = $stock->amount - ($stock_rs->amount * $rs['quantity']);
                                if ($stock->save()) {
                                    $log_stock = new LogStock();
                                    $log_stock->stock_id = $stock_rs->stock_id;
                                    $log_stock->order_id = $order->id;
                                    $log_stock->menu_option_id = $rs['option'];
                                    $log_stock->old_amount = $stock_rs->amount;
                                    $log_stock->amount = ($stock_rs->amount * $rs['quantity']);
                                    $log_stock->status = 2;
                                    $log_stock->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Event notifications
        $order = [
            'is_member' => 0,
            'text' => '📦 มีออเดอร์ใหม่'
        ];
        event(new OrderCreated($order));
        
        if (!empty($categories_id)) {
            foreach ($categories_id as $rs) {
                $order = [
                    'is_member' => 1,
                    'categories_id' => $rs,
                    'text' => '📦 มีออเดอร์ใหม่'
                ];
                event(new OrderCreated($order));
            }
        }
        
        $data = [
            'status' => true,
            'message' => 'สั่งออเดอร์เรียบร้อยแล้ว',
        ];
    }
    return response()->json($data);
}

   public function checkCoupon(Request $request)
{
    $data = [
        'status' => false,
        'message' => 'คูปองไม่ถูกต้อง',
    ];

    $code = $request->input('code');
    $subtotal = $request->input('subtotal');
    $tableId = session('table_id');

    if ($code && $subtotal > 0) {
        $coupon = Coupon::where('code', $code)->first();
        
        if ($coupon) {
            // ✅ ตรวจสอบว่าคูปองหมดอายุหรือใช้หมดแล้ว
            if (!$coupon->isValid()) {
                $data['message'] = 'คูปองหมดอายุแล้วหรือใช้ครบจำนวนที่กำหนด';
                return response()->json($data);
            }
            
            /// ตรวจสอบว่าโต๊ะนี้เคยใช้คูปองแล้วหรือยัง
            if ($tableId && Orders::where('table_id', $tableId)->whereNotNull('coupon_code')->exists()) {
                $data['message'] = 'คูปองถูกใช้ไปแล้ว';
                return response()->json($data);
            }

            // ✅ ตรวจสอบว่าคูปองถูกใช้ครบจำนวนหรือไม่
            if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                $data['message'] = 'คูปองนี้ถูกใช้ครบจำนวนแล้ว';
                return response()->json($data);
            }
            // ตรวจสอบว่าผู้ใช้นี้เคยใช้คูปองนี้แล้วหรือไม่
            if (Session::get('user') && CouponUsageLog::where('user_id', Session::get('user')->id)
                    ->where('coupon_code', $code)->exists()) {
                $data['message'] = 'คูปองนี้ถูกใช้ไปแล้ว';
                return response()->json($data);
            }
            
            $discount = $coupon->calculateDiscount($subtotal);
            $bonusPoints = $coupon->getBonusPoints();
            
            $data = [
                'status' => true,
                'message' => 'คูปองใช้ได้',
                'coupon_type' => $coupon->discount_type, 
                'discount' => $discount,
                'bonus_points' => $bonusPoints, 
                'final_total' => $subtotal - $discount,
                'original_total' => $subtotal
            ];
        } else {
            $data['message'] = 'ไม่พบคูปองนี้ในระบบ';
        }
    }

    return response()->json($data);
}

    public function sendEmp()
    {
        event(new OrderCreated(['ลูกค้าเรียกจากจุดที่ ' . session('table_id')]));
    }

    public function applyCoupons(Request $request)
    {
        $coupons = Coupon::where('code', $request->coupons_code)->first();

        if (!$coupons) {
            return response()->json(['message' => 'coupons not found'], 404);
        }

        if (!$coupons->isValid()) {
            return response()->json(['message' => 'coupons expired or usage limit reached'], 400);
        }

        // คำนวณส่วนลด
        $subtotal = $request->subtotal; // รับจาก Frontend
        if ($coupons->discount_type == 'percent') {
            $discount = ($subtotal * $coupons->discount_value) / 100;
        } else {
            $discount = $coupons->discount_value;
        }

        $discount = min($discount, $subtotal); // ส่วนลดไม่เกินยอดซื้อ

        return response()->json([
            'message' => 'Coupons applied',
            'discount' => $discount,
            'final_total' => $subtotal - $discount
        ]);
    }
    public function couponStatus()
    {
        $tableId = session('table_id');
        $used = false;
        if ($tableId) {
            $used = Orders::where('table_id', $tableId)
                ->whereNotNull('coupon_code')->exists();
        }

        return response()->json(['used' => $used]);
    }
   

}
