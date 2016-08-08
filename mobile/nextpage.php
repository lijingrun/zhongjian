<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$page = $_POST['page'];
$c_id = $_POST['c_id'];
$order_rule = $_POST['order_rule'];
//echo 111;exit;
$cat_goods = assign_cat_goods($c_id, 0, 'wap', $order_rule);
$num = count($cat_goods['goods']);
if ($num > 0) {
    $page_num = '10';
    $pages = ceil($num / $page_num);
    if ($page <= 0) {
        $page = 1;
    }
    if ($pages == 0) {
        $pages = 1;
    }
    if ($page > $pages) {
        echo "<span align='center'>没有更多商品了</span><input type='hidden' value=1 id='over' />";
    }
    $i = 1;
    foreach ($cat_goods['goods'] as $goods_data) {
        if (($i > ($page_num * ($page - 1 ))) && ($i <= ($page_num * $page))) {
            $price = empty($goods_info['promote_price_org']) ? $goods_data['shop_price'] : $goods_data['promote_price'];
            //$wml_data .= "<a href='goods.php?id={$goods_data['id']}'>".encode_output($goods_data['name'])."</a>[".encode_output($price)."]<br/>";
            $data[] = array('i' => $i, 'price' => encode_output($price), 'id' => $goods_data['id'], 'name' => encode_output($goods_data['name']), 'thumb' => $goods_data['thumb']); //16:41 2013-07-16
        }
        $i++;
    }
    if(!empty($data)){
        foreach($data as $goods):
            echo "<li style='padding: 10px;border-top: none;overflow: hidden;border-top: 1px solid #DED6C9;line-height: 1.6em;'> ";
            echo "<a href='goods.php?id=".$goods['id']." style='display: block;overflow: hidden;lear: both;padding: .22em 0;'> ";
            echo "<span class='mu-tmb' style='/*float:left;*/margin-right:8px;'>";
            if($goods['i'] <= 3){
                echo "<b style='position:absolute;padding:2px 2px 2px 2px;font-size:.65em;background:red;color:white;'>HOT</b>";
            }
            echo "<img src='/".$goods['thumb']."' alt='".$goods['name']."' width='100' height='100'> ";
            echo "</span><p>".$goods['name']."</p>"."<span class='red' style='display: block;'>";
            echo $goods['price'];
            echo "</span></a></li>";
        endforeach;
    }
}
exit;

