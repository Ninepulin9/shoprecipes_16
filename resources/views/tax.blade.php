<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ใบเสร็จรับเงิน</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px 0;
            color: #2d2d2d;
            background: #ffffff;
        }

        .receipt {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 30px;
            border-radius: 5px;
        }

        .receipt h2 {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
            font-weight: 600;
            color: #1e293b;
        }

        .receipt span {
            font-weight: 700;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 1px;
        }

        .header .info,
        .header .tax-label {
            display: table-cell;
            vertical-align: top;
        }

        .header .info {
            text-align: left;
        }

        .header .tax-label {
            text-align: right;
            font-weight: 600;
            color: #475569;
        }

        .info p {
            margin: 4px 0;
            font-size: 14px;
        }

        /* ส่วนข้อมูลสมาชิก */
        .member-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .member-info h4 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
            font-weight: 600;
        }

        .member-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .member-info .highlight {
            color: #007bff;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        th:nth-child(1),
        td:nth-child(1) {
            text-align: left;
            width: 60%;
        }

        th:nth-child(2),
        td:nth-child(2) {
            text-align: center;
            width: 10%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            text-align: right;
            width: 30%;
        }

        .total {
            text-align: right;
            font-weight: 700;
            color: #1e293b;
            border-top: 2px solid #000;
            margin-top: 20px;
            padding-top: 12px;
            font-size: 16px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            #print-area {
                position: absolute;
                top: 20;
                left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div id="print-area">
        <div class="receipt">
            <h2><span>{{$config->name}}</span></h2>
            <div class="header">
                <div class="info">
                    <p><strong>เลขที่ใบเสร็จ #{{$pay->payment_number}}</strong></p>
                    <p>วันที่: {{$pay->created_at}}</p>
                    @if($pay->table_id)
                        <p>จุดที่: {{$pay->table_id}}</p>
                    @endif
                </div>
            </div>

            <!-- ข้อมูลสมาชิก -->
            <div class="member-info">
                <h4> ข้อมูลสมาชิก</h4>
                @if($pay->user)
                    <p><strong>UID:</strong> <span class="highlight">{{ $pay->user->UID }}</span></p>
                    <p><strong>ชื่อ:</strong> {{ $pay->user->name }}</p>
                    <p><strong>เบอร์โทร:</strong> {{ $pay->user->tel }}</p>
                    <p><strong>แต้มคงเหลือ:</strong> <span class="highlight">{{ number_format($pay->user->point ?? 0) }} แต้ม</span></p>
                @else
                    <p><strong>UID:</strong> -</p>
                    <p><strong>ชื่อ:</strong> -</p>
                    <p><strong>เบอร์โทร:</strong> -</p>
                    <p><strong>แต้มคงเหลือ:</strong> -</p>
                @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th>รายการ</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order as $rs)
                    <tr>
                        <td>
                            <div>{{ $rs['menu']->name }}</div>
                            @foreach($rs['option'] as $option)
                            <div style="font-size: 12px; color: #6b7280;">+ {{$option['option']->type}}</div>
                            @endforeach
                            @if($rs->remark)
                                <div style="font-size: 12px; color: #6b7280;">หมายเหตุ: {{$rs->remark}}</div>
                            @endif
                        </td>
                        <td>{{ $rs->quantity }}</td>
                        <td>{{ number_format($rs->price, 2) }} ฿</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="total">รวมทั้งสิ้น: {{ number_format($pay->total, 2) }} ฿</p>

            <!-- Footer -->
            <div class="footer">
                <p>ขอบคุณที่ใช้บริการ</p>
                @if($pay->user)
                    <p style="color: #007bff;">สะสมแต้มเพื่อรับสิทธิพิเศษ</p>
                @else
                    <p style="color: #6c757d;">สมัครสมาชิกเพื่อสะสมแต้ม</p>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
<script>
    window.print();
</script>