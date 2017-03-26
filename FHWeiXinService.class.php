<?php
/**
 * @author yanghuichao
 * @date 2015/03/22
 * @brief 飞华微信支付服务类
 *
 **/
class FHWeiXinService  {
    const ENV = 'dev';

	//微信公众号信息
	const TEST_TOKEN = '8eae41f2c3b2a2476cba159c1606f85f';
    const TEST_APPID = 'wx8f2b8539d6c228ca';
    const TEST_APPSECRET  = '6cc3682225fd0594ae4b960bafba9604';

    const FH_WEIXIN_TOKEN = 'KfUqOpRDLOjvVZmtnRZWjEaj1mXCqq48';
    const FH_WEIXIN_APPID = 'wx823c7c2d12f8ecb5';
    const FH_WEIXIN_APPSECRET  = 'dda76e45545917897a56ddb86c8e85a2';
    //const FH_WEIXIN_APP_KEY    = 'tjNjSJMKgHT05n4JYZktoueIBwBM5CPsV0HaIUPskGGE8gujdOjFTE1qC13y62zbqrmTjkN2Z3hwhoCbVQfUBWK6HUxFBo7BnMhW0qRcPxpP7QGMMtqoGzgDGOyr6yTP';//PaySignKey
    const FH_WEIXIN_PARTER_KEY = 't9E0dNI06MxF8CRX6BqjJ4OQEwx7zcv8';
    const FH_WEIXIN_PARTER_ID  = '1239567802';

    private static $weixin;

    public static $templatesConf = array(
        CommonConst::MODULE_PASSPORT_ID => array(
            'ShareArticle'  => '1k05NEayM8ZIXuVS7y_nuXFQl9gIu8XwbEqJyFyqSaQ',//医生向患者分享文章
            'SystemNotice'  => 'DbFrp2AhEPfZX1KrQnUrbhFpYV8GJJGUB4y746CXBGE',//系统提醒
            'DoctorAdvice'  => '1k05NEayM8ZIXuVS7y_nuXFQl9gIu8XwbEqJyFyqSaQ',//医嘱通知     
            'ServiceComment'=> 'ZNemMhxBNi-I_9xBi9r4umQka48O9zU9SzUrNXp7XKw',//服务评价 
        ),
    	CommonConst::MODULE_ONLINE_ID => array(//电话咨询模板
    		'PaySuccess'	=> 'L4BYpuOEJTYx0s2U8OKbbFVSoPdhsY6KJ-NbRttI7qs',//咨询订单支付成功通知
    		'CancelSuccess' => '3FwneLupaISk5okVV5DugijmquElICFuQPg_oXQq5ME',//咨询订单取消通知
    		'BookingSuccess'=> 'lsVmnCyf4yPjzmPW7_uoVkdZnb0KS18Hoc09TAqdaD0',//预约通话成功通知
    		'Transfer'		=> 'd_5sn_9Q0-RdRolBVwLiAAWHlKbIeWJZcfx5-X6lDqk',//咨询转单成功通知
    		'Finish'		=> 'j77M8Fhfe6mcMMXxPewPH01-B2FE7ZeZZWduRhIwetA',//咨询订单完成通知
    		'Change'		=> 'UrsUbs0zXTkxPdHtiFXFxy3LM86-dZUYWmvbmB6JVso',//预约变更通知
    	),
    	CommonConst::MODULE_CHAT_ID => array(//图文咨询模板
			'PaySuccess'	=> 'L4BYpuOEJTYx0s2U8OKbbFVSoPdhsY6KJ-NbRttI7qs',//咨询订单支付成功通知
			'UnReply'		=> '12vg_pP16WLdae6JPXb-9ym60eRjF8x-isBggb6ZvX4',//咨询订单未回复提醒
    		'CancelSuccess' => '3FwneLupaISk5okVV5DugijmquElICFuQPg_oXQq5ME',//咨询订单取消通知
    		'Replay'		=> 'y0W23BQVq9G4rBFfRwkAELzqD9x6u3nCEBrkFaSeg7A',//咨询回复提醒
    		'Transfer'		=> 'd_5sn_9Q0-RdRolBVwLiAAWHlKbIeWJZcfx5-X6lDqk',//咨询转单成功通知
            'Finish'        => 'j77M8Fhfe6mcMMXxPewPH01-B2FE7ZeZZWduRhIwetA',//咨询订单完成通知
    	),
        CommonConst::MODULE_BESPEAK_ID => array(//门诊预约模板
            'BespeakPay'    => '1OmaZ3nRH9Vfs1SRkcHdqgTy0SFV_mLVzmn5P7wi-NY',//订单支付成功通知
            'BespeakCancel' => 'srUASwQIrskBeBYDf8YcwGHGhNnbPgiCA_ko0pgd70I',//订单取消通知
            'BespeakSuccess'=> 'jkd3wyfHxanpqxh5swuirmf3NDDrLo9l7LYamw0ozkE',//预约成功通知
            'BespeakChange' => 'sAwyYmFFNgbg38YyCPPlnan4qPUuYH7phJ6R_wdaEpc',//门诊预约改约通知
            'BespeakFinish' => 'HWg0_xwGLQY9pHexEUWE8mA1IZiOcQL1P42BIUwNJ5c',//门诊预约订单完成通知
        ),

    	'test'				=> array(//图文咨询模板
			'PaySuccess'	=> 'y0VZ6BOd4M8E5GMT63sUepmaopzcP2UK8uhhA_Tc5F4',//咨询订单支付成功通知
    		'Replay'		=> 'bz6K4jH7dkG9vfChtnW7xWtYqBdrLwhfOdi4rutKsrQ',//咨询回复提醒
    		'UnReply'		=> 'y82rtIwkcAtbX0B9ee7gFdzIVSygNtESY2LlgoAcwQc',//咨询未回复提醒
    		'CancelSuccess' => 'edfHpKKG-Y2fdpthNwgl4H5LM3V1-O7jYoJnt96WPQg',//咨询订单取消通知
    		'BookingSuccess'=> 't7sPMcI3Z7AHNTK2htzf7bCy40Oc8YBpGDNXe4ETa38',//预约通话成功通知
    		'Finish'		=> 'omVHd3tCaKx3Y6TzH1gAJhLfmA3CU8JvT-EWnwDkHFM',//咨询订单完成通知
    		'Transfer'		=> 'rugya8NhcWv2n0zo9ja01CQ-HDfTAUhvc6GH4-Uoun8',//咨询转单成功通知
    		'Change'		=> 'QexQgzIbakOXpN9Iazte2O5tKQOD8_PTkgNQI-CGryU',//通话时间(预约)变更通知
            'ShareArticle'  => 'reCd5m6eJlH9gaBT32-yhyJ4pOisTEkFWJqaKtUJq4c',//医生向患者分享文章
            'BespeakPay'    => 'zXRB0IhNe-v4oki1SIGQTkvWiS6vUDKTvIJv7fmicQw',//订单支付成功通知
            'BespeakCancel' => 'srUASwQIrskBeBYDf8YcwGHGhNnbPgiCA_ko0pgd70I',//订单取消通知
            'BespeakSuccess'=> 'jkd3wyfHxanpqxh5swuirmf3NDDrLo9l7LYamw0ozkE',//预约成功通知
    	    'DoctorAdvice'  => 'reCd5m6eJlH9gaBT32-yhyJ4pOisTEkFWJqaKtUJq4c',
        ),
        'sandbox'              => array(//图文咨询模板
            'PaySuccess'    => 'Xu2TgpdIg7ZN8DRG_Qk_FGLPrKQ5zVfrPMymojrh6Z4',//咨询订单支付成功通知ooo
            'Replay'        => 'YG5C8xNBWYfQgkW6YQvgURDtjm7SD_aalYuxcnrv9ew',//咨询回复提醒ooo
            'UnReply'       => 'uxH2mQQZvmjvB-0yOcpKmBjnY3pR0u5sS5jpgcUyEvk',//咨询未回复提醒ooo
            'CancelSuccess' => 'WDU47edwifznmYrqPYAi1cOO1qDdORT0-7vklD9C3ec',//咨询订单取消通知ooo
            'BookingSuccess'=> 'rfKvYb6ovBZhy9J8cLEujSRJfdwzknW1gjpfLeJrJvs',//预约通话成功通知ooo
            'Finish'        => 'YO0UvzPPLeLMjGQVs8QZY28BfBwKzrPxlzVy_-bb5mM',//咨询订单完成通知ooo
            'Transfer'      => '9JqfJPHgIVZNfkxZCIZRs0JbiISz8QQfdLnvLXPD8Ek',//咨询转单成功通知ooo
            'Change'        => '-Z7xvCxytfqwrcm2fjM6CSwKv-CEU2JZlgG4dAPpa-E',//通话时间(预约)变更通知ooo
            'ShareArticle'  => 'n3IKBq5zysRzQOOnT_dzfKVQGt4k-e4GWOnFIJWQXJc',//医生向患者分享文章ooo
            'BespeakPay'    => '1OmaZ3nRH9Vfs1SRkcHdqgTy0SFV_mLVzmn5P7wi-NY',//订单支付成功通知
            'BespeakCancel' => 'srUASwQIrskBeBYDf8YcwGHGhNnbPgiCA_ko0pgd70I',//订单取消通知
            'BespeakSuccess'=> 'jkd3wyfHxanpqxh5swuirmf3NDDrLo9l7LYamw0ozkE',//预约成功通知
        ),

    );

