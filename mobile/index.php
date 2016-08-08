<?php

/**
 * ECSHOP mobile首页
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: index.php 15013 2010-03-25 09:31:42Z liuhui $
 */
define('IN_ECS', true);
define('ECS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');

//微信登录授权验证
$code = $_GET['code'];
$state = $_GET['state'];
//如果code和state不为空，就获取微信数据，并查看用户表有无，无就注册账号，有就直接登录
if (!empty($code) && !empty($state)) {
    $appid = '';
    $appsecret = '';
    $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
    $token = json_decode(file_get_contents($token_url));
    if (isset($token->errcode)) {
        echo '<h1>错误：</h1>' . $token->errcode;
        echo '<br/><h2>错误信息：</h2>' . $token->errmsg;
        exit;
    }
    $access_token_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=' . $appid . '&grant_type=refresh_token&refresh_token=' . $token->refresh_token;
//转成对象
    $access_token = json_decode(file_get_contents($access_token_url));
    if (isset($access_token->errcode)) {
        echo '<h1>错误：</h1>' . $access_token->errcode;
        echo '<br/><h2>错误信息：</h2>' . $access_token->errmsg;
        exit;
    }
    $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token->access_token . '&openid=' . $access_token->openid . '&lang=zh_CN';
//转成对象
    $user_info = json_decode(file_get_contents($user_info_url));
    if (isset($user_info->errcode)) {
        echo '<h1>错误：</h1>' . $user_info->errcode;
        echo '<br/><h2>错误信息：</h2>' . $user_info->errmsg;
        exit;
    }
//打印用户信息
    echo '<pre>';
    print_r($user_info);
    echo '</pre>';
}


$best_goods = get_recommend_goods('best');
$best_num = count($best_goods);
$smarty->assign('best_num', $best_num);
if ($best_num > 0) {
    $i = 0;
    foreach ($best_goods as $key => $best_data) {
        $best_goods[$key]['shop_price'] = encode_output($best_data['shop_price']);
        $best_goods[$key]['name'] = encode_output($best_data['name']);
        /* if ($i > 2)
          {
          break;
          } */
        $i++;
    }
    $smarty->assign('best_goods', $best_goods);
}

/* 热门商品 */
$hot_goods = get_recommend_goods('hot');
$hot_num = count($hot_goods);
$smarty->assign('hot_num', $hot_num);
if ($hot_num > 0) {
    $i = 0;
    foreach ($hot_goods as $key => $hot_data) {
        $hot_goods[$key]['shop_price'] = encode_output($hot_data['shop_price']);
        $hot_goods[$key]['name'] = encode_output($hot_data['name']);
        /* if ($i > 2)
          {
          break;
          } */
        $i++;
    }
    $smarty->assign('hot_goods', $hot_goods);
}

/* 最新商品 */
$order_rule = 'ORDER BY shop_price DESC, g.last_update DESC';
$new_goods = wap_get_recommend_goods('new', $order_rule);
$smarty->assign('new_goods', $new_goods);


$promote_goods = get_promote_goods();
$promote_num = count($promote_goods);
$smarty->assign('promote_num', $promote_num);
if ($promote_num > 0) {
    $i = 0;
    foreach ($promote_goods as $key => $promote_data) {
        $promote_goods[$key]['shop_price'] = encode_output($promote_data['shop_price']);
        $promote_goods[$key]['name'] = encode_output($promote_data['name']);
        /* if ($i > 2)
          {
          break;
          } */
        $i++;
    }
    $smarty->assign('promote_goods', $promote_goods);
}

