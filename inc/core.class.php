<?php
use Swoole\Coroutine\Http\Client;
class inc{
    private $apidomain;
    private $AppID;
    private $token;
    private $op_data;
    private $ttl;
    private $client;
    private $s;
    private $op_message = [];

    public function __construct($apidomain,$AppID,$token){
        $this->apidomain = $apidomain;
        $this->AppID = $AppID;
        $this->token = $token;
    }

    public function connect_ws()
    {
        $client = new Client($this->apidomain, 443, true);
        $client->upgrade('/websocket');
        $sendjson = json_encode(
            [
                'op' => 2,
                'd' => [
                    'token' => 'Bot '.$this->AppID.'.'.$this->token,
                    'intents' => 33554432,
                    'shard' => [
                        0 => 0,
                        1 => 1,
                    ],
                    'properties' => []
                ]
            ]
        );
        $client->push($sendjson);
        while (true) {
            $response = $client->recv();
            $responseData = json_decode($response->data, true);
            if (isset($responseData['op']) && $responseData['op'] === 10) {
                $this->ttl = $responseData['d']['heartbeat_interval'];
                $this->startHeartbeat($client);
                return $client;
            } else {
                return $client;
            }
        }
    }
    
    public function startHeartbeat($client)
    {
    echo "[Debug]TTL set".$this->ttl."\n";
    $Timerid = Swoole\Timer::tick($this->ttl, function () use ($client) {
        if (empty($this->s)){
            $d = "null";
        }
        else{
            $d = $this->s;
        }
        $heartbeat = json_encode(
            [
                'op' => 1,
                'd' => $d
            ]
        );
        $client->push($heartbeat);
        echo "[".date('Y.n.j-H:i:s') ."]". "[Debug]SendHeartbeat {$d}\n";
    });
    echo "[Debug]TimerStart ID:{$Timerid}\n";
    }

    public function update_s($s,){
        $this->s = $s;
    }

    public function update_op_message($op_data,$uid){
        $this->op_message[$uid] = $op_data;
    }

    //发送群聊信息
    public function group_send_msg($group_id,$message){
    $op_data=$this->op_message[swoole\Coroutine::getuid()];
    $sendjson = json_encode(
            [
                'action' => 'send_group_msg',
                'params' =>
                    [
                        'group_id' => $group_id,
                        'message' =>  $message
                    ]
            ]
        );
    $this->op_data->push($sendjson);
    echo '[' . date('Y.n.j-H:i:s') . ']' . '[' . $group_id . ']'. '发送群聊消息：' . $message . PHP_EOL;
}
    //发送群聊回复信息
    public function group_send_reply($group_id,$message) {
    $op_data=$this->op_message[swoole\Coroutine::getuid()];
    $sendjson = json_encode([
        'action' => 'send_msg',
        'params' => [
            'group_id' => $group_id,
            "message" => [
            [
                'type' => 'text',
                'data' => [
                    'text' => $message
                ]
            ] ,
            [
                'type' => 'reply',
                'data' => array(
                    'id' => $op_data['message_id'])
            ] ]
        ]
    ]);
        $this->op_data->push($sendjson);
        echo '[' . date('Y.n.j-H:i:s') . ']' . '[' . $group_id . ']'. '发送群聊回复消息：' . $message . PHP_EOL;
}
    //发送私聊信息
    public function private_send_msg($user_id,$message){
    $op_data=$this->$this->op_message[swoole\Coroutine::getuid()];
    $sendjson = json_encode(
            [
                'action' => 'send_private_msg',
                'params' =>
                    [
                        'user_id' => $user_id,
                        'message' =>  $message
                    ]
            ]
        );
    $this->op_data->push($sendjson);
    echo '[' . date('Y.n.j-H:i:s') . ']' . '[' . $user_id . ']'. '发送私聊消息：' . $message . PHP_EOL;
}
    //发送私聊回复信息
    public function private_send_reply($user_id,$message) {
        $op_data=$this->$this->op_message[swoole\Coroutine::getuid()];
        $sendjson = json_encode([
            'action' => 'send_msg',
            'params' => [
                'user_id' => $user_id,
                "message" => [
                [
                    'type' => 'text',
                    'data' => [
                        'text' => $message
                    ]
                ] ,
                [
                    'type' => 'reply',
                    'data' => array(
                        'id' => $op_data['message_id'])
                ] ]
            ]
        ]);
            $this->op_data->push($sendjson);
            echo '[' . date('Y.n.j-H:i:s') . ']' . '[' . $user_id . ']'. '发送私聊回复消息：' . $message . PHP_EOL;
    }
}