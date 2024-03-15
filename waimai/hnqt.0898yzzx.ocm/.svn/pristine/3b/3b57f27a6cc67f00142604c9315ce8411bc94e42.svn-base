<?php

/**
 * 是否登录
 * @return boolean
 */
function isLogin()
{
    return session('user_openid') == null ? false : true;
}

/**
 * 获得树状数据
 * @param $data 数据
 * @param $title 字段名
 * @param string $fieldPri 主键id
 * @param string $fieldPid 父id
 * @return array
 */
function getTreeData($data, $title, $fieldPri = 'id', $fieldPid = 'pid')
{
    $data = \util\Data::tree($data, $title, $fieldPri, $fieldPid);
    $result = [];
    foreach ($data as $key => $value) {
        $result[] = $value;
    }
    return $result;
}



/**************************************************/

/***
 * 身份证真实性验证规则
 */
function validation_filter_id_card($id_card)
{
    if (strlen($id_card) == 18) {
        return idcard_checksum18($id_card);
    } elseif ((strlen($id_card) == 15)) {
        $id_card = idcard_15to18($id_card);
        return idcard_checksum18($id_card);
    } else {
        return false;
    }
}

// 计算身份证校验码，根据国家标准GB 11643-1999
function idcard_verify_number($idcard_base)
{
    if (strlen($idcard_base) != 17) {
        return false;
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++) {
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}

// 将15位身份证升级到18位
function idcard_15to18($idcard)
{
    if (strlen($idcard) != 15) {
        return false;
    } else {
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false) {
            $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
        } else {
            $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
        }
    }
    $idcard = $idcard . idcard_verify_number($idcard);
    return $idcard;
}

// 18位身份证校验码有效性检查
function idcard_checksum18($idcard)
{
    if (strlen($idcard) != 18) {
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
        return false;
    } else {
        return true;
    }
}

//验证是否是正确的手机号
function isMobile($value)
{
    $rule = '/^0?(13|14|15|17|18)[0-9]{9}$/';
    $result = preg_match($rule, $value);
    if ($result) {
        return true;
    } else {
        return false;
    }
}


//返还的json数据
function show($code,$msg,$data=[],$totalCount=''){
    $result = [
        'code' => $code,
        'msg' => $msg
    ];
    if(!empty($data)){
        $result['data'] = $data;
    }
    if($totalCount!=''){
        $result['totalCount'] = $totalCount;
    }
    exit(json_encode($result));
}

function verify($param=''){
    $input = input('post.');
    $param = explode(",",$param);
    if(!empty($param))foreach($param as $v){
        if(!isset($input[$v])||$input[$v]===''){
            exit(json_encode(['code'=>400,'msg'=>$v.'不能为空']));
        }
    }
}

/**
** 将数组转换成 in()
**/
function dbCreateIn($itemList)
{
    if(empty($itemList )){
        return " ";
    }else{
        $_s = '';
        foreach ($itemList as $key => $value) {
            if($key==0){
                $_s = $value['id'];
            }else{
                $_s .= ','.$value['id'];
            }
        }
        return $_s;
    }
}

function fen_change_yuan($fen){
    $yuan = $fen/100;

    return $yuan;
}

function yuan_change_fen($yuan){
    $fen = $yuan*100;

    return $fen;
}


