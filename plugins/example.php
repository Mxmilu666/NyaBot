<?php
if ($op_data['message'] === '/test') {
    if ($op_data['message_type'] == "group"){
        $Info = explode(' ', substr($op_data['message'], strpos($op_data['message'], "/test")));
        if (@$Info['1'] == NULL) {
            if ($Info['0'] == $op_data['message']) {
                $inc->group_send_reply($op_data['group_id'],"你好呀");
                $inc->group_send_msg($op_data['group_id'],"你好呀");
            }
        }
    }
    elseif ($op_data['message_type'] == "private"){
        $Info = explode(' ', substr($op_data['message'], strpos($op_data['message'], "/test")));
        if (@$Info['1'] == NULL) {
            if ($Info['0'] == $op_data['message']) {
                $inc->private_send_reply($op_data['user_id'],"你好呀");
                $inc->private_send_msg($op_data["user_id"],"你好呀");
            }
        }
    }
}