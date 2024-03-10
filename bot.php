<?php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;
date_default_timezone_set('Asia/Shanghai');
require './inc/config.php';
echo"[NyaBot]欢迎使用NyaBot!". PHP_EOL;
echo"[NyaBot]Github开源地址:https://github.com/Mxmilu666/NyaBot". PHP_EOL;
require './inc/core.class.php';
$list = glob('./class/*.class.php');
    foreach ($list as $file) {
        $file = explode('/', $file)['2'];
        require './class/' . $file;
    }
echo"[NyaBot]全局函数加载成功!". PHP_EOL;
echo"[NyaBot]正在连接WS服务器ing..". PHP_EOL;
run(function (){
    include './inc/config.php';
    $inc = new inc($config['apidomain'], $config['AppID'], $config['Token']);
    $client = $inc->connect_ws();
    if ($client->getStatusCode() == '403') {
        echo "[NyaBot]Toekn怎么错误,检查一下吧：" . $client->getStatusCode() . '/' . $client->errCode.PHP_EOL;
    } else if ($client->getStatusCode() == '-1' or $client->errCode == '114') {
        echo "[NyaBot]网络连接失败了呢,检查一下吧".PHP_EOL;
    } else {
        echo "[NyaBot]连接ws服务端成功：" . ' 你的BOT_QQ是：' . json_decode($client->recv()->data, true)['d']['user']['username'] .PHP_EOL;
    }
    while ($client->getStatusCode() != '403') {
        $ws_data = $client->recv();
        if (empty($ws_data)) {
            echo "[NyaBot]网络怎么中断了,但是正在重连!".PHP_EOL;
            //$client->close();
            //$client = $inc->connect_ws();
            Swoole\Coroutine\System::sleep(5);
        } else {
            $op_data = json_decode($ws_data->data, true);
            if (isset($op_data['t'])) {
                switch ($op_data['t']) {
                    case 'DIRECT_MESSAGE_CREATE'://频道私信消息
                        break;
                    case 'GROUP_AT_MESSAGE_CREATE'://接收群聊消息
                        $op_data["message"] = $op_data['d']['content'];
                        $op_data["group_id"] = $op_data['d']['group_id'];
                        $inc->update_s($op_data["s"]);
                        echo '[' . date('Y.n.j-H:i:s') . ']' . '[' . $op_data['group_id'] . ']' . '收到群聊消息：' . $op_data['message'] . PHP_EOL;
                        //Coroutine::create(function () use ($client, $op_data,$inc) {
                        //    foreach (glob('./plugins/*.php') as $file) {
                        //        $inc->update_op_message($op_data,Swoole\Coroutine::getCid());
                        //        $file = explode('/', $file)['2'];
                        //        require './plugins/' . $file;
                        //    }
                    //});
                        break;
                }
            }
        }
    }
});