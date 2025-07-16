@extends('admin.layout')
@section('style')
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row d-flex justify-content-center">
                    <div class="col-10">
                        <form action="{{route('userSave')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{$info->id}}">
                            <div class="card">
                                <div class="card-header">
                                    แก้ไขข้อมูลสมาชิก
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="categories_id" class="form-label">หมวดหมู่ : </label>
                                            <select class="form-control" name="categories_id" id="categories_id" required>
                                                <option value="" disabled>เลือก</option>
                                                @foreach($categories as $rs)
                                                <option value="{{$rs->id}}" 
                                                    @if($info->categories && $info->categories->categories_id == $rs->id) selected @endif>
                                                    {{$rs->name}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">ชื่อ : </label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{$info->name}}" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="email" class="form-label">อีเมล : </label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{$info->email}}" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="tel" class="form-label">เบอร์ติดต่อ : </label>
                                            <input type="text" class="form-control" id="tel" name="tel" value="{{$info->tel}}" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="point" class="form-label">คะแนนสะสม : </label>
                                            <input type="number" class="form-control" id="point" name="point" value="{{$info->point ?? 0}}" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <a href="{{route('user')}}" class="btn btn-outline-secondary me-2">ยกเลิก</a>
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