<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<table>
    <!-- <thead>
    <tr>
    <th>Mã kho</th>
    <th>Tên sản phẩm</th>
    @foreach($data['listTech'] as $key=> $item)
      <th>{{ $item }}</th>
    @endforeach


    </tr>
    </thead> -->
    <tbody>
        <tr>
          <th>Mã kho</th>
          <th>Tên sản phẩm</th>
          @foreach($data['listTech'] as $key=> $item)
          <th>{{ $item }}</th>
          @endforeach
        </tr>
        @foreach($data['infoProduct'] as $key=> $item)
            <tr>
              <td>{{ $item['makho'] }}</td>
              <td>{{ $item['tensanpham'] }}</td>
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
