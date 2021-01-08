<?php

function common_new_service_create($post_data,$curlogic,$logicSence,$extra=[]){
    unset($post_data['id']);// 防止有传
    if( false == $curlogic->run($post_data,$logicSence) ){
        return array("code"=>400,"msg"=>$curlogic->getError());
    }
    $save_data = $curlogic->getSaveData();
    $res =  $curlogic->data($save_data)->save();
    if($res){
        return ['code'=>200,'msg'=> '新增成功','id'=>$res];
    }else{
        return ['code'=>400,'msg'=> '新增失败'];
    }
}
function common_new_service_update($id,$post_data,$curlogic,$logicSence,$extra=[]){
    $post_data['id'] = $id;
    if( false == $curlogic->run($post_data,$logicSence) ){
        return array("code"=>400,"msg"=>$curlogic->getError());
    }
    $save_data = $curlogic->getSaveData();
    $res = $curlogic->update($save_data,['id'=>$id]);
    if($res){
        return ['code'=>200,'msg'=> '更新成功'];
    }else{
        return ['code'=>400,'msg'=> '更新失败'];
    }
}

function common_new_service_delete($id,$curlogic,$logicSence,$extra=[]){
    $post_data       = [];
    $post_data['id'] = $id;
    if( false == $curlogic->run($post_data,$logicSence) ){
        return array("code"=>400,"msg"=>$curlogic->getError());
    }
    if(isset($extra['is_delete_true']) && $extra['is_delete_true']){
        $res = $curlogic->destroy(['id'=>$id]);
    }else{
        $save_data = $curlogic->getSaveData();
        $res = $curlogic->update($save_data,['id'=>$id]);
    }
    if($res){
        return ['code'=>200,'msg'=> '删除成功'];
    }else{
        return ['code'=>400,'msg'=> '删除失败'];
    }
}
function is_email_format_pass($email){
    if(preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/",$email)){
        return true;
    }else{
        return false;
    }
}

function is_phone_format_pass($phone){
    if(preg_match("/^1[3|4|5|7|8]\d{9}$/",$phone)){
        return true;
    }else{
        return false;
    }
}
function is_notempty_pass($value){
    if($value==null || $value==""){
        return false;
    }
    return true;
}
function common_filter_strs($str,$strlenMax=50){
    if(strlen($str)>$strlenMax){ return "";}
    $str     = str_replace(" ","",$str);
    return $str;
}
function common_input(&$params=[],$input_field='',$default_value='',$filter_method='trim',$args=[]){
    if(isset($params[$input_field])){
        array_unshift($args, $params[$input_field]);
        return call_user_func_array($filter_method, $args);
    }else{
        $params[$input_field] = $default_value;
        return $default_value;
    }
}

function common_remove_xss($dirty_html='',$dd=[]){
    if($dirty_html == ''){
        return '';
    }
    // var_dump($dirty_html);
    $purifier = new HTMLPurifier(); 
    // 返回过滤后的数据
    $ddd =  $purifier->purify($dirty_html);
    return $ddd;
}

/**
 * redis中的一些数据不是实时的。比如粉丝数，
 * 有些数据需要触发更新的,如 座驾id,每次切换座驾后，需更新下这个缓存值
 *
 */
function common_update_redis_user_token($user_info,$token){
    \EasySwoole\RedisPool\Redis::invoke('redis', function (\EasySwoole\Redis\Redis $redis) use ($token,$user_info) {
        // if($user_info['id'] == 1){
        //     // 目前管理员不做 第一次登录的验证11
        // }else{
            // 获取上次的并清除
            $last_token = $redis->get('last_token_by_uid_'.$user_info['id']);
            $redis->del('User_token_'.$last_token); // 删除上一个的token
        // }
        // 设置新的
        $redis->set('user_info_'.$user_info['id'],json_encode($user_info),360000000);
        $redis->set('User_token_'.$token,json_encode($user_info),360000000);
        $redis->set('last_token_by_uid_'.$user_info['id'],$token);
    });
}
function common_update_redis_new_user_info($user_info,$token){
    \EasySwoole\RedisPool\Redis::invoke('redis', function (\EasySwoole\Redis\Redis $redis) use ($token,$user_info) {
        $last_token = $redis->get('last_token_by_uid_'.$user_info['id']);
        $redis->del('User_token_'.$last_token); // 删除上一个的token
        
        // 设置新的
        $redis->set('user_info_'.$user_info['id'],json_encode($user_info),360000000);
        $redis->set('User_token_'.$token,json_encode($user_info),360000000);
        $redis->set('last_token_by_uid_'.$user_info['id'],$token);
    });
}

