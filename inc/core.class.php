<?php
use Swoole\Coroutine\Http\Client;
use Swoole\Coroutine;
class inc{
    private $apidomain;
    private $AppID;
    private $token;
    private $AppSecret;
    private $accesstoken;
    private $op_data;
    private $wsttl;
    private $tokenttl;
    private $client;
    private $s;
    private $op_message = [];

    public function __construct($apidomain,$AppID,$token,$AppSecret){
        $this->apidomain = $apidomain;
        $this->AppID = $AppID;
        $this->token = $token;
        $this->AppSecret = $AppSecret;
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
                $this->wsttl = $responseData['d']['heartbeat_interval'];
                $this->startHeartbeat($client);
                return $client;
            } else {
                return $client;
            }
        }
    }
    
    public function getAccessToken()
    {
        $client = new Client('bots.qq.com',443,true);
        $client->setHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
        $sendjson = json_encode(
            [
                'appId' => $this->AppID,
                'clientSecret' => $this->AppSecret
            ]
        );
        $client->post('/app/getAppAccessToken',$sendjson);
        $client->close();
        $this->accesstoken = json_decode($client->body, true)['access_token'];
        mlog ("GetNewAccesstoken:".$this->accesstoken,1);
        $this->tokenttl = json_decode($client->body, true)['expires_in'];
        $this->startAccessTokenTimer();
    }

    public function startAccessTokenTimer()
    {
    mlog ("TokenTTL set ".$this->tokenttl * 1000,1);
    $Timerid = Swoole\Timer::tick($this->tokenttl * 1000, function () {
        $client = new Client('bots.qq.com',443,true);
        $client->setHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
        $sendjson = json_encode(
            [
                'appId' => $this->AppID,
                'clientSecret' => $this->AppSecret
            ]
        );
        $client->post('/app/getAppAccessToken',$sendjson);
        $client->close();
        $this->accesstoken = json_decode($client->body, true)['access_token'];
        $this->tokenttl = json_decode($client->body, true)['expires_in'];
        mlog("GetNewAccesstoken {$this->accesstoken}",1);
    });
    mlog("AccessTokenTimerStart ID:{$Timerid}",1);
    }

    public function startHeartbeat($client)
    {
    mlog ("WsTTL set ".$this->wsttl,1);
    $Timerid = Swoole\Timer::tick($this->wsttl, function () use ($client) {
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
        mlog("SendHeartbeat {$d}",1);
    });
    mlog("HeartbeatTimerStart ID:{$Timerid}",1);
    }

    public function update_s($s,){
        $this->s = $s;
    }

    public function update_op_message($op_data,$uid){
        $this->op_message[$uid] = $op_data;
    }

    //发送群聊回复信息
    public function group_send_reply($group_id,$message) {
    $op_data=$this->op_message[swoole\Coroutine::getuid()];
    $client = new Client($this->apidomain,443,true);
    $client->setHeaders([
        'Authorization' => "QQBot {$this->accesstoken}",
        'X-Union-Appid' => $this->AppID,
        'Content-Type' => 'application/json; charset=utf-8',
    ]);
    $sendjson = json_encode(
        [
            'content' => $message,
            'msg_type' => 0,
            'msg_id' => $op_data['d']['id'],
            'msg_seq' => 1
        ]
    );
    $client->post("/v2/groups/{$group_id}/messages",$sendjson);
    $client->close();
    mlog('['.$op_data['group_id'].']'.'发送群聊被动消息:'.$message);
}
}