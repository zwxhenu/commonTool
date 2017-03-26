<?php
/**
 * 导入Excel 工具类
 *
 * @param authro  ChengBo
 * @param addTime 2013-12-04
 */
class ImportExcels {
	CONST QUSERID   = 765604;//765604
	CONST QUSERNAME = 'ruanj_wd';//ruanj_wd
	
	CONST AUSERID 	= 410263;//410263
	CONST AUSERNAME = 'ouac2012';//ouac2012
	
	CONST MAXNUM    = 201;//因为有第一行是标题
	
	CONST SEX_CID   = 209;//两性分类id
	
	CONST CACHE_CATEGORY = '_IaskCategoryDataCache_admin_excel';
	
	//提问者用户信息 array(uid,uid1);
	static $commons = array(1307008,1307021,1307023,1307028,1307030,1308681);
	
	//医生 用户信息 现在医生和两性都是随机用一个医生回答
	static $doctors = array(
		array('author' => 'a444942548',    'uid' => 492120, 'usertype' => CommonConst::USER_TYPE_PAETTIME), 
		array('author' => 'jiankang521120','uid' => 692045, 'usertype' => CommonConst::USER_TYPE_PAETTIME), 
		array('author' => 'ouac2012',      'uid' => 410263, 'usertype' => CommonConst::USER_TYPE_PAETTIME), 
		array('author' => 'yuxiuzhi203',   'uid' => 1297408,'usertype' => CommonConst::USER_TYPE_PAETTIME), 
		array('author' => 'qinyufen',      'uid' => 1308698,'usertype' => CommonConst::USER_TYPE_PAETTIME), 
		array('author' => 'zhandouhan',    'uid' => 1308699,'usertype' => CommonConst::USER_TYPE_PAETTIME), 
	);
	
	//两性专家 用户信息
	static $authors = array(
		array('author' => 'fh888414','uid' => 507365, 'usertype' => CommonConst::USER_TYPE_GENERAL),
		array('author' => 'fh005487','uid' => 507373, 'usertype' => CommonConst::USER_TYPE_GENERAL), 
		array('author' => 'fh533840','uid' => 507376, 'usertype' => CommonConst::USER_TYPE_GENERAL), 
		array('author' => 'fh854551','uid' => 507892, 'usertype' => CommonConst::USER_TYPE_GENERAL), 
		array('author' => 'fh542323','uid' => 507896, 'usertype' => CommonConst::USER_TYPE_GENERAL), 
		array('author' => 'fh969269','uid' => 507898, 'usertype' => CommonConst::USER_TYPE_GENERAL), 
	);
	
	/**
	 * 导入Excle数据到 问答数据库中
	 * @param $path   要导入的excel文件路径
	 *
	 * @param author  ChengBo
	 * @param addTime 2013-12-04
	 */
	public static function importData($path = ''){
		if(!is_file($path)){return false;}
		
		require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');

		$PHPExcel  = new PHPExcel();	
		$PHPReader = new PHPExcel_Reader_Excel2007(); 		
		if(!$PHPReader->canRead($path)){						
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($path)){						
				return false;
			}
		}
		$PHPExcel     = $PHPReader->load($path);
		$currentSheet = $PHPExcel->getSheet(0);
		//取得一共有多少列
		$allColumn = $currentSheet->getHighestColumn();
		//取得一共有多少行
		$allRow    = array($currentSheet->getHighestRow());

		if($allRow[0] > self::MAXNUM){
			//删除 上传文件
			@unlink($path);
			return '内容条数超过200条!';
		}
		
		//调去分类数据
		$cateData 		= self::getCateData();

