<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Categories_member;
use App\Models\User;
use App\Models\UsersCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class Member extends Controller
{
    public function member()
    {
        $data['function_key'] = __FUNCTION__;
        return view('member.index', $data);
    }

    public function memberlistData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $table = User::with('categories.categories')->where('is_member', 1)->get();

        if (count($table) > 0) {
            $info = [];
            foreach ($table as $rs) {
                $action = '<a href="' . route('memberEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteTable" title="ลบ"><i class="bx bxs-trash"></i></button>';
                $info[] = [
                    'name' => $rs->name,
                    'categories' => $rs['categories']['categories']->name ?? '-',
                    'email' => $rs->email,
                    'tel' => $rs->tel,
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

    public function memberDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = User::find($id);
            if ($delete->delete()) {
                $data = [
                    'status' => true,
                    'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                ];
            }
        }

        return response()->json($data);
    }

    public function memberCreate()
    {
        $data['function_key'] = 'member';
        $data['categories'] = Categories_member::get();
        return view('member.create', $data);
    }

    public function memberEdit($id)
    {
        $function_key = 'member';
        $categories = Categories_member::get();
        $info = User::with('categories')->find($id);

        return view('member.edit', compact('info', 'function_key', 'categories'));
    }

   public function memberSave(Request $request)
{
    $input = $request->input();
    if (!isset($input['id'])) {
        // สร้าง UID ที่ unique สำหรับ member ใหม่
        do {
            $uid = Str::upper(Str::random(8));
        } while (User::where('UID', $uid)->exists());

        $table = new User();
        $table->name = $input['name'];
        $table->email = $input['email'];
        $table->tel = $input['tel'];
        $table->UID = $uid;
        $table->role = 'admin';
        $table->email_verified_at = now();
        $table->password = Hash::make('123456789');
        $table->remember_token = null;
        $table->is_member = 1;
        $table->point = 0;
        
        if ($table->save()) {
            $categories = new UsersCategories();
            $categories->users_id = $table->id;
            $categories->categories_id = $input['categories_id'];
            if ($categories->save()) {
                return redirect()->route('member')->with('success', 'บันทึกรายการเรียบร้อยแล้ว UID: ' . $uid);
            }
        }
    } else {
        $table = User::find($input['id']);
        $table->name = $input['name'];
        $table->email = $input['email'];
        $table->tel = $input['tel'];
        if ($table->save()) {
            $categories = UsersCategories::where('users_id', $input['id'])->first();
            if ($categories) {
                $categories->categories_id = $input['categories_id'];
                if ($categories->save()) {
                    return redirect()->route('member')->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
                }
            }
        }
    }
    return redirect()->route('member')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
}   

    public function user()
    {
        $data['function_key'] = 'user';
        return view('member.user_index', $data);
    }

    public function userCreate()
    {
        $data['function_key'] = 'user';
        $data['categories'] = Categories_member::get();
        return view('member.user_create', $data);
    }
       public function userDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = User::find($id);
            if ($delete->delete()) {
                $data = [
                    'status' => true,
                    'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                ];
            }
        }

        return response()->json($data);
    }

   public function UsermemberlistData()
{
    $data = [
        'status' => false,
        'message' => '',
        'data' => []
    ];

    $table = User::with('categories.categories')->where('role', 'user')->get();

    if (count($table) > 0) {
        $info = [];
        foreach ($table as $rs) {
            $action = '<a href="' . route('userEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
    <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteUserTable" title="ลบ"><i class="bx bxs-trash"></i></button>';

            $info[] = [
                'name' => $rs->name ?? '-',
                'uid' => $rs->UID ?? '-',
                'email' => $rs->email ?? '-',
                'tel' => $rs->tel ?? '-',
                'point' => number_format($rs->point ?? 0, 0) . ' คะแนน',
                'categories' => $rs->categories->categories->name ?? '-',
                'action' => $action
            ];
        }
        $data = [
            'data' => $info,
            'status' => true,
            'message' => 'success'
        ];
    } else {
        $data['message'] = 'ไม่พบข้อมูล user';
    }

    return response()->json($data);
}

    public function userSave(Request $request)
{
    $input = $request->input();

    if (!isset($input['id'])) {
        // สร้าง user ใหม่ + สร้าง UID ที่ unique
        do {
            $uid = Str::upper(Str::random(8));
        } while (User::where('UID', $uid)->exists());

        $table = new User();
        $table->name = $input['name'];
        $table->email = $input['email'];
        $table->tel = $input['tel'];
        $table->UID = $uid;
        $table->role = 'user';
        $table->email_verified_at = now();
        $table->password = Hash::make('123456789');
        $table->remember_token = null;
        $table->is_member = 0;
        $table->point = 0; // เพิ่ม point เริ่มต้น 0

        if ($table->save()) {
            $categories = new UsersCategories();
            $categories->users_id = $table->id;
            $categories->categories_id = $input['categories_id'];
            if ($categories->save()) {
                return redirect()->route('user')->with('success', 'บันทึกข้อมูล user เรียบร้อยแล้ว UID: ' . $uid);
            }
        }
    } else {
        $table = User::where('id', $input['id'])->where('role', 'user')->first();
        if ($table) {
            $table->name = $input['name'];
            $table->email = $input['email'];
            $table->tel = $input['tel'];

            if ($table->save()) {
                $categories = UsersCategories::where('users_id', $input['id'])->first();
                if ($categories) {
                    $categories->categories_id = $input['categories_id'];
                    if ($categories->save()) {
                        return redirect()->route('user')->with('success', 'อัพเดทข้อมูล user เรียบร้อยแล้ว');
                    }
                }
            }
        } else {
            return redirect()->route('user')->with('error', 'ไม่พบข้อมูล user');
        }
    }

    return redirect()->route('user')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
}
    public function userEdit($id)
    {
        $function_key = 'user';
        $categories = Categories_member::get();
        $info = User::with('categories')->where('id', $id)->where('role', 'user')->first();

        if (!$info) {
            return redirect()->route('user')->with('error', 'ไม่พบข้อมูล user');
        }

        return view('member.edit', compact('info', 'function_key', 'categories'));
    }
    public function checkEmailExists(Request $request)
{
    $email = $request->input('email');
    $id = $request->input('id');
    
    $query = User::where('email', $email);
    
    if ($id) {
        $query->where('id', '!=', $id);
    }
    
    $exists = $query->exists();
    
    return response()->json(['exists' => $exists]);
}

public function checkTelExists(Request $request)
{
    $tel = $request->input('tel');
    $id = $request->input('id');
    
    $query = User::where('tel', $tel);
    
    if ($id) {
        $query->where('id', '!=', $id);
    }
    
    $exists = $query->exists();
    
    return response()->json(['exists' => $exists]);
}
}