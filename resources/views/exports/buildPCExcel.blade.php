<!DOCTYPE html>
<html>
<head>
<style>
    table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
/* .center img {
        display: block;
        margin: 0 auto;
    } */

</style>
</head>
<body>

<!-- <h1 style="">bảng báo giá</h1>
<h4>kính gửi quý đại lý :vi tính Nguyên kim xin gửi bảng báo giá</h4>
<h5>Người liên hệ: <span>Mr Bùi Quang Bảo (Nguyên Kim)</span></h5><br> -->
<table>
  <thead>
    <tr>
      <th colspan="5">Bảng báo giá</th>
    </tr>
    <tr>
      <th colspan="5">Kính gửi quý đại lý :Công Nghệ Chính Nhân xin gửi bảng báo giá</th>
    </tr>
    <tr>
      <th colspan="5">Người liên hệ: <span>Mr Bùi Quang Bảo (Chính Nhân)</span></th>
    </tr>
  </thead>
</table>

<table>
  <thead>
    <tr>
      <th colspan="5">245B Trần Quang Khải,Phường Tân Định , Q. 1 ,TP.HCM</th>
    </tr>
    <tr>
      <th colspan="4">Hotline: 1900 6739 (8:00-18:30 hàng ngày)</th>
      <th>Ngày</th>

    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="4" rowspan="2"></td>
      <td>{{ $data[0]['time'] }}</td>
    </tr>
    <tr>
      <td>Đơn vị tiền VNĐ</td>
    </tr>
  </tbody>

</table>
<table class="table table-striped">
    <thead>

      <tr>
        <th>#</th>
        <th>Tên hàng</th>
        <th>Hình ảnh</th>
        <th>Số lượng sản phẩm</th>
        <th>Giá sản phẩm</th>
        <th>Thành tiền</th>


      </tr>
    </thead>
    <tbody>

    <?php
      $total=0;

    ?>
        @foreach($data as $key=> $item)
        <?php
              $total+=$item['quantity']*$item['price'];
              $imgPath = base_path().'/public/uploads/'.$item['picture'];

              //no-image.jpg


        ?>
            <tr>
              <td>{{ ($key+1) }}</td>
              <td>{{ $item['productName'] }}</td>
              @if (!empty($item['picture']) && file_exists($imgPath))
                <td  class="center"  style="text-align: center; vertical-align: middle;">
                    <img style="display: block; margin: 0 auto;"  width="100px" height="100px"  src="{{ base_path().'/public/uploads/'.$item['picture'] }}">

                </td>
              @else
                <td class="center"  style="text-align: center; vertical-align: middle;">


                    <img style="display: block; margin: 0 auto;" width="100px" height="100px" src="{{ base_path().'/public/uploads/no-image.jpg'}}">

                </td>
              @endif
                <td>{{  $item['quantity'] }}</td>
              <td>{{ number_format($item['price'], 0, '', ',')  }} <span>đ</span></td>
              <td>{{ number_format($item['quantity']*$item['price'], 0, '', ',') }} <span>đ</span></td>
            </tr>
        @endforeach
        <tr>
          <td  colspan = "2">Lưu ý :Đã bao gồm 10% thuế VAT</td>
          <td  colspan = "2">Phí vận chuyển</td>
          <td> 0 <span>đ</span></td>
            <!-- <td colspan = "2">Tổng chi phí: {{  number_format($total, 0, '', ',') }} <span>đ</span></td> -->
         </tr>
         <tr>
          <td colspan = "2" rowspan="2"></td>
          <td colspan = "2">Chi phí khác</td>
          <td> 0 <span>đ</span></td>
         </tr>
         <tr>
          <td colspan = "2">Tổng tiền đơn hàng</td>
          <td >{{  number_format($total, 0, '', ',') }} <span>đ</span></td>
         </tr>
         <tr>
          <td colspan = "5">CHÂN THÀNH CẢM ƠN!</td>
         </tr>
         <tr>
          <td colspan = "5">chinhnhan.net</td>
         </tr>

    </tbody>

</table>
<!-- <h5>* Để được hưởng chế độ bảo hành tốt nhất, quý đại lý vui lòng xuất đơn VAT cho người tiêu dùng cuối khi bán hàng.</h5>
<h5>* Cho sản phẩm mực in ,đầu in quý khách chỉ dùng hàng chính hảng để được đảm bảo quyền lợi bảo hành từ nhà SX</h5>
<h5>*Trách nghiệm về hiện thực bản quyền sở hữu trí tuệ thuộc về người mua ,Nguyên Kim không chịu trách nghiệm trong việc người mua vi phạm bản quyền sở hữu trí tuệ khi mua máy tại Nguyên Kim.</h5>
<h5>*Khi gửi sản phẩm bảo hành về Nguyên Kim ,quý khách vui lòng ghi nhận Cty TNHH Vi Tính Nguyên Kim.</h5>
<h5>Địa chỉ :245B Trần Quang Khải ,phường Tân Định ,QUận 1,TP Hồ Chí Minh ĐT:028-22 246 246"</h5> -->
</body>
</html>
