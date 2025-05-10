<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table>
        <thead>
        <tr>
        {{-- <th>Id đơn hàng</th> --}}
        <th>Mã đơn hàng</th>
        <th>Tên khách hàng</th>
        <th>Số điện thoại</th>
        <th>Email</th>
        <th>Tổng giá</th>
        <th>Phương pháp vận chuyển</th>
        <th>Phương thức thanh toán</th>
        <th>Trạng thái</th>
        <th>Ngày đặt hàng</th>
        <th>Đang chờ xử lý</th>
        <th>Chờ thanh toán</th>
        <th>Đã thanh toán</th>
        <th>Đang giao hàng</th>
        <th>Đã hoàn tất</th>
        <th>Đã hủy bỏ</th>
        <th>Khách hàng hủy bỏ</th>

      </tr>
        </thead>
        <tbody>
            @foreach($data as $key=> $item)
                <tr>
                  {{-- <td>{{ $item['order_id'] }}</td> --}}
                  <td>{{ "'" . $item['order_code'] }}</td>
                  <td>{{ $item['d_name'] }}</td>
                  <td>{{ $item['d_phone'] }}</td>
                  <td>{{  $item['d_email'] }}</td>
                  <td>{{ number_format($item['total_cart'], 0, '', ',') }} <span>VNĐ</span></td>
                  <td>{{  $item['shipping_method'] }}</td>
                  <td>{{ $item['payment_method'] }}</td>
                  <td>{{ $item['status'] }}</td>
                  <td>{{ $item['date_order'] }}</td>
                  <td>{{ $item['date_order_status1'] }}</td>
                  <td>{{ $item['date_order_status2'] }}</td>
                  <td>{{ $item['date_order_status3'] }}</td>
                  <td>{{ $item['date_order_status4'] }}</td>
                  <td>{{ $item['date_order_status5'] }}</td>
                  <td>{{ $item['date_order_status6'] }}</td>
                  <td>{{ $item['date_order_status7'] }}</td>


                </tr>
            @endforeach

        </tbody>

    </table>
</body>
</html>
