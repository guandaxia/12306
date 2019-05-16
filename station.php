<?php
/**
 * Description:
 * User: guansixu
 * Date: 2019/5/16
 * Time: 9:56 AM
 */

$stationUrl = 'https://www.12306.cn/index/script/core/common/station_name_v10028.js';

$str = file_get_contents($stationUrl);

$str = substr($str, 20);
$str = substr($str, 0, -2);

$array = explode('@', $str);

$servername = "127.0.0.1";
$username = "root";
$password = "123456";
$dbname = 'train';
// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

$conn->set_charset("utf8");

// 检测连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
//echo "连接成功";

$stationArray = [];
foreach ($array as $key=>$item){
    if(empty($item)){
        continue;
    }
    $item = explode('|', $item);

    $firstCharacter = substr($item[4], 0, 1);
    $firstCharacter = strtoupper($firstCharacter);

    $stationArray[] = [
        'station_name'  =>  $item[1],
        'station_code'  =>  $item[2],
        'pinyin'        =>  $item[3],
        'short_pinyin'  =>  $item[4],
        'first_character'=> $firstCharacter,
    ];
}

$stationArray = arraySort($stationArray, 'pinyin', SORT_ASC);

$sql = "INSERT INTO station_list (station_name, station_code, pinyin, short_pinyin, first_character) VALUES ";
foreach ($stationArray as $key=>$item){
    if(empty($item)){
        continue;
    }
    $sql .= "('". $item['station_name']. "', '".
        $item['station_code']. "', '". $item['pinyin']. "', '".
        $item['short_pinyin']. "', '". $item['first_character']. "'),";
}

$sql = substr($sql, 0, -1);

echo $sql;
if ($conn->query($sql) === TRUE) {
    echo "新记录插入成功";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();


/**
 * 二维数组根据某个字段排序
 * @param array $array 要排序的数组
 * @param string $keys   要排序的键字段
 * @param string $sort  排序类型  SORT_ASC     SORT_DESC
 * @return array 排序后的数组
 */
function arraySort($array, $keys, $sort = SORT_DESC) {
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}