		//问题描述
		$questionData   = array();
		for($currentRow = 2;$currentRow<=$allRow[0];$currentRow++){
			if($currentRow > self::MAXNUM)break;
			$tmp_depart = '';
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				$address= $currentColumn.$currentRow;
				$value  =  $currentSheet->getCell($address)->getValue()."\t";
				if(!empty($value)){
					$value = trim(strip_tags($value));
					switch($currentColumn){
						case 'A' : $key = 'sex';$value = preg_replace('/	/is','',$value);$value = in_array(trim($value),array('男','女')) ? trim($value) : '';break;
						case 'B' : $key = 'age';$value = preg_replace('/	/is','',$value);$value = is_numeric($value) ? intval($value) : '';break;
						case 'C' : $key = 'category';$value = preg_replace('/	/is','',$value);$value = empty($value) ? '' : trim($value);break;
						case 'D' : $key = 'title';$value = preg_replace('/	/is','',$value);$value = empty($value) ? '' : trim($value);break;
						case 'E' : $key = 'description';$value = preg_replace('/	/is','',$value);$value = empty($value) ? '' : trim($value);break;
						case 'F' : $key = 'answer';break;
						case 'G' : $key = 'uid';break;
						default : $key = '';
					}
					if(!empty($key)){
						if(in_array($key,array('sex','age','category','title','description'))){
							if(empty($value)){
								//删除 上传文件
								@unlink($path);
								return $currentRow.$currentColumn.'数据错误!';
							}

							//检查分类是否存在
							if($key == 'category'){
								//判断分类
								if(!isset($cateData['name'][$value])){
									//删除 上传文件
									@unlink($path);
									return $currentRow.$currentColumn.'数据错误, 分类不存在!';
								}
								//判断分类是否为最后一级
								if(($cateData['name'][$value]['grade'] == 2 && isset($cateData['pid'][$cateData['name'][$value]['id']])) || $cateData['name'][$value]['grade'] == 1){
									//删除 上传文件
									@unlink($path);
									return $currentRow.$currentColumn.'数据错误, 分类必须为最小级!';
								}
								//匹配cid1 cid2 cid3 数据
								$cid = $cateData['name'][$value]['id'];
								if($cateData['name'][$value]['grade'] == 2){
									$questionData[$currentRow-2]['cid3'] = 0;
									$questionData[$currentRow-2]['cid2'] = $cid;
									$questionData[$currentRow-2]['cid1'] = $cateData['id'][$cid];
								}else{
									$questionData[$currentRow-2]['cid3'] = $cid;
									$questionData[$currentRow-2]['cid2'] = $cateData['id'][$cid];
									$questionData[$currentRow-2]['cid1'] = $cateData['id'][$cateData['id'][$cid]];
								}
							}else{
								$questionData[$currentRow-2][$key] = $value;
							}
						}
						//添加回答内容
						if($key == 'answer'){
							$questionData[$currentRow-2][$key] = $value;
						}
						//添加 向某人提问uid
						if($key == 'uid' && !empty($value)){
							if(is_numeric($value) && intval($value)>0){
								$questionData[$currentRow-2][$key] = $value;
							}else{
								//删除 上传文件
								@unlink($path);
								return $currentRow.$currentColumn.'数据错误, uid必须为数字!';
							}
						}
					}
				}
			}
		}
		unset($cateData);

		//统计 问题和回答数
		$qnums = 0;
		$anums = 0;
		foreach($questionData as $k=>$v){
			if(isset($v['title']) && !empty($v['title'])){$qnums++;}
			if(isset($v['title']) && !empty($v['title']) && isset($v['answer']) && !empty($v['answer'])){$anums++;}
		}
		
		//导入excel 队列参数
		$taskData['path'] = realpath($path);
		
		//写入队列
		$submit_service = SubmitService::getInstance();
		$submit_arr[] 	= array(
					'module'  => CommonConst::MODULE_ADMIN_ID,
					'name' 	  => 'import_excel',
					'content' => json_encode($taskData)
		);
		$submit_service->addTask($submit_arr);
		
		return '成功导入: '.$qnums.'条问题,'.$anums.'条回答!';
	}
	
	/**
	 * 处理excel导入队列
	 * 
	 */
	public static function dealTaskExcel($path = ''){
		if(!is_file($path)){return false;}
		
		require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');
		
		$PHPExcel  = new PHPExcel();	
		$PHPReader = new PHPExcel_Reader_Excel2007(); 		
		if(!$PHPReader->canRead($path)){						
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($path)){						
				return false;
			}
		}
		$PHPExcel     = $PHPReader->load($path);
		$currentSheet = $PHPExcel->getSheet(0);
		//取得一共有多少列
		$allColumn = $currentSheet->getHighestColumn();
		//取得一共有多少行
		$allRow    = array($currentSheet->getHighestRow());

		if($allRow[0] > self::MAXNUM){
			//内容条数超过200条!
			CLog::debug("The excel file rows more than ".self::MAXNUM.', path:'.$path);
			return false;
		}
		
		//调去分类数据
		$cateData 		= self::getCateData();

		//问题数据
		$questionData   = array();
		for($currentRow = 2;$currentRow<=$allRow[0];$currentRow++){
			if($currentRow > self::MAXNUM)break;
			$tmp_depart = '';
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				$address= $currentColumn.$currentRow;
				$value  =  $currentSheet->getCell($address)->getValue()."\t";
				if(!empty($value)){
					$value = trim(strip_tags($value));
					switch($currentColumn){
						case 'A' : $key = 'sex';$value = preg_replace('/	/is','',$value);$value = in_array(trim($value),array('男','女')) ? trim($value) : '';break;
						case 'B' : $key = 'age';$value = preg_replace('/	/is','',$value);$value = is_numeric($value) ? intval($value) : '';break;
						case 'C' : $key = 'category';$value = preg_replace('/	/is','',$value);$value = empty($value) ? '' : trim($value);break;
						case 'D' : $key = 'title';$value = preg_replace('/	/is','',$value);$value = empty($value) ? '' : trim($value);break;
						case 'E' : $key = 'description';$value = preg_replace('/	/is','',$value);$value = empty($value) ? '' : trim($value);break;
						case 'F' : $key = 'answer';break;
						case 'G' : $key = 'uid';break;
						default : $key = '';
					}
					if(!empty($key)){
						if(in_array($key,array('sex','age','category','title','description'))){
							if(empty($value)){
								//某一行的数据错误
								CLog::debug("The excel file data error:".$currentRow.$currentColumn.', path:'.$path);
								return false;
							}

							//检查分类是否存在
							if($key == 'category'){
								//判断分类
								if(!isset($cateData['name'][$value])){
									//某行数据错误, 分类不存在!
									CLog::debug("The excel file categoryData not exists:".$currentRow.$currentColumn.', path:'.$path);
									return false;
								}
								//判断分类是否为最后一级
								if(($cateData['name'][$value]['grade'] == 2 && isset($cateData['pid'][$cateData['name'][$value]['id']])) || $cateData['name'][$value]['grade'] == 1){
									//数据错误, 分类必须为最小级!
									CLog::debug("The excel file categoryData must the smaillest level:".$currentRow.$currentColumn.', path:'.$path);
									return false;
								}
								//匹配cid1 cid2 cid3 数据
								$cid = $cateData['name'][$value]['id'];
								if($cateData['name'][$value]['grade'] == 2){
									$questionData[$currentRow-2]['cid3'] = 0;
									$questionData[$currentRow-2]['cid2'] = $cid;
									$questionData[$currentRow-2]['cid1'] = $cateData['id'][$cid];
								}else{
									$questionData[$currentRow-2]['cid3'] = $cid;
									$questionData[$currentRow-2]['cid2'] = $cateData['id'][$cid];
									$questionData[$currentRow-2]['cid1'] = $cateData['id'][$cateData['id'][$cid]];
								}
							}else{
								$questionData[$currentRow-2][$key] = $value;
							}
						}
						//添加回答内容
						if($key == 'answer'){
							$questionData[$currentRow-2][$key] = $value;
						}
						//添加 向某人提问uid
						if($key == 'uid' && !empty($value)){
							if(is_numeric($value) && intval($value)>0){
								$questionData[$currentRow-2][$key] = $value;
							}
						}
					}
				}
			}
		}
		unset($cateData);

		//问题 回答入库
		$answerDatas = array();
		foreach($questionData as $k=>$v){
			self::insertQuestionDb($v, $path);
		}
		
		return true;
	}
	
	/**
	 * 插入问题数据
	 * @param $data 插入的数据
	 * @param $path 原始文件路径名称
	 * return array 回答数组
	 * 
	 * @param author  ChengBo
	 * @param addTime 2014-06-17
	 */
	public static function insertQuestionDb($data = array(), $path = ''){
		//验证数据完整性
		$fileds = array('sex','age','cid1','cid2','cid3','title','description');
		if(array_diff($fileds,array_keys($data))) return false;
		
		//随机生成quid
		$quserid 				 = self::$commons[mt_rand(0,count(self::$commons)-1)];
		
		//插入问题数据
		$question['authorid']    = $quserid;
		$question['title']	     = isset($data['title']) && !empty($data['title']) ? trim(strip_tags($data['title'])) : mb_substr(htmlspecialchars_decode($data['description']),0,30,"utf-8");
		$question['description'] = trim(strip_tags($data['description']));
		$question['sex']		 = ($data['sex'] == '男' ? 1 : ($data['sex'] == '女' ? 2 : 0));
		$question['age']		 = $data['age']*12;
		$question['time']        = time();
		$question['answers']     = !isset($data['answer']) || empty($data['answer']) ? 0 : 1;//插入回答时更新回答数 2014-08-20个人中心找过来让改的
		$question['status']		 = 1;//已审核
		$question['state']		 = !isset($data['answer']) || empty($data['answer']) ? 0 : 3;//有回答时 设为已关闭
		$question['adopt']		 = 0;//!isset($data['answer']) || empty($data['answer']) ? 0 : 1;;//采纳
		$question['sourceid']	 = CommonConst::QUESTION_SOURCE_EXCEL;//Excel导入
		$question['ip']		 	 = '111.196.70.16';
		$question['touid']		 = 0;//isset($data['uid']) && !empty($data['uid']) && is_numeric($data['uid']) ? intval($data['uid']) : 0;
		$question['articleid']	 = 0;
		
		$question['cid1']		 = (isset($data['cid1']) ? $data['cid1'] : 0);
		$question['cid2']        = (isset($data['cid2']) ? $data['cid2'] : 0);
		$question['cid3']		 = (isset($data['cid3']) ? $data['cid3'] : 0);	
		
		//插入问题表 并返回qid 初始化SDK
		$iaskSapi  = new IaskSapi(CommonConst::HOST_IASK);
		$result	   = $iaskSapi->questionAdd($question['authorid'],$question['title'],$question['touid'],$question['sourceid'],$question['articleid'],$question['description'],'',$question['sex'],$question['age'],'',$question['ip'],$question['status'],$question['cid1'],$question['cid2'],$question['cid3'],$question['answers'],$question['adopt'],$question['state']);
		
		//处理返回值
		if(!isset($result['errno']) || $result['errno'] != CommonConst::SUCCESS){
			//插入失败
			$errmsg = sprintf('Import Excel: admin module, import Excel file fail When add question param:%s', var_export($question, true));
			CLog::debug($errmsg);
			
			if(!empty($path)){
				//生成日志文件目录
				$importFailFileLog = dirname($path).'/../../excel_log/';
				ImportExcels::dir_create($importFailFileLog);
				$importFailFileLog.= 'importFail_'.date('Ym', time()).'.log';
				$basenames 		   = explode('/', $path);
				$basename		   = array_pop($basenames);
				
				//记录日志内容
				$logStr  = 'AddTime      :'.date('Y-m-d H:i:s', time()).chr(10);
				$logStr .= 'Filename     :'.$basename.chr(10);
				$logStr .= 'QuestionTitle:'.$question['title'].chr(10);
				$logStr .= 'QuestionDesc :'.$question['description'].chr(10);
				$logStr .= 'InsertResult :'.json_encode($result).chr(10).chr(10);
				@file_put_contents($importFailFileLog, $logStr, FILE_APPEND | LOCK_EX);
				@chmod($importFailFileLog, 0777);
			}
			
			return false;
		}else{
			$questionID = $result['qid'];
		}
		
		//组合回答数据
		$answerData 	= array();
		$data['answer'] = isset($data['answer']) ? trim(strip_tags($data['answer'])) : '';
		if(isset($questionID) && !empty($data['answer'])){
			//判断是否是向指定医生提问
			if(isset($data['uid']) && !empty($data['uid'])){
				$user_service = UserService::getInstance();
				$toUserData   = $user_service->getUserInfoByUid($data['uid']);
				$authors = array('author' => $toUserData['username'],'uid' => $toUserData['uid'], 'usertype' => $toUserData['usertype']);
			}else{
				//判断是否是两性回答
				if($question['cid1'] == self::SEX_CID){
					$authors = self::$authors[mt_rand(0,5)];
				}else{
					$authors = self::$doctors[mt_rand(0,5)];
				}
			}
			//验证是否为空 如果为空再随机生成一个
			if(empty($authors)){
				$authors = self::$doctors[mt_rand(0,5)];
			}
			
			//匹配回答数据
			$answerData['qid']       = $questionID;
			$answerData['quid']      = $quserid;
			$answerData['auid']		 = $authors['uid'];
			$answerData['authortype']= $authors['usertype'];
			$answerData['title']     = $question['title'];
			$answerData['content']   = $data['answer'];
			$answerData['status']    = 1;//默认已审核
			
			self::insertAnswerDb($answerData, $path);
		}
		
		return true;
	}
	
	/**
	 * 插入回答数据
	 *
	 * @param array $data 回答数据数组
	 * @param str   $path 上传文件的源地址
	 * return boolean 
	 *
	 * @param author  ChengBo
	 * @param addTime 2014-06-17
	 */
	public static function insertAnswerDb($data = array(), $path = ''){
		if(empty($data))return false;
		
		//插入回答表 初始化SDK
		$iaskSapi  = new IaskSapi(CommonConst::HOST_IASK);
		$result    = $iaskSapi->answerImport(array('answers'=>array($data)));

		//处理返回值
		if(!isset($result['errno']) || $result['errno'] != CommonConst::SUCCESS){
			//插入失败
			$errmsg = sprintf('admin module, import Excel file fail When add answer param:%s',var_export($data, true));
			CLog::debug($errmsg);
			
			if(!empty($path)){
				//生成日志文件目录
				$importFailFileLog = dirname($path).'/../../../excel_log/';
				ImportExcels::dir_create($importFailFileLog);
				$importFailFileLog.= 'importFail_'.date('Ym', time()).'.log';
				
				//记录日志内容
				$logStr  = 'AddTime      :'.date('Y-m-d H:i:s', time()).chr(10);
				$logStr .= 'Filename     :'.basename($path).chr(10);
				$logStr .= 'QuestionTitle:'.$question['title'].chr(10);
				$logStr .= 'AnswerContent:'.$question['content'].chr(10);
				$logStr .= 'InsertResult :'.json_encode($result).chr(10).chr(10);
				@file_put_contents($importFailFileLog, $logStr, FILE_APPEND | LOCK_EX);
				@chmod($importFailFileLog, 0777);
			}
			
			return false;
		}
		
		return true;
	}
	
	/**
	 *	调去分类 信息数据
	 *	@return  Array('catname'=>'cid')
	 *	@Note    去掉 妇科大类下的妇科子类 and 男科也是	产科也是
	 *
	 *  @author  ChengBo
	 *  @addtime 2014-03-01
	 */
	public static function getCateData(){
		//设置redis变量
		$redis    = RedisWrapper::getInstance(CommonConst::MODULE_IASK_ID);
		$cateData = $redis->get(CommonConst::MODULE_IASK_ID.self::CACHE_CATEGORY);
		if(!empty($cateData)){
			return json_decode($cateData, true);
		}
	
		//调去分类数据
		$categoryService = CategoryService::getInstance();
		$cateData		 = $categoryService->getAllList();

		$cateDatas 		 = array();
		foreach($cateData as $k=>$v){
			$cateDatas['name'][$v['name']]  = array('id'=>$v['id'],'pid'=>$v['pid'],'grade'=>$v['grade']);
			$cateDatas['id'][$v['id']]      = $v['pid'];
			if($v['grade'] == 3){
				$cateDatas['pid'][$v['pid']]= $v['id'];
			}
		}

		// $redis->set(CommonConst::MODULE_IASK_ID.self::CACHE_CATEGORY, json_encode($cateDatas), 3* 24 * 60 * 60);
		$redis->setex(CommonConst::MODULE_IASK_ID.self::CACHE_CATEGORY, 3* 24 * 60 * 60, json_encode($cateDatas));
		
		return $cateDatas;
	}
	
	/**
	 *  需要创建目录
	 *	@param   $path 目录绝度路径 
	 *  @param   $mode 目录访问权限
	 *  @author  ChengBo
	 *  @addtime 2013-11-22
	 */
	public static function dir_create($path, $mode = 0777) {
		if(is_dir($path)) return TRUE;
		$ftp_enable = 0;
		$path = self::dir_path($path);
		$temp = explode('/', $path);
		$cur_dir = '';
		$max = count($temp) - 1;
		for($i=0; $i<$max; $i++) {
			$cur_dir .= $temp[$i].'/';
			if (@is_dir($cur_dir)) continue;
			@mkdir($cur_dir, 0777,true);
			@chmod($cur_dir, 0777);
		}
		return is_dir($path);
	}
	public static function dir_path($path) {
		$path = str_replace('\\', '/', $path);
		if(substr($path, -1) != '/') $path = $path.'/';
		return $path;
	}

	public static function importOrder($path='')
	{
		if(!is_file($path)){return false;}
		
		require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');
		
		$PHPExcel  = new PHPExcel();	
		$PHPReader = new PHPExcel_Reader_Excel2007(); 		
		if(!$PHPReader->canRead($path)){						
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($path)){						
				return false;
			}
		}
		$PHPExcel     = $PHPReader->load($path);
		$currentSheet = $PHPExcel->getSheet(0);
		//取得一共有多少列
		$allColumn = $currentSheet->getHighestColumn();
		//取得一共有多少行
		$allRow    = array($currentSheet->getHighestRow());

		//删除尾部空白行
		$row = $allRow[0];
		for ($i=$allRow[0]; $i>1; $i--) { 
			$duidValue  =  $currentSheet->getCell('A'.$i)->getValue();
			if (is_null($duidValue) || empty($duidValue)) {
				$row--;
			} else {
				break;
			}
		}

		if($row > 101){
			//删除 上传文件
			@unlink($path);
			return '内容条数超过100条!';
		}

		for($currentRow = 2;$currentRow<=$row;$currentRow++){
			if($currentRow > self::MAXNUM)break;
			$tmp_depart = '';
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				$address= $currentColumn.$currentRow;
				$value  =  $currentSheet->getCell($address)->getValue()."\t";
				$value = trim(strip_tags($value));
				if(!empty($value)){
					switch($currentColumn){
						case 'A': 
						$key='duid';
						break;
						case 'B':
						$key = 'patient_name';
						break;
						case 'C':
						$key = 'patient_sex';
						break;
						case 'D':
						$key = 'patient_age';
						break;
						case 'E':
						$key = 'symptom';
						break;
						case 'F':
						$key = 'description';
						break;
						case 'G':
						$key = 'cost';
						break;
						case 'H':
						$key = 'comment';
						break;
						case 'I':
						$key = 'consult_time';
						break;
					}
				}
				$orderData[$currentRow-2][$key] = $value;
			}
			
		}
		$ret = false;
		foreach($orderData as $k=>$v){
			if (empty($v['duid']) || is_null($v['duid']) || !isset($v['duid'])) {
				break;
			}
			if ($ret === false) {
				$ret = self::insertOrder($v);
			} else {
				self::insertOrder($v);
			}
		}
		return $ret;
	}
	
	public static function 	insertOrder($data)
	{	

		$userService 		= UserService::getInstance();
		$port_sapi = new PassportSapi(CommonConst::HOST_PASSPORT);
		$auto_create_user 	= $port_sapi->autoreguser();		
		$doctor_data = $userService->getUserInfoByUid($data['duid']);
		$time_arr =  self::timeBuilder();
		$province_arr 		= AreaService::getProvinceList();
		$idgenerator_service = IdGeneratorService::getInstance();
		$patient_id = $idgenerator_service->getId(CommonConst::MODULE_PASSPORT_ID,CommonConst::ID_PASSPORT_ONLINE_PATIENT);

		$param['uid'] 				= $auto_create_user['userinfo']['uid'];						//下单人uid
		$param['relationship'] 		= rand(0,1);                					//与患者关系
		$param['patient_id'] 		= $patient_id;									//患者id
		$param['patient_name'] 		= $data['patient_name'];						//患者称呼
		$param['patient_sex'] 		= $data['patient_sex'];							//患者性别
		$param['patient_age'] 		= $data['patient_age'];							//患者年龄
		$param['patient_area'] 		= $province_arr[rand(2,35)];					//患者所在地
		$param['trade_num'] 		= '';											//交易单号
		$param['pay_type']  		= CommonConst::PAY_TYPE_ALIPAY;	 				//支付方式
		$param['doctor_uid'] 		= $data['duid']; 								//医生uid
		$param['doctor_name'] 		= $doctor_data['zname'];						//医生姓名
		$param['contact_number']	= self::buildPhoneNumber(); 					//联系电话
		$param['symptom'] 			= $data['symptom'];								//病情或症状描述
		$param['description'] 		= $data['description'];							//病情描述
		$param['supply'] 			= ''; 											//病情补充
		$param['original_price']	= $data['cost'];								//原价
		$param['discount'] 			= 1;											//折扣率
		$param['consult_price']		= $data['cost'];								//咨询价格
		$param['consult_time']  	= $data['consult_time'];						//咨询时间
		$param['book_talk_time']	= $time_arr['book_talk_time'];	 				//预约通话时间
		$param['expect_talk_time']	= $time_arr['expect_talk_time'];  				//期望通话时间
		$param['order_status']  	= CommonConst::ORDER_STATUS_DONE;				//订单状态
		$param['pay_status'] 		= CommonConst::PAY_STATUS_PAYED;				//支付状态
		$param['comment_status']	= 2;							 				//评论状态
		$param['add_time'] 			= $time_arr['add_time']; 						//订单添加时间
		$param['pay_time'] 			= $time_arr['pay_time']; 					 	//支付时间
		$param['finish_time'] 		= $time_arr['finish_time']; 					//完成时间
		$param['comment_time'] 		= $time_arr['comment_time'];					//评论时间
		$param['degree']	 		= 1;											//满意度
		$param['comment'] 	 		= $data['comment'];								//评论内容
		$param['talk_mark']			= '';											//打电话备注
		$param['pay_mark']	 		= '';											//支付备注
		$param['remark'] 			= '';											//流程备注
		$param['sourceid'] 			= CommonConst::ORDER_SOURCE_POUR;				//订单来源
		
		$TelSapi = new TelSapi(CommonConst::HOST_ONLINE);
		$ret = $TelSapi->orderIrrigation($param);
		
		if (isset($ret['result']) && $ret['result'] == true) {
			return true;
		} else {
			CLog::warning("Insertion orders fail data[%s]",var_export($param,true));
			return false;
		}
	}
	
	/**
	*生成订单相关各种时间
	*/

	public static function timeBuilder()
	{
	
		//基础时间，电话咨询平台上线时间
		$base_time = strtotime("2014-07-20 00:00:00");
		
		//最大时间，当前时间减去6小时	
		$max_time = time()-(60*60*24*6);
		
		//随机生成订单创建时间戳
		$rand_stamp_time = rand($base_time, $max_time);
		
		//校验时间，排除0点到6点的时间
		if(intval(date("H",$rand_stamp_time)) < 6){
			$rand_stamp_time =$rand_stamp_time+(60*60*9);
		}
		
		//生成订单创建时间
		$add_time = $rand_stamp_time;
		//生成支付时间
	    $pay_time = $add_time+rand(10*60,(60*60*24*3-60));
		//生成订单完成时间
		$finish_time = $pay_time+rand(30*60,(60*60*24*3-60));
		//生成订单评论时间
		$comment_time = $finish_time+rand(10*60,(60*60*24-60));
		
		$time_arr = array();
		$time_arr['add_time'] = $add_time;
		$time_arr['pay_time'] = $pay_time;
		$time_arr['finish_time'] = $finish_time;
		$time_arr['comment_time'] = $comment_time;	   
		$time_arr['expect_talk_time'] = "越快越好";
		$time_arr['book_talk_time']= date("Y-m-d H:i:s",rand(($pay_time+10*60),($finish_time-10*60)));
		return $time_arr;
	    
	}
	
	public static function buildPhoneNumber()
	{
	
		$second_arr = array(3,5,7,8);
		$phone_number='1';
		$phone_number.= $second_arr[rand(0,3)];
		$rand = array();
		if ($phone_number == '13' || $phone_number == '18') {
			$phone_number .= rand(0,9);
		} elseif ($phone_number == '15') {
			$rand = array(0,1,2,3,5,6,7,8,9);
			$phone_number .= $rand[rand(0,8)];
		} elseif ($phone_number == '17') {
			$phone_number = '170';
		}
		$n = rand(10000000,99999999);
		if ($n >= 50000000 && $n <= 50000005) {
			$rand = array('00000000',$n);
			$phone_number .= $rand[rand(0,1)];
		} else {
			$phone_number .= $n;
		}
		return $phone_number;
	}

	public static function importPA($path='')
	{
		if(!is_file($path)){return false;}
		
		require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');
		
		$PHPExcel  = new PHPExcel();	
		$PHPReader = new PHPExcel_Reader_Excel2007(); 		
		if(!$PHPReader->canRead($path)){						
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($path)){						
				return false;
			}
		}
		$PHPExcel     = $PHPReader->load($path);
		$currentSheet = $PHPExcel->getSheet(0);
		//取得一共有多少列
		$allColumn = $currentSheet->getHighestColumn();
		//取得一共有多少行
		$allRow    = array($currentSheet->getHighestRow());

		//删除尾部空白行
		$row = $allRow[0];
		for ($i=$allRow[0]; $i>1; $i--) { 
			$uidValue  =  $currentSheet->getCell('A'.$i)->getValue();
			if (is_null($uidValue) || empty($uidValue)) {
				$row--;
			} else {
				break;
			}
		}

		if($row > 1001){
			//删除 上传文件
			@unlink($path);
			return '内容条数超过100条!';
		}

		for($currentRow = 2;$currentRow<=$row;$currentRow++){
			if($currentRow > self::MAXNUM)break;
			$tmp_depart = '';
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				$address= $currentColumn.$currentRow;
				$value  =  $currentSheet->getCell($address)->getValue()."\t";
				$value = trim(strip_tags($value));
				if(!empty($value)){
					switch($currentColumn){
						case 'A': 
						$key='uid';
						break;
						case 'B':
						$key = 'money';
						break;
						case 'C':
						$key = 'reason';
						break;
						case 'D':
						$key = 'operate';
						break;
						case 'E':
						$key = 'remark';
						break;
					}
				}
				$paData[$currentRow-2][$key] = $value;
			}
			
		}

		foreach($paData as $k=>$v){

			if (empty($v['uid']) || is_null($v['uid']) || !isset($v['uid'])) {
				break;
			}

			$ret = self::insertPA($v);
			
			if($k%50 == 0){
				sleep(1);
			}
		}

		return $ret;
	}	

	public static function insertPA($data)
	{

		$time = strtotime(date('Y-m'.'-09'));
		$wallet_sapi = new WalletSapi(CommonConst::HOST_WALLET);

		switch($data['operate']){
		  	case CommonConst::ADMIN_PUNISH :
                                       		 $param['uid']       = $data['uid'];
                                       		 $param['num']       = Utils::check_int($data['money']) ? $data['money'] : 0;
                                       		 $param['operate']   = CommonConst::WALLET_MONEY_OPERATE_SUBTRACT;
                                       		 $param['snum']      = Utils::check_int($data['money']) ? $data['money'] : 0;
                                       		 $param['soperate']  = CommonConst::WALLET_MONEY_OPERATE_ADD;
                                       		 $param['reason']    = $data['reason'];
                                       		 $param['log_time']  = $time;
                                       		 $param['module']    = CommonConst::MODULE_ADMIN_ID;
                                       		 $param['type']      = CommonConst::ADMIN_PUNISH;
                                       		 $param['status']    = CommonConst::WALLET_MONEY_STATUS_FINISH;
                                       		 $param['remark']    = $data['remark'];
                                       		 $param['third_id']  = $data['uid'];
						 break;

                        //添加活动奖励 22
                        case CommonConst::USER_ACTIVITY_AWARD :
                                       		 $param['uid']       = $data['uid'];
                                       		 $param['num']       = Utils::check_int($data['money']) ? $data['money'] : 0;
                                       		 $param['operate']   = CommonConst::WALLET_MONEY_OPERATE_ADD;
                                       		 $param['snum']      = Utils::check_int($data['money']) ? $data['money'] : 0;
                                       		 $param['soperate']  = CommonConst::WALLET_MONEY_OPERATE_SUBTRACT;
                                       		 $param['reason']    = $data['reason'];
                                       		 $param['log_time']  = $time;
                                       		 $param['module']    = CommonConst::MODULE_ADMIN_ID;
                                       		 $param['type']      = CommonConst::USER_ACTIVITY_AWARD;
                                       		 $param['status']    = CommonConst::WALLET_MONEY_STATUS_FINISH;
                                       		 $param['remark']    = $data['remark'];
                                       		 $param['third_id']  = $data['uid'];
						 break;
		}
	
		$ret= $wallet_sapi->insertBidirectionalWaterLog($param);
		
		if($ret['errno'] == 0){
			return true;
		}else{
			return false;
		}	
	}


    //通过excel导入用户数据
    public static function importUser($path='')
    {
        if(!is_file($path)){return false;}
        require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');
        
        $PHPExcel  = new PHPExcel();    
        $PHPReader = new PHPExcel_Reader_Excel2007();       
        if(!$PHPReader->canRead($path)){                        
            $PHPReader = new PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($path)){                        
                return false;
            }
        }

        $PHPExcel     = $PHPReader->load($path);
        $currentSheet = $PHPExcel->getSheet(0);

        //取得一共有多少列
        $allColumn = $currentSheet->getHighestColumn();
        //取得一共有多少行
        $allRow    = array($currentSheet->getHighestRow()); 

        //删除尾部空白行
        $row = $allRow[0];
        for ($i=$allRow[0]; $i>1; $i--) { 
            $duidValue  =  $currentSheet->getCell('A'.$i)->getValue();
            if (is_null($duidValue) || empty($duidValue)) {
                $row--;
            } else {
                break;
            }
        }

        //数据处理，生成用户数据数组
        for($currentRow = 2;$currentRow<=$row;$currentRow++){
            if($currentRow > self::MAXNUM)break;
            $tmp_depart = '';
            for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                $address= $currentColumn.$currentRow;
                $value  =  $currentSheet->getCell($address)->getValue()."\t";
                $value = trim(strip_tags($value));

                switch($currentColumn){
                        case 'A':
                            $key='serice';
                            break;
                        case 'B': 
                            $key='patient_name';
                            break;
                        case 'C':
                            $key = 'sex';
                            break;
                        case 'D':
                            $key = 'birthday';
                            break;
                        case 'E':
                            $key = 'idcard';
                            break;
                        case 'F':
                            $key = 'mobile';
                            break;
                        case 'G':
                            $key = 'province';
                            break;
                        case 'H':
                            $key = 'city';
                            break;
                        case 'I':
                            $key = 'doctor_name';
                            break;
                        case 'J':
                            $key = 'doctor_uid';
                            break;
                        case 'K':
                            $key = 'file_num';
                            break;
                        default:$key = '';
                    
                    }
               
                $userData[$currentRow-2][$key] = $value;
             
            }            
        }


        //数据入库
        $misdata = array();
        $mobile_arr = array();
        foreach($userData as $k=>$v){            
            
              //验证表中的手机好是否重复
              if(in_array($v['mobile'],$mobile_arr))
              {
                $misdata[] = $v;

              }else{
               
                $mobile_arr[] = $v['mobile'];
                $ret = self::insertUser($v);
                
                //数据导入失败，将失败数据返回。
                if($ret == false)
                { 
                   $misdata[] = $v;
                }
              } 
        }

        if(empty($misdata))
        {            
            @unlink($path);
            return true;

        }else
        {   
            @unlink($path);
            return $misdata;
        }

    }

    //将导入的数据进行处理  
    public function insertUser($data)
    {

        $port_sapi = new PassportSapi(CommonConst::HOST_PASSPORT); 
        $idgenerator_service = IdGeneratorService::getInstance();
        $widgetApi      = new WidgetSapi(CommonConst::HOST_WIDGET);
        $oldPatientService = OldPatientService::getInstance();

        //数据验证
        if(self::checkUserData($data) == false)
        {
            return false;
        }


        //验证电话号码是否存在
        if(self::checkMobile($data['mobile']) == false)
        {
            $user_temp_data = $port_sapi->autoreguser(array('password'=>'fh123456')); //不存在自动生成用户
            
            if(isset($user_temp_data['userinfo'])){
                $user_data = $user_temp_data['userinfo'];
            }else
            {
                return false;
            }
            CLog::debug("auto_create_user ret[%s]",var_export($user_data,true));
            //绑定电话号码
            $port_sapi->updateuserinfo($user_data['uid'],json_encode(array('mobile'=>$data['mobile']))); 

        }else
        {  
            $user_data = self::checkMobile($data['mobile']);                      //存在绑定已有用户
            CLog::debug("old_user ret[%s]",var_export($user_data,true));
        }

        //验证该患者是否在该用户下存在
        $is_repeat_ret =  $port_sapi->getPatientByWhere(array('name'=>$data['patient_name'],'uid'=>$user_data['uid'],'master'=>1));
        CLog::debug("old_patient ret[%s]",var_export($is_repeat_ret,true));
        

        if($is_repeat_ret == false || (isset($is_repeat_ret['errno']) && $is_repeat_ret['errno'] !=0))
        {
            return false;
        }

        //验证患者是否重复
        $is_repeat_pt_ret = $port_sapi->isRepeatName($data['patient_name'],$user_data['uid']);
        if($is_repeat_pt_ret['status']==1)
        {
            return false;
        }

        if(isset($is_repeat_ret['data']['data'][0]['id']) && $is_repeat_ret['data']['data'][0]['id']>0)
        {
            
            $patient_id = $is_repeat_ret['data']['data'][0]['id'];

            if(!is_numeric($patient_id))
            {
                return false;
            }
        
        }else{

       
         
        $patient_data=array();
        $patient_data['name'] = $data['patient_name'];
        $patient_data['sex'] = $data['sex'] == '男' ? 1 : 2 ;
        $patient_data['birthday'] = $data['birthday'];
        $patient_data['province'] = AreaService::getIdByName($data['province'])?AreaService::getIdByName($data['province']):0;
        $patient_data['city'] = AreaService::getIdByName($data['province'].$data['city'],2)?AreaService::getIdByName($data['province'].$data['city'],2):0 ; 
        $patient_data['mobile'] = $data['mobile'];
        $patient_data['sourceid'] = 1;      
        $add_patient_ret =  $port_sapi->addPatient($user_data['uid'],$patient_data);

    
    
        if($add_patient_ret == false || ($add_patient_ret['errno'] !=0))
        {
            return false;
        }else
        {
            $patient_id = $add_patient_ret['ret']; 
        }


        CLog::debug("new_patient ret[%s]",var_export($patient_data,true));
        }

        $old_patient_ret = $oldPatientService->getOldPatientByWhere(array('uid'=>$user_data['uid'],'mobile'=>$data['mobile'],'patient_id'=>$patient_id));

        CLog::debug("old_patient_ret ret[%s]",var_export($old_patient_ret,true));
        if(empty($old_patient_ret)){
        $param = array();
        $param['patient_id'] = $patient_id;
        $param['patient_name'] = $data['patient_name'];
        $param['sex'] = $data['sex'] == '男' ? 1 : 2 ;
        $param['birthday'] = $data['birthday'];
        $param['idcard'] = $data['idcard'];
        $param['mobile'] = $data['mobile'];
        $param['province'] = AreaService::getIdByName($data['province'])?AreaService::getIdByName($data['province']):0; 
        $param['city'] =  AreaService::getIdByName($data['province'].$data['city'],2)?AreaService::getIdByName($data['province'].$data['city'],2):0 ;
        $param['username'] = $user_data['username'];
        $param['uid'] = $user_data['uid'];
        $param['doctor_name'] = $data['doctor_name'];
        $param['doctor_uid'] = $data['doctor_uid'];
        $param['file_num'] = $data['file_num'];
      
        CLog::debug("oldPatient param[%s]",var_export($param,true));
        $add_old_patient_ret = $oldPatientService -> addOldPatient($param);
        
        }
        
        $add_follow_ret = $widgetApi->addRelation($user_data['uid'], $data['doctor_uid'],0,$patient_id);
        return true;
            
    }

    //数据验证，姓名，性别，出生日期，电话，医生姓名，医生id不能为空
    public static function checkUserData($data)
    {
        $single  = true;
        if(!isset($data['patient_name']) || empty($data['patient_name']))
        {
            $single = false;
        }

        if(!isset($data['sex']) || empty($data['sex']))
        { 
            $single = false;
        }

        if(!isset($data['birthday']) || empty($data['birthday']))
        {   
            $single = false;
        }

        if(!isset($data['mobile']) || empty($data['mobile']))
        {
            $single = false;
        }
    
        if(!isset($data['doctor_name']) || empty($data['doctor_name']))
        {
            $single = false;   
        }
        
        if(!isset($data['doctor_uid']) || empty($data['doctor_uid']))
        {
            $single = false;
        }

        return $single;
    }


    //检测电话号码是否存在，并将存在的电话号码和用户绑定
    public static  function checkMobile($mobile)
    {
        
        if(empty($mobile))
        {
            return false;
        }

        //检查电话号码是否存在
        $user_service = UserService::getInstance();
        $passport_sapi = new PassportSapi(CommonConst::HOST_PASSPORT);   
        $mobile_ret = $user_service->find(array('mobile'=>$mobile),array(),true);
   
        //将电话号码绑定手机号
        if(!empty($mobile_ret))
        {
            $ret = $passport_sapi->updateuserinfo($mobile_ret['uid'],json_encode(array('mobile'=>$mobile))); 
            if($ret){ 
                return $mobile_ret; 
            }else
            {
                return false;
            }
        }else{
            return false;
        }

    }
	
	/**
	 * 导入发放爱心币的excel
	 * @author jll
	 */
	public static function importLove($path=''){
		if(!is_file($path)){return false;}
		
		require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');
		
		$PHPExcel  = new PHPExcel();	
		$PHPReader = new PHPExcel_Reader_Excel2007(); 		
		if(!$PHPReader->canRead($path)){						
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($path)){						
				return false;
			}
		}
		$PHPExcel     = $PHPReader->load($path);
		$currentSheet = $PHPExcel->getSheet(0);
		//取得一共有多少列
		$allColumn = $currentSheet->getHighestColumn();
		//取得一共有多少行
		$allRow    = array($currentSheet->getHighestRow());

		//删除尾部空白行
		$row = $allRow[0];
		for ($i=$allRow[0]; $i>1; $i--) { 
			$uidValue  =  $currentSheet->getCell('A'.$i)->getValue();
			if (is_null($uidValue) || empty($uidValue)) {
				$row--;
			} else {
				break;
			}
		}

		// if($row > 1001){
		// 	//删除 上传文件
		// 	@unlink($path);
		// 	return '内容条数超过100条!';
		// }

		for($currentRow = 2;$currentRow<=$row;$currentRow++){
			if($currentRow > self::MAXNUM)break;
			$tmp_depart = '';
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				$address= $currentColumn.$currentRow;
				$value  =  $currentSheet->getCell($address)->getValue()."\t";
				$value = trim(strip_tags($value));
				if(!empty($value)){
					switch($currentColumn){
						case 'A': 
						$key='username';
						break;
						case 'B':
						$key = 'num';
						break;
						case 'C':
						$key = 'reason';
						break;
					}
					$paData[$currentRow-2][$key] = $value;
				}
				
			}
		}
		foreach($paData as $k=>&$v){

			if (empty($v['username']) || is_null($v['username']) || !isset($v['username'])) {
				break;
			}

			$ret[] = self::insertLove($v);
			
			if($k%50 == 0){
				sleep(1);
			}
		}

		return $ret;
	}
	/**
	 *	插入爱心币数据
	 * @author jll
	 */
	public static function insertLove($data){
		$username = trim($data['username']);
		$user_service = UserService::getInstance();
		// $where = " AND `username`='$username' or `zname`='$username' ";
		$where = " AND `username`='$username' ";
		$user_lists = $user_service->getList($where, 0, 10);
		$passport_sapi = new PassportSapi(CommonConst::HOST_PASSPORT);
		if(!empty($user_lists)){
			$uid = $user_lists[0]['uid'];
			$userinfo = $user_service->getUserInfoByUid($uid);                
			$result = $passport_sapi->addCurrencyLog($uid,$data['num'],$data['reason'],1,$_SESSION[AdminConfig::USER_AUTH_KEY],CommonConst::MODULE_ADMIN_ID,1);
			if($result['errno']==0 && $result['result'] == true){
				$info['status'] = true;
				$info['info'] = $username."发放爱心币成功";
			}else{
				$info['status'] = false;
				$info['info'] = $username.'存在,但发放爱心币失败';
			}
		}else{
			$info['status'] = false;
			$info['info'] = $username.'不存在,发放爱心币失败';
		}
		Clog::debug("ImportExcels insertLove info[%s] result[%s]",var_export($info,true),var_export($result,true));
		return $info;
	}

	/**
	 * 导入keywords
	 * @author ：zhangyu (562572613@qq.com)
	 */
	public static function importTaskPfKeyWords($uploadfile=''){

		require_once(dirname(__FILE__) .'/../excel/PHPExcel.php');

		// 建立reader对象
		$PHPReader = new PHPExcel_Reader_Excel2007();
		if(!$PHPReader->canRead($uploadfile)){
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($uploadfile)){
				return 0;
			}
		}
		//建立excel对象，此时你即可以通过excel对象读取文件，也可以通过它写入文件
		$PHPExcel = $PHPReader->load($uploadfile);
		/**读取excel文件中的第一个工作表*/
		$currentSheet = $PHPExcel->getSheet(0);
		/**取得最大的列号*/
		$allColumn = $currentSheet->getHighestColumn();
		/**取得一共有多少行*/
		$allRow = $currentSheet->getHighestRow();
		$result = array();
		//循环读取每个单元格的内容。注意行从1开始，列从A开始
		for($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++){
			for($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++){
				$addr = $colIndex.$rowIndex;
				$cell = $currentSheet->getCell($addr)->getValue();
				if($cell instanceof PHPExcel_RichText){
					$cell = $cell->__toString();  //富文本转换字符串
				}
				if(!$cell) {
					continue;
				}
				$result[] = $cell;
			}
		}

		return $result;
	}
}
