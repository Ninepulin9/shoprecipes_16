<div class="mb-3">
    <h6>ประวัติการใช้คูปอง</h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>UID</th>
                    <th>ชื่อ</th>
                    <th>อีเมล</th>
                    <th>คูปอง</th>
                    <th>ส่วนลด</th>
                    <th>ใช้เมื่อ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($couponLogs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $user->UID }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $log->coupon_code }}</td>
                    <td>{{ number_format($log->discount_amount,2) }}</td>
                    <td>{{ $log->used_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">ไม่มีข้อมูล</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mb-3">
    <h6>ประวัติการใช้แต้มสะสม</h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>UID</th>
                    <th>ชื่อ</th>
                    <th>อีเมล</th>
                    <th>ใช้แต้ม</th>
                    <th>รายการ</th>
                    <th>ใช้เมื่อ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($redeemLogs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $user->UID }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $log->point_used }}</td>
                    <td>{{ $log->benefit->name ?? '-' }}</td>
                    <td>{{ $log->redeemed_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">ไม่มีข้อมูล</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>