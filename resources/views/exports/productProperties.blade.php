<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<table>

    <tbody>
        <tr>
            <th>Mã kho</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Mã số</th>
            <th>Mã cn</th>
            <th>Gía quy định</th>
            <th>Gía thị trường</th>
            <th>Thương hiệu</th>
            <th>Hình ảnh chi tiết</th>
            <th>Trạng thái</th>
            <th>Hiển thị</th>
            <th>Thông số kĩ thuật</th>
            <th>Mô tả</th>
            @foreach($data['listTech'] as $key=> $item)
            <th>{{ $item }}</th>
            @endforeach
        </tr>
        @foreach($data['infoProduct'] as $key=> $item)
            <tr>
              <td>{{ $item['makho'] }}</td>
              <td>{{ $item['tensanpham'] }}</td>
              <td>{{ $item['catname'] }}</td>
              <td>{{$item['maso'] }}</td>
              <td>{{ $item['macn'] }}</td>
              <td>{{  $item['price'] }}</td>
              <td>{{ $item['price_old'] }}</td>
              <td>{{ $item['brand_name'] }}</td>
              <td>{{ $item['picture'] }}</td>
              <td>{{ $item['static'] }}</td>
              <td>{{ $item['display'] }}</td>
              <td>{{ $item['technology'] }}</td>
              <td>{{ $item['describe'] }}</td>
              @foreach($data['listTech'] as $item1)
                @foreach( $item['catOp'] as $item2)
                  @if($item1 == $item2['catOption'])
                    <td>{{ $item2['nameCatOption'] }}</td>
                  @endif
                @endforeach
              @endforeach
            </tr>
        @endforeach

    </tbody>

</table>
</body>
</html>