function common_user_info_by_uid($uid) {
    $redis = \EasySwoole\RedisPool\Redis::defer('redis');
    $user  = $redis->get('user_info_'.$uid);
    if($user){
        return json_decode($user, true);
    }else{
        return \App\HttpController\User\Datamanager\UserDatamanager::getInfoForApp($uid,null,'single');
    }
}

// 离线事件
function common_offline_event_auto_save($appkey='',$from_uid=0,$to_uid=0,array $data=[]){
    // 获取type
    $type     = (string)$data['type'];
    // 系统的离线消息
    $offline_event = [
        'appkey'           => $appkey,
        'type'             => $type,
        'from_uid'         => $from_uid,
        'to_uid'           => $to_uid,
        'create_time'      => time(),
        'is_offline_msg'   => 1,
        'data'             => json_encode($data)
    ];
    //插入离线消息
    \App\HttpController\Sys\Model\OfflineEventModel::create($offline_event)->save();
}

function common_offline_push_message_auto_save($appkey='',$from_uid=0,$to_uid=0,array $data=[]){
    $create_microtime = $data['create_microtime'];
    $offline_message = [
        'appkey'           => $appkey,
        'message_type'     => 'push_message',
        'from_uid'         => $from_uid,
        'to_uid'           => $to_uid,
        'original_content' => '',
        'content'          => '',
        'create_microtime' => $create_microtime,
        'data'             => json_encode($data),
        'is_offline_msg'   => 1,
        'flag_id'          => $appkey.'_'.$from_uid.'_'.$to_uid.'_'.$create_microtime
    ];
    //插入离线消息
    \App\HttpController\User\Model\UserMessageModel::create($offline_message)->save();
}

function common_db_start($cur_method_name="",$request){
    if( null === $request->getAttribute('common_db_first_method') ){
        $request->withAttribute('common_db_first_method',$cur_method_name);
        $request->withAttribute('common_db_end_method',$cur_method_name);
        \EasySwoole\ORM\DbManager::getInstance()->startTransaction();
    }
}
function common_db_commit($cur_method_name="",$request){
    if( $cur_method_name == $request->getAttribute('common_db_end_method') ){
        \EasySwoole\ORM\DbManager::getInstance()->commit();
        $request->withAttribute('common_db_first_method',null); // 置空
    }
}
function common_db_rollback($cur_method_name="",$request){
    if( $cur_method_name == $request->getAttribute('common_db_end_method') ){
        \EasySwoole\ORM\DbManager::getInstance()->rollback();
        $request->withAttribute('common_db_first_method',null); // 置空
    }
}
function common_clear_setting_by_module_name($module_name='lottery'){
    $redis   = \EasySwoole\RedisPool\Redis::defer('redis');
    $redis->del($module_name.'_setting');
}

function common_get_setting_by_module_name($module_name='lottery'){
    $redis   = \EasySwoole\RedisPool\Redis::defer('redis');
    $setting = $redis->get($module_name.'_setting');
    if($setting == null){
        $builder = new \EasySwoole\Mysqli\QueryBuilder();
        $builder->where('s.setting_module',$module_name)
                ->get('pk_setting as s', null, 's.setting_key,s.setting_value');
        $data = \EasySwoole\ORM\DbManager::getInstance()->query($builder, true);
        $setting_data = $data->getResult();
        $setting = [];
        foreach ($setting_data as $key => $value){
            $setting[$value['setting_key']] = $value['setting_value'];
        }
        $redis->set($module_name.'_setting', json_encode($setting),3600);
        return $setting;
    }
    return json_decode($setting,true);
}

function common_caller_to_request($caller){
    $args = $caller->getArgs();
    if( isset($args['trans_request']) && $args['trans_request'] ){
        $request = $args['trans_request'];
    }else{
        $request = new \EasySwoole\Http\Request();
        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $user = $redis->get('User_token_'.$args['token']);
        $user = json_decode($user,true);
        $request->withAttribute('request_user',$user);
        $args['trans_request'] = $request;
        $caller->setArgs($args);
    }
    return $request;
}


