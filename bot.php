<?php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;
date_default_timezone_set('Asia/Shanghai');
require './inc/config.php';//加载配置文件
require './inc/mlog.class.php';//加载Mlog
echo"[NyaBot]NyaBot v0.0.1-Dev". PHP_EOL;
echo"[NyaBot]Github:https://github.com/Mxmilu666/NyaBot". PHP_EOL;
require './inc/core.class.php';//加载全局函数
$list = glob('./class/*.class.php');
    foreach ($list as $file) {
        $file = explode('/', $file)['2'];
        require './class/' . $file;
    }
mlog("全局函数加载成功!");
$count = 0;//进行插件计数
foreach (glob('./plugins/*.php') as $file) {
    $count++;
}
mlog("找到{$count}个插件:");
mlog("正在连接WebSocket服务器");
run(function (){
    include './inc/config.php';
    $inc = new inc($config['apidomain'], $config['AppID'], $config['Token'], $config['AppSecret']);
    $client = $inc->connect_ws();
    if ($client->getStatusCode() == '-1' or $client->errCode == '114') {
        mlog("网络连接失败了呢,检查一下吧". json_decode($client->recv()->data, true)['d']['user']['username'],2);
    } else {
        $code = json_decode($client->recv()->data, true);
        if ($code['op'] == 0){
            mlog("连接WebSocket服务器成功,你的BOT_QQ是:". $code['d']['user']['username']);
            $inc->getAccessToken();
        }
        elseif ($code['op'] == 9){
            mlog("连接WebSocket服务器时参数有误",2);
        }
    }
    while ($client->getStatusCode() != '403') {
        $ws_data = $client->recv();
        if (empty($ws_data)) {
            mlog("与WebSocket服务器的连接中断,正在尝试重连",2);
            $client->close();
            $client = $inc->connect_ws();
            if ($client->getStatusCode() == '-1' or $client->errCode == '114') {
                mlog("网络连接失败了呢,检查一下吧". json_decode($client->recv()->data, true)['d']['user']['username'],2);
            } else {
                $code = json_decode($client->recv()->data, true);
                if ($code['op'] == 0){
                    mlog("连接WebSocket服务器成功,你的BOT_QQ是:". $code['d']['user']['username']);
                }
                elseif ($code['op'] == 9){
                    mlog("连接WebSocket服务器时参数有误",2);
                }
            }
            Swoole\Coroutine\System::sleep(5);
        } else {
            $op_data = json_decode($ws_data->data, true);
            if (isset($op_data['t'])) {
                switch ($op_data['t']) {
                    case 'DIRECT_MESSAGE_CREATE'://频道私信消息
                        break;
                    case 'GROUP_AT_MESSAGE_CREATE'://接收群聊消息
                        $op_data["message"] = trim($op_data['d']['content']);
                        $op_data["group_id"] = $op_data['d']['group_id'];
                        $inc->update_s($op_data["s"]);
                        mlog('['.$op_data['group_id'].']'.'收到群聊消息:'.$op_data['message']);
                        Coroutine::create(function () use ($client, $op_data,$inc) {
                            foreach (glob('./plugins/*.php') as $file) {
                                $inc->update_op_message($op_data,Swoole\Coroutine::getCid());
                                $file = explode('/', $file)['2'];
                                require './plugins/' . $file;
                            }
                    });
                        break;
                }
            }
        }
    }
});