$pcat_array = get_categories_tree();
$pcat_count = count($pcat_array);
$a = $pcat_count / 2;
$b = ceil($a);
$p_height = $b * 60 + 30;
$p_height = $p_height . "px";
$smarty->assign('p_height', $p_height);
foreach ($pcat_array as $key => $pcat_data) {
    $pcat_array[$key]['name'] = encode_output($pcat_data['name']);
    if ($pcat_data['cat_id']) {
        if (count($pcat_data['cat_id']) > 3) {
            $pcat_array[$key]['cat_id'] = array_slice($pcat_data['cat_id'], 0, 3);
        }
        foreach ($pcat_array[$key]['cat_id'] as $k => $v) {
            $pcat_array[$key]['cat_id'][$k]['name'] = encode_output($v['name']);
        }
    }
}
$smarty->assign('pcat_array', $pcat_array);
$brands_array = get_brands();
if (!empty($brands_array)) {
    if (count($brands_array) > 3) {
        $brands_array = array_slice($brands_array, 0, 10);
    }
    foreach ($brands_array as $key => $brands_data) {
        $brands_array[$key]['brand_name'] = encode_output($brands_data['brand_name']);
    }
    $smarty->assign('brand_array', $brands_array);
}

$article_array = $db->GetALLCached("SELECT article_id, title FROM " . $ecs->table("article") . " WHERE cat_id != 0 AND is_open = 1 AND open_type = 0 ORDER BY article_id DESC LIMIT 0,4");
//$article_array = get_cat_articles(3);
if (!empty($article_array)) {
    foreach ($article_array as $key => $article_data) {
        $article_array[$key]['title'] = encode_output($article_data['title']);
    }
    $smarty->assign('article_array', $article_array);
}
if ($_SESSION['user_id'] > 0) {
    $smarty->assign('user_name', $_SESSION['user_name']);
}

if (!empty($GLOBALS['_CFG']['search_keywords'])) {
    $searchkeywords = explode(',', trim($GLOBALS['_CFG']['search_keywords']));
} else {
    $searchkeywords = array();
}
$smarty->assign('searchkeywords', $searchkeywords);

$smarty->assign('wap_logo', $_CFG['wap_logo']);
$smarty->assign('footer', get_footer());
$smarty->display("index.html");

/**
 * 获得文章分类下的文章列表
 *
 * @access  public
 * @param   integer     $cat_id
 * @param   integer     $page
 * @param   integer     $size
 *
 * @return  array
 */
function get_cat_articles($cat_id, $page = 1, $size = 20, $requirement = '') {
    //取出所有非0的文章
    if ($cat_id == '-1') {
        $cat_str = 'cat_id > 0';
    } else {
        $cat_str = get_article_children($cat_id);
    }
    //增加搜索条件，如果有搜索内容就进行搜索    
    if ($requirement != '') {
        $sql = 'SELECT article_id, title, author, add_time, file_url, open_type' .
                ' FROM ' . $GLOBALS['ecs']->table('article') .
                ' WHERE is_open = 1 AND title like \'%' . $requirement . '%\' ' .
                ' ORDER BY article_type DESC, article_id DESC';
    } else {

        $sql = 'SELECT article_id, title, author, add_time, file_url, open_type' .
                ' FROM ' . $GLOBALS['ecs']->table('article') .
                ' WHERE is_open = 1 AND ' . $cat_str .
                ' ORDER BY article_type DESC, article_id DESC';
    }

    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    if ($res) {
        while ($row = $GLOBALS['db']->fetchRow($res)) {
            $article_id = $row['article_id'];

            $arr[$article_id]['id'] = $article_id;
            $arr[$article_id]['title'] = $row['title'];
            $arr[$article_id]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
            $arr[$article_id]['author'] = empty($row['author']) || $row['author'] == '_SHOPHELP' ? $GLOBALS['_CFG']['shop_name'] : $row['author'];
            $arr[$article_id]['url'] = $row['open_type'] != 1 ? build_uri('article', array('aid' => $article_id), $row['title']) : trim($row['file_url']);
            $arr[$article_id]['add_time'] = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        }
    }

    return $arr;
}

/**
 * 获得推荐商品
 *
 * @access  public
 * @param   string	  $type	   推荐类型，可以是 best, new, hot
 * @param   string	  $order_rule	 指定商品排序规则
 * @return  array
 */
