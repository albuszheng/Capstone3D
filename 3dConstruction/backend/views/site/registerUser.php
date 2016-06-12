<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;



$this->title = 'Register User';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">入住登记</h4>
          </div>
          <div class="modal-body">
            <form class="form-inline">
                <div class="form-group">
                    <label for="roomID">房间号</label>
                    <input type="text" class="form-control" id="room-id" placeholder="room num">
                </div>
                <br />
                <div class="form-group">
                    <label for="userID">住户ID</label>
                    <input type="text" class="form-control" id="user-id" placeholder="input user id">
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            <button type="button" class="btn btn-primary" id="submit">提交</button>
          </div>
        </div>
    </div>
</div>

<div class="site-register-user">
    <div class="alert alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong class="info"></strong>
    </div>
    
    <h1><?= Html::encode($this->title) ?></h1>

    <p>住户登记</p>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
      入住登记
    </button>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
            ],
            [
                'attribute' => 'user_id',
            ],
            [
                'attribute' => 'last_modify_id',
            ],
            [
                'attribute' => 'last_modify_time',
            ]
        ],
    ]);
    ?>
</div>

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>

<script>
    var submitButton = $('#submit');
    submitButton.click(function registerRoom() {
        var room_id = $('#room-id').val();
        var user_id = $('#user-id').val();

        $.ajax({
            type: 'post',
            data: {id:room_id, user_id:user_id},
            url: 'index.php?r=site/register-room',
            success: function(result) {
                if (result.result === true) {
                    $('.alert').addClass("alert-success")
                    $('.alert.info').append(result.message);

                    console.log("success");
                } else {
                    console.log("fail");

                    $('.alert').addClass("alert-warning")
                    $('.alert.info').append(result.message);
                }
            },

            error: function(xhr) {
                console.log(xhr.responseText);
            }
        }); 
    }); 

</script>

