<?php
class TxOauth{
	protected static $errCode;
	protected static $errMsg;

	/**
	 *获取微信用户信息
	 *@param string $appid 公众号APPID
	 *@param string $secret 公众号appscret
	 *@param string $code 微信授权code
	 *@return string openid 用户openid
	 *		  string nickname 用户昵称
	 *		  int sex 1男2女0未知
	 *		  string province 省份
	 *		  string city 城市
	 *		  string country 国家
	 *		  string headimgurl用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像） 
	 */
	public static function getOpenid($appid, $secret, $code){
		//https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
		$url = sprintf('https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code',
			$appid, $secret, $code);
		$ret = self::httpGet($url);
		if($ret){
			$arr = json_decode($ret,1);
			if($arr['errcode']){
				self::$errCode = $arr['errcode'];
				self::$errMsg = $arr['errmsg'];
				CLog::debug('getOpenid fail errode(%d), errmsg(%s)',$arr['errcode'],$arr['errmsg']);
			}
			return $arr;
		}
		return false;
	}


	/**
     * GET 请求
     * @param string $url
     */
    private static function httpGet($url) {
        //return Http::socketGet( $url );
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
//          curl_setopt($oCurl,CURLOPT_SSLVERSION,CURL_SSLVERSION_TLSv1);
            curl_setopt($oCurl,CURLOPT_SSLVERSION,1);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

}