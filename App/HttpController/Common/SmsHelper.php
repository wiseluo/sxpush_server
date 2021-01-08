<?php
namespace App\HttpController\Common;

class SmsHelper
{
    /**
     * 短信发送
     *
     * @param string $uid 短信账号
     * @param string $pwd MD5接口密码
     * @param string $mobile 手机号码
     * @param string $content 短信内容
     * @param string $template 短信模板ID
     * @return array
     */
    public static function sendSMS($uid, $pwd, $mobile, $content, $template = '')
    {
		$data = array(
				'account' => 'N5155667',                        //用户账号N5155667
				'password' => 'dPTUChaG0idd9d',    //MD5位32密码,密码和用户名拼接字符dPTUChaG0idd9d
				'phone' => $mobile,            //号码
				'msg' => $content,                            //内容
				'report' => 'true',                                //接口返回信息格式 json\xml\txt
			);
        $result = self::curlPost('https://smssh1.253.com/msg/send/json',$data);                   //POST方式提交
        //$this->message = '发送成功';
       // $re = $this->json_to_array($result);                        //JSON数据转为数组
        return $result;
    }

    /**
     * POST方式HTTP请求
     *
     * @param string $url URL地址
     * @param array $data POST参数
     * @return string
     */
    public static function curlPost($url,$postFields){
			$postFields = json_encode($postFields);
			
			$ch = curl_init ();
			curl_setopt( $ch, CURLOPT_URL, $url ); 
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8'   //json版本需要填写  Content-Type: application/json;
				)
			);
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt( $ch, CURLOPT_TIMEOUT,60); 
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
			$ret = curl_exec ( $ch );
			if (false == $ret) {
				$result = curl_error(  $ch);
			} else {
				$rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
				if (200 != $rsp) {
					$result = "请求状态 ". $rsp . " " . curl_error($ch);
				} else {
					$result = $ret;
				}
			}
			curl_close ( $ch );
			return $result;
	}

    //url转码
    public static function json_urlencode($p)
    {
        if (is_array($p)) {
            foreach ($p as $key => $value) $p[$key] = self::json_urlencode($value);
        } else {
            $p = urlencode($p);
        }
        return $p;
    }
}



