<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>工程师首页</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>
  <link rel="stylesheet" href="css/screen.css" type="text/css"/>
  <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.js"></script>
</head>
<body class="engineer user">
  <nav class="top-nav navbar navbar-default navbar-fixed-top">
    <div class="logo">
      <h2>Hotel Name</h2>
    </div>
    <ul class="nav-menu">
      <li><a class="active">Hotel</a></li>
      <li><a>Model</a></li>
    </ul>
    <div class="profile pull-right">
      <span class="glyphicon glyphicon-user"></span>
    </div>
  </nav>
  <section class="main-content">
    <div class="hotel-management">
      <div class="sub-title">
        <h3 class="title">Hotel</h3>
        <div class="search"></div>
      </div>
      <div class="table-responsive hotel-list">
        <table class="table">
          <thead class="list-header">
            <tr>
              <th class="name">Name</th>
              <th class="address">Address</th>
              <th class="stores">楼层数</th>
              <th class="room-num">房间数</th>
              <th class="more"></th>
            </tr>
          </thead>
          <tbody>
            <tr class="list-item">
              <th class="name"><a>某酒店名称</a></th>
              <th class="address">738 Powlowski Harbors</th>
              <th class="stores">3</th>
              <th class="room-num">24</th>
              <th class="more">
                <a><span>More</span></a>  
              </th>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!--  -->
    <div class="model-management">
      
    </div>
  </section>
</body>