    public static $templatesParam = array(
        'ZNemMhxBNi-I_9xBi9r4umQka48O9zU9SzUrNXp7XKw' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'您的xxx订单已完成，如对医生服务满意，请予评价！', 'color'=>'#0066ff','key'=>'first'),
                'patient_info'  =>array('default'=>'您的门诊预约订单已完成，如对医生服务满意，请予评价！','key'=>'keyword1'),
                'doctor_info'   =>array('default'=>'北京协和医院 马东来  主任医师','key'=>'keyword2'),
                'remark'        =>array('default'=>'现在就去评价','color'=>'#333333','key'=>'remark'),
            ),
        ),
        'DbFrp2AhEPfZX1KrQnUrbhFpYV8GJJGUB4y746CXBGE' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'如对医患帮的服务满意，请推荐给您的朋友。', 'color'=>'#0066ff','key'=>'first'),
                'op_type'       =>array('default'=>'分享医患帮','key'=>'keyword1'),
                'op_time'       =>array('default'=>'2015年7月21日 15:21','key'=>'keyword2'),
                'remark'        =>array('default'=>'现在就去分享','color'=>'#333333','key'=>'remark'),
            ),
        ),
        'HWg0_xwGLQY9pHexEUWE8mA1IZiOcQL1P42BIUwNJ5c' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'您的门诊预约订单已完成，如对医生服务满意，请予评价！', 'color'=>'#0066ff','key'=>'first'),
                'patient_name'  =>array('default'=>'张三 男 8岁','key'=>'keyword1'),
                'visit_time'    =>array('default'=>'2015年7月21日（周二）上午','key'=>'keyword2'),
                'hospital_name' =>array('default'=>'北京协和医院','color'=>'#333333','key'=>'keyword3'),
                'doctor_name'   =>array('default'=>'马东来','color'=>'#333333','key'=>'keyword4'),
                'remark'        =>array('default'=>'现在就去评价','color'=>'#333333','key'=>'remark'),
            ),
        ),
        'sAwyYmFFNgbg38YyCPPlnan4qPUuYH7phJ6R_wdaEpc' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'改约描述', 'color'=>'#0066ff','key'=>'first'),
                'hospital_name' =>array('default'=>'北京协和医院','color'=>'#333333','key'=>'keyword1'),
                'doctor_name'   =>array('default'=>'马东来','color'=>'#333333','key'=>'keyword2'),
                'content'       =>array('default'=>'改约内容','key'=>'keyword3'),
                'remark'        =>array('default'=>' ','color'=>'#333333','key'=>'remark'),
            ),
        ),
        'jkd3wyfHxanpqxh5swuirmf3NDDrLo9l7LYamw0ozkE' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'您的门诊预约已成功，请准时去医院就诊', 'color'=>'#0066ff','key'=>'first'),
                'patient_name'  =>array('default'=>'就诊人','color'=>'#333333','key'=>'keyword1'),
                'card_no'       =>array('default'=>'无','color'=>'#333333','key'=>'keyword2'),
                'department'    =>array('default'=>'科室','color'=>'#333333','key'=>'keyword3'),
                'doctor_name'   =>array('default'=>'XX医院 XX医生','color'=>'#333333','key'=>'keyword4'),
                'visit_time'    =>array('default'=>'就诊时间','color'=>'#ff0000','key'=>'keyword5'),
                'remark'        =>array('default'=>'\n点击详情可查看订单','color'=>'#333333','key'=>'remark'),
            ),
        ),
        '1OmaZ3nRH9Vfs1SRkcHdqgTy0SFV_mLVzmn5P7wi-NY' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'您的门诊预约已提交申请，请耐心等待', 'color'=>'#0066ff','key'=>'first'),
                'patient_name'  =>array('default'=>'就诊人','color'=>'#333333','key'=>'keyword1'),
                'hospital_name' =>array('default'=>'医院','color'=>'#333333','key'=>'keyword2'),
                'department'    =>array('default'=>'科室','color'=>'#333333','key'=>'keyword3'),
                'doctor_name'   =>array('default'=>'医生','color'=>'#333333','key'=>'keyword4'),
                'remark'        =>array('default'=>'\n点击详情可查看订单','color'=>'#333333','key'=>'remark'),
            ),
        ),
        'srUASwQIrskBeBYDf8YcwGHGhNnbPgiCA_ko0pgd70I' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'您好，由于医生已停诊，您的门诊预约已取消，请重新预约！', 'color'=>'#0066ff','key'=>'first'),
                'patient_name'  =>array('default'=>'就诊人','color'=>'#333333','key'=>'keynote1'),
                'visit_time'    =>array('default'=>'就诊时间','color'=>'#333333','key'=>'keynote2'),
                'doctor_name'   =>array('default'=>'医生','color'=>'#333333','key'=>'keynote3'),
                'hospital_name' =>array('default'=>'医院','color'=>'#333333','key'=>'keynote4'),
                'remark'        =>array('default'=>'\n点击详情可查看订单','color'=>'#333333','key'=>'remark'),
            ),
        ),
        '1k05NEayM8ZIXuVS7y_nuXFQl9gIu8XwbEqJyFyqSaQ' => array(
            'topcolor'      => '',
            'bodycolor'     => '',
            'footercolor'   => '',
            'fields'        => array(
                'description'   =>array('default'=>'您的医生给您分享了一篇文章。\n《针对糖尿病患者的日常注意事项》', 'color'=>'#0066ff','key'=>'first'),
                'doctor_name'   =>array('default'=>'医生','color'=>'#333333','key'=>'keyword1'),
                'doctor_post'   =>array('default'=>'职称','color'=>'#333333','key'=>'keyword2'),
                'hospital_name' =>array('default'=>'医院','color'=>'#333333','key'=>'keyword3'),
                'department'    =>array('default'=>'科室','color'=>'#333333','key'=>'keyword4'),
                'remark'        =>array('default'=>'\n点击详情可查看医生都说了什么！','color'=>'#333333','key'=>'remark'),
            ),
        ),
		'L4BYpuOEJTYx0s2U8OKbbFVSoPdhsY6KJ-NbRttI7qs' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'咨询订单支付成功', 'color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'fee'			=>array('default'=>'咨询费','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'y0W23BQVq9G4rBFfRwkAELzqD9x6u3nCEBrkFaSeg7A' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'咨询回复提醒','color'=>'#0066ff','key'=>'first'),
				'question'		=>array('default'=>'咨询内容','color'=>'#333333','key'=>'keyword1'),
				'answer'		=>array('default'=>'回复内容','color'=>'#333333','key'=>'keyword2'),
				'doctor_name'	=>array('default'=>'医生名','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'12vg_pP16WLdae6JPXb-9ym60eRjF8x-isBggb6ZvX4' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'咨询订单未回复提醒','color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'ctime'			=>array('default'=>'下单时间','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'3FwneLupaISk5okVV5DugijmquElICFuQPg_oXQq5ME' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'咨询订单取消通知','color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'fee'			=>array('default'=>'咨询费','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'\n查看余额','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'lsVmnCyf4yPjzmPW7_uoVkdZnb0KS18Hoc09TAqdaD0' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'预约通话成功通知','color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'time'			=>array('default'=>'通话时间','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'j77M8Fhfe6mcMMXxPewPH01-B2FE7ZeZZWduRhIwetA' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'咨询订单完成通知','color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'fee'			=>array('default'=>'咨询费','color'=>'#333333','key'=>'keyword3'),
                'remark'		=>array('default'=>'\n去评价','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'd_5sn_9Q0-RdRolBVwLiAAWHlKbIeWJZcfx5-X6lDqk' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'咨询转单成功通知','color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'fee'			=>array('default'=>'咨询费','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'','color'=>'#333333','key'=>'remark'),
    		),
    	),
    	'UrsUbs0zXTkxPdHtiFXFxy3LM86-dZUYWmvbmB6JVso' => array(
    		'topcolor'		=> '',
    		'bodycolor'		=> '',
    		'footercolor'	=> '',
    		'fields' 		=> array(
    			'description'	=>array('default'=>'预约变更通知','color'=>'#0066ff','key'=>'first'),
				'hospital_name'	=>array('default'=>'咨询医院','color'=>'#333333','key'=>'keyword1'),
				'doctor_name'	=>array('default'=>'咨询医生','color'=>'#333333','key'=>'keyword2'),
				'change_content'=>array('default'=>'变更内容','color'=>'#333333','key'=>'keyword3'),
				'remark'		=>array('default'=>'','color'=>'#333333','key'=>'remark'),
    		),
    	),

    );
	private function __construct() {}
    
    //防止克隆
	private function __clone() {}


 	public static function getWeixinSdk(){
		CLog::debug('get weixin sdk');
		if(!self::$weixin){
			self::$weixin = new TXweixinSapi(
				self::getToken(),
				self::getAppid(), 
				self::getAppsecret()
			);
		}
		return self::$weixin;
	}

	public static function sendText($openid, $msg){
		self::getWeixinSdk()->sendText($msg, $openid);//exit;
	}

	public static function getToken($test = true){
		if(self::ENV == 'dev'){
			return self::TEST_TOKEN;
		}
		return self::FH_WEIXIN_TOKEN;
	}

	public static function getAppid($test = true){
		if(self::ENV == 'dev'){
			return self::TEST_APPID;
		}
		return self::FH_WEIXIN_APPID;
	}

	public static function getAppsecret($test = true){
		if(self::ENV == 'dev'){
	       return self::TEST_APPSECRET;
		}
		return self::FH_WEIXIN_APPSECRET;
	}

	public static function getAppkey(){
		return self::FH_WEIXIN_APP_KEY;
	}

	public static function getParterkey(){
		return self::FH_WEIXIN_PARTER_KEY;
	}

	public static function getParterid(){
		return self::FH_WEIXIN_PARTER_ID;
	}

	/**
	 *更新菜单
	 */
	public static function updateMenu(){
        $wxOauthUrl = sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base',self::getAppid(),CommonConst::HOST_WECHAT . '/wx/auth');
    	$fhwzs = $wxOauthUrl . '&state=go_fhwzs#wechat_redirect';
    	$home = $wxOauthUrl . '&state=go_home#wechat_redirect';
        $mydoctors = $wxOauthUrl . '&state=go_mydoctors#wechat_redirect';
        /*
        $yyk = $wxOauthUrl . '&state=go_yyk#wechat_redirect';
        $zjk = $wxOauthUrl . '&state=go_zjk#wechat_redirect';
        $zzk = $wxOauthUrl . '&state=go_zzk#wechat_redirect';
        $jbk = $wxOauthUrl . '&state=go_jbk#wechat_redirect';
        $ypk = $wxOauthUrl . '&state=go_ypk#wechat_redirect';*/
    	/*
    	$menu = array(
    	        'button'=>array(
    	                array('name'=>'飞华诊室','type'=>'view','url'=>$fhwzs),
    	                array(
    	                    'name'=>'看病助手','sub_button'=>array(
    	                    		array('type'=>'view','name'=>'查医院','url'=>sprintf('%s?appid=%s&openid=%s&token=%s',CommonConst::HOST_HOSPITAL_WAP, $appid, $openid, $token)),
    	                    		array('type'=>'view','name'=>'查专家','url'=>sprintf('%s?appid=%s&openid=%s&token=%s',CommonConst::HOST_EXPERT_WAP, $appid, $openid, $token)),
    	                    		array('type'=>'view','name'=>'查症状','url'=>sprintf('%s?appid=%s&openid=%s&token=%s',CommonConst::HOST_SYMPTOM_WAP, $appid, $openid, $token)),
    	                    		array('type'=>'view','name'=>'查疾病','url'=>sprintf('%s?appid=%s&openid=%s&token=%s',CommonConst::HOST_DISEASE_WAP, $appid, $openid, $token)),
    	                    		array('type'=>'view','name'=>'查药品','url'=>sprintf('%s?appid=%s&openid=%s&token=%s',CommonConst::HOST_MEDICINAL_WAP, $appid, $openid, $token)),
    	                    	)														
    	                ),
    	                array('name'=>'我','type'=>'view','url'=>$home),
    	            )
    	    );*/
        /*
        $menu = array(
                'button'=>array(
                        array('name'=>'飞华诊室','type'=>'view','url'=>$fhwzs),
                        array(
                            'name'=>'看病助手','sub_button'=>array(
                                    array('type'=>'view','name'=>'查医院','url'=>$yyk),
                                    array('type'=>'view','name'=>'查专家','url'=>$zjk),
                                    array('type'=>'view','name'=>'查症状','url'=>$zzk),
                                    array('type'=>'view','name'=>'查疾病','url'=>$jbk),
                                    array('type'=>'view','name'=>'查药品','url'=>$ypk),
                                )                                                       
                        ),
                        array('name'=>'我','type'=>'view','url'=>$home),
                    )
            );*/
         $menu = array(
                'button'=>array(
                        array('name'=>'我的医生','type'=>'view','url'=>$mydoctors),
                        array('name'=>'患者服务','type'=>'view','url'=>$fhwzs),
                        array('name'=>'个人中心','type'=>'view','url'=>$home),
                    )
            );
		return self::getWeixinSdk()->setMenu($menu);
	}


	/**
	 *发送微信模板消息
	 *@param int $module 模块id
	 *@param string $openid 微信openid
	 *@param string $templateType 模板类型
	 *@param array $fields 模板参数
	 *@param string $url 模板链接，链接参数需要带上openid和appid=weixin和token
	 *@return int  错误码，0发送成功
	 *@example 
	 *$fields = array('description'=>'您的订单已支付成功','hospital_name'=>'北京协和医院','doctor_name'=>'马东来');
	 *FHWeiXinService::sendTemplate(CommonConst::MODULE_CHAT_ID,'oT4cDsyBfhq7M0DQRyuizmSBZDgE','test',$fields);
	 *
	 
		$paysuccess = array('description'=>'您的订单已支付成功','hospital_name'=>'北京协和医院','doctor_name'=>'马东来');
		$replay = array(
			'description'=>'您的咨询，有了新的回复',
			'question'=>'肚子痛怎么办',
			'answer'	=> '吃药了吗',
			'doctor_name'=>'马东来'
			);

	$unreplay = array('description'	=>'咨询订单未回复提醒','hospital_name'	=>'北京协和医院','doctor_name'	=>'马东来','ctime'			=>date('Y年m月d日 H:i'),);
	$CancelSuccess = array('description'=>'咨询订单取消通知','hospital_name'=>'北京协和医院','doctor_name'=>'马东来','fee'=>'20元');
	$BookingSuccess = array('description'=>'预约通话成功通知','hospital_name'=>'北京协和医院','doctor_name'=>'马东来','time'=>date('Y年m月d日（周四）H:i'),);
	$Finish = array('description'=>'咨询订单取消通知','hospital_name'=>'北京协和医院','doctor_name'=>'马东来','fee'=>'20元');
	$Transfer = array('description'=>'咨询转单成功通知','hospital_name'=>'北京协和医院','doctor_name'=>'马东来','fee'=>'20元');
	$Change = array('description'=>'预约变更通知','hospital_name'=>'北京协和医院','doctor_name'=>'马东来','fee'=>'20元');
	
	var_dump(FHWeiXinService::sendTemplate(CommonConst::MODULE_CHAT_ID,'oT4cDsyBfhq7M0DQRyuizmSBZDgE','Change',$Change));exit;
	
	
	var_dump(FHWeiXinService::sendTemplate(CommonConst::MODULE_CHAT_ID,'oT4cDsyBfhq7M0DQRyuizmSBZDgE','Transfer',$Transfer));exit;
	
	var_dump(FHWeiXinService::sendTemplate(CommonConst::MODULE_CHAT_ID,'oT4cDsyBfhq7M0DQRyuizmSBZDgE','BookingSuccess',$BookingSuccess));exit;
	
	 */
	public static function sendTemplate($module,$openid,$templateType,$fields = array(),$url=''){//return true;
		//$module = 'test';//测试环境
		CLog::debug('send weixin template input param module(%d),openid(%s),templateType(%s),fields(%s),url(%s)',
			$module,$openid,$templateType,var_export($fields,1),$url);
		if(!self::$templatesConf[$module]){
			$msg = sprintf(CommonConst::COMMON_PARAM_ERROR . ' the module(%s) has no templates', $module);
			throw new Exception($msg);
		}
		if(!self::$templatesConf[$module][$templateType]){
			$msg = sprintf(CommonConst::COMMON_PARAM_ERROR . ' the module(%s) has no template(%s)', $module,$templateType);
			throw new Exception($msg);
		}
		if(!$openid){
			$msg = sprintf(CommonConst::COMMON_PARAM_ERROR . ' to input not valid param openid(%s)', $module);
			throw new Exception($msg);
		}
		if(!is_array($fields)){
			$msg = sprintf(CommonConst::COMMON_PARAM_ERROR . ' to input not valid param fields(%s)', $fields);
			throw new Exception($msg);
		}
		$templateId = self::$templatesConf[$module][$templateType];
		$templateConf = self::$templatesParam[$templateId];
        $templateId = self::ENV == 'dev' ? self::$templatesConf['test'][$templateType] : $templateId;
		$template = array(
			'touser'	=> $openid,
			'template_id' => $templateId,//$templateId,
			'topcolor' => $templateConf['topcolor'],
			'url' => $url,
			'data'	=> array(),
		);
        //unset($templateConf['fields']['remark']);
		foreach($templateConf['fields'] as $key => $_fields){
			$template['data'][$_fields['key']] = array(
				'value' => isset($fields[$key]) ? addcslashes($fields[$key],"\"\n") : $_fields['default'],
				'color'	=> $_fields['color'],
			);
		}
        //print_r($template);//exit;
//print_r($template);exit;
		$tryNum = 0;
		$errNo = 0;
		while($tryNum < 3){
			if(!self::getWeixinSdk()->sendTemplate($template)){
				CLog::warning('send weixin template fail');
				$errNo = self::getWeixinSdk()->errCode;
				$tryNum++;
                break;//不重发。。。
			}else{
				break;
			}
		}
		
		return $errNo;

	}


	/**
	 *获取微信js sdk的配置参数
	 */
	public static function getJsConfig(){
		$jsapiTicket = self::getWeixinSdk()->getJsApiTicket();

		// 注意 URL 一定要动态获取，不能 hardcode.
	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	    $timestamp = time();
	    $nonceStr = self::createNonceStr();

	    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
	    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

	    $signature = sha1($string);

	    $jsConfig = array(
	      "appId"     => self::getAppid(),
	      "nonceStr"  => $nonceStr,
	      "timestamp" => $timestamp,
	      "url"       => $url,
	      "signature" => $signature,
	      "rawString" => $string
	    );
	    return $jsConfig; 
	}


	/**
	 *生成微信js支付参数
	 *@param array $goodsInfo商品信息，格式如下
		'description'	商品描述，必填
		'order_num'		商品订单号，必填
		'fee'			商品总金额，单位：分，必填
		'client_ip'		客户端ip地址，必填
		'notify_url'	微信支付结果通知URL，绝对路径，必填
	 */
	public static function creatJsPayPackage($goodsInfo){
		CLog::debug('creatJsPayPackage input param(%s)',var_export($goodsInfo, 1));
		$requiredFields = array('openid'=>'openid','description'=>'body','notify_url'=>'notify_url','order_num'=>'out_trade_no','client_ip'=>'spbill_create_ip','fee'=>'total_fee');
		$packegParam = array();
		foreach($requiredFields as $key => $field){
			if($goodsInfo[$key]){
				$packegParam[$field] = $goodsInfo[$key];
			}else{
				$msg = sprintf(CommonConst::COMMON_PARAM_ERROR . ' to input param %s[%s] not valid',$key,$goodsInfo[$key]);
				throw new Exception($msg);
			}
		}
		//$packegParam['openid'] = 'o38HhsiZxcB28pXNYLJPMWQo3k-4';
        $packegParam['trade_type'] = 'JSAPI';
        $packegParam["nonce_str"] = self::createNonceStr(32);
		//$packegParam['fee_type'] = '1';
		//$packegParam['input_charset'] = 'UTF-8';
		$packegParam['mch_id'] = self::getParterid();//print_r($packegParam);exit;
        $packegParam['appid'] = self::getAppid(false);
        $parterKey = self::getParterkey();
        $string1 = self::formatQueryParaMap($packegParam, 0);
        $sign = strtoupper(md5($string1 . '&key=' . $parterKey));
        $packegParam["sign"] = $sign;
		//echo 
/*
        $packegParam = array (
  'openid' => 'o38HhsrzGvkzaW_tYuM1PumF3qEY',
  'body' => '贡献一分钱',
  'spbill_create_ip' => '127.0.0.1',
  'out_trade_no' => 'wx823c7c2d12f8ecb51431072366',
  'total_fee' => '1',
  'notify_url' => 'http://www.xxxxxx.com/demo/notify_url.php',
  'trade_type' => 'JSAPI',
  'appid' => 'wx823c7c2d12f8ecb5',
  'mch_id' => '1239567802',
  'nonce_str' => 'i3caz0izxlmqssswfv00nt3www2qmm80',
  'sign' => '6B657D55562ED120C6A03DE93E90FE9E',
);*/
		$prePayId = self::getWeixinSdk()->getPrepayId($packegParam);

/*
        $jsApiObj["appId"] = WxPayConf_pub::APPID;
        $timeStamp = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $this->createNoncestr();
        $jsApiObj["package"] = "prepay_id=$this->prepay_id";
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $this->getSign($jsApiObj);
        $this->parameters = json_encode($jsApiObj);
*/		
		
	    $nativeObj["appId"] = self::getAppid(false);
	    $nativeObj["timeStamp"] = time();
        //$nativeObj["timeStamp"] = 11111111111;
	    $nativeObj["nonceStr"] = self::createNonceStr(32);
        //$nativeObj["nonceStr"] = 'asdfghjklasdfghjklasdfghjklasdfg';
        $nativeObj["package"] = 'prepay_id=' . $prePayId['prepay_id'];
        //$nativeObj["package"] = '123456789';
        $nativeObj["signType"] = 'MD5';
        $string2 = self::formatQueryParaMap($nativeObj, 0);
        //var_export($nativeObj);
        $paySign = strtoupper(md5($string2 . '&key=' . $parterKey));
        $nativeObj["paySign"] = $paySign;
        
        $nativeObj = array(
            'appid' => $nativeObj["appId"],
            'timestamp' => $nativeObj["timeStamp"],
            'noncestr' => $nativeObj["nonceStr"],
            'package' => $nativeObj["package"],
            'signtype' => $nativeObj["signType"],
            'paysign' => $nativeObj["paySign"],
        );
        //var_export($nativeObj);
	    return $nativeObj;
	}


    public static function creatJsPayPackage1($goodsInfo){
        CLog::debug('creatJsPayPackage input param(%s)',var_export($goodsInfo, 1));
        $requiredFields = array('description'=>'body','notify_url'=>'notify_url','order_num'=>'out_trade_no','client_ip'=>'spbill_create_ip','fee'=>'total_fee');
        $packegParam = array();
        foreach($requiredFields as $key => $field){
            if($goodsInfo[$key]){
                $packegParam[$field] = $goodsInfo[$key];
            }else{
                $msg = sprintf(CommonConst::COMMON_PARAM_ERROR . ' to input param %s[%s] not valid',$key,$goodsInfo[$key]);
                throw new Exception($msg);
            }
        }
        
        $packegParam['bank_type'] = 'WX';
        $packegParam['fee_type'] = '1';
        $packegParam['input_charset'] = 'UTF-8';
        $packegParam['partner'] = self::getParterid();//print_r($packegParam);exit;
        //echo 
        $string1 = self::formatQueryParaMap($packegParam, 0);
        $parterKey = self::getParterkey();
        $sign = strtoupper(md5($string1 . '&key=' . $parterKey));

        $string2 = self::formatQueryParaMap($packegParam, 1);
//echo $string2;exit;
        $package = $string2 . '&sign=' . $sign;
        $nativeObj["package"] = $package;
        $nativeObj["timestamp"] = time();
        $nativeObj["noncestr"] = self::createNonceStr();
        $paysignParam = array(
            'appid' => self::getAppid(false),
            'timestamp' => $nativeObj["timestamp"],
            'noncestr' => $nativeObj["noncestr"],
            'package' => $nativeObj["package"],
            'appkey' => self::getAppkey(),
        );
        
//var_export($paysignParam);exit;
        $paysign = sha1(self::formatQueryParaMap($paysignParam, false));//exit;
        
        $nativeObj["paysign"] = $sign;
        $nativeObj["signtype"] = 'sha1';
        $nativeObj["appid"] = self::getAppid(false);
//print_r($nativeObj);exit;
        return $nativeObj;

    }

    /**
     *  作用：array转xml
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">"; 

             }
             else
             {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
             }

        }
        $xml.="</xml>";//var_dump($xml);
        return $xml; 
    }

	/**
	 *验证微信支付通知的支付签名
	 	<xml><OpenId><![CDATA[ogVrBjja3sPsfqYQmY5VpqRtzqNc]]></OpenId>
		<AppId><![CDATA[wxe4e3f3d482c94292]]></AppId>
		<IsSubscribe>1</IsSubscribe>
		<TimeStamp>1426992040</TimeStamp>
		<NonceStr><![CDATA[OrNX9JXOge7pJQlC]]></NonceStr>
		<AppSignature><![CDATA[4cf4a3ac1232253f2ee2f190199575a6c09d8946]]></AppSignature>
		<SignMethod><![CDATA[sha1]]></SignMethod>
		</xml>
	 *@param array $xmlArrData 微信通知参数数组
	 *@param return boolean true合法，false非法伪造
	 */
	public static function checkPayNoticeSign($xmlArrData){
		CLog::debug('checkPayNoticeSign input param (%s)', var_export($xmlArrData, 1));
        if($xmlArrData['appid'] != self::getAppid(false)){
            return false;
        }
        $signParam = $xmlArrData;
        unset($signParam['sign']);
        $string = self::formatQueryParaMap($signParam, 0);//var_dump($string);
        //var_export($nativeObj);
        $realSign = strtoupper(md5($string . '&key=' . self::getParterkey()));
		$ret = $realSign == $xmlArrData['sign'];
        $xml = array();
		if($ret == FALSE){
            $xml['return_code'] = 'FAIL';
            $xml['return_msg'] = '签名失败';
        }else{
            $xml['return_code'] = 'SUCCESS';
        }
        $returnXml = self::arrayToXml($xml);
        CLog::debug('check wei xin pay notice sign sign(%s),realsign(%s), ret(%s)', $xmlArrData['sign'],$realSign,$returnXml);
        return $returnXml;
	}

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function data_to_xml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml .= "<$key>";
            $xml .= (is_array($val) || is_object($val)) ? self::data_to_xml($val) : self::xmlSafeStr($val);
            list($key,) = explode(' ', $key);
            $xml .= "</$key>";
        }
        return $xml;
    }

	/**
	 *按照数组字段ASCII码从小到大排序，把值非空的项拼成'key=val&ke=val...'返回
	 *@param array $paramMap 数组
	 *@param boolean $urlencode 是否进行urlencode转码
	 *@return string
	 */
    public static function formatQueryParaMap($paraMap, $urlencode = false){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v){
			if (null != $v && "null" != $v && "sign" != $k) {
			    if($urlencode){
				   $v = urlencode($v);
				}
				$buff .= $k . "=" . $v . "&";
			}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}


  	/**
  	 *生成16位随机字符串
  	 */
    public static function createNonceStr($length = 16) {
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $str = "";
	    for ($i = 0; $i < $length; $i++) {
	      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	    }
	    return $str;
	}


	/**
     *获取用户二维码URL，只从微信获取图片地址，不下载到本地服务器
     *@param string $sceneType 二维码类型
     *@param int $uid 用户id
     *@param return string 微信服务器二维码图片地址
     */
    public static function getUserQrcodeUrl($sceneType, $uid){
		CLog::debug('create user(%d) qrcode', $uid);
        $qrcode = self::getWeixinSdk()->createQrcode($sceneType . '_' . $uid);
    	if(!$qrcode['url']){
    		CLog::warning('create user qrcode fail, result(%s)', var_export($qrcode, 1));
    		return false;
    	}
    	$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $qrcode['ticket'];
        //$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qrcode['ticket']);
    	CLog::debug('request weixin create user(%d) qrcode result(%s)', $uid, var_export($qrcode, 1));
		return $url;
    }

    /**
     *获取用户临时二维码URL
     *@param int $sceneType 二维码类型2位从10开始
     *@param int $uid 用户id
     *@param boolean $download 下载本地服务器，临时二维码不建议下载
     *@param return string 微信服务器二维码图片地址
     */
    public static function getUserTempQrcodeUrl($sceneType=10, $uid, $download = false){
        CLog::debug('getUserTempQrcodeUrl user(%d) qrcode', $uid);
        return self::getTempQrcodeUrl($sceneType, $uid, $download);
    }

    /**
     *获取医患帮永久二维码URL
     *@param string $sourceid 来源id
     *@param boolean $download 下载本地服务器，临时二维码不建议下载
     *@param return string 微信服务器二维码图片地址
     */
    public static function getYHBQrcodeUrl($sourceid, $download = false){
        CLog::debug('getYHBQrcodeUrl sourceid(%s) qrcode', $sourceid);
        return self::getForeverQrcode('from', $sourceid, $download);
    }

    /**
     *获取医院永久二维码URL
     *@param int $hospitalid 医院id
     *@param boolean $download 下载本地服务器，临时二维码不建议下载
     *@param return string 微信服务器二维码图片地址
     */
    public static function getHospitalQrcodeUrl($hospitalid, $download = false){
        CLog::debug('getHospitalQrcodeUrl hospitalid(%d) qrcode', $hospitalid);
        return self::getForeverQrcode('hospital', $hospitalid, $download);
    }

    /**
     *获取科室永久二维码URL
     *@param int $level 科室级别 1,2
     *@param int $departmentid 医院科室id
     *@param boolean $download 下载本地服务器，临时二维码不建议下载
     *@param return string 微信服务器二维码图片地址
     */
    public static function getDepartmentQrcodeUrl($departmentid, $download = false){
        CLog::debug('getDepartmentQrcodeUrl  departmentid(%d) qrcode', $departmentid);
        return self::getForeverQrcode('department', $departmentid, $download);
    }

    /**
     *获取临时二维码URL
     *@param int $sceneType 二维码类型2位从10开始
     *@param int $id 唯一id
     *@param boolean $download 下载本地服务器，临时二维码不建议下载
     *@param return string 微信服务器二维码图片地址
     */
    public static function getTempQrcodeUrl($sceneType, $id, $download = false){
        CLog::debug('getTempQrcodeUrl type(%d) id(%s) download(%d)', $sceneType, $id, $download);
        $sceneid = $sceneType . $id;
        $qrcode = self::getWeixinSdk()->createTempQrcode( $sceneid );
        if(!$qrcode['url']){
            CLog::warning('getTempQrcodeUrl fail, result(%s)', var_export($qrcode, 1));
            return false;
        }
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $qrcode['ticket'];
        //$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qrcode['ticket']);
        CLog::debug('getTempQrcodeUrl user(%d) qrcode result(%s)', $uid, var_export($qrcode, 1));
        if(!$download){
            return $url;
        }
        $imgstream = self::getWeixinSdk()->getQrcode($qrcode['ticket']);
        if(!strlen($imgstream)){
            CLog::warning('download temp qrcode image fail');
            return false;
        }
        $tmpfile = uniqid('/tmp/'.$sceneType.'_qrcode_');
        file_put_contents($tmpfile, $imgstream);
        if(file_exists($tmpfile)){
            if($path = UploadFileService::upload($tmpfile)){
                CLog::debug('getTempQrcodeUrl return url(%s)', $path);
                return $path;
            }
        }
        CLog::warning('upload temp qrcode image fail');
        return false;
        
    }


    /**
     *获取用户二维码URL，请求微信生成二维码，并把二维码下载到本地服务器，返回本地图片地址
     *@param string $sceneType 二维码类型
     *@param int $uid 用户id
     *@param return string 本地服务器二维码图片地址
     */
    public static function getForeverQrcode($sceneType, $id, $download = false){
        CLog::debug('create getForeverQrcode(%d, %d, %d) qrcode', $sceneType, $id, $download);
        $qrcode = self::getWeixinSdk()->createQrcode($sceneType . '_' . $id);
        if(!$qrcode['ticket']){
            CLog::warning('createQrcode fail, result(%s)', var_export($qrcode, 1));
            return false;
        }
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $qrcode['ticket'];
        CLog::debug('request weixin createQrcode result(%s)', var_export($qrcode, 1));
        if(!$download){
            return $url;
        }
        $imgstream = self::getWeixinSdk()->getQrcode($qrcode['ticket']);
        if(!strlen($imgstream)){
            CLog::warning('getForeverQrcode download qrcode image fail');
            return false;
        }
        $tmpfile = uniqid('/tmp/chat_qrcode_');
        file_put_contents($tmpfile, $imgstream);
        if(file_exists($tmpfile)){
            if($path = UploadFileService::upload($tmpfile)){
                CLog::debug('getForeverQrcode return url(%s)', $path);
                return $path;
            }
        }
        CLog::warning('getForeverQrcode upload qrcode image fail');
        return false;
    }

	/**
     *获取用户二维码URL，请求微信生成二维码，并把二维码下载到本地服务器，返回本地图片地址
     *@param string $sceneType 二维码类型
     *@param int $uid 用户id
     *@param return string 本地服务器二维码图片地址
     */
    public static function downloadUserQrcode($sceneType, $uid){
    	CLog::debug('create user(%d) qrcode', $uid);
        $qrcode = self::getWeixinSdk()->createQrcode($sceneType . '_' . $uid);
    	if(!$qrcode['ticket']){
    		CLog::warning('create user qrcode fail, result(%s)', var_export($qrcode, 1));
    		return false;
    	}
    	CLog::debug('request weixin create user(%d) qrcode result(%s)', $uid, var_export($qrcode, 1));
    	$imgstream = self::getWeixinSdk()->getQrcode($qrcode['ticket']);
    	if(!strlen($imgstream)){
    		CLog::warning('download user qrcode image fail');
    		return false;
    	}
		$tmpfile = uniqid('/tmp/chat_qrcode_');
		file_put_contents($tmpfile, $imgstream);
		if(file_exists($tmpfile)){
			if($path = UploadFileService::upload($tmpfile)){
				return $path;
			}
		}
		CLog::warning('upload user qrcode image fail');
		return false;
    }

    /**
     *  生成微信短链接地址 Wiki: http://mp.weixin.qq.com/wiki/10/165c9b15eddcfbd8699ac12b0bd89ae6.html
     *  Note: 截止到2015-04-02 当天调取频率不能超过1000次，谨慎使用哈！！！
     * 
     * @param str $longUrl 长链接地址 
     *  
     *  return str
     *  
     *  @param addTim 2015-04-02
     *  @param author  ChengBo
     */
    public static function getWShortUrl($longUrl = ''){
    	if(!Utils::check_string($longUrl)) return '';
    	
    	//调取接口获取微信短链接地址
    	$urlData = self::getWeixinSdk()->getShortUrl($longUrl);
    	
    	//处理返回值
    	if(!isset($urlData['errcode']) || $urlData['errcode'] != 0 || !Utils::check_string($urlData['short_url'])){
    		return '';
    	}
    	    	
    	return $urlData['short_url'];
    }


    /**
     *发货通知，支付完成后通知微信已发货
     *@param string $openid 用户openid
     *@param string $transid 交易单号 
     *@param string $out_trade_no 第三方订单号
     *@param int $deliver_timestamp 发货时间
     *@param int $deliver_status发货状态，1表明成功，0表明失败，失败时需要在deliver_msg填上失败原因
     *@param string 发货状态信息，失败时可以填上UTF8编码的错诨提示信息，比如“该商品已退款”
     */
    public static function deliverNotify($openid,$transid,$out_trade_no,$deliver_timestamp,$deliver_status,$deliver_msg = 'ok'){
        $param = array(
            'appid'             => self::getAppid(false),
            'openid'            => $openid,
            'transid'           => $transid,
            'out_trade_no'      => $out_trade_no,
            'deliver_timestamp' => $deliver_timestamp,
            'deliver_status'    => $deliver_status,
            'deliver_msg'       => $deliver_msg,
        );
        $param['app_signature'] = sha1(self::formatQueryParaMap($param, false));
        $param['sign_method'] = 'sha1';
        return self::getWeixinSdk()->deliverNotify($param);
    }
}