function wap_get_recommend_goods($type = '', $order_rule = '') {
    if (!in_array($type, array('best', 'new', 'hot'))) {
        return array();
    }

    //取不同推荐对应的商品
    static $type_goods = array();
    if (empty($type_goods[$type])) {
        //初始化数据
        $type_goods['best'] = array();
        $type_goods['new'] = array();
        $type_goods['hot'] = array();
        $data = read_static_cache('recommend_goods');
        if ($data === false) {
            $sql = 'SELECT g.goods_id, g.is_best, g.is_new, g.is_hot, g.is_promote, b.brand_name,g.sort_order ' .
                    ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                    ' LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
                    ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND (g.is_best = 1 OR g.is_new =1 OR g.is_hot = 1)' .
                    ' ORDER BY g.sort_order, g.last_update DESC';
            $goods_res = $GLOBALS['db']->getAll($sql);
            //定义推荐,最新，热门，促销商品
            $goods_data['best'] = array();
            $goods_data['new'] = array();
            $goods_data['hot'] = array();
            $goods_data['brand'] = array();
            if (!empty($goods_res)) {
                foreach ($goods_res as $data) {
                    if ($data['is_best'] == 1) {
                        $goods_data['best'][] = array('goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']);
                    }
                    if ($data['is_new'] == 1) {
                        $goods_data['new'][] = array('goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']);
                    }
                    if ($data['is_hot'] == 1) {
                        $goods_data['hot'][] = array('goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']);
                    }
                    if ($data['brand_name'] != '') {
                        $goods_data['brand'][$data['goods_id']] = $data['brand_name'];
                    }
                }
            }
            write_static_cache('recommend_goods', $goods_data);
        } else {
            $goods_data = $data;
        }

        $time = gmtime();
        $order_type = $GLOBALS['_CFG']['recommend_order'];

        //按推荐数量及排序取每一项推荐显示的商品 order_type可以根据后台设定进行各种条件显示
        static $type_array = array();
        $type2lib = array('best' => 'recommend_best', 'new' => 'recommend_new', 'hot' => 'recommend_hot');
        if (empty($type_array)) {
            foreach ($type2lib as $key => $data) {
                if (!empty($goods_data[$key])) {
                    $data_count = count($goods_data[$key]);
                    $num = $data_count;
                    if ($order_type == 0) {
                        //usort($goods_data[$key], 'goods_sort');
                        $rand_key = array_slice($goods_data[$key], 0, $num);
                        foreach ($rand_key as $key_data) {
                            $type_array[$key][] = $key_data['goods_id'];
                        }
                    } else {
                        $rand_key = array_rand($goods_data[$key], $num);
                        if ($num == 1) {
                            $type_array[$key][] = $goods_data[$key][$rand_key]['goods_id'];
                        } else {
                            foreach ($rand_key as $key_data) {
                                $type_array[$key][] = $goods_data[$key][$key_data]['goods_id'];
                            }
                        }
                    }
                } else {
                    $type_array[$key] = array();
                }
            }
        }

        //取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, " .
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ";
        $type_merge = array_merge($type_array['new'], $type_array['best'], $type_array['hot']);
        $type_merge = array_unique($type_merge);
        $sql .= ' WHERE g.goods_id ' . db_create_in($type_merge);
        $order_rule = empty($order_rule) ? ' ORDER BY g.sort_order, g.last_update DESC' : $order_rule;
        $sql .= $order_rule;

        $result = $GLOBALS['db']->getAll($sql);
        foreach ($result AS $idx => $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            } else {
                $goods[$idx]['promote_price'] = '';
            }

            $goods[$idx]['id'] = $row['goods_id'];
            $goods[$idx]['name'] = $row['goods_name'];
            $goods[$idx]['brief'] = $row['goods_brief'];
            $goods[$idx]['brand_name'] = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
            $goods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);

            $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                    sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price'] = price_format($row['shop_price']);
            $goods[$idx]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$idx]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
            if (in_array($row['goods_id'], $type_array['best'])) {
                $type_goods['best'][] = $goods[$idx];
            }
            if (in_array($row['goods_id'], $type_array['new'])) {
                $type_goods['new'][] = $goods[$idx];
            }
            if (in_array($row['goods_id'], $type_array['hot'])) {
                $type_goods['hot'][] = $goods[$idx];
            }
        }
    }
    return $type_goods[$type];
}

?>
