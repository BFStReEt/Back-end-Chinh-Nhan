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
                <th>Link sản phẩm</th>
                <th>Thời gian</th>
                <th>Số lần</th>
                <th>Module</th>
                <th>Action</th>
                <th>Ip</th>
                <th>Tên khách hàng</th>
                <!-- <th>Action</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($data as $val)
                <tr>
                        <td>{{$val['url']}}</td>
                        <td>{{$val['date']}}</td>
                        <td>{{$val['count']}}</td>
                        <td>{{$val['module']}}</td>
                        <td>{{$val['action']}}</td>
                        <td>{{$val['ip']}}</td>
                        <td>{{$val['name']}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
