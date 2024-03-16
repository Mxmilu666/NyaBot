<?php
if ($op_data['message'] == '/status') {
    $Info = explode(' ', substr($op_data['message'], strpos($op_data['message'], "/status")));
    if (@$Info['1'] == NULL) {
        if ($Info['0'] == $op_data['message']) {
            $mess = "\nNyaBot v0.0.1-Dev Status\nPHP Version:".phpversion()."\nSwoole Version:".SWOOLE_VERSION;
            $inc->group_send_reply($op_data['group_id'],$mess);
            
        }
    }
}