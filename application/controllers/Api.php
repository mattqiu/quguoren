<?php
/**
**
**
**/
defined('BASEPATH') OR exit('No direct script access allowed');


include_once(dirname(dirname(dirname(__FILE__))).'/360safe/360webscan.php');


class Api extends Application 
{
	
	 public function __construct() 
	 {
		 // Do something with $params
		 parent::__construct();
//		 $_SESSION['uid']=2;

		//验证访问来源 非本站不可访问 或非允许链接不可访问
		 if( $_SERVER['HTTP_REFERER'] )
		 {		  
		 	if($this->uri->segment(2) != 'pay' )
			{
				$res = $this->_check_from( $_SERVER['HTTP_REFERER'] );
	
				if( !$res )
				{
					echo '-1';exit;
				}
			}
		 }
     }
	

    /*
	*	@保存用户头像接口
	*	@guoliang
	*	@2017年2月4日
	*/
	public function update_user_icon()
	{
		unset($_REQUEST['api/update_user_icon']);
		$postdata = html_filter_array($_REQUEST);
		$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
		if( $postdata['action'] == 'update_user_icon' )
		{
			if( $postdata['image'] )
			{
				$res1 = preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result1);
				
				$ext = $result1[2];						
				if( $ext == 'jpeg' )
				{
					$ext = 'jpg';
				}	
				$targetFolder = './uploads/'.date('Ymd');			
				make_dirs($targetFolder);		
				$img_folder = './uploads/'.date('Ymd');
				$filename = $img_folder.'/'.md5(date('Ymdhis').uniqid()).'.'.$ext;
								
				if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result))
				{
					$image = str_replace($result[1], '', $_POST['image']);			 
				}		
				else
				{
					$image = $_POST['image'];
				}							
				
				$upload_file = $this->Common->base64_to_img( $image,$filename );	
									
				if( $upload_file )
				{	
					$upload_file = str_replace('./','/',$upload_file);	
					
					$update[$type] = $upload_file;
					
					$res = $this->Common->update( $this->user_table, array('id'=>$postdata['id']),$update );
					if( $res )
					{
						$return_arr['status'] = 1;
						$return_arr['msg'] = '更新头像成功';
						$return_arr['image'] = $this->api_url.$upload_file;
					}
					else
					{
						$return_arr['status'] = 0;
						$return_arr['msg'] = '更新头像失败';
					}
				}
				else
				{
					$return_arr['status'] = 0;
					$return_arr['msg'] = '更新头像失败';					
				}
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
/*		$uid = $_SESSION['uid'];

		$postinfo = html_filter_array($_POST);
		if(!empty($_FILES['icon']['name'])){
			$icon = $this->Common->do_upload('icon');
		}
		if(!empty($uid) && !empty($icon)){
			
			$res = $this->Common->update("gr_users",array('id'=>$uid),array("icon"=>$icon));

			echo $res===true?1:-1;
			
		}else{
			echo 0;
		}
		exit;*/
	}


    /*
	*	@更新个人信息
	*	@guoliang
	*	@2017年2月4日
	*/
	public function update_user_info()
	{
		$uid = $_SESSION['uid'];

		$postinfo = html_filter_array($_POST);

		if(!empty($uid) && !empty($postinfo)){
			
			$res = $this->Common->update("gr_users",array('id'=>$uid),$postinfo);

			echo $res===true?1:-1;
			
		}else{
			echo 0;
		}
		exit;
	}


	 /*
	*	@保存个人相册
	*	@guoliang
	*	@2017年2月4日
	*/
	public function add_photo()
	{
/*		$uid = $_SESSION['uid'];

		$postinfo = html_filter_array($_POST);

		if(!empty($_FILES['photo']['name'])){
			$photo = $this->Common->do_upload('photo');
		}
		if(!empty($uid) && !empty($photo)){
			
			$this->Common->add("gr_user_photo",array('uid'=>$uid,'pic'=>$photo,'ctime'=>time()));
			
			echo 1;
			
		}else{
			echo 0;
		}
		exit;*/
		unset($_REQUEST['api/add_photo']);
		
		$postdata = html_filter_array($_REQUEST);
		$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
		
		if( $postdata['action'] == 'add_photo' )
		{
			if( $postdata['image'] )
			{
				$_POST['image'] = str_replace(' ','',$_POST['image']);
				$res1 = preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result1);
				
				$ext = $result1[2];						
				if( $ext == 'jpeg' )
				{
					$ext = 'jpg';
				}	
				$targetFolder = './uploads/'.date('Ymd');			
				make_dirs($targetFolder);		
				$img_folder = './uploads/'.date('Ymd');
				$filename = $img_folder.'/'.md5(date('Ymdhis').uniqid()).'.'.$ext;
								
				if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result))
				{
					$image = str_replace($result[1], '', $_POST['image']);			 
				}		
				else
				{
					$image = $_POST['image'];
				}							
				
				$upload_file = $this->Common->base64_to_img( $image,$filename );	
									
				if( $upload_file )
				{	
					$upload_file = str_replace('./','/',$upload_file);	
					
					$insert['uid'] = $postdata['id'];
					$insert['pic'] = $upload_file;
					$insert['ctime'] = time();
					$insert['if_show'] = 1;
					
					if( $postdata['list_num'] )
						$insert['list_num'] = $postdata['list_num'];
					$res = $this->Common->add( $this->photo_table, $insert );
					
					if( $res )
					{
						$return_arr['status'] = 1;
						$return_arr['msg'] = '上传成功';
						$return_arr['image'] = $this->api_url.$upload_file;
					}
					else
					{
						$return_arr['status'] = 0;
						$return_arr['msg'] = '上传失败';					
					}
				}	
				else
				{
					$return_arr['status'] = 0;
					$return_arr['msg'] = '图片上传失败';
				}
				
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '获取图片失败';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';			
		}
		echo json_encode($return_arr);
		exit;
	}

	/*
	*	@删除个人相册
	*	@guoliang
	*	@2017年2月4日
	*/
	public function delete_photo()
	{
		unset($_REQUEST['api/delete_photo']);
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'delete_photo' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			if( is_array($postdata['ids']) )
			{
				foreach( $postdata['ids'] as $value )
				{
					$where = array(
						'id' => $value,
						'uid' => $postdata['uid'],
						);
					$res = $this->Common->update( $this->photo_table, $where, array('if_show'=>0) );					
				}
			}
			else
			{
				$where = array(
					'id' => $postdata['ids'],
					'uid' => $postdata['uid'],
					);
				$res = $this->Common->update( $this->photo_table, $where, array('if_show'=>0) );
			}
			if($res )
			{
				$return_arr['status'] = 1;
				$return_arr['msg'] = '删除成功';					
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '删除失败';				
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';			
		}
		echo json_encode($return_arr);
		exit;
		
	}


	/*
	*	@添加关注,活动
	*	@guoliang
	*	@2017年1月26日
	*/
	public function add_guanzhu()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'add_guanzhu' )
		{
			$ip = $this->Common->get_real_ip();
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			if(!empty($postdata['uid']) && !empty($postdata['zhubo_id']))
			{
				$if_guanzhu = $this->Common->get_one("gr_guanzhu",array('uid'=>$postdata['uid'],'zhubo_id'=>$postdata['zhubo_id']));
				if(!empty($if_guanzhu))
				{
					$res = $this->Common->delete("gr_guanzhu",array('id'=>$if_guanzhu['id']));
					if($res )
					{
						$return_arr['status'] = 1;
						$return_arr['msg'] = '取消关注成功';
					}
					else
					{
						$return_arr['status'] = 0;
						$return_arr['msg'] = '取消关注失败';						
					}
				}
				else
				{
					$res = $this->Common->add("gr_guanzhu",array('uid'=>$postdata['uid'],'zhubo_id'=>$postdata['zhubo_id'],'ctime'=>time()));
					if($res>0)
					{
						//记录此主播的积分
						$jf['uid'] = $postdata['zhubo_id'];
						$jf['jifen'] = 1;
						$jf['ctime'] = time();
						$jf['reason'] = 'get attention';
						$this->Common->add('gr_jifen',$jf);
						
						$return_arr['status'] = 1;
						$return_arr['msg'] = '添加关注成功';
					}
					else
					{
						$return_arr['status'] = 0;
						$return_arr['msg'] = '添加关注失败';						
					}
				}
				$this->Quguoren->update_fans_num($zhubo_id);
				

			}
			else
			{
				$return_arr['status'] = -1;
				$return_arr['msg'] = 'empty uid or zbid';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;			
		
		
	}



	/*
	*	@报名活动
	*	@guoliang
	*	@2017年1月26日
	*/
	public function join_info()
	{
		$uid = $_SESSION['uid'];
		$postinfo = html_filter_array($_POST);
		$info_id = $postinfo['info_id'];
		if(!empty($uid) && !empty($info_id)){
			//已经报名
			$if_join = $this->Common->get_one("gr_joininfo",array('uid'=>$uid,'info_id'=>$info_id));
			if(!empty($if_join)){
				$this->Common->delete("gr_joininfo",array('id'=>$if_join['id']));
				echo -1;
			}else{
				$insert_id = $this->Common->add("gr_joininfo",array('uid'=>$uid,'info_id'=>$info_id,'ctime'=>time()));
				if($insert_id>0)echo 1;
				else echo 2;
			}
		}else{
			echo 0;
		}
		exit;
	}


	/*
	*	@接受主播的报名申请
	*	@guoliang
	*	@2017年1月26日
	*/
	public function accept_info()
	{
		$uid = $_SESSION['uid'];

		//核对这个人是否是当前活动的发起方

		$postinfo = html_filter_array($_POST);
		$info_id = $postinfo['info_id'];
		$zhubo_id = $postinfo['zhubo_id'];

		$if_info = $this->Common->get_one("gr_postinfo",array('uid'=>$uid,'id'=>$info_id));
		if(!empty($if_info)){
			echo -2;
		}else{
			if(!empty($zhubo_id) && !empty($info_id)){
				//已经报名
				$if_join = $this->Common->get_one("gr_joininfo",array('uid'=>$zhubo_id,'info_id'=>$info_id));
				if(empty($if_join) || $if_join['if_accept']==1){
					$res = $this->Common->update("gr_joininfo",array('id'=>$if_join['id']),array('if_accept'=>0,'accept_time'=>''));
					echo $res===true?0:-1;
				}else{
					$res = $this->Common->update("gr_joininfo",array('id'=>$if_join['id']),array('if_accept'=>1,'accept_time'=>time()));
					echo $res===true?1:-1;
				}
			}else{
				echo 0;
			}
		}

		
		exit;
	}

	/*
	*	@标记主播完成当前活动
	*	@guoliang
	*	@2017年1月26日
	*/
	public function finish_info()
	{
		$uid = $_SESSION['uid'];

		//核对这个人是否是当前活动的发起方

		$postinfo = html_filter_array($_POST);
		$info_id = $postinfo['info_id'];
		$zhubo_id = $postinfo['zhubo_id'];

		$if_info = $this->Common->get_one("gr_postinfo",array('uid'=>$uid,'id'=>$info_id));
		if(!empty($if_info)){
			echo -2;
		}else{
			if(!empty($zhubo_id) && !empty($info_id)){
				//已经报名
				$if_join = $this->Common->get_one("gr_joininfo",array('uid'=>$zhubo_id,'info_id'=>$info_id));
				if(empty($if_join) || $if_join['if_finish']==1){
					$res = $this->Common->update("gr_joininfo",array('id'=>$if_join['id']),array('if_finish'=>0,'finish_time'=>''));
					echo $res===true?0:-1;
				}else{
					$res = $this->Common->update("gr_joininfo",array('id'=>$if_join['id']),array('if_finish'=>1,'finish_time'=>time()));
					echo $res===true?1:-1;
				}
				$this->Quguoren->update_service_num($zhubo_id);
			}else{
				echo 0;
			}
		}

		
		exit;
	}





	/*
	*	@发起评论，对主播评论
	*	@guoliang
	*	@2017年1月26日
	*/
	public function add_comment()
	{
		
		$postinfo = html_filter_array($_POST);
		$zhubo_id = $postinfo['zhubo_id'];
		$star_num = $postinfo['star_num'];
		$content = $postinfo['content'];
		$info_id = $postinfo['info_id'];
		$uid = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];//$_SESSION['uid'];
		$ip = $this->Common->get_real_ip();

		if(!empty($uid) && !empty($zhubo_id)){
			//一个人只能一天内评论另一个人3次
			$start = strtotime(date("Y-m-d")." 00:00:00");
			$end= strtotime(date("Y-m-d")." 23:59:59");
			$if_support= $this->Common->get_count("gr_comments",array('uid'=>$uid,'zhubo_id'=>$zhubo_id,'ctime>='=>$start,'ctime<='=>$end));
			if($if_support>=3){
				echo -1;
			}else{
				$insert_id = $this->Common->add("gr_comments",array(
					'uid'=>$uid,
					'zhubo_id'=>$zhubo_id,
					'info_id' => $info_id,
					'star_num'=>$star_num,
					'content'=>$content,
					'ctime'=>time()));
				//update join info status as finish
				$this->Common->update($this->join_table, array('uid'=>$uid,'info_id'=>$info_id),array('if_finish'=>1,'finish_time'=>time()));
				if($insert_id>0)echo 1;
				else echo 2;
			}
		}else{
			echo 0;
		}
		exit;
	}


	/*
	*	@点赞，点赞给主播
	*	@guoliang
	*	@2017年1月26日
	*/
	public function add_support()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'add_support' )
		{
			$ip = $this->Common->get_real_ip();
			
			if(!empty($postdata['uid']) && !empty($postdata['zhubo_id']))
			{
				//检测当前用户的积分
				$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
				$userinfo = $this->Common->get_one( $this->user_table, array('id' => $postdata['uid']) );
				if( $userinfo['integral'] > 0 )
				{
					//保存点赞信息
											
					//查询赞的数量
					$count = $this->Common->get_count($this->support_table,array('uid'=>$postdata['uid'],'zhubo_id'=>$postdata['zhubo_id']) );
					

					//已经点赞过10次则今天不能点赞
					$start = strtotime(date("Y-m-d")." 00:00:00");
					$end= strtotime(date("Y-m-d")." 23:59:59");
					$if_support= $this->Common->get_count("gr_support",array('uid'=>$postdata['uid'],'zhubo_id'=>$postdata['zhubo_id'],'ctime>='=>$start,'ctime<='=>$end));
					if( $if_support >= 10 )
					{
						$return_arr['status'] = 0;					
						$return_arr['msg'] = '点赞次数过多';
					}
					else
					{					
						$insert_id = $this->Common->add("gr_support",array('uid'=>$postdata['uid'],'zhubo_id'=>$postdata['zhubo_id'],'ip'=>$ip,'ctime'=>time()));
						if( $insert_id )
						{
							$count = $this->Common->get_count($this->support_table,array('uid'=>$postdata['uid'],'zhubo_id'=>$postdata['zhubo_id']) );
							//点赞成功 扣除用户的积分 -1
							$sql = "UPDATE ".$this->user_table." SET integral = integral-1 WHERE id=".$postdata['uid'];
							$this->Common->get_sql( $sql,'update' );
							//记录积分的消耗
							$reason = '点赞给用户:'.$userinfo['id'].'-'.$userinfo['nickname'];
							$this->_log_integral($postdata['uid'],'-1',$reason);							
							$return_arr['status'] = 1;
							$return_arr['msg'] = '点赞成功';
							$return_arr['count'] = $count;
						}
					}						
									
				}
				else
				{
					$return_arr['status'] = 0;
					$return_arr['msg'] = '积分不足';
				}

			}
			else
			{
				$return_arr['status'] = -1;
				$return_arr['msg'] = 'empty uid or zbid';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;		
	}

	/*
	*	@收藏某个活动,取消收藏
	*	@guoliang
	*	@2017年1月26日
	*/
	public function add_favor()
	{
		$uid = $_SESSION['uid'];
		$postinfo = html_filter_array($_POST);
		$info_id = $postinfo['info_id'];

		if(!empty($uid) && !empty($info_id)){
			//已经收藏
			$if_favor = $this->Common->get_one("gr_favor",array('uid'=>$uid,'info_id'=>$info_id));
			if(!empty($if_favor)){
				$this->Common->delete("gr_favor",array('id'=>$if_favor['id']));
				echo -1;
			}else{
				$insert_id = $this->Common->add("gr_favor",array('uid'=>$uid,'info_id'=>$info_id,'ctime'=>time()));
				if($insert_id>0)echo 1;
				else echo 2;
			}
		}else{
			echo 0;
		}
		exit;
	}


	/*
	*	@添加标签
	*	@guoliang
	*	@2017年2月4日
	*/
	public function add_tag()
	{
		unset($_REQUEST['api/add_tag']);
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'add_tags' && $postdata['uid'] )
		{
			$uid = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$tags = explode(',',$postdata['tag_id']);
			
			$res = $this->Common->update( $this->user_table,array('id'=>$uid),array('tags'=>json_encode($tags)) );
			if( $res )
			{
				$return_arr['status'] = 1;
				$return_arr['tags'] = $tags;
				$return_arr['msg'] = '标签添加成功';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '标签添加失败';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;		
	}


	/*
	*	@取消标签
	*	@guoliang
	*	@2017年2月4日
	*/
	public function delete_tag()
	{
		unset($_REQUEST['api/delete_tag']);
		$uid = $_SESSION['uid'];
		$postinfo = html_filter_array($_POST);
		$tag_id = $postinfo['tag_id'];

		if(!empty($uid) && !empty($tag_id)){
			//已经收藏
			$res = $this->Common->get_one("gr_users",array('id'=>$uid),'tags');
			$all_tags = $res['tags'];
			if(!empty($all_tags)){
				$tags = json_decode($all_tags,true);
				$tags2[] = $tag_id;
				$tags = array_diff($tags,$tags2);
				$all_tags = json_encode($tags);
			}
			
			$res = $this->Common->update("gr_users",
				array('id'=>$uid),array("tags"=>$all_tags));
			//echo $this->db->last_query();exit;
			echo $res===true?1:-1;

		}else{
			echo 0;
		}
		exit;
	}






	 
	/*
	*	@发送手机验证码
	*	@guoliang
	*	@2017年1月26日
	*/
	public function send_sms(){

		$postinfo = html_filter_array($_POST);
		//检测只有本网站才能调用当前


		$telephone = $postinfo['telephone'];

		echo 1;exit;
	}


	/*
	*	@验证手机验证码
	*	@guoliang
	*	@2017年1月26日
	*/
	public function check_sms(){
		$postinfo = html_filter_array($_POST);
		$telephone = $postinfo['telephone'];
		$vcode = $postinfo['vcode'];


		echo 1;exit;
	}
	
	private function _check_from( $from_url = '' )
	{
		$url = base_url();
		$urls = array(
			0 => $url,
			);
	
		foreach( $urls as $value )
		{
			if( strstr($from_url,$value) && $if_can == false )
			{
				$if_can = true;
			}				
		}
	
		return $if_can;
	}
	
	/*
	*	@登录
	*	@yinting
	*	@2017年2月8日
	*/
	public function login()
	{
		unset($_REQUEST['api/login']);
		$postdata = html_filter_array($_REQUEST);
	
		if( $postdata['action'] == 'login' )
		{
			//Step 1 : 检测用户名密码
			$where = array(
				'telephone' => $postdata['telephone'],				
				);
			$userinfo = $this->Common->get_one( $this->user_table, $where );
			if( $userinfo )
			{
                $password_verify = password_verify($postdata['password'], $userinfo['password']);  
				 if( !$password_verify )	//验证失败 
				 {
					 $return_arr['status'] = 0;
					 $return_arr['msg'] = '密码不正确';
				 }
				 else
				 {
					 $return_arr['status'] = 1;
					 $return_arr['msg'] = '登录成功';
					 $return_arr['uid'] = $userinfo['id'];
					 $return_arr['nickname'] = $userinfo['nickname'];
					 $return_arr['telephone'] = $userinfo['telephone'];
					 $return_arr['icon'] = $userinfo['icon'];
					 $return_arr['wx_openid'] = $userinfo['wx_openid'];
					 $return_arr['gr_type'] = $userinfo['gr_type'];
					 $_SESSION['uid'] = $userinfo['id'];
				 }
			}
			else
			{
				 $return_arr['status'] = 0;
				 $return_arr['msg'] = '手机号尚未注册';
			}
		}
		else
		{
			 $return_arr['status'] = 0;
			 $return_arr['msg'] = 'action error';
		}
					 
		 echo json_encode($return_arr);
		 exit;		
	}
	
	/*
	*	@动态检测手机号是否注册接口
	*	@yinting
	*	@2017年2月10日
	*/	
	public function check_mobile()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'check_mobile' )
		{
			//Step 1 : 查询手机号是否存在
			$where = array(
				'telephone' => $postdata['telephone'],
				);
			$data = $this->Common->get_one( $this->user_table, $where );
			if( $data )
			{
				echo 1;exit;	//手机号存在
			}
			else
			{
				echo 0;exit; //手机号不存在
			}
		}
		else
		{
			echo '-1';exit;
		}
	}
	
	/*
	*	@动态检测手机号验证码接口
	*	@yinting
	*	@2017年2月10日
	*/	
	public function check_msg_code()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'check_msg_code' )
		{
			//Step 1 : 查询手机号是否存在
			$time = date('Y-m-d H:i:s',strtotime('-20 minute'));
			$time = strtotime($time);
			$where = array(
				'telephone' => $postdata['telephone'],
				'code' => $postdata['code'],
				'ctime >=' => $time,
				'if_show' => 1,
				);
			$data = $this->Common->get_one( $this->sms_table, $where );
			if( $data )
			{				
				echo 1;exit;	//验证码正确
			}
			else
			{
				echo 0;exit; //验证码不正确或已过期
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	

	/*
	*	@发送手机验证码接口
	*	@yinting
	*	@2017年2月10日
	*/	
	public function send_mobile_code()
	{	
		if( $_REQUEST['action'] == 'send_mobile_code' && $_REQUEST['telephone'])
		{			
			$postdata = html_filter_array($_REQUEST);			
			//Step 1 : 生成验证码
			$ychar = "0,1,2,3,4,6,7,8,9";  
			$list = explode(",",$ychar);  
			for( $i=0; $i<4; $i++ )
			{  
				$randnum = mt_rand( 0,8 );							  
				$authnum .= $list[$randnum];  
			}  
		
			//Step 3: 发送手机验证码
			$template_code = $this->alidayu_sms_code;				
			$appkey = $this->alidayu_app_id;
			$secretKey = $this->alidayu_app_secrect;
			
			$this->load->library('Alidayu');				
			$c = new TopClient;
			
			$c->appkey = $appkey;
			$c->secretKey = $secretKey;
			$req = new AlibabaAliqinFcSmsNumSendRequest;
			$req->setExtend("123456");
			$req->setSmsType("normal");
			$req->setSmsFreeSignName($this->alidayu_sms_sign);
			//$req->setSmsParam("{\"code\":\"1234\",\"product\":\"alidayu\"}");
			$params_str = json_encode(array('number'=>$authnum));
			$req->setSmsParam($params_str);
			$req->setRecNum($postdata['telephone']);
			$req->setSmsTemplateCode($template_code);		
			$resp = $c->execute($req);
				
			if($resp->code == 0) 
			{			
				$insert['mobile'] = $postdata['telephone'];
				$insert['code'] = $authnum;					
				$insert['ctime'] = time();
				$result = $this->Common->add( $this->sms_table, $insert );
				
				echo '发送验证码成功';exit;//验证码发送成功							
			}
			else
			{
				echo '发送验证码失败，请重试';exit;//验证码发送失败				
			}								
		}
		else
		{
			echo 'action error';exit; //action或手机号为空
		}										
	}	
	
	/*
	*	@注册接口
	*	@yinting
	*	@2017年2月10日
	*/	
	public function register()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'register' )
		{
			$insert = $postdata;
			unset($insert['action']);
			unset($insert['api/register']);
			
			//检测验证码
			$time = date('Y-m-d H:i:s',strtotime('-20 minute'));
			$time = strtotime($time);
			$where = array(
				'mobile' => $postdata['telephone'],
				'code' => $postdata['code'],
				'ctime >=' => $time,
				'if_show' => 1,
				);
			$code = $this->Common->get_limit_order( $this->sms_table, $where,0,1,'ctime','DESC' );			
			
			if( empty($code[0]) )
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '验证码不正确';
//				echo '-2'; exit; //手机验证码不正确
			}
			else
			{				
				if( $postdata['password'] != $postdata['repassword'] )
				{
					$return_arr['status'] = 0;
					$return_arr['msg'] = '两次密码不一致';
				}
				else
				{
					if( $postdata['invite_id'] == '' )
					{
						unset($insert['invite_id']);
					}
					$this->Common->update( $this->sms_table, array('id'=>$code[0]['id']),array('if_show'=>0)); //验证码验证通过后失效
					$insert['password'] = password_hash($postdata['password'],1);
					unset($insert['repassword']);
					unset($insert['code']);
/*					if( $postdata['gr_type'] == 1 )
					{					
						if( $_FILES['xushengzheng']['name'] )
						{
							$res = $this->Common->do_upload('xushengzheng');
							//图片上传成功
							if( !is_array($res) )
							{
								$postdata['xueshengzheng'] = $res;
							}
						}
					}
					else
					{					
						if( $_FILES['icon']['name'] )
						{
							$res = $this->Common->do_upload('icon');
							//图片上传成功
							if( !is_array($res) )
							{
								$postdata['icon'] = $res;
							}
						}																		
					}*/
					//注册送5积分	
					$insert['integral'] = 5;			
					log_message('error','REGISTER POST DATA:'.json_encode($insert));		
					$insert['icon']=empty($insert['icon'])?'/statics/img/tip_logo.png':$insert['icon'];	
					$insert['nickname']=empty($insert['nickname'])?'果仁儿':$insert['nickname'];					
					$res = $this->Common->add( $this->user_table, $insert );
					if( $res )
					{			
						if( $postdata['invite_id'] )
						{
							//记录新增的积分
							$jifen['uid'] = $postdata['invite_id'];
							$jifen['jifen'] = 10;
							$jifen['ctime'] = time();
							$jifen['reason'] = 'invite one';
							$this->Common->add( 'gr_jifen',$jifen);
							$sql = "UPDATE ".$this->user_table." SET integral = integral+10 WHERE id=".$postdata['invite_id'];
							$this->Common->get_sql($sql,'update');
						}
						//记录新用户积分获得
						$this->_log_integral($res,'5','注册');
						
						$return_arr['status'] = 1;
						$return_arr['msg'] = '注册成功';								
						$insert['uid'] = $res;
						$_SESSION['uid'] = $res;
						unset($insert['password']);
						$return_arr['data'] = $insert;

					}
					else
					{
						$return_arr['status'] = 0;
						$return_arr['msg'] = '注册失败';
					}
				}
			}
						
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;		
	}	
	 
	/*
	*	@忘记密码接口
	*	@yinting
	*	@2017年2月10日
	*/	
	public function forget_password()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'forget_password' && $postdata['telephone'] )
		{			
			//检测验证码
			$time = date('Y-m-d H:i:s',strtotime('-20 minute'));
			$time = strtotime($time);
			$where = array(
				'mobile' => $postdata['telephone'],
				'code' => $postdata['code'],
				'ctime >=' => $time,
				'if_show' => 1,
				);
			$code = $this->Common->get_limit_order( $this->sms_table, $where,0,1,'ctime','DESC' );			
			if( empty($code[0]) )
			{
				$return['status'] = 0;
				$return['msg'] = '验证码不正确';
			}
			else
			{
				$this->Common->update( $this->sms_table, array('id'=>$code[0]['id']),array('if_show'=>0)); //验证码验证通过后失效
				
				if( $postdata['password'] != $postdata['repassword'] )
				{
					$return['status'] = 0;
					$return['msg'] = '两次密码不一致';
				}
				else
				{
					$insert['password'] = password_hash($postdata['password'],1);
					$where = array(
						'telephone' => $postdata['telephone'],
						);
					$res = $this->Common->update( $this->user_table,$where, $insert );
					if( $res )
					{						
						$return['status'] = 1;
						$return['msg'] = '找回密码成功';						
					}
					else
					{
						$return['status'] = 0;
						$return['msg'] = '重置密码失败';						
					}
				}
			}						
		}
		else
		{
			$return['status'] = 0;
			$return['msg'] = 'action error';
		}
		echo json_encode($return);exit;
	}		 
	 
	/*
	*	@活动列表接口
	*	@yinting
	*	@2017年2月13日
	*	@参数： page第几页 action, city,status,category,orderby
	*/	
	public function event()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_event_list' )
		{
			//Step 1 : 查询所有活动
			$where = array(1=>1);
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0;
			$data = $this->Quguoren->get_all_info( $postdata,$page );
		
			if( $data )
			{
				//查询已报名人数
				foreach( $data as $value )
				{
					$info_id[] = $value['id'];
				}
				$sql = "SELECT count(*) as count, info_id FROM ".$this->join_table." WHERE if_accept=1 AND info_id IN(".implode(',',$info_id).") GROUP BY info_id";
				$join = $this->Common->get_sql( $sql );
				foreach( $data as $key => $value )
				{
					$data[$key]['start_time'] = date_md($value['start_time']);
					foreach( $join as $val )
					{
						if( $value['id'] == $val['info_id'] )
							$data[$key]['join_count'] = $val['count'];
						else
							$data[$key]['join_count'] = 0;
					}
					$data[$key]['userinfo'] = get_userinfo_by_uid($value['uid']);
					unset($data[$key]['userinfo']['telephone']);
				}
				$return_arr['data'] = $data;
				echo json_encode($return_arr);
				exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}		 
	 
	/*
	*	@活动详情接口
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, id
	*/	
	public function detail()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_event_detail' )
		{
			//Step 1 : 查询所有活动
			$data = $this->Quguoren->get_info( $postdata['id'] );
			
			if( $data )
			{
				//查询报名人数
				$sql = "SELECT count(*) as count FROM ".$this->join_table." WHERE info_id =".$postdata['id'];			
				$join = $this->Common->get_sql( $sql );					
				$data['join_count'] = $join[0]['count'];
				$join_zhubo = $this->Quguoren->get_join_users( $postdata['id'],0,7);
				foreach( $join_zhubo as $value )
					$uid[] = $value['uid'];
				if( $uid )
				{
					$sql = "SELECT id,nickname,icon FROM ".$this->user_table." WHERE id IN(".implode(',',$uid).")";
					$join_user = $this->Common->get_sql( $sql );
					foreach( $join_zhubo as $key => $value )
					{
						foreach( $join_user as $val )
						{
							if( $val['id'] == $value['uid'] )
							{
								$join_zhubo[$key]['nickname'] = $val['nickname'];
								$join_zhubo[$key]['icon'] = $val['icon'];
							}
						}
					}
				}
				$data['join_user'] = $join_zhubo;	

				//查询已加入人数
				$sql1 = "SELECT count(*) as count FROM ".$this->join_table." WHERE info_id =".$postdata['id'].' AND if_accept=1';			
				$accept = $this->Common->get_sql( $sql1 );					
				$data['accept_count'] = $accept[0]['count'];
				$accept_zhubo = $this->Quguoren->get_accept_users( $postdata['id'],0,7);
				
				foreach( $accept_zhubo as $value )
					$uid[] = $value['uid'];
				if( $uid )
				{
					$sql = "SELECT id,nickname,icon FROM ".$this->user_table." WHERE id IN(".implode(',',$uid).")";
					$join_user = $this->Common->get_sql( $sql );
					foreach( $accept_zhubo as $key => $value )
					{
						foreach( $join_user as $val )
						{
							if( $val['id'] == $value['uid'] )
							{
								$accept_zhubo[$key]['nickname'] = $val['nickname'];
								$accept_zhubo[$key]['icon'] = $val['icon'];
							}
						}
					}
				}				

				$data['accept_user'] = $accept_zhubo;	
					
				$return_arr['data'] = $data;
				echo json_encode($return_arr);
				exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	
	
	//获取活动报名列表 
	public function get_accept_list()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_accept_list' )
		{
			//Step 1 : 查询报名用户
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0;
			$sql = "SELECT u.id,u.icon,u.nickname,j.ctime FROM ".$this->user_table." as u,".$this->join_table." as j WHERE info_id=".$postdata['id']." AND uid=u.id AND if_accept=1 ORDER BY ctime ASC LIMIT ".$page.",".$this->per_page;
			$data = $this->Common->get_sql( $sql );
			
			if( $data )
			{
				foreach( $data as $key => $value )
					$data[$key]['ctime'] = date_md( $value['ctime'] );
					
				$return_arr['data'] = $data;
				$return_arr['status'] = 1;
				$return_arr['msg'] = 'success';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = 'empty result';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;		
	}
	
	
	//获取活动报名接受列表 
	public function get_signup_list()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_info_list' )
		{
			//Step 1 : 查询报名用户
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0;
			$sql = "SELECT u.id,u.icon,u.nickname,j.ctime FROM ".$this->user_table." as u,".$this->join_table." as j WHERE info_id=".$postdata['id']." AND uid=u.id  ORDER BY ctime ASC LIMIT ".$page.",".$this->per_page;
			$data = $this->Common->get_sql( $sql );
			
			if( $data )
			{
				foreach( $data as $key => $value )
					$data[$key]['ctime'] = date_md( $value['ctime'] );
					
				$return_arr['data'] = $data;
				$return_arr['status'] = 1;
				$return_arr['msg'] = 'success';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = 'empty result';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;		
	}
		
	/*
	*	@报名
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, uid 用户ID, info_id活动ID
	*/	
	public function signup()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'signup' )
		{
			//Step 1 : 查询活动信息
			$info = $this->Common->get_one( $this->event_table, array('id' => $postdata['info_id'],'status>='=>1) );
			if( $info['type'] == 2 )	//需要积分报名
			{
				//Step 2 : 查询用户积分
				$userinfo = $this->Common->get_one( $this->user_table, array('id' => $postdata['uid']) );
				
				if( $userinfo['integral'] < $info['integral'] ) //用户积分少于报名所需积分，提示积分不足
				{
					$return_arr['status'] = 0;
					$return_arr['msg'] = '积分不足';
				}				
			}

			//查询已报名人数是否达到上限
			//查询已加入人数
			$sql1 = "SELECT count(*) as count FROM ".$this->join_table." WHERE info_id =".$postdata['info_id'].' AND if_accept=1';			
			$accept = $this->Common->get_sql( $sql1 );					
			$accept_count = $accept[0]['count'];
			if( $accept_count == $info['need_num'] )
			{
				$return['status'] = 0;
				$return['msg'] = '已达到报名人数上限';
			}
							
			if( empty($return_arr) )
			{
				//Step 3 : 保存报名信息
				$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
				$insert['uid'] = $postdata['uid'];
				$insert['info_id'] = $postdata['info_id'];
				$insert['ctime'] = time();
				$res = $this->Common->add( $this->join_table, $insert );
				if( $res )
				{
					if( $info['type'] == 2 )
					{
						//积分报名需要扣除用户的积分
						$sql = "UPDATE ".$this->user_table." SET integral=integral-".$info['integral']." WHERE id=".$postdata['uid'];
						$this->Common->get_sql( $sql,'update' );
						$reason = '报名活动：'.$info['id'].'-'.$info['title'];
						$this->_log_integral( $postdata['uid'],$info['integral'],$reason);
						//报名成功 发送短信通知
						$this->_send_signup_msg( $userinfo['nickname'],$userinfo['telephone'],$info);
						//扣除积分后更新报名状态为已接受
						$this->Common->update( $this->join_table, array('id'=>$res),array('if_accept'=>1,'accept_time'=>time()));
					}
					else
					{
						//付费报名 生成订单
						$pay['uid'] = $postdata['uid'];
						$pay['out_trade_no'] = get_order_number();
						$pay['money'] = $info['single_price'];
						$pay['info_id'] = $info['id'];
						$pay['pay_time'] = time();
						$pay_id = $this->Common->add( 'gr_payment',$pay );		
						
						$return_arr['pay_id'] = $pay_id;				
					}
					
					$return_arr['status'] = 1;
					$return_arr['msg'] = 'success';
					$return_arr['infodata'] = $info;
					
					echo json_encode($return_arr);
					exit;
				}
				else
				{
					echo 0;exit;
				}
			}
			else
			{
				echo json_encode($return_arr);
				exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}			 
	
	/*
	*	@取消报名
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, uid 用户ID, info_id活动ID
	*/	
	public function cancel()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'cancel_signup' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//Step 1 : 取消报名信息
			$where = array(
				'uid' => $postdata['uid'],
				'info_id' => $postdata['info_id'],
				);
			$data = $this->Common->get_one( $this->join_table, $where );
			if( $data )
			{
				//取消报名操作
				$res = $this->Common->delete( $this->join_table, $where );
				if( $res )
				{
					echo 1;exit;
				}
				else
				{
					echo 0;exit;
				}
			}
			else
			{
				echo -2;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	
	 
	/*
	*	@接受报名
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, uid 用户ID, info_id活动ID
	*/	
	public function accept()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'accept_signup' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//Step 1 : 取消报名信息
			$where = array(
				'uid' => $postdata['uid'],
				'info_id' => $postdata['info_id'],
				);
			$data = $this->Common->get_one( $this->join_table, $where );
			if( $data )
			{
				//取消报名操作
				$res = $this->Common->update( $this->join_table, $where,array('if_accept'=>1,'accept_time' => time()) );
				if( $res )
				{
					echo 1;exit;
				}
				else
				{
					echo 0;exit;
				}
			}
			else
			{
				echo -2;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	
	
	/*
	*	@收藏活动
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, uid 用户ID, info_id活动ID
	*/	
	public function favor()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'favor' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//Step 1 : 收藏活动信息
			$insert = array(
				'uid' => $postdata['uid'],
				'info_id' => $postdata['info_id'],
				'ctime' => time(),
				);
			$res = $this->Common->add( $this->favor_table, $insert );

			if( $res )
			{
				echo 1;exit;
			}
			else
			{
				echo 0;exit;
			}

		}
		else
		{
			echo '-1';exit;
		}
	}		 

	/*
	*	@取消收藏活动
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, uid 用户ID, info_id活动ID
	*/	
	public function cancel_favor()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'cancel_favor' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//Step 1 : 取消收藏活动信息
			$where = array(
				'uid' => $postdata['uid'],
				'info_id' => $postdata['info_id'],
				);
			$data = $this->Common->get_one( $this->favor_table, $where );
			if( $data )
			{
				//取消收藏活动
				$res = $this->Common->delete( $this->favor_table, $where );
				if( $res )
				{
					echo 1;exit;
				}
				else
				{
					echo 0;exit;
				}
			}
			else
			{
				echo -2;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}		
	
	/*
	*	@发布活动
	*	@yinting
	*	@2017年2月13日
	*	@参数： action, uid 用户ID, info_id活动ID
	*/	
	public function release()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'release_event' )
		{
			//Step 1 : 发布活动
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$insert = $postdata;
			
			unset($insert['action']);
			unset($insert['api/release']);
			
			//$insert['start_time'] = strtotime($insert['start_time']);
			//$insert['end_time'] = strtotime($insert['endtime']);			
			$insert['ctime'] = time();
			
			$res = $this->Common->add( $this->event_table, $insert );
//log_message('error','RELEASE :'.$this->db->last_query());	
			if( $res )
			{
/*				//生成订单
				$pay['uid'] = $postdata['uid'];
				$pay['out_trade_no'] = get_order_number();
				$pay['money'] = $insert['need_num']*$insert['single_price']*0.1;
				$pay['info_id'] = $res;
				$pay['pay_time'] = time();
				$pay_id = $this->Common->add( 'gr_payment',$pay );*/
				
				$return_arr['id'] = $res;
				//$return_arr['pay_id'] = $pay_id;
				$return_arr['status'] = 1;
				$return_arr['msg'] = '发布活动成功，等待审核';
			}
			else
			{
				$return_arr['status'] = -1;
				$return_arr['msg'] = '发布活动失败，请重试';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;
	}	
	
	/*
	*	@关闭活动
	*	@yinting
	*	@2017年3月2日
	*	@参数： action,uid,info_id
	*/		
	public function close_event()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'close_event' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//Step 1 : 查询活动信息
			$where = array(
				'uid' => $postdata['uid'],
				'id' => $postdata['info_id'],
				
				);
			$res = $this->Common->get_one($this->event_table, $where );
			if( $res )
			{
				//关闭活动
				$res = $this->Common->update( $this->event_table, $where, array('status'=>'-1'));
				if( $res )
				{
					$return_arr['status'] = 1;
					$return_arr['msg'] = '活动已关闭';					
				}
				else
				{
					$return_arr['status'] = 0;
					$return_arr['msg'] = '关闭活动失败，请重试';					
				}
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '您无权关闭此活动';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;			
	}
	
	//获取支付信息
	public function get_pay_info( )
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_pay_info' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//Step 1 : 查询订单信息
			$where = array(
				'uid' => $postdata['uid'],
				'id' => $postdata['id'],
				
				);
			$res = $this->Common->get_one( 'gr_payment', $where );
//log_message('error','RELEASE :'.$this->db->last_query());	
			if( $res )
			{
				$return_arr['data'] = $res;
				$return_arr['status'] = 1;
				$return_arr['msg'] = 'success';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '暂无此订单';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;		
	}
	
	
	//修改活动浏览量
	public function set_view()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'release_view' )
		{
			//Step 1 : 更新浏览量
			$where = array(
				'id' => $postdata['id'],
				'status>' => 0,
				);
			$data = $this->Common->get_one( $this->event_table, $where );
			$update['view'] = $data['view']+1;
			$res = $this->Common->update( $this->event_table, $where, $update );
//log_message('error','RELEASE :'.$this->db->last_query());	
			if( $res )
			{
				$return_arr['status'] = 1;
				$return_arr['msg'] = '更新浏览量成功';
			}
			else
			{
				$return_arr['status'] = 1;
				$return_arr['msg'] = '更新浏览量失败';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;		
	}
	
	/*
	*	@上传图片
	*	@yinting
	*	@2017年2月13日
	*	@参数： action
	*/	
	public function upload_img()
	{
		$postdata = html_filter_array($_REQUEST);
		$path = dirname(dirname(dirname(__FILE__)));
		$path = str_replace('\\','/',$path);
		$type = $postdata['type'];
		if( $postdata['action'] == 'upload_image' )
		{
			//Base64解析图片
			if( $postdata['image'] )
			{
				$_POST['image'] = str_replace(' ','',$_POST['image']);
				$res1 = preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result1);
				
				$ext = $result1[2];						
				if( $ext == 'jpeg' )
				{
					$ext = 'jpg';
				}	
				$targetFolder = './uploads/'.date('Ymd');			
				make_dirs($targetFolder);		
				$img_folder = './uploads/'.date('Ymd');
				$filename = $img_folder.'/'.md5(date('Ymdhis').uniqid()).'.'.$ext;
								
				if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result))
				{
					$image = str_replace($result[1], '', $_POST['image']);			 
				}		
				else
				{
					$image = $_POST['image'];
				}							
				
				$upload_file = $this->Common->base64_to_img( $image,$filename );	
									
				if( $upload_file )
				{	
					
					if( $type != 'event' )
					{
						$upload_file = str_replace('./','/',$upload_file);	
						$update[$type] = $upload_file;
						$res = $this->Common->update( $this->user_table, array('id'=>$postdata['id']),$update );
					}
					else
					{
						//生成缩略图
						$upload_file = str_replace('./','/',$upload_file);
						$pathinfo = pathinfo($upload_file);
						
						$ext = $pathinfo['extension'];
						$filename = $pathinfo['filename'];
						$img_path = $pathinfo['dirname'];
							
						$path = dirname(dirname(__FILE__));
						$path = str_replace('\\','/',$path);	
						$file_base_path = dirname($path);
						
						$file = $file_base_path.str_replace('./','/',$upload_file);
						chmod($file_base_path.$img_path,0775);
						chmod($file,0775);
																	
						include_once $path.'/libraries/phpthumb/ThumbLib.inc.php';	
						$thumb = PhpThumbFactory::create($file); 
						if( $postdata['height'] != 9999 )
							$thumb->resize($postdata['width'],$postdata['height']);	
						else
							$thumb->resize($postdata['width']);
						$thumb->save( $file_base_path.$img_path.'/'.$filename.'_thumb.'.$ext);	
						
						$upload_file = str_replace($filename.'.'.$ext,$filename.'_thumb.'.$ext,$upload_file);	
						$upload_file = str_replace('./','/',$upload_file);		
										
					}
					$return_arr['status'] = 1;
					$return_arr['msg'] = '修改成功';
					$return_arr['image'] = $this->api_url.$upload_file;
					echo json_encode($return_arr);exit;	
				}
				else
				{
					$return_arr['msg'] = '上传失败，请重试';
					$return_arr['status'] = 0;
					echo json_encode($return_arr);exit;			
				}
			}
//			$image = '';
//			//Step 1 : 上传图片
//			if(!empty($_FILES['image']['name'])){
//				$image = $this->Common->do_upload('image');
//			}			
//				
//			if( !is_array($image) )
//			{				
//				echo $image;exit;
//			}
//			else
//			{
//				echo 0;exit;
//			}
		}
		else
		{
			$return_arr['msg'] = 'action error';
			$return_arr['status'] = 0;
			echo json_encode($return_arr);exit;		
		}
	}
	
	
	/*
	*	@我的收藏
	*	@yinting
	*	@2017年2月13日
	*	@参数： action
	*/	
	public function my_favor()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_favor' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			$data = $this->Quguoren->get_user_favor( $postdata['id'],$postdata['start'],$postdata['per_page']);
//print_r($this->db->last_query());exit;			
			if( $data )
			{
				foreach( $data as $value )
				{
					$event_ids[] = $value['info_id'];
				}
				$sql = "SELECT * FROM ".$this->event_table." WHERE id IN(".implode(',',$event_ids).")";
				$event = $this->Common->get_sql( $sql );
				
				$return_arr['data'] = $event;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	
	
	/*
	*	@我的关注
	*	@yinting
	*	@2017年2月13日
	*	@参数： action
	*/	
	public function my_follow()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_follow' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			$data = $this->Quguoren->get_user_guanzhu( $postdata['id'],$postdata['start'],$postdata['per_page']);
			if( $data )
			{
				foreach( $data as $value )
				{
					$zb_ids[] = $value['zhubo_id'];
				}
				$sql = "SELECT * FROM ".$this->user_table." WHERE id IN(".implode(',',$zb_ids).")";
				$users = $this->Common->get_sql( $sql );
				
				$return_arr['data'] = $users;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	
	
	
	/*
	*	@我的消息
	*	@yinting
	*	@2017年2月16日
	*	@参数： action
	*/	
	public function my_message()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_message' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			$res = $this->Quguoren->get_user_message($postdata['id']);
			if( $res)
			{						
				$return_arr['data'] = $res;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}	
	
	/*
	*	@查看消息
	*	@yinting
	*	@2017年2月14日
	*	@参数： action
	*/	
	public function get_message()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_message' )
		{
			//更新消息信息
		
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$res = $this->Quguoren->get_our_message( $postdata['uid'], $postdata['to_uid'] );
			
			if( $res )
			{						
				$return_arr['status'] = 1;
				$return_arr['data'] = $res;
				
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '暂无此消息';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}		
	
	/*
	*	@查看用户发送给主播的消息
	*	@yinting
	*	@2017年2月20日
	*	@参数： action
	*/	
	public function get_my_message()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_message' )
		{
			//查询消息信息	
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];		
			$start = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0 ;
			$sql = "SELECT * FROM ".$this->message_table." WHERE ((uid=".$postdata['id']." AND to_uid=".$postdata['zhubo_id'].") OR (uid=".$postdata['zhubo_id']." AND to_uid=".$postdata['id'].")) AND if_check=1 ORDER BY ctime ASC LIMIT ".$start.",".$this->per_page;
//print_r($sql);exit;			
			$res = $this->Common->get_sql($sql);
			if( $res )
			{						
				foreach( $res as $key => $value )
				{
					$res[$key]['ctime'] = get_date_content( $value['ctime'] );
				}
				$return_arr['status'] = 1;
				$return_arr['data'] = $res;
				
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '暂无此消息';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}		
	
	public function add_message()
	{
		unset($_REQUEST['api/add_message']);
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'add_message' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$insert = $postdata;
			unset($insert['action']);
			unset($insert['api/add_meeage']);
			$insert['if_check'] = 1;
			$insert['check_time'] = time();
			$insert['ctime'] = time();
			$res = $this->Common->add( $this->message_table, $insert );
			if( $res )
			{
				$return_arr['status'] = 1;
				$return_arr['msg'] = '发送消息成功';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '发送消息失败';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;
	}
	/*
	*	@修改消息
	*	@yinting
	*	@2017年2月14日
	*	@参数： action
	*/	
	public function update_message()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'update_message' )
		{
			//更新消息信息
			$where = array(
				'id' => $postdata['id'],
				);
			if( $_SESSION['uid'] )
				$where['uid'] = $_SESSION['uid'];
			$update = $postdata;
			unset($update['action']);
			unset($update['id']);
			unset($update['api/update_message']);
			$res = $this->Common->update( $this->message_table, $where, $update );
			if( $res )
			{						
				$return_arr['status'] = 1;
				$return_arr['msg'] = '更新成功';
				
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '更新失败';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}	
	
	/*
	*	@删除消息
	*	@yinting
	*	@2017年2月14日
	*	@参数： action
	*/	
	public function delete_message()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'delete_message' )
		{
			//删除消息信息
			$where = array(
				'id' => $postdata['id'],
				);
			if( $_SESSION['uid'] )
			{
				$where['uid'] = $_SESSION['uid'];
			}
			$res = $this->Common->delete( $this->message_table, $where );
			if( $res )
			{						
				$return_arr['status'] = 1;
				$return_arr['msg'] = '删除成功';
				
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '删除失败';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}				
	
	/*
	*	@我发布的活动
	*	@yinting
	*	@2017年2月14日
	*	@参数： action
	*/	
	public function my_event()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_event' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//查询我发布的活动
			$where = array( 'uid' => $postdata['uid']);
			if( $postdata['status'] )
				$where['status'] = $postdata['status'];
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0;
			$data = $this->Common->get_limit_order( $this->event_table, $where,$page, $this->per_page,'ctime','DESC');
//print_r($this->db->last_query());exit;			
			if( $data )
			{						
				//查询活动报名人数
				foreach( $data as $value )
					$info_ids[] = $value['id'];
				$sql = "SELECT info_id,count(*) as count FROM ".$this->join_table." WHERE info_id IN(".implode(',',$info_ids).") GROUP BY info_id";
				$join = $this->Common->get_sql( $sql );
				foreach( $data as $key => $value )
				{
					foreach( $join as $val )
					{
						if( $val['info_id'] == $value['id'] )
						{
							$data[$key]['join_count'] = $val['count'];
							if( $value['end_time'] < time() )
							{
								$data[$key]['if_finish'] = 1;
							}
							else
							{
								$data[$key]['if_finish'] = $val['if_finish'];
							}
						}
					}
				}
//print_r($data);exit;				
				$return_arr['data'] = $data;
				$return_arr['status'] = 1;
				$return_arr['msg'] = 'success';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = 'empty data';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}

		echo json_encode($return_arr);
		exit;		
	}		
	
	/*
	*	@我报名的活动
	*	@yinting
	*	@2017年2月14日
	*	@参数： action
	*/	
	public function my_join()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_join' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			//查询我报名的活动
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0;
			$sql = "SELECT j.*,title,en_title,start_time,end_time,city,en_city,e.ctime,e.status,j.uid,e.image FROM ".$this->join_table." as j, ".$this->event_table." as e WHERE j.if_accept=1 AND j.info_id = e.id AND e.status > 0 AND j.uid =".$postdata['id'];//." AND end_time>=".time();

			if( $postdata['type'] == 2 )	//募集中
			{
				$sql .= " AND e.status = 1 AND end_time >= ".time();
			}
			elseif( $postdata['type'] == 3 )	//待评价
			{
				//SELECT j.*,title,en_title,start_time,end_time FROM gr_joininfo as j,gr_postinfo as e WHERE j.info_id = e.id AND j.uid = 2 AND j.info_id NOT IN( SELECT info_id FROM gr_comments WHERE zhubo_id =2)
				$sql .= " AND j.info_id NOT IN (SELECT info_id FROM ".$this->comment_table." WHERE uid=".$postdata['id'].")";
			}
			elseif( $postdata['type'] == 4 )	//已完成
			{
				$sql .= " AND j.info_id IN (SELECT info_id FROM ".$this->comment_table." WHERE uid=".$postdata['id'].")";
			}
						
			$sql .= " ORDER BY end_time DESC LIMIT ".$page.",".$this->per_page;			
			$data = $this->Common->get_sql($sql);
//print_r($sql);exit;			
			if( $data )
			{					
				foreach( $data as $key => $value )
				{
					$data[$key]['title'] = $value['en_title'] ? $value['en_title'] : $value['title'];
					$data[$key]['city'] = $value['en_city'] ? $value['en_city'] : $value['city'];
					$data[$key]['start_time'] = date_md($value['start_time']).' '.date_hi($value['start_time']);
					$data[$key]['end_time'] = ymdhis($value['end_time']);
					$data[$key]['ctime'] = ymdhis($value['ctime']);
					$data[$key]['image'] = strstr($value['image'],'http') ? $value['image'] : $this->api_url.$value['image'];
					if( $value['end_time'] > time() )
						$data[$key]['status'] = '募集中';
					else
						$data[$key]['status'] = '已过期';
					//$data[$key]['status'] = $value['end_time'] > time() ? get_info_status($value['status']) : '';;
					$info_ids[] = $value['info_id'];
					
				}
				$sql = "SELECT * FROM ".$this->comment_table." WHERE info_id IN(".implode(',',$info_ids).") AND uid = ".$postdata['id'];
		
				$comments = $this->Common->get_sql( $sql );

				$sql = "SELECT u.id as info_uid,nickname as info_username,icon as info_usericon,e.id as info_id FROM ".$this->user_table." as u,".$this->event_table." as e WHERE e.uid = u.id AND e.id IN(".implode(',',$info_ids).")";
				$users = $this->Common->get_sql( $sql );

				foreach( $data as $key => $value )
				{
					foreach( $comments as $val )
					{
						if( $val['info_id'] == $value['info_id'] )
						{
							$data[$key]['if_comment'] = 1;
						}
					}
					foreach( $users as $val )
					{
						if( $val['info_id'] == $value['info_id'] )
						{
							$data[$key]['info_uid'] = $val['info_uid'];
							$data[$key]['info_username'] = $val['info_username'];
							$data[$key]['info_usericon'] = $val['info_usericon'];
						}
					}					
				}

//print_r($data);exit;
				$return_arr['data'] = $data;
				$return_arr['status'] = 1;
				$return_arr['msg'] = 'success';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = 'empty data';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		
		echo json_encode($return_arr);
		exit;
	}	
	
	public function get_myjoin_count()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_my_joincount' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			//查询我报名的活动个数
			$base_sql = $sql = "SELECT count(*) as count FROM ".$this->join_table." WHERE uid=".$postdata['id'];//." AND info_id IN( SELECT info_id FROM ".$this->event_table." WHERE status = 1) ";			
			//募集中
			$sql .= " AND info_id IN( SELECT info_id FROM ".$this->event_table." WHERE status = 1) ";
			$raise = $this->Common->get_sql( $sql );
			//进行中
			$sql = $base_sql." AND info_id IN( SELECT info_id FROM ".$this->event_table." WHERE status = 2) ";
			$ongoing = $this->Common->get_sql( $sql );
			//待评价
			$sql = $base_sql." AND info_id IN(SELECT info_id FROM ".$this->event_table." WHERE status = 3)";
			$pending = $this->Common->get_sql($sql);
			//已完成
			$sql = $base_sql." AND info_id IN(SELECT info_id FROM ".$this->event_table." WHERE status = 4)";
			$finish = $this->Common->get_sql($sql);
			
			$return_arr['status'] = 1;
			$return_arr['msg'] = 'success';
			$return_arr['raise'] = $raise[0]['count'] ? $raise[0]['count'] : 0;
			$return_arr['ongoing'] = $raise[0]['ongoing'] ? $raise[0]['ongoing'] : 0;
			$return_arr['pending'] = $raise[0]['pending'] ? $raise[0]['pending'] : 0;
			$return_arr['finish'] = $raise[0]['finish'] ? $raise[0]['finish'] : 0;
			
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		
		echo json_encode($return_arr);
		exit;		
	}
	
	/*
	*	@编辑个人资料
	*	@yinting
	*	@2017年2月13日
	*	@参数： action,id
	*/	
	public function update_userinfo()
	{
		unset($_REQUEST['api/update_userinfo']);
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'update_userinfo' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			$where = array(
				'id' => $postdata['id'],
				);
			$update = $postdata;
			unset($update['action']);
			unset($update['id']);
			unset($update['api/update_userinfo']);
			$res = $this->Common->update( $this->user_table, $where, $update );
			if( $res )
			{			
				$return_arr['status'] = 1;
				$return_arr['msg'] = '保存成功';
				
				echo json_encode($return_arr);exit;
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '保存失败';
				
				echo json_encode($return_arr);exit;
			}
		}
		else
		{
				$return_arr['status'] = 0;
				$return_arr['msg'] = 'action error';
				
				echo json_encode($return_arr);exit;
		}
	}	
	
	/*
	*	@账户积分
	*	@yinting
	*	@2017年2月13日
	*	@参数： action,id
	*/	
	public function integral()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_user_integral' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$where = array(
				'id' => $postdata['uid'],
				);
			$userinfo = $this->Common->get_one( $this->user_table, $where );
			
			//$sql = "SELECT SUM(jifen) as num FROM ".$this->integral_table." WHERE uid =".$postdata['uid'];
			
			//$res = $this->Common->get_sql( $sql );
			if( $userinfo )
			{
				$res[0]['num'] = $userinfo['integral'];
				echo json_encode($res);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}					 
	 	 
	/*
	*	@主播列表
	*	@yinting
	*	@2017年2月13日
	*	@参数： action
	*/	
	public function anchor_list()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'anchor_list' )
		{
			//判断用户是否登录，根据用户的性别及用户类型查询相应展示的主播
			if( $postdata['uid'] )
			{
				$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
				$userinfo = $this->Common->get_one( $this->user_table, array('id' => $postdata['uid']) );
				//当用户类型是学生且性别是男时，只查询女主播的排行
				if( $userinfo['sex'] == 1 && $userinfo['gr_type'] == 4 )
				{
					$postdata['sex'] = 2;
				}
			}
			$postdata['gr_type'] = 1;
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$this->per_page : 0;
			$data = $this->Quguoren->get_all_zhubo( $postdata, $page );
			
			if( $data )
			{
				$return_arr['data'] = $data;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}
	
	/*
	*	@主播详情
	*	@yinting
	*	@2017年2月14日
	*	@参数： action,id
	*/	
	public function anchor()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_author' )
		{
			$data = $this->Quguoren->get_user( $postdata['id'] );
			
			if( $data )
			{
				$return_arr['data'] = $data;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}
		
	/*
	*	@查看主播的评价
	*	@yinting
	*	@2017年2月14日
	*	@参数： action,id,page,per_page,type: 0全部 1好评 2中评 3差评
	*/	
	public function anchor_comment()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_authors_comment' )
		{
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$postdata['per_page'] : 0;

			$data = $this->Quguoren->get_user_comments( $postdata['id'],$page,$postdata['per_page'],$postdata['type'] );
			
			if( $data )
			{
				foreach( $data as $value )
				{
					$uids[] = $value['uid'];
					$info_ids[] = $value['info_id'];
				}
				$sql = "SELECT id,nickname,icon FROM ".$this->user_table." WHERE id IN(".implode(',',$uids).")";
				$users = $this->Common->get_sql( $sql );
				$sql = "SELECT id,title,en_title,start_time,end_time,ctime FROM ".$this->event_table." WHERE id IN(".implode(',',$info_ids).") AND if_check = 1";
				$info = $this->Common->get_sql( $sql );
				foreach( $data as $key => $value )
				{
					foreach( $users as $val )
					{
						if( $val['id'] == $value['uid'] )
						{
							$data[$key]['nickname'] = $val['nickname'];
							$data[$key]['icon'] = $val['icon'];
						}
					}
					foreach( $info as $val )
					{
						if( $val['id'] == $value['info_id'] )
						{
							$data[$key]['title'] = $val['title'];
							$data[$key]['en_title'] = $val['en_title'];
							$data[$key]['start_time'] = ymdhis($value['start_time']);
							$data[$key]['end_time'] = ymdhis($value['end_time']);
							$data[$key]['ctime'] = ymdhis($value['ctime']);
						}
					}					
				}
				$return_arr['data'] = $data;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}						 
	 	
	/*
	*	@搜索活动
	*	@yinting
	*	@2017年2月14日
	*	@参数： action,title活动标题
	*/	
	public function search()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'search_event' )
		{
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$postdata['per_page'] : 0;
						
			$data = $this->Quguoren->get_all_info( $postdata,$page);
			if( $data )
			{
				foreach( $data as $key => $value )
				{
					$data[$key]['start_time'] = date_md($value['start_time']);
					
					$data[$key]['userinfo'] = get_userinfo_by_uid($value['uid']);
					unset($data[$key]['userinfo']['telephone']);
				}
				$return_arr['data'] = $data;

				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}						 
	 	
	/*
	*	@搜索主播
	*	@yinting
	*	@2017年2月14日
	*	@参数： action,title活动标题
	*/	
	public function search_anthor()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'search_event' )
		{
			$postdata['gr_type'] = 1;
			$page = $postdata['page'] > 1 ? ($postdata['page']-1)*$postdata['per_page'] : 0;
						
			$data = $this->Quguoren->get_all_zhubo( $postdata,$page);
			
			if( $data )
			{
				$return_arr['data'] = $data;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}			

	/*
	*	@个人中心
	*	@yinting
	*	@2017年2月14日
	*	@参数： action,id
	*/	
	public function userinfo()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_userinfo' )
		{
			$data = $this->Quguoren->get_user( $postdata['id'] );
			
			if( $data )
			{
				$data['telephone'] = hide_email($data['telephone']);
				
				$res = $this->Quguoren->get_user_num( $data['id'] );
				$data['message_num'] = $res['message_num'];
				$data['favor_num'] = $res['favor_num'];
				$data['guanzhu_num'] = $res['guanzhu_num'];
				$data['photo_num'] = $res['photo_num'];
				//查询photo信息
				$photo = $this->Quguoren->get_all_photo( array('uid'=>$data['id'],'order_by'=>'ctime') );
				$data['photo'] = $photo;
				
				$return_arr['data'] = $data;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}
	
	/*
	*	@根据wx_openid查询用户是否存在
	*	@yinting
	*	@2017年2月17日
	*	@参数： action,wx_openid
	*/	
	public function get_user_info()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_user_info' )
		{
			$data = $this->Common->get_one( $this->user_table,array('wx_openid'=>$postdata['wx_openid']) );
			
			if( $data )
			{
				$return_arr['status'] = 1;				
				$return_arr['data'] = $data;
				
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '用户不存在';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}	
	
	/*
	*	@查询用户评份等级
	*	@yinting
	*	@2017年2月20日
	*	@参数： action,id
	*/	
	public function get_user_star()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_comment_star' )
		{
			$postdata['id'] = ($postdata['uid'] != $_SESSION['uid'])&&!empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['id'];
			//$sql = "SELECT AVG(star_num) as star FROM ".$this->comment_table." WHERE zhubo_id = ".$postdata['id']." AND if_check = 1";
			$sql = "SELECT comment_level as star FROM ".$this->user_table." WHERE id =".$postdata['id'];
			$data = $this->Common->get_sql($sql );
			
			if( $data )
			{
				$return_arr['status'] = 1;				
				$return_arr['star'] = $data[0]['star'];
				
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '暂无评级';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}	
	
	/*
	*	@查询用户点赞数量
	*	@yinting
	*	@2017年2月20日
	*	@参数： action,id
	*/	
	public function get_user_support()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_support_count' )
		{
			$sql = "SELECT count(id) as support FROM ".$this->support_table." WHERE zhubo_id = ".$postdata['id'];
			$data = $this->Common->get_sql($sql );
			
			if( $data )
			{
				$return_arr['status'] = 1;				
				$return_arr['support'] = $data[0]['support'];
				
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '暂无点赞信息';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}			

	/*
	*	@查询用户参与活动次数
	*	@yinting
	*	@2017年2月20日
	*	@参数： action,id
	*/	
	public function get_user_join()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_join_count' )
		{
			$sql = "SELECT count(id) as count FROM ".$this->join_table." WHERE uid = ".$postdata['id'];
			$data = $this->Common->get_sql($sql );
			
			if( $data )
			{
				$return_arr['status'] = 1;				
				$return_arr['join'] = $data[0]['count'];
				
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '尚未参与活动';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}	
	
	/*
	*	@查询用户是否关注主播
	*	@yinting
	*	@2017年2月20日
	*	@参数： action,uid,zhubo_id
	*/	
	public function check_if_follow()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'check_user_follow' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$where = array(
				'uid' => $postdata['uid'],
				'zhubo_id' => $postdata['zhubo_id'],
				);
			$data = $this->Common->get_one( $this->follow_table, $where );
			if( $data )
			{
				$return_arr['status'] = 1;				
				$return_arr['if_follow'] = 1;
				
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '未关注';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}	
		
	/*
	*	@查询用户收到的评价个数 
	*	@yinting
	*	@2017年2月20日
	*	@参数： action,id
	*/	
	public function get_comment_count()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_comment_count' )
		{
			$sql = "SELECT count(id) as comment FROM ".$this->comment_table." WHERE zhubo_id = ".$postdata['id']." AND if_check =1";
			$data = $this->Common->get_sql($sql );
			
			if( $data )
			{
				$return_arr['status'] = 1;				
				$return_arr['comment'] = $data[0]['comment'];
				
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '尚未收到评价';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}			
		
	/*
	*	@查询用户收到的评价 
	*	@yinting
	*	@2017年2月20日
	*	@参数： action,id
	*/	
	public function get_user_comment()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_user_comment' )
		{
			$start = $postdata['page'] > 1  ? ($postdata['page']-1)*$this->per_page : 0;
			//Step 1 : 查询用户收到的评价
			$sql = "SELECT c.*,e.uid as info_uid,e.en_title,e.title,e.start_time FROM ".$this->comment_table." as c,".$this->event_table." as e WHERE c.info_id = e.id AND zhubo_id=".$postdata['id']." AND if_check = 1";
			$sql .= " ORDER BY c.ctime DESC LIMIT ".$start.",".$this->per_page;
			$return_arr['data'] = $this->Common->get_sql( $sql ); //全部
			
			if( $return_arr )
			{								
				$return_arr['status'] = 1;	
				$return_arr['msg'] = 'success';					
			}
			else
			{
				$return_arr['status'] = 0;				
				$return_arr['msg'] = '尚未收到评价';
			}
		}
		else
		{
			$return_arr['status'] = 0;				
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);exit;
	}			



	/*
	*	@精选活动
	*	@yinting
	*	@2017年2月14日
	*	@参数： action,id
	*/	
	public function hot_events()
	{
		$postdata = html_filter_array($_REQUEST);
		if( $postdata['action'] == 'get_hot_event' )
		{			
			$data = $this->Common->get_limit_order( $this->event_table,array('status>='=>1,'status<='=>2),0,3,'view','DESC' );
						
			if( $data )
			{
				$data['start_time'] = ymdhis($data['start_time']);
				$data['end_time'] = ymdhis($data['end_time']);
				$return_arr['data'] = $data;
				echo json_encode($return_arr);exit;
			}
			else
			{
				echo 0;exit;
			}
		}
		else
		{
			echo '-1';exit;
		}
	}

	public function get_city()
	{
		$all_city = array (  
  1 =>   
  array (  
    'province_name' => '北京市',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '北京市',  
        'area' =>   
        array (  
          1 => '东城区',  
          2 => '西城区',  
          3 => '崇文区',  
          4 => '宣武区',  
          5 => '朝阳区',  
          6 => '丰台区',  
          7 => '石景山区',  
          8 => '海淀区',  
          9 => '门头沟区',  
          10 => '房山区',  
          11 => '通州区',  
          12 => '顺义区',  
          13 => '昌平区',  
          14 => '大兴区',  
          15 => '怀柔区',  
          16 => '平谷区',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '北京周边',  
        'area' =>   
        array (  
          1 => '密云县',  
          2 => '延庆县',  
        ),  
      ),  
    ),  
  ),  
  2 =>   
  array (  
    'province_name' => '天津市',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '天津市',  
        'area' =>   
        array (  
          1 => '和平区',  
          2 => '河东区',  
          3 => '河西区',  
          4 => '南开区',  
          5 => '河北区',  
          6 => '红桥区',  
          7 => '塘沽区',  
          8 => '汉沽区',  
          9 => '大港区',  
          10 => '东丽区',  
          11 => '西青区',  
          12 => '津南区',  
          13 => '北辰区',  
          14 => '武清区',  
          15 => '宝坻区',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '天津周边',  
        'area' =>   
        array (  
          1 => '宁河县',  
          2 => '静海县',  
          3 => '蓟　县',  
        ),  
      ),  
    ),  
  ),  
  3 =>   
  array (  
    'province_name' => '河北省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '石家庄市',  
        'area' =>   
        array (  
          1 => '长安区',  
          2 => '桥东区',  
          3 => '桥西区',  
          4 => '新华区',  
          5 => '井陉矿区',  
          6 => '裕华区',  
          7 => '井陉县',  
          8 => '正定县',  
          9 => '栾城县',  
          10 => '行唐县',  
          11 => '灵寿县',  
          12 => '高邑县',  
          13 => '深泽县',  
          14 => '赞皇县',  
          15 => '无极县',  
          16 => '平山县',  
          17 => '元氏县',  
          18 => '赵　县',  
          19 => '辛集市',  
          20 => '藁城市',  
          21 => '晋州市',  
          22 => '新乐市',  
          23 => '鹿泉市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '唐山市',  
        'area' =>   
        array (  
          1 => '路南区',  
          2 => '路北区',  
          3 => '古冶区',  
          4 => '开平区',  
          5 => '丰南区',  
          6 => '丰润区',  
          7 => '滦　县',  
          8 => '滦南县',  
          9 => '乐亭县',  
          10 => '迁西县',  
          11 => '玉田县',  
          12 => '唐海县',  
          13 => '遵化市',  
          14 => '迁安市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '秦皇岛市',  
        'area' =>   
        array (  
          1 => '海港区',  
          2 => '山海关区',  
          3 => '北戴河区',  
          4 => '青龙满族自治县',  
          5 => '昌黎县',  
          6 => '抚宁县',  
          7 => '卢龙县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '邯郸市',  
        'area' =>   
        array (  
          1 => '邯山区',  
          2 => '丛台区',  
          3 => '复兴区',  
          4 => '峰峰矿区',  
          5 => '邯郸县',  
          6 => '临漳县',  
          7 => '成安县',  
          8 => '大名县',  
          9 => '涉　县',  
          10 => '磁　县',  
          11 => '肥乡县',  
          12 => '永年县',  
          13 => '邱　县',  
          14 => '鸡泽县',  
          15 => '广平县',  
          16 => '馆陶县',  
          17 => '魏　县',  
          18 => '曲周县',  
          19 => '武安市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '邢台市',  
        'area' =>   
        array (  
          1 => '桥东区',  
          2 => '桥西区',  
          3 => '邢台县',  
          4 => '临城县',  
          5 => '内丘县',  
          6 => '柏乡县',  
          7 => '隆尧县',  
          8 => '任　县',  
          9 => '南和县',  
          10 => '宁晋县',  
          11 => '巨鹿县',  
          12 => '新河县',  
          13 => '广宗县',  
          14 => '平乡县',  
          15 => '威　县',  
          16 => '清河县',  
          17 => '临西县',  
          18 => '南宫市',  
          19 => '沙河市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '保定市',  
        'area' =>   
        array (  
          1 => '新市区',  
          2 => '北市区',  
          3 => '南市区',  
          4 => '满城县',  
          5 => '清苑县',  
          6 => '涞水县',  
          7 => '阜平县',  
          8 => '徐水县',  
          9 => '定兴县',  
          10 => '唐　县',  
          11 => '高阳县',  
          12 => '容城县',  
          13 => '涞源县',  
          14 => '望都县',  
          15 => '安新县',  
          16 => '易　县',  
          17 => '曲阳县',  
          18 => '蠡　县',  
          19 => '顺平县',  
          20 => '博野县',  
          21 => '雄　县',  
          22 => '涿州市',  
          23 => '定州市',  
          24 => '安国市',  
          25 => '高碑店市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '张家口市',  
        'area' =>   
        array (  
          1 => '桥东区',  
          2 => '桥西区',  
          3 => '宣化区',  
          4 => '下花园区',  
          5 => '宣化县',  
          6 => '张北县',  
          7 => '康保县',  
          8 => '沽源县',  
          9 => '尚义县',  
          10 => '蔚　县',  
          11 => '阳原县',  
          12 => '怀安县',  
          13 => '万全县',  
          14 => '怀来县',  
          15 => '涿鹿县',  
          16 => '赤城县',  
          17 => '崇礼县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '承德市',  
        'area' =>   
        array (  
          1 => '双桥区',  
          2 => '双滦区',  
          3 => '鹰手营子矿区',  
          4 => '承德县',  
          5 => '兴隆县',  
          6 => '平泉县',  
          7 => '滦平县',  
          8 => '隆化县',  
          9 => '丰宁满族自治县',  
          10 => '宽城满族自治县',  
          11 => '围场满族蒙古族自治县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '沧州市',  
        'area' =>   
        array (  
          1 => '新华区',  
          2 => '运河区',  
          3 => '沧　县',  
          4 => '青　县',  
          5 => '东光县',  
          6 => '海兴县',  
          7 => '盐山县',  
          8 => '肃宁县',  
          9 => '南皮县',  
          10 => '吴桥县',  
          11 => '献　县',  
          12 => '孟村回族自治县',  
          13 => '泊头市',  
          14 => '任丘市',  
          15 => '黄骅市',  
          16 => '河间市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '廊坊市',  
        'area' =>   
        array (  
          1 => '安次区',  
          2 => '广阳区',  
          3 => '固安县',  
          4 => '永清县',  
          5 => '香河县',  
          6 => '大城县',  
          7 => '文安县',  
          8 => '大厂回族自治县',  
          9 => '霸州市',  
          10 => '三河市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '衡水市',  
        'area' =>   
        array (  
          1 => '桃城区',  
          2 => '枣强县',  
          3 => '武邑县',  
          4 => '武强县',  
          5 => '饶阳县',  
          6 => '安平县',  
          7 => '故城县',  
          8 => '景　县',  
          9 => '阜城县',  
          10 => '冀州市',  
          11 => '深州市',  
        ),  
      ),  
    ),  
  ),  
  4 =>   
  array (  
    'province_name' => '山西省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '太原市',  
        'area' =>   
        array (  
          1 => '小店区',  
          2 => '迎泽区',  
          3 => '杏花岭区',  
          4 => '尖草坪区',  
          5 => '万柏林区',  
          6 => '晋源区',  
          7 => '清徐县',  
          8 => '阳曲县',  
          9 => '娄烦县',  
          10 => '古交市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '大同市',  
        'area' =>   
        array (  
          1 => '城　区',  
          2 => '矿　区',  
          3 => '南郊区',  
          4 => '新荣区',  
          5 => '阳高县',  
          6 => '天镇县',  
          7 => '广灵县',  
          8 => '灵丘县',  
          9 => '浑源县',  
          10 => '左云县',  
          11 => '大同县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '阳泉市',  
        'area' =>   
        array (  
          1 => '城　区',  
          2 => '矿　区',  
          3 => '郊　区',  
          4 => '平定县',  
          5 => '盂　县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '长治市',  
        'area' =>   
        array (  
          1 => '城　区',  
          2 => '郊　区',  
          3 => '长治县',  
          4 => '襄垣县',  
          5 => '屯留县',  
          6 => '平顺县',  
          7 => '黎城县',  
          8 => '壶关县',  
          9 => '长子县',  
          10 => '武乡县',  
          11 => '沁　县',  
          12 => '沁源县',  
          13 => '潞城市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '晋城市',  
        'area' =>   
        array (  
          1 => '城　区',  
          2 => '沁水县',  
          3 => '阳城县',  
          4 => '陵川县',  
          5 => '泽州县',  
          6 => '高平市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '朔州市',  
        'area' =>   
        array (  
          1 => '朔城区',  
          2 => '平鲁区',  
          3 => '山阴县',  
          4 => '应　县',  
          5 => '右玉县',  
          6 => '怀仁县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '晋中市',  
        'area' =>   
        array (  
          1 => '榆次区',  
          2 => '榆社县',  
          3 => '左权县',  
          4 => '和顺县',  
          5 => '昔阳县',  
          6 => '寿阳县',  
          7 => '太谷县',  
          8 => '祁　县',  
          9 => '平遥县',  
          10 => '灵石县',  
          11 => '介休市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '运城市',  
        'area' =>   
        array (  
          1 => '盐湖区',  
          2 => '临猗县',  
          3 => '万荣县',  
          4 => '闻喜县',  
          5 => '稷山县',  
          6 => '新绛县',  
          7 => '绛　县',  
          8 => '垣曲县',  
          9 => '夏　县',  
          10 => '平陆县',  
          11 => '芮城县',  
          12 => '永济市',  
          13 => '河津市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '忻州市',  
        'area' =>   
        array (  
          1 => '忻府区',  
          2 => '定襄县',  
          3 => '五台县',  
          4 => '代　县',  
          5 => '繁峙县',  
          6 => '宁武县',  
          7 => '静乐县',  
          8 => '神池县',  
          9 => '五寨县',  
          10 => '岢岚县',  
          11 => '河曲县',  
          12 => '保德县',  
          13 => '偏关县',  
          14 => '原平市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '临汾市',  
        'area' =>   
        array (  
          1 => '尧都区',  
          2 => '曲沃县',  
          3 => '翼城县',  
          4 => '襄汾县',  
          5 => '洪洞县',  
          6 => '古　县',  
          7 => '安泽县',  
          8 => '浮山县',  
          9 => '吉　县',  
          10 => '乡宁县',  
          11 => '大宁县',  
          12 => '隰　县',  
          13 => '永和县',  
          14 => '蒲　县',  
          15 => '汾西县',  
          16 => '侯马市',  
          17 => '霍州市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '吕梁市',  
        'area' =>   
        array (  
          1 => '离石区',  
          2 => '文水县',  
          3 => '交城县',  
          4 => '兴　县',  
          5 => '临　县',  
          6 => '柳林县',  
          7 => '石楼县',  
          8 => '岚　县',  
          9 => '方山县',  
          10 => '中阳县',  
          11 => '交口县',  
          12 => '孝义市',  
          13 => '汾阳市',  
        ),  
      ),  
    ),  
  ),  
  5 =>   
  array (  
    'province_name' => '内蒙古自治区',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '呼和浩特市',  
        'area' =>   
        array (  
          1 => '新城区',  
          2 => '回民区',  
          3 => '玉泉区',  
          4 => '赛罕区',  
          5 => '土默特左旗',  
          6 => '托克托县',  
          7 => '和林格尔县',  
          8 => '清水河县',  
          9 => '武川县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '包头市',  
        'area' =>   
        array (  
          1 => '东河区',  
          2 => '昆都仑区',  
          3 => '青山区',  
          4 => '石拐区',  
          5 => '白云矿区',  
          6 => '九原区',  
          7 => '土默特右旗',  
          8 => '固阳县',  
          9 => '达尔罕茂明安联合旗',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '乌海市',  
        'area' =>   
        array (  
          1 => '海勃湾区',  
          2 => '海南区',  
          3 => '乌达区',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '赤峰市',  
        'area' =>   
        array (  
          1 => '红山区',  
          2 => '元宝山区',  
          3 => '松山区',  
          4 => '阿鲁科尔沁旗',  
          5 => '巴林左旗',  
          6 => '巴林右旗',  
          7 => '林西县',  
          8 => '克什克腾旗',  
          9 => '翁牛特旗',  
          10 => '喀喇沁旗',  
          11 => '宁城县',  
          12 => '敖汉旗',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '通辽市',  
        'area' =>   
        array (  
          1 => '科尔沁区',  
          2 => '科尔沁左翼中旗',  
          3 => '科尔沁左翼后旗',  
          4 => '开鲁县',  
          5 => '库伦旗',  
          6 => '奈曼旗',  
          7 => '扎鲁特旗',  
          8 => '霍林郭勒市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '鄂尔多斯市',  
        'area' =>   
        array (  
          1 => '东胜区',  
          2 => '达拉特旗',  
          3 => '准格尔旗',  
          4 => '鄂托克前旗',  
          5 => '鄂托克旗',  
          6 => '杭锦旗',  
          7 => '乌审旗',  
          8 => '伊金霍洛旗',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '呼伦贝尔市',  
        'area' =>   
        array (  
          1 => '海拉尔区',  
          2 => '阿荣旗',  
          3 => '莫力达瓦达斡尔族自治旗',  
          4 => '鄂伦春自治旗',  
          5 => '鄂温克族自治旗',  
          6 => '陈巴尔虎旗',  
          7 => '新巴尔虎左旗',  
          8 => '新巴尔虎右旗',  
          9 => '满洲里市',  
          10 => '牙克石市',  
          11 => '扎兰屯市',  
          12 => '额尔古纳市',  
          13 => '根河市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '巴彦淖尔市',  
        'area' =>   
        array (  
          1 => '临河区',  
          2 => '五原县',  
          3 => '磴口县',  
          4 => '乌拉特前旗',  
          5 => '乌拉特中旗',  
          6 => '乌拉特后旗',  
          7 => '杭锦后旗',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '乌兰察布市',  
        'area' =>   
        array (  
          1 => '集宁区',  
          2 => '卓资县',  
          3 => '化德县',  
          4 => '商都县',  
          5 => '兴和县',  
          6 => '凉城县',  
          7 => '察哈尔右翼前旗',  
          8 => '察哈尔右翼中旗',  
          9 => '察哈尔右翼后旗',  
          10 => '四子王旗',  
          11 => '丰镇市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '兴安盟',  
        'area' =>   
        array (  
          1 => '乌兰浩特市',  
          2 => '阿尔山市',  
          3 => '科尔沁右翼前旗',  
          4 => '科尔沁右翼中旗',  
          5 => '扎赉特旗',  
          6 => '突泉县',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '锡林郭勒盟',  
        'area' =>   
        array (  
          1 => '二连浩特市',  
          2 => '锡林浩特市',  
          3 => '阿巴嘎旗',  
          4 => '苏尼特左旗',  
          5 => '苏尼特右旗',  
          6 => '东乌珠穆沁旗',  
          7 => '西乌珠穆沁旗',  
          8 => '太仆寺旗',  
          9 => '镶黄旗',  
          10 => '正镶白旗',  
          11 => '正蓝旗',  
          12 => '多伦县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '阿拉善盟',  
        'area' =>   
        array (  
          1 => '阿拉善左旗',  
          2 => '阿拉善右旗',  
          3 => '额济纳旗',  
        ),  
      ),  
    ),  
  ),  
  6 =>   
  array (  
    'province_name' => '辽宁省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '沈阳市',  
        'area' =>   
        array (  
          1 => '和平区',  
          2 => '沈河区',  
          3 => '大东区',  
          4 => '皇姑区',  
          5 => '铁西区',  
          6 => '苏家屯区',  
          7 => '东陵区',  
          8 => '新城子区',  
          9 => '于洪区',  
          10 => '辽中县',  
          11 => '康平县',  
          12 => '法库县',  
          13 => '新民市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '大连市',  
        'area' =>   
        array (  
          1 => '中山区',  
          2 => '西岗区',  
          3 => '沙河口区',  
          4 => '甘井子区',  
          5 => '旅顺口区',  
          6 => '金州区',  
          7 => '长海县',  
          8 => '瓦房店市',  
          9 => '普兰店市',  
          10 => '庄河市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '鞍山市',  
        'area' =>   
        array (  
          1 => '铁东区',  
          2 => '铁西区',  
          3 => '立山区',  
          4 => '千山区',  
          5 => '台安县',  
          6 => '岫岩满族自治县',  
          7 => '海城市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '抚顺市',  
        'area' =>   
        array (  
          1 => '新抚区',  
          2 => '东洲区',  
          3 => '望花区',  
          4 => '顺城区',  
          5 => '抚顺县',  
          6 => '新宾满族自治县',  
          7 => '清原满族自治县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '本溪市',  
        'area' =>   
        array (  
          1 => '平山区',  
          2 => '溪湖区',  
          3 => '明山区',  
          4 => '南芬区',  
          5 => '本溪满族自治县',  
          6 => '桓仁满族自治县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '丹东市',  
        'area' =>   
        array (  
          1 => '元宝区',  
          2 => '振兴区',  
          3 => '振安区',  
          4 => '宽甸满族自治县',  
          5 => '东港市',  
          6 => '凤城市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '锦州市',  
        'area' =>   
        array (  
          1 => '古塔区',  
          2 => '凌河区',  
          3 => '太和区',  
          4 => '黑山县',  
          5 => '义　县',  
          6 => '凌海市',  
          7 => '北宁市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '营口市',  
        'area' =>   
        array (  
          1 => '站前区',  
          2 => '西市区',  
          3 => '鲅鱼圈区',  
          4 => '老边区',  
          5 => '盖州市',  
          6 => '大石桥市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '阜新市',  
        'area' =>   
        array (  
          1 => '海州区',  
          2 => '新邱区',  
          3 => '太平区',  
          4 => '清河门区',  
          5 => '细河区',  
          6 => '阜新蒙古族自治县',  
          7 => '彰武县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '辽阳市',  
        'area' =>   
        array (  
          1 => '白塔区',  
          2 => '文圣区',  
          3 => '宏伟区',  
          4 => '弓长岭区',  
          5 => '太子河区',  
          6 => '辽阳县',  
          7 => '灯塔市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '盘锦市',  
        'area' =>   
        array (  
          1 => '双台子区',  
          2 => '兴隆台区',  
          3 => '大洼县',  
          4 => '盘山县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '铁岭市',  
        'area' =>   
        array (  
          1 => '银州区',  
          2 => '清河区',  
          3 => '铁岭县',  
          4 => '西丰县',  
          5 => '昌图县',  
          6 => '调兵山市',  
          7 => '开原市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '朝阳市',  
        'area' =>   
        array (  
          1 => '双塔区',  
          2 => '龙城区',  
          3 => '朝阳县',  
          4 => '建平县',  
          5 => '喀喇沁左翼蒙古族自治县',  
          6 => '北票市',  
          7 => '凌源市',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '葫芦岛市',  
        'area' =>   
        array (  
          1 => '连山区',  
          2 => '龙港区',  
          3 => '南票区',  
          4 => '绥中县',  
          5 => '建昌县',  
          6 => '兴城市',  
        ),  
      ),  
    ),  
  ),  
  7 =>   
  array (  
    'province_name' => '吉林省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '长春市',  
        'area' =>   
        array (  
          1 => '南关区',  
          2 => '宽城区',  
          3 => '朝阳区',  
          4 => '二道区',  
          5 => '绿园区',  
          6 => '双阳区',  
          7 => '农安县',  
          8 => '九台市',  
          9 => '榆树市',  
          10 => '德惠市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '吉林市',  
        'area' =>   
        array (  
          1 => '昌邑区',  
          2 => '龙潭区',  
          3 => '船营区',  
          4 => '丰满区',  
          5 => '永吉县',  
          6 => '蛟河市',  
          7 => '桦甸市',  
          8 => '舒兰市',  
          9 => '磐石市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '四平市',  
        'area' =>   
        array (  
          1 => '铁西区',  
          2 => '铁东区',  
          3 => '梨树县',  
          4 => '伊通满族自治县',  
          5 => '公主岭市',  
          6 => '双辽市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '辽源市',  
        'area' =>   
        array (  
          1 => '龙山区',  
          2 => '西安区',  
          3 => '东丰县',  
          4 => '东辽县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '通化市',  
        'area' =>   
        array (  
          1 => '东昌区',  
          2 => '二道江区',  
          3 => '通化县',  
          4 => '辉南县',  
          5 => '柳河县',  
          6 => '梅河口市',  
          7 => '集安市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '白山市',  
        'area' =>   
        array (  
          1 => '八道江区',  
          2 => '抚松县',  
          3 => '靖宇县',  
          4 => '长白朝鲜族自治县',  
          5 => '江源县',  
          6 => '临江市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '松原市',  
        'area' =>   
        array (  
          1 => '宁江区',  
          2 => '前郭尔罗斯蒙古族自治县',  
          3 => '长岭县',  
          4 => '乾安县',  
          5 => '扶余县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '白城市',  
        'area' =>   
        array (  
          1 => '洮北区',  
          2 => '镇赉县',  
          3 => '通榆县',  
          4 => '洮南市',  
          5 => '大安市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '延边朝鲜族自治州',  
        'area' =>   
        array (  
          1 => '延吉市',  
          2 => '图们市',  
          3 => '敦化市',  
          4 => '珲春市',  
          5 => '龙井市',  
          6 => '和龙市',  
          7 => '汪清县',  
          8 => '安图县',  
        ),  
      ),  
    ),  
  ),  
  8 =>   
  array (  
    'province_name' => '黑龙江省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '哈尔滨市',  
        'area' =>   
        array (  
          1 => '道里区',  
          2 => '南岗区',  
          3 => '道外区',  
          4 => '香坊区',  
          5 => '动力区',  
          6 => '平房区',  
          7 => '松北区',  
          8 => '呼兰区',  
          9 => '依兰县',  
          10 => '方正县',  
          11 => '宾　县',  
          12 => '巴彦县',  
          13 => '木兰县',  
          14 => '通河县',  
          15 => '延寿县',  
          16 => '阿城市',  
          17 => '双城市',  
          18 => '尚志市',  
          19 => '五常市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '齐齐哈尔市',  
        'area' =>   
        array (  
          1 => '龙沙区',  
          2 => '建华区',  
          3 => '铁锋区',  
          4 => '昂昂溪区',  
          5 => '富拉尔基区',  
          6 => '碾子山区',  
          7 => '梅里斯达斡尔族区',  
          8 => '龙江县',  
          9 => '依安县',  
          10 => '泰来县',  
          11 => '甘南县',  
          12 => '富裕县',  
          13 => '克山县',  
          14 => '克东县',  
          15 => '拜泉县',  
          16 => '讷河市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '鸡西市',  
        'area' =>   
        array (  
          1 => '鸡冠区',  
          2 => '恒山区',  
          3 => '滴道区',  
          4 => '梨树区',  
          5 => '城子河区',  
          6 => '麻山区',  
          7 => '鸡东县',  
          8 => '虎林市',  
          9 => '密山市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '鹤岗市',  
        'area' =>   
        array (  
          1 => '向阳区',  
          2 => '工农区',  
          3 => '南山区',  
          4 => '兴安区',  
          5 => '东山区',  
          6 => '兴山区',  
          7 => '萝北县',  
          8 => '绥滨县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '双鸭山市',  
        'area' =>   
        array (  
          1 => '尖山区',  
          2 => '岭东区',  
          3 => '四方台区',  
          4 => '宝山区',  
          5 => '集贤县',  
          6 => '友谊县',  
          7 => '宝清县',  
          8 => '饶河县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '大庆市',  
        'area' =>   
        array (  
          1 => '萨尔图区',  
          2 => '龙凤区',  
          3 => '让胡路区',  
          4 => '红岗区',  
          5 => '大同区',  
          6 => '肇州县',  
          7 => '肇源县',  
          8 => '林甸县',  
          9 => '杜尔伯特蒙古族自治县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '伊春市',  
        'area' =>   
        array (  
          1 => '伊春区',  
          2 => '南岔区',  
          3 => '友好区',  
          4 => '西林区',  
          5 => '翠峦区',  
          6 => '新青区',  
          7 => '美溪区',  
          8 => '金山屯区',  
          9 => '五营区',  
          10 => '乌马河区',  
          11 => '汤旺河区',  
          12 => '带岭区',  
          13 => '乌伊岭区',  
          14 => '红星区',  
          15 => '上甘岭区',  
          16 => '嘉荫县',  
          17 => '铁力市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '佳木斯市',  
        'area' =>   
        array (  
          1 => '永红区',  
          2 => '向阳区',  
          3 => '前进区',  
          4 => '东风区',  
          5 => '郊　区',  
          6 => '桦南县',  
          7 => '桦川县',  
          8 => '汤原县',  
          9 => '抚远县',  
          10 => '同江市',  
          11 => '富锦市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '七台河市',  
        'area' =>   
        array (  
          1 => '新兴区',  
          2 => '桃山区',  
          3 => '茄子河区',  
          4 => '勃利县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '牡丹江市',  
        'area' =>   
        array (  
          1 => '东安区',  
          2 => '阳明区',  
          3 => '爱民区',  
          4 => '西安区',  
          5 => '东宁县',  
          6 => '林口县',  
          7 => '绥芬河市',  
          8 => '海林市',  
          9 => '宁安市',  
          10 => '穆棱市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '黑河市',  
        'area' =>   
        array (  
          1 => '爱辉区',  
          2 => '嫩江县',  
          3 => '逊克县',  
          4 => '孙吴县',  
          5 => '北安市',  
          6 => '五大连池市',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '绥化市',  
        'area' =>   
        array (  
          1 => '北林区',  
          2 => '望奎县',  
          3 => '兰西县',  
          4 => '青冈县',  
          5 => '庆安县',  
          6 => '明水县',  
          7 => '绥棱县',  
          8 => '安达市',  
          9 => '肇东市',  
          10 => '海伦市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '大兴安岭地区',  
        'area' =>   
        array (  
          1 => '呼玛县',  
          2 => '塔河县',  
          3 => '漠河县',  
        ),  
      ),  
    ),  
  ),  
  9 =>   
  array (  
    'province_name' => '上海市',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '市辖区',  
        'area' =>   
        array (  
          1 => '黄浦区',  
          2 => '卢湾区',  
          3 => '徐汇区',  
          4 => '长宁区',  
          5 => '静安区',  
          6 => '普陀区',  
          7 => '闸北区',  
          8 => '虹口区',  
          9 => '杨浦区',  
          10 => '闵行区',  
          11 => '宝山区',  
          12 => '嘉定区',  
          13 => '浦东新区',  
          14 => '金山区',  
          15 => '松江区',  
          16 => '青浦区',  
          17 => '南汇区',  
          18 => '奉贤区',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '上海周边',  
        'area' =>   
        array (  
          1 => '崇明县',  
        ),  
      ),  
    ),  
  ),  
  10 =>   
  array (  
    'province_name' => '江苏省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '南京市',  
        'area' =>   
        array (  
          1 => '玄武区',  
          2 => '白下区',  
          3 => '秦淮区',  
          4 => '建邺区',  
          5 => '鼓楼区',  
          6 => '下关区',  
          7 => '浦口区',  
          8 => '栖霞区',  
          9 => '雨花台区',  
          10 => '江宁区',  
          11 => '六合区',  
          12 => '溧水县',  
          13 => '高淳县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '无锡市',  
        'area' =>   
        array (  
          1 => '崇安区',  
          2 => '南长区',  
          3 => '北塘区',  
          4 => '锡山区',  
          5 => '惠山区',  
          6 => '滨湖区',  
          7 => '江阴市',  
          8 => '宜兴市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '徐州市',  
        'area' =>   
        array (  
          1 => '鼓楼区',  
          2 => '云龙区',  
          3 => '九里区',  
          4 => '贾汪区',  
          5 => '泉山区',  
          6 => '丰　县',  
          7 => '沛　县',  
          8 => '铜山县',  
          9 => '睢宁县',  
          10 => '新沂市',  
          11 => '邳州市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '常州市',  
        'area' =>   
        array (  
          1 => '天宁区',  
          2 => '钟楼区',  
          3 => '戚墅堰区',  
          4 => '新北区',  
          5 => '武进区',  
          6 => '溧阳市',  
          7 => '金坛市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '苏州市',  
        'area' =>   
        array (  
          1 => '沧浪区',  
          2 => '平江区',  
          3 => '金阊区',  
          4 => '虎丘区',  
          5 => '吴中区',  
          6 => '相城区',  
          7 => '常熟市',  
          8 => '张家港市',  
          9 => '昆山市',  
          10 => '吴江市',  
          11 => '太仓市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '南通市',  
        'area' =>   
        array (  
          1 => '崇川区',  
          2 => '港闸区',  
          3 => '海安县',  
          4 => '如东县',  
          5 => '启东市',  
          6 => '如皋市',  
          7 => '通州市',  
          8 => '海门市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '连云港市',  
        'area' =>   
        array (  
          1 => '连云区',  
          2 => '新浦区',  
          3 => '海州区',  
          4 => '赣榆县',  
          5 => '东海县',  
          6 => '灌云县',  
          7 => '灌南县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '淮安市',  
        'area' =>   
        array (  
          1 => '清河区',  
          2 => '楚州区',  
          3 => '淮阴区',  
          4 => '清浦区',  
          5 => '涟水县',  
          6 => '洪泽县',  
          7 => '盱眙县',  
          8 => '金湖县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '盐城市',  
        'area' =>   
        array (  
          1 => '亭湖区',  
          2 => '盐都区',  
          3 => '响水县',  
          4 => '滨海县',  
          5 => '阜宁县',  
          6 => '射阳县',  
          7 => '建湖县',  
          8 => '东台市',  
          9 => '大丰市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '扬州市',  
        'area' =>   
        array (  
          1 => '广陵区',  
          2 => '邗江区',  
          3 => '郊　区',  
          4 => '宝应县',  
          5 => '仪征市',  
          6 => '高邮市',  
          7 => '江都市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '镇江市',  
        'area' =>   
        array (  
          1 => '京口区',  
          2 => '润州区',  
          3 => '丹徒区',  
          4 => '丹阳市',  
          5 => '扬中市',  
          6 => '句容市',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '泰州市',  
        'area' =>   
        array (  
          1 => '海陵区',  
          2 => '高港区',  
          3 => '兴化市',  
          4 => '靖江市',  
          5 => '泰兴市',  
          6 => '姜堰市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '宿迁市',  
        'area' =>   
        array (  
          1 => '宿城区',  
          2 => '宿豫区',  
          3 => '沭阳县',  
          4 => '泗阳县',  
          5 => '泗洪县',  
        ),  
      ),  
    ),  
  ),  
  11 =>   
  array (  
    'province_name' => '浙江省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '杭州市',  
        'area' =>   
        array (  
          1 => '上城区',  
          2 => '下城区',  
          3 => '江干区',  
          4 => '拱墅区',  
          5 => '西湖区',  
          6 => '滨江区',  
          7 => '萧山区',  
          8 => '余杭区',  
          9 => '桐庐县',  
          10 => '淳安县',  
          11 => '建德市',  
          12 => '富阳市',  
          13 => '临安市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '宁波市',  
        'area' =>   
        array (  
          1 => '海曙区',  
          2 => '江东区',  
          3 => '江北区',  
          4 => '北仑区',  
          5 => '镇海区',  
          6 => '鄞州区',  
          7 => '象山县',  
          8 => '宁海县',  
          9 => '余姚市',  
          10 => '慈溪市',  
          11 => '奉化市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '温州市',  
        'area' =>   
        array (  
          1 => '鹿城区',  
          2 => '龙湾区',  
          3 => '瓯海区',  
          4 => '洞头县',  
          5 => '永嘉县',  
          6 => '平阳县',  
          7 => '苍南县',  
          8 => '文成县',  
          9 => '泰顺县',  
          10 => '瑞安市',  
          11 => '乐清市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '嘉兴市',  
        'area' =>   
        array (  
          1 => '秀城区',  
          2 => '秀洲区',  
          3 => '嘉善县',  
          4 => '海盐县',  
          5 => '海宁市',  
          6 => '平湖市',  
          7 => '桐乡市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '湖州市',  
        'area' =>   
        array (  
          1 => '吴兴区',  
          2 => '南浔区',  
          3 => '德清县',  
          4 => '长兴县',  
          5 => '安吉县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '绍兴市',  
        'area' =>   
        array (  
          1 => '越城区',  
          2 => '绍兴县',  
          3 => '新昌县',  
          4 => '诸暨市',  
          5 => '上虞市',  
          6 => '嵊州市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '金华市',  
        'area' =>   
        array (  
          1 => '婺城区',  
          2 => '金东区',  
          3 => '武义县',  
          4 => '浦江县',  
          5 => '磐安县',  
          6 => '兰溪市',  
          7 => '义乌市',  
          8 => '东阳市',  
          9 => '永康市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '衢州市',  
        'area' =>   
        array (  
          1 => '柯城区',  
          2 => '衢江区',  
          3 => '常山县',  
          4 => '开化县',  
          5 => '龙游县',  
          6 => '江山市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '舟山市',  
        'area' =>   
        array (  
          1 => '定海区',  
          2 => '普陀区',  
          3 => '岱山县',  
          4 => '嵊泗县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '台州市',  
        'area' =>   
        array (  
          1 => '椒江区',  
          2 => '黄岩区',  
          3 => '路桥区',  
          4 => '玉环县',  
          5 => '三门县',  
          6 => '天台县',  
          7 => '仙居县',  
          8 => '温岭市',  
          9 => '临海市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '丽水市',  
        'area' =>   
        array (  
          1 => '莲都区',  
          2 => '青田县',  
          3 => '缙云县',  
          4 => '遂昌县',  
          5 => '松阳县',  
          6 => '云和县',  
          7 => '庆元县',  
          8 => '景宁畲族自治县',  
          9 => '龙泉市',  
        ),  
      ),  
    ),  
  ),  
  12 =>   
  array (  
    'province_name' => '安徽省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '合肥市',  
        'area' =>   
        array (  
          1 => '瑶海区',  
          2 => '庐阳区',  
          3 => '蜀山区',  
          4 => '包河区',  
          5 => '长丰县',  
          6 => '肥东县',  
          7 => '肥西县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '芜湖市',  
        'area' =>   
        array (  
          1 => '镜湖区',  
          2 => '马塘区',  
          3 => '新芜区',  
          4 => '鸠江区',  
          5 => '芜湖县',  
          6 => '繁昌县',  
          7 => '南陵县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '蚌埠市',  
        'area' =>   
        array (  
          1 => '龙子湖区',  
          2 => '蚌山区',  
          3 => '禹会区',  
          4 => '淮上区',  
          5 => '怀远县',  
          6 => '五河县',  
          7 => '固镇县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '淮南市',  
        'area' =>   
        array (  
          1 => '大通区',  
          2 => '田家庵区',  
          3 => '谢家集区',  
          4 => '八公山区',  
          5 => '潘集区',  
          6 => '凤台县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '马鞍山市',  
        'area' =>   
        array (  
          1 => '金家庄区',  
          2 => '花山区',  
          3 => '雨山区',  
          4 => '当涂县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '淮北市',  
        'area' =>   
        array (  
          1 => '杜集区',  
          2 => '相山区',  
          3 => '烈山区',  
          4 => '濉溪县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '铜陵市',  
        'area' =>   
        array (  
          1 => '铜官山区',  
          2 => '狮子山区',  
          3 => '郊　区',  
          4 => '铜陵县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '安庆市',  
        'area' =>   
        array (  
          1 => '迎江区',  
          2 => '大观区',  
          3 => '郊　区',  
          4 => '怀宁县',  
          5 => '枞阳县',  
          6 => '潜山县',  
          7 => '太湖县',  
          8 => '宿松县',  
          9 => '望江县',  
          10 => '岳西县',  
          11 => '桐城市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '黄山市',  
        'area' =>   
        array (  
          1 => '屯溪区',  
          2 => '黄山区',  
          3 => '徽州区',  
          4 => '歙　县',  
          5 => '休宁县',  
          6 => '黟　县',  
          7 => '祁门县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '滁州市',  
        'area' =>   
        array (  
          1 => '琅琊区',  
          2 => '南谯区',  
          3 => '来安县',  
          4 => '全椒县',  
          5 => '定远县',  
          6 => '凤阳县',  
          7 => '天长市',  
          8 => '明光市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '阜阳市',  
        'area' =>   
        array (  
          1 => '颍州区',  
          2 => '颍东区',  
          3 => '颍泉区',  
          4 => '临泉县',  
          5 => '太和县',  
          6 => '阜南县',  
          7 => '颍上县',  
          8 => '界首市',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '宿州市',  
        'area' =>   
        array (  
          1 => '墉桥区',  
          2 => '砀山县',  
          3 => '萧　县',  
          4 => '灵璧县',  
          5 => '泗　县',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '巢湖市',  
        'area' =>   
        array (  
          1 => '居巢区',  
          2 => '庐江县',  
          3 => '无为县',  
          4 => '含山县',  
          5 => '和　县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '六安市',  
        'area' =>   
        array (  
          1 => '金安区',  
          2 => '裕安区',  
          3 => '寿　县',  
          4 => '霍邱县',  
          5 => '舒城县',  
          6 => '金寨县',  
          7 => '霍山县',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '亳州市',  
        'area' =>   
        array (  
          1 => '谯城区',  
          2 => '涡阳县',  
          3 => '蒙城县',  
          4 => '利辛县',  
        ),  
      ),  
      16 =>   
      array (  
        'city_name' => '池州市',  
        'area' =>   
        array (  
          1 => '贵池区',  
          2 => '东至县',  
          3 => '石台县',  
          4 => '青阳县',  
        ),  
      ),  
      17 =>   
      array (  
        'city_name' => '宣城市',  
        'area' =>   
        array (  
          1 => '宣州区',  
          2 => '郎溪县',  
          3 => '广德县',  
          4 => '泾　县',  
          5 => '绩溪县',  
          6 => '旌德县',  
          7 => '宁国市',  
        ),  
      ),  
    ),  
  ),  
  13 =>   
  array (  
    'province_name' => '福建省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '福州市',  
        'area' =>   
        array (  
          1 => '鼓楼区',  
          2 => '台江区',  
          3 => '仓山区',  
          4 => '马尾区',  
          5 => '晋安区',  
          6 => '闽侯县',  
          7 => '连江县',  
          8 => '罗源县',  
          9 => '闽清县',  
          10 => '永泰县',  
          11 => '平潭县',  
          12 => '福清市',  
          13 => '长乐市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '厦门市',  
        'area' =>   
        array (  
          1 => '思明区',  
          2 => '海沧区',  
          3 => '湖里区',  
          4 => '集美区',  
          5 => '同安区',  
          6 => '翔安区',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '莆田市',  
        'area' =>   
        array (  
          1 => '城厢区',  
          2 => '涵江区',  
          3 => '荔城区',  
          4 => '秀屿区',  
          5 => '仙游县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '三明市',  
        'area' =>   
        array (  
          1 => '梅列区',  
          2 => '三元区',  
          3 => '明溪县',  
          4 => '清流县',  
          5 => '宁化县',  
          6 => '大田县',  
          7 => '尤溪县',  
          8 => '沙　县',  
          9 => '将乐县',  
          10 => '泰宁县',  
          11 => '建宁县',  
          12 => '永安市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '泉州市',  
        'area' =>   
        array (  
          1 => '鲤城区',  
          2 => '丰泽区',  
          3 => '洛江区',  
          4 => '泉港区',  
          5 => '惠安县',  
          6 => '安溪县',  
          7 => '永春县',  
          8 => '德化县',  
          9 => '金门县',  
          10 => '石狮市',  
          11 => '晋江市',  
          12 => '南安市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '漳州市',  
        'area' =>   
        array (  
          1 => '芗城区',  
          2 => '龙文区',  
          3 => '云霄县',  
          4 => '漳浦县',  
          5 => '诏安县',  
          6 => '长泰县',  
          7 => '东山县',  
          8 => '南靖县',  
          9 => '平和县',  
          10 => '华安县',  
          11 => '龙海市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '南平市',  
        'area' =>   
        array (  
          1 => '延平区',  
          2 => '顺昌县',  
          3 => '浦城县',  
          4 => '光泽县',  
          5 => '松溪县',  
          6 => '政和县',  
          7 => '邵武市',  
          8 => '武夷山市',  
          9 => '建瓯市',  
          10 => '建阳市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '龙岩市',  
        'area' =>   
        array (  
          1 => '新罗区',  
          2 => '长汀县',  
          3 => '永定县',  
          4 => '上杭县',  
          5 => '武平县',  
          6 => '连城县',  
          7 => '漳平市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '宁德市',  
        'area' =>   
        array (  
          1 => '蕉城区',  
          2 => '霞浦县',  
          3 => '古田县',  
          4 => '屏南县',  
          5 => '寿宁县',  
          6 => '周宁县',  
          7 => '柘荣县',  
          8 => '福安市',  
          9 => '福鼎市',  
        ),  
      ),  
    ),  
  ),  
  14 =>   
  array (  
    'province_name' => '江西省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '南昌市',  
        'area' =>   
        array (  
          1 => '东湖区',  
          2 => '西湖区',  
          3 => '青云谱区',  
          4 => '湾里区',  
          5 => '青山湖区',  
          6 => '南昌县',  
          7 => '新建县',  
          8 => '安义县',  
          9 => '进贤县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '景德镇市',  
        'area' =>   
        array (  
          1 => '昌江区',  
          2 => '珠山区',  
          3 => '浮梁县',  
          4 => '乐平市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '萍乡市',  
        'area' =>   
        array (  
          1 => '安源区',  
          2 => '湘东区',  
          3 => '莲花县',  
          4 => '上栗县',  
          5 => '芦溪县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '九江市',  
        'area' =>   
        array (  
          1 => '庐山区',  
          2 => '浔阳区',  
          3 => '九江县',  
          4 => '武宁县',  
          5 => '修水县',  
          6 => '永修县',  
          7 => '德安县',  
          8 => '星子县',  
          9 => '都昌县',  
          10 => '湖口县',  
          11 => '彭泽县',  
          12 => '瑞昌市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '新余市',  
        'area' =>   
        array (  
          1 => '渝水区',  
          2 => '分宜县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '鹰潭市',  
        'area' =>   
        array (  
          1 => '月湖区',  
          2 => '余江县',  
          3 => '贵溪市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '赣州市',  
        'area' =>   
        array (  
          1 => '章贡区',  
          2 => '赣　县',  
          3 => '信丰县',  
          4 => '大余县',  
          5 => '上犹县',  
          6 => '崇义县',  
          7 => '安远县',  
          8 => '龙南县',  
          9 => '定南县',  
          10 => '全南县',  
          11 => '宁都县',  
          12 => '于都县',  
          13 => '兴国县',  
          14 => '会昌县',  
          15 => '寻乌县',  
          16 => '石城县',  
          17 => '瑞金市',  
          18 => '南康市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '吉安市',  
        'area' =>   
        array (  
          1 => '吉州区',  
          2 => '青原区',  
          3 => '吉安县',  
          4 => '吉水县',  
          5 => '峡江县',  
          6 => '新干县',  
          7 => '永丰县',  
          8 => '泰和县',  
          9 => '遂川县',  
          10 => '万安县',  
          11 => '安福县',  
          12 => '永新县',  
          13 => '井冈山市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '宜春市',  
        'area' =>   
        array (  
          1 => '袁州区',  
          2 => '奉新县',  
          3 => '万载县',  
          4 => '上高县',  
          5 => '宜丰县',  
          6 => '靖安县',  
          7 => '铜鼓县',  
          8 => '丰城市',  
          9 => '樟树市',  
          10 => '高安市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '抚州市',  
        'area' =>   
        array (  
          1 => '临川区',  
          2 => '南城县',  
          3 => '黎川县',  
          4 => '南丰县',  
          5 => '崇仁县',  
          6 => '乐安县',  
          7 => '宜黄县',  
          8 => '金溪县',  
          9 => '资溪县',  
          10 => '东乡县',  
          11 => '广昌县',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '上饶市',  
        'area' =>   
        array (  
          1 => '信州区',  
          2 => '上饶县',  
          3 => '广丰县',  
          4 => '玉山县',  
          5 => '铅山县',  
          6 => '横峰县',  
          7 => '弋阳县',  
          8 => '余干县',  
          9 => '鄱阳县',  
          10 => '万年县',  
          11 => '婺源县',  
          12 => '德兴市',  
        ),  
      ),  
    ),  
  ),  
  15 =>   
  array (  
    'province_name' => '山东省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '济南市',  
        'area' =>   
        array (  
          1 => '历下区',  
          2 => '市中区',  
          3 => '槐荫区',  
          4 => '天桥区',  
          5 => '历城区',  
          6 => '长清区',  
          7 => '平阴县',  
          8 => '济阳县',  
          9 => '商河县',  
          10 => '章丘市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '青岛市',  
        'area' =>   
        array (  
          1 => '市南区',  
          2 => '市北区',  
          3 => '四方区',  
          4 => '黄岛区',  
          5 => '崂山区',  
          6 => '李沧区',  
          7 => '城阳区',  
          8 => '胶州市',  
          9 => '即墨市',  
          10 => '平度市',  
          11 => '胶南市',  
          12 => '莱西市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '淄博市',  
        'area' =>   
        array (  
          1 => '淄川区',  
          2 => '张店区',  
          3 => '博山区',  
          4 => '临淄区',  
          5 => '周村区',  
          6 => '桓台县',  
          7 => '高青县',  
          8 => '沂源县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '枣庄市',  
        'area' =>   
        array (  
          1 => '市中区',  
          2 => '薛城区',  
          3 => '峄城区',  
          4 => '台儿庄区',  
          5 => '山亭区',  
          6 => '滕州市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '东营市',  
        'area' =>   
        array (  
          1 => '东营区',  
          2 => '河口区',  
          3 => '垦利县',  
          4 => '利津县',  
          5 => '广饶县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '烟台市',  
        'area' =>   
        array (  
          1 => '芝罘区',  
          2 => '福山区',  
          3 => '牟平区',  
          4 => '莱山区',  
          5 => '长岛县',  
          6 => '龙口市',  
          7 => '莱阳市',  
          8 => '莱州市',  
          9 => '蓬莱市',  
          10 => '招远市',  
          11 => '栖霞市',  
          12 => '海阳市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '潍坊市',  
        'area' =>   
        array (  
          1 => '潍城区',  
          2 => '寒亭区',  
          3 => '坊子区',  
          4 => '奎文区',  
          5 => '临朐县',  
          6 => '昌乐县',  
          7 => '青州市',  
          8 => '诸城市',  
          9 => '寿光市',  
          10 => '安丘市',  
          11 => '高密市',  
          12 => '昌邑市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '济宁市',  
        'area' =>   
        array (  
          1 => '市中区',  
          2 => '任城区',  
          3 => '微山县',  
          4 => '鱼台县',  
          5 => '金乡县',  
          6 => '嘉祥县',  
          7 => '汶上县',  
          8 => '泗水县',  
          9 => '梁山县',  
          10 => '曲阜市',  
          11 => '兖州市',  
          12 => '邹城市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '泰安市',  
        'area' =>   
        array (  
          1 => '泰山区',  
          2 => '岱岳区',  
          3 => '宁阳县',  
          4 => '东平县',  
          5 => '新泰市',  
          6 => '肥城市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '威海市',  
        'area' =>   
        array (  
          1 => '环翠区',  
          2 => '文登市',  
          3 => '荣成市',  
          4 => '乳山市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '日照市',  
        'area' =>   
        array (  
          1 => '东港区',  
          2 => '岚山区',  
          3 => '五莲县',  
          4 => '莒　县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '莱芜市',  
        'area' =>   
        array (  
          1 => '莱城区',  
          2 => '钢城区',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '临沂市',  
        'area' =>   
        array (  
          1 => '兰山区',  
          2 => '罗庄区',  
          3 => '河东区',  
          4 => '沂南县',  
          5 => '郯城县',  
          6 => '沂水县',  
          7 => '苍山县',  
          8 => '费　县',  
          9 => '平邑县',  
          10 => '莒南县',  
          11 => '蒙阴县',  
          12 => '临沭县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '德州市',  
        'area' =>   
        array (  
          1 => '德城区',  
          2 => '陵　县',  
          3 => '宁津县',  
          4 => '庆云县',  
          5 => '临邑县',  
          6 => '齐河县',  
          7 => '平原县',  
          8 => '夏津县',  
          9 => '武城县',  
          10 => '乐陵市',  
          11 => '禹城市',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '聊城市',  
        'area' =>   
        array (  
          1 => '东昌府区',  
          2 => '阳谷县',  
          3 => '莘　县',  
          4 => '茌平县',  
          5 => '东阿县',  
          6 => '冠　县',  
          7 => '高唐县',  
          8 => '临清市',  
        ),  
      ),  
      16 =>   
      array (  
        'city_name' => '滨州市',  
        'area' =>   
        array (  
          1 => '滨城区',  
          2 => '惠民县',  
          3 => '阳信县',  
          4 => '无棣县',  
          5 => '沾化县',  
          6 => '博兴县',  
          7 => '邹平县',  
        ),  
      ),  
      17 =>   
      array (  
        'city_name' => '荷泽市',  
        'area' =>   
        array (  
          1 => '牡丹区',  
          2 => '曹　县',  
          3 => '单　县',  
          4 => '成武县',  
          5 => '巨野县',  
          6 => '郓城县',  
          7 => '鄄城县',  
          8 => '定陶县',  
          9 => '东明县',  
        ),  
      ),  
    ),  
  ),  
  16 =>   
  array (  
    'province_name' => '河南省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '郑州市',  
        'area' =>   
        array (  
          1 => '中原区',  
          2 => '二七区',  
          3 => '管城回族区',  
          4 => '金水区',  
          5 => '上街区',  
          6 => '邙山区',  
          7 => '中牟县',  
          8 => '巩义市',  
          9 => '荥阳市',  
          10 => '新密市',  
          11 => '新郑市',  
          12 => '登封市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '开封市',  
        'area' =>   
        array (  
          1 => '龙亭区',  
          2 => '顺河回族区',  
          3 => '鼓楼区',  
          4 => '南关区',  
          5 => '郊　区',  
          6 => '杞　县',  
          7 => '通许县',  
          8 => '尉氏县',  
          9 => '开封县',  
          10 => '兰考县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '洛阳市',  
        'area' =>   
        array (  
          1 => '老城区',  
          2 => '西工区',  
          3 => '廛河回族区',  
          4 => '涧西区',  
          5 => '吉利区',  
          6 => '洛龙区',  
          7 => '孟津县',  
          8 => '新安县',  
          9 => '栾川县',  
          10 => '嵩　县',  
          11 => '汝阳县',  
          12 => '宜阳县',  
          13 => '洛宁县',  
          14 => '伊川县',  
          15 => '偃师市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '平顶山市',  
        'area' =>   
        array (  
          1 => '新华区',  
          2 => '卫东区',  
          3 => '石龙区',  
          4 => '湛河区',  
          5 => '宝丰县',  
          6 => '叶　县',  
          7 => '鲁山县',  
          8 => '郏　县',  
          9 => '舞钢市',  
          10 => '汝州市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '安阳市',  
        'area' =>   
        array (  
          1 => '文峰区',  
          2 => '北关区',  
          3 => '殷都区',  
          4 => '龙安区',  
          5 => '安阳县',  
          6 => '汤阴县',  
          7 => '滑　县',  
          8 => '内黄县',  
          9 => '林州市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '鹤壁市',  
        'area' =>   
        array (  
          1 => '鹤山区',  
          2 => '山城区',  
          3 => '淇滨区',  
          4 => '浚　县',  
          5 => '淇　县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '新乡市',  
        'area' =>   
        array (  
          1 => '红旗区',  
          2 => '卫滨区',  
          3 => '凤泉区',  
          4 => '牧野区',  
          5 => '新乡县',  
          6 => '获嘉县',  
          7 => '原阳县',  
          8 => '延津县',  
          9 => '封丘县',  
          10 => '长垣县',  
          11 => '卫辉市',  
          12 => '辉县市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '焦作市',  
        'area' =>   
        array (  
          1 => '解放区',  
          2 => '中站区',  
          3 => '马村区',  
          4 => '山阳区',  
          5 => '修武县',  
          6 => '博爱县',  
          7 => '武陟县',  
          8 => '温　县',  
          9 => '济源市',  
          10 => '沁阳市',  
          11 => '孟州市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '濮阳市',  
        'area' =>   
        array (  
          1 => '华龙区',  
          2 => '清丰县',  
          3 => '南乐县',  
          4 => '范　县',  
          5 => '台前县',  
          6 => '濮阳县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '许昌市',  
        'area' =>   
        array (  
          1 => '魏都区',  
          2 => '许昌县',  
          3 => '鄢陵县',  
          4 => '襄城县',  
          5 => '禹州市',  
          6 => '长葛市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '漯河市',  
        'area' =>   
        array (  
          1 => '源汇区',  
          2 => '郾城区',  
          3 => '召陵区',  
          4 => '舞阳县',  
          5 => '临颍县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '三门峡市',  
        'area' =>   
        array (  
          1 => '湖滨区',  
          2 => '渑池县',  
          3 => '陕　县',  
          4 => '卢氏县',  
          5 => '义马市',  
          6 => '灵宝市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '南阳市',  
        'area' =>   
        array (  
          1 => '宛城区',  
          2 => '卧龙区',  
          3 => '南召县',  
          4 => '方城县',  
          5 => '西峡县',  
          6 => '镇平县',  
          7 => '内乡县',  
          8 => '淅川县',  
          9 => '社旗县',  
          10 => '唐河县',  
          11 => '新野县',  
          12 => '桐柏县',  
          13 => '邓州市',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '商丘市',  
        'area' =>   
        array (  
          1 => '梁园区',  
          2 => '睢阳区',  
          3 => '民权县',  
          4 => '睢　县',  
          5 => '宁陵县',  
          6 => '柘城县',  
          7 => '虞城县',  
          8 => '夏邑县',  
          9 => '永城市',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '信阳市',  
        'area' =>   
        array (  
          1 => '师河区',  
          2 => '平桥区',  
          3 => '罗山县',  
          4 => '光山县',  
          5 => '新　县',  
          6 => '商城县',  
          7 => '固始县',  
          8 => '潢川县',  
          9 => '淮滨县',  
          10 => '息　县',  
        ),  
      ),  
      16 =>   
      array (  
        'city_name' => '周口市',  
        'area' =>   
        array (  
          1 => '川汇区',  
          2 => '扶沟县',  
          3 => '西华县',  
          4 => '商水县',  
          5 => '沈丘县',  
          6 => '郸城县',  
          7 => '淮阳县',  
          8 => '太康县',  
          9 => '鹿邑县',  
          10 => '项城市',  
        ),  
      ),  
      17 =>   
      array (  
        'city_name' => '驻马店市',  
        'area' =>   
        array (  
          1 => '驿城区',  
          2 => '西平县',  
          3 => '上蔡县',  
          4 => '平舆县',  
          5 => '正阳县',  
          6 => '确山县',  
          7 => '泌阳县',  
          8 => '汝南县',  
          9 => '遂平县',  
          10 => '新蔡县',  
        ),  
      ),  
    ),  
  ),  
  17 =>   
  array (  
    'province_name' => '湖北省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '武汉市',  
        'area' =>   
        array (  
          1 => '江岸区',  
          2 => '江汉区',  
          3 => '乔口区',  
          4 => '汉阳区',  
          5 => '武昌区',  
          6 => '青山区',  
          7 => '洪山区',  
          8 => '东西湖区',  
          9 => '汉南区',  
          10 => '蔡甸区',  
          11 => '江夏区',  
          12 => '黄陂区',  
          13 => '新洲区',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '黄石市',  
        'area' =>   
        array (  
          1 => '黄石港区',  
          2 => '西塞山区',  
          3 => '下陆区',  
          4 => '铁山区',  
          5 => '阳新县',  
          6 => '大冶市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '十堰市',  
        'area' =>   
        array (  
          1 => '茅箭区',  
          2 => '张湾区',  
          3 => '郧　县',  
          4 => '郧西县',  
          5 => '竹山县',  
          6 => '竹溪县',  
          7 => '房　县',  
          8 => '丹江口市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '宜昌市',  
        'area' =>   
        array (  
          1 => '西陵区',  
          2 => '伍家岗区',  
          3 => '点军区',  
          4 => '猇亭区',  
          5 => '夷陵区',  
          6 => '远安县',  
          7 => '兴山县',  
          8 => '秭归县',  
          9 => '长阳土家族自治县',  
          10 => '五峰土家族自治县',  
          11 => '宜都市',  
          12 => '当阳市',  
          13 => '枝江市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '襄樊市',  
        'area' =>   
        array (  
          1 => '襄城区',  
          2 => '樊城区',  
          3 => '襄阳区',  
          4 => '南漳县',  
          5 => '谷城县',  
          6 => '保康县',  
          7 => '老河口市',  
          8 => '枣阳市',  
          9 => '宜城市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '鄂州市',  
        'area' =>   
        array (  
          1 => '梁子湖区',  
          2 => '华容区',  
          3 => '鄂城区',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '荆门市',  
        'area' =>   
        array (  
          1 => '东宝区',  
          2 => '掇刀区',  
          3 => '京山县',  
          4 => '沙洋县',  
          5 => '钟祥市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '孝感市',  
        'area' =>   
        array (  
          1 => '孝南区',  
          2 => '孝昌县',  
          3 => '大悟县',  
          4 => '云梦县',  
          5 => '应城市',  
          6 => '安陆市',  
          7 => '汉川市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '荆州市',  
        'area' =>   
        array (  
          1 => '沙市区',  
          2 => '荆州区',  
          3 => '公安县',  
          4 => '监利县',  
          5 => '江陵县',  
          6 => '石首市',  
          7 => '洪湖市',  
          8 => '松滋市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '黄冈市',  
        'area' =>   
        array (  
          1 => '黄州区',  
          2 => '团风县',  
          3 => '红安县',  
          4 => '罗田县',  
          5 => '英山县',  
          6 => '浠水县',  
          7 => '蕲春县',  
          8 => '黄梅县',  
          9 => '麻城市',  
          10 => '武穴市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '咸宁市',  
        'area' =>   
        array (  
          1 => '咸安区',  
          2 => '嘉鱼县',  
          3 => '通城县',  
          4 => '崇阳县',  
          5 => '通山县',  
          6 => '赤壁市',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '随州市',  
        'area' =>   
        array (  
          1 => '曾都区',  
          2 => '广水市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '恩施土家族苗族自治州',  
        'area' =>   
        array (  
          1 => '恩施市',  
          2 => '利川市',  
          3 => '建始县',  
          4 => '巴东县',  
          5 => '宣恩县',  
          6 => '咸丰县',  
          7 => '来凤县',  
          8 => '鹤峰县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '省直辖行政单位',  
        'area' =>   
        array (  
          1 => '仙桃市',  
          2 => '潜江市',  
          3 => '天门市',  
          4 => '神农架林区',  
        ),  
      ),  
    ),  
  ),  
  18 =>   
  array (  
    'province_name' => '湖南省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '长沙市',  
        'area' =>   
        array (  
          1 => '芙蓉区',  
          2 => '天心区',  
          3 => '岳麓区',  
          4 => '开福区',  
          5 => '雨花区',  
          6 => '长沙县',  
          7 => '望城县',  
          8 => '宁乡县',  
          9 => '浏阳市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '株洲市',  
        'area' =>   
        array (  
          1 => '荷塘区',  
          2 => '芦淞区',  
          3 => '石峰区',  
          4 => '天元区',  
          5 => '株洲县',  
          6 => '攸　县',  
          7 => '茶陵县',  
          8 => '炎陵县',  
          9 => '醴陵市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '湘潭市',  
        'area' =>   
        array (  
          1 => '雨湖区',  
          2 => '岳塘区',  
          3 => '湘潭县',  
          4 => '湘乡市',  
          5 => '韶山市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '衡阳市',  
        'area' =>   
        array (  
          1 => '珠晖区',  
          2 => '雁峰区',  
          3 => '石鼓区',  
          4 => '蒸湘区',  
          5 => '南岳区',  
          6 => '衡阳县',  
          7 => '衡南县',  
          8 => '衡山县',  
          9 => '衡东县',  
          10 => '祁东县',  
          11 => '耒阳市',  
          12 => '常宁市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '邵阳市',  
        'area' =>   
        array (  
          1 => '双清区',  
          2 => '大祥区',  
          3 => '北塔区',  
          4 => '邵东县',  
          5 => '新邵县',  
          6 => '邵阳县',  
          7 => '隆回县',  
          8 => '洞口县',  
          9 => '绥宁县',  
          10 => '新宁县',  
          11 => '城步苗族自治县',  
          12 => '武冈市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '岳阳市',  
        'area' =>   
        array (  
          1 => '岳阳楼区',  
          2 => '云溪区',  
          3 => '君山区',  
          4 => '岳阳县',  
          5 => '华容县',  
          6 => '湘阴县',  
          7 => '平江县',  
          8 => '汨罗市',  
          9 => '临湘市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '常德市',  
        'area' =>   
        array (  
          1 => '武陵区',  
          2 => '鼎城区',  
          3 => '安乡县',  
          4 => '汉寿县',  
          5 => '澧　县',  
          6 => '临澧县',  
          7 => '桃源县',  
          8 => '石门县',  
          9 => '津市市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '张家界市',  
        'area' =>   
        array (  
          1 => '永定区',  
          2 => '武陵源区',  
          3 => '慈利县',  
          4 => '桑植县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '益阳市',  
        'area' =>   
        array (  
          1 => '资阳区',  
          2 => '赫山区',  
          3 => '南　县',  
          4 => '桃江县',  
          5 => '安化县',  
          6 => '沅江市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '郴州市',  
        'area' =>   
        array (  
          1 => '北湖区',  
          2 => '苏仙区',  
          3 => '桂阳县',  
          4 => '宜章县',  
          5 => '永兴县',  
          6 => '嘉禾县',  
          7 => '临武县',  
          8 => '汝城县',  
          9 => '桂东县',  
          10 => '安仁县',  
          11 => '资兴市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '永州市',  
        'area' =>   
        array (  
          1 => '芝山区',  
          2 => '冷水滩区',  
          3 => '祁阳县',  
          4 => '东安县',  
          5 => '双牌县',  
          6 => '道　县',  
          7 => '江永县',  
          8 => '宁远县',  
          9 => '蓝山县',  
          10 => '新田县',  
          11 => '江华瑶族自治县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '怀化市',  
        'area' =>   
        array (  
          1 => '鹤城区',  
          2 => '中方县',  
          3 => '沅陵县',  
          4 => '辰溪县',  
          5 => '溆浦县',  
          6 => '会同县',  
          7 => '麻阳苗族自治县',  
          8 => '新晃侗族自治县',  
          9 => '芷江侗族自治县',  
          10 => '靖州苗族侗族自治县',  
          11 => '通道侗族自治县',  
          12 => '洪江市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '娄底市',  
        'area' =>   
        array (  
          1 => '娄星区',  
          2 => '双峰县',  
          3 => '新化县',  
          4 => '冷水江市',  
          5 => '涟源市',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '湘西土家族苗族自治州',  
        'area' =>   
        array (  
          1 => '吉首市',  
          2 => '泸溪县',  
          3 => '凤凰县',  
          4 => '花垣县',  
          5 => '保靖县',  
          6 => '古丈县',  
          7 => '永顺县',  
          8 => '龙山县',  
        ),  
      ),  
    ),  
  ),  
  19 =>   
  array (  
    'province_name' => '广东省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '广州市',  
        'area' =>   
        array (  
          1 => '东山区',  
          2 => '荔湾区',  
          3 => '越秀区',  
          4 => '海珠区',  
          5 => '天河区',  
          6 => '芳村区',  
          7 => '白云区',  
          8 => '黄埔区',  
          9 => '番禺区',  
          10 => '花都区',  
          11 => '增城市',  
          12 => '从化市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '韶关市',  
        'area' =>   
        array (  
          1 => '武江区',  
          2 => '浈江区',  
          3 => '曲江区',  
          4 => '始兴县',  
          5 => '仁化县',  
          6 => '翁源县',  
          7 => '乳源瑶族自治县',  
          8 => '新丰县',  
          9 => '乐昌市',  
          10 => '南雄市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '深圳市',  
        'area' =>   
        array (  
          1 => '罗湖区',  
          2 => '福田区',  
          3 => '南山区',  
          4 => '宝安区',  
          5 => '龙岗区',  
          6 => '盐田区',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '珠海市',  
        'area' =>   
        array (  
          1 => '香洲区',  
          2 => '斗门区',  
          3 => '金湾区',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '汕头市',  
        'area' =>   
        array (  
          1 => '龙湖区',  
          2 => '金平区',  
          3 => '濠江区',  
          4 => '潮阳区',  
          5 => '潮南区',  
          6 => '澄海区',  
          7 => '南澳县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '佛山市',  
        'area' =>   
        array (  
          1 => '禅城区',  
          2 => '南海区',  
          3 => '顺德区',  
          4 => '三水区',  
          5 => '高明区',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '江门市',  
        'area' =>   
        array (  
          1 => '蓬江区',  
          2 => '江海区',  
          3 => '新会区',  
          4 => '台山市',  
          5 => '开平市',  
          6 => '鹤山市',  
          7 => '恩平市',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '湛江市',  
        'area' =>   
        array (  
          1 => '赤坎区',  
          2 => '霞山区',  
          3 => '坡头区',  
          4 => '麻章区',  
          5 => '遂溪县',  
          6 => '徐闻县',  
          7 => '廉江市',  
          8 => '雷州市',  
          9 => '吴川市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '茂名市',  
        'area' =>   
        array (  
          1 => '茂南区',  
          2 => '茂港区',  
          3 => '电白县',  
          4 => '高州市',  
          5 => '化州市',  
          6 => '信宜市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '肇庆市',  
        'area' =>   
        array (  
          1 => '端州区',  
          2 => '鼎湖区',  
          3 => '广宁县',  
          4 => '怀集县',  
          5 => '封开县',  
          6 => '德庆县',  
          7 => '高要市',  
          8 => '四会市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '惠州市',  
        'area' =>   
        array (  
          1 => '惠城区',  
          2 => '惠阳区',  
          3 => '博罗县',  
          4 => '惠东县',  
          5 => '龙门县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '梅州市',  
        'area' =>   
        array (  
          1 => '梅江区',  
          2 => '梅　县',  
          3 => '大埔县',  
          4 => '丰顺县',  
          5 => '五华县',  
          6 => '平远县',  
          7 => '蕉岭县',  
          8 => '兴宁市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '汕尾市',  
        'area' =>   
        array (  
          1 => '城　区',  
          2 => '海丰县',  
          3 => '陆河县',  
          4 => '陆丰市',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '河源市',  
        'area' =>   
        array (  
          1 => '源城区',  
          2 => '紫金县',  
          3 => '龙川县',  
          4 => '连平县',  
          5 => '和平县',  
          6 => '东源县',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '阳江市',  
        'area' =>   
        array (  
          1 => '江城区',  
          2 => '阳西县',  
          3 => '阳东县',  
          4 => '阳春市',  
        ),  
      ),  
      16 =>   
      array (  
        'city_name' => '清远市',  
        'area' =>   
        array (  
          1 => '清城区',  
          2 => '佛冈县',  
          3 => '阳山县',  
          4 => '连山壮族瑶族自治县',  
          5 => '连南瑶族自治县',  
          6 => '清新县',  
          7 => '英德市',  
          8 => '连州市',  
        ),  
      ),  
      17 =>   
      array (  
        'city_name' => '东莞市',  
      ),  
      18 =>   
      array (  
        'city_name' => '中山市',  
      ),  
      19 =>   
      array (  
        'city_name' => '潮州市',  
        'area' =>   
        array (  
          1 => '湘桥区',  
          2 => '潮安县',  
          3 => '饶平县',  
        ),  
      ),  
      20 =>   
      array (  
        'city_name' => '揭阳市',  
        'area' =>   
        array (  
          1 => '榕城区',  
          2 => '揭东县',  
          3 => '揭西县',  
          4 => '惠来县',  
          5 => '普宁市',  
        ),  
      ),  
      21 =>   
      array (  
        'city_name' => '云浮市',  
        'area' =>   
        array (  
          1 => '云城区',  
          2 => '新兴县',  
          3 => '郁南县',  
          4 => '云安县',  
          5 => '罗定市',  
        ),  
      ),  
    ),  
  ),  
  20 =>   
  array (  
    'province_name' => '广西壮族自治区',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '南宁市',  
        'area' =>   
        array (  
          1 => '兴宁区',  
          2 => '青秀区',  
          3 => '江南区',  
          4 => '西乡塘区',  
          5 => '良庆区',  
          6 => '邕宁区',  
          7 => '武鸣县',  
          8 => '隆安县',  
          9 => '马山县',  
          10 => '上林县',  
          11 => '宾阳县',  
          12 => '横　县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '柳州市',  
        'area' =>   
        array (  
          1 => '城中区',  
          2 => '鱼峰区',  
          3 => '柳南区',  
          4 => '柳北区',  
          5 => '柳江县',  
          6 => '柳城县',  
          7 => '鹿寨县',  
          8 => '融安县',  
          9 => '融水苗族自治县',  
          10 => '三江侗族自治县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '桂林市',  
        'area' =>   
        array (  
          1 => '秀峰区',  
          2 => '叠彩区',  
          3 => '象山区',  
          4 => '七星区',  
          5 => '雁山区',  
          6 => '阳朔县',  
          7 => '临桂县',  
          8 => '灵川县',  
          9 => '全州县',  
          10 => '兴安县',  
          11 => '永福县',  
          12 => '灌阳县',  
          13 => '龙胜各族自治县',  
          14 => '资源县',  
          15 => '平乐县',  
          16 => '荔蒲县',  
          17 => '恭城瑶族自治县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '梧州市',  
        'area' =>   
        array (  
          1 => '万秀区',  
          2 => '蝶山区',  
          3 => '长洲区',  
          4 => '苍梧县',  
          5 => '藤　县',  
          6 => '蒙山县',  
          7 => '岑溪市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '北海市',  
        'area' =>   
        array (  
          1 => '海城区',  
          2 => '银海区',  
          3 => '铁山港区',  
          4 => '合浦县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '防城港市',  
        'area' =>   
        array (  
          1 => '港口区',  
          2 => '防城区',  
          3 => '上思县',  
          4 => '东兴市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '钦州市',  
        'area' =>   
        array (  
          1 => '钦南区',  
          2 => '钦北区',  
          3 => '灵山县',  
          4 => '浦北县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '贵港市',  
        'area' =>   
        array (  
          1 => '港北区',  
          2 => '港南区',  
          3 => '覃塘区',  
          4 => '平南县',  
          5 => '桂平市',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '玉林市',  
        'area' =>   
        array (  
          1 => '玉州区',  
          2 => '容　县',  
          3 => '陆川县',  
          4 => '博白县',  
          5 => '兴业县',  
          6 => '北流市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '百色市',  
        'area' =>   
        array (  
          1 => '右江区',  
          2 => '田阳县',  
          3 => '田东县',  
          4 => '平果县',  
          5 => '德保县',  
          6 => '靖西县',  
          7 => '那坡县',  
          8 => '凌云县',  
          9 => '乐业县',  
          10 => '田林县',  
          11 => '西林县',  
          12 => '隆林各族自治县',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '贺州市',  
        'area' =>   
        array (  
          1 => '八步区',  
          2 => '昭平县',  
          3 => '钟山县',  
          4 => '富川瑶族自治县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '河池市',  
        'area' =>   
        array (  
          1 => '金城江区',  
          2 => '南丹县',  
          3 => '天峨县',  
          4 => '凤山县',  
          5 => '东兰县',  
          6 => '罗城仫佬族自治县',  
          7 => '环江毛南族自治县',  
          8 => '巴马瑶族自治县',  
          9 => '都安瑶族自治县',  
          10 => '大化瑶族自治县',  
          11 => '宜州市',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '来宾市',  
        'area' =>   
        array (  
          1 => '兴宾区',  
          2 => '忻城县',  
          3 => '象州县',  
          4 => '武宣县',  
          5 => '金秀瑶族自治县',  
          6 => '合山市',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '崇左市',  
        'area' =>   
        array (  
          1 => '江洲区',  
          2 => '扶绥县',  
          3 => '宁明县',  
          4 => '龙州县',  
          5 => '大新县',  
          6 => '天等县',  
          7 => '凭祥市',  
        ),  
      ),  
    ),  
  ),  
  21 =>   
  array (  
    'province_name' => '海南省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '海口市',  
        'area' =>   
        array (  
          1 => '秀英区',  
          2 => '龙华区',  
          3 => '琼山区',  
          4 => '美兰区',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '三亚市',  
      ),  
      3 =>   
      array (  
        'city_name' => '省直辖县级行政单位',  
        'area' =>   
        array (  
          1 => '五指山市',  
          2 => '琼海市',  
          3 => '儋州市',  
          4 => '文昌市',  
          5 => '万宁市',  
          6 => '东方市',  
          7 => '定安县',  
          8 => '屯昌县',  
          9 => '澄迈县',  
          10 => '临高县',  
          11 => '白沙黎族自治县',  
          12 => '昌江黎族自治县',  
          13 => '乐东黎族自治县',  
          14 => '陵水黎族自治县',  
          15 => '保亭黎族苗族自治县',  
          16 => '琼中黎族苗族自治县',  
          17 => '西沙群岛',  
          18 => '南沙群岛',  
          19 => '中沙群岛的岛礁及其海域',  
        ),  
      ),  
    ),  
  ),  
  22 =>   
  array (  
    'province_name' => '重庆市',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '市辖区',  
        'area' =>   
        array (  
          1 => '万州区',  
          2 => '涪陵区',  
          3 => '渝中区',  
          4 => '大渡口区',  
          5 => '江北区',  
          6 => '沙坪坝区',  
          7 => '九龙坡区',  
          8 => '南岸区',  
          9 => '北碚区',  
          10 => '万盛区',  
          11 => '双桥区',  
          12 => '渝北区',  
          13 => '巴南区',  
          14 => '黔江区',  
          15 => '长寿区',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '重庆周边',  
        'area' =>   
        array (  
          1 => '綦江县',  
          2 => '潼南县',  
          3 => '铜梁县',  
          4 => '大足县',  
          5 => '荣昌县',  
          6 => '璧山县',  
          7 => '梁平县',  
          8 => '城口县',  
          9 => '丰都县',  
          10 => '垫江县',  
          11 => '武隆县',  
          12 => '忠　县',  
          13 => '开　县',  
          14 => '云阳县',  
          15 => '奉节县',  
          16 => '巫山县',  
          17 => '巫溪县',  
          18 => '石柱土家族自治县',  
          19 => '秀山土家族苗族自治县',  
          20 => '酉阳土家族苗族自治县',  
          21 => '彭水苗族土家族自治县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '市',  
        'area' =>   
        array (  
          1 => '江津市',  
          2 => '合川市',  
          3 => '永川市',  
          4 => '南川市',  
        ),  
      ),  
    ),  
  ),  
  23 =>   
  array (  
    'province_name' => '四川省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '成都市',  
        'area' =>   
        array (  
          1 => '锦江区',  
          2 => '青羊区',  
          3 => '金牛区',  
          4 => '武侯区',  
          5 => '成华区',  
          6 => '龙泉驿区',  
          7 => '青白江区',  
          8 => '新都区',  
          9 => '温江区',  
          10 => '金堂县',  
          11 => '双流县',  
          12 => '郫　县',  
          13 => '大邑县',  
          14 => '蒲江县',  
          15 => '新津县',  
          16 => '都江堰市',  
          17 => '彭州市',  
          18 => '邛崃市',  
          19 => '崇州市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '自贡市',  
        'area' =>   
        array (  
          1 => '自流井区',  
          2 => '贡井区',  
          3 => '大安区',  
          4 => '沿滩区',  
          5 => '荣　县',  
          6 => '富顺县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '攀枝花市',  
        'area' =>   
        array (  
          1 => '东　区',  
          2 => '西　区',  
          3 => '仁和区',  
          4 => '米易县',  
          5 => '盐边县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '泸州市',  
        'area' =>   
        array (  
          1 => '江阳区',  
          2 => '纳溪区',  
          3 => '龙马潭区',  
          4 => '泸　县',  
          5 => '合江县',  
          6 => '叙永县',  
          7 => '古蔺县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '德阳市',  
        'area' =>   
        array (  
          1 => '旌阳区',  
          2 => '中江县',  
          3 => '罗江县',  
          4 => '广汉市',  
          5 => '什邡市',  
          6 => '绵竹市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '绵阳市',  
        'area' =>   
        array (  
          1 => '涪城区',  
          2 => '游仙区',  
          3 => '三台县',  
          4 => '盐亭县',  
          5 => '安　县',  
          6 => '梓潼县',  
          7 => '北川羌族自治县',  
          8 => '平武县',  
          9 => '江油市',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '广元市',  
        'area' =>   
        array (  
          1 => '市中区',  
          2 => '元坝区',  
          3 => '朝天区',  
          4 => '旺苍县',  
          5 => '青川县',  
          6 => '剑阁县',  
          7 => '苍溪县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '遂宁市',  
        'area' =>   
        array (  
          1 => '船山区',  
          2 => '安居区',  
          3 => '蓬溪县',  
          4 => '射洪县',  
          5 => '大英县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '内江市',  
        'area' =>   
        array (  
          1 => '市中区',  
          2 => '东兴区',  
          3 => '威远县',  
          4 => '资中县',  
          5 => '隆昌县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '乐山市',  
        'area' =>   
        array (  
          1 => '市中区',  
          2 => '沙湾区',  
          3 => '五通桥区',  
          4 => '金口河区',  
          5 => '犍为县',  
          6 => '井研县',  
          7 => '夹江县',  
          8 => '沐川县',  
          9 => '峨边彝族自治县',  
          10 => '马边彝族自治县',  
          11 => '峨眉山市',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '南充市',  
        'area' =>   
        array (  
          1 => '顺庆区',  
          2 => '高坪区',  
          3 => '嘉陵区',  
          4 => '南部县',  
          5 => '营山县',  
          6 => '蓬安县',  
          7 => '仪陇县',  
          8 => '西充县',  
          9 => '阆中市',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '眉山市',  
        'area' =>   
        array (  
          1 => '东坡区',  
          2 => '仁寿县',  
          3 => '彭山县',  
          4 => '洪雅县',  
          5 => '丹棱县',  
          6 => '青神县',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '宜宾市',  
        'area' =>   
        array (  
          1 => '翠屏区',  
          2 => '宜宾县',  
          3 => '南溪县',  
          4 => '江安县',  
          5 => '长宁县',  
          6 => '高　县',  
          7 => '珙　县',  
          8 => '筠连县',  
          9 => '兴文县',  
          10 => '屏山县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '广安市',  
        'area' =>   
        array (  
          1 => '广安区',  
          2 => '岳池县',  
          3 => '武胜县',  
          4 => '邻水县',  
          5 => '华莹市',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '达州市',  
        'area' =>   
        array (  
          1 => '通川区',  
          2 => '达　县',  
          3 => '宣汉县',  
          4 => '开江县',  
          5 => '大竹县',  
          6 => '渠　县',  
          7 => '万源市',  
        ),  
      ),  
      16 =>   
      array (  
        'city_name' => '雅安市',  
        'area' =>   
        array (  
          1 => '雨城区',  
          2 => '名山县',  
          3 => '荥经县',  
          4 => '汉源县',  
          5 => '石棉县',  
          6 => '天全县',  
          7 => '芦山县',  
          8 => '宝兴县',  
        ),  
      ),  
      17 =>   
      array (  
        'city_name' => '巴中市',  
        'area' =>   
        array (  
          1 => '巴州区',  
          2 => '通江县',  
          3 => '南江县',  
          4 => '平昌县',  
        ),  
      ),  
      18 =>   
      array (  
        'city_name' => '资阳市',  
        'area' =>   
        array (  
          1 => '雁江区',  
          2 => '安岳县',  
          3 => '乐至县',  
          4 => '简阳市',  
        ),  
      ),  
      19 =>   
      array (  
        'city_name' => '阿坝藏族羌族自治州',  
        'area' =>   
        array (  
          1 => '汶川县',  
          2 => '理　县',  
          3 => '茂　县',  
          4 => '松潘县',  
          5 => '九寨沟县',  
          6 => '金川县',  
          7 => '小金县',  
          8 => '黑水县',  
          9 => '马尔康县',  
          10 => '壤塘县',  
          11 => '阿坝县',  
          12 => '若尔盖县',  
          13 => '红原县',  
        ),  
      ),  
      20 =>   
      array (  
        'city_name' => '甘孜藏族自治州',  
        'area' =>   
        array (  
          1 => '康定县',  
          2 => '泸定县',  
          3 => '丹巴县',  
          4 => '九龙县',  
          5 => '雅江县',  
          6 => '道孚县',  
          7 => '炉霍县',  
          8 => '甘孜县',  
          9 => '新龙县',  
          10 => '德格县',  
          11 => '白玉县',  
          12 => '石渠县',  
          13 => '色达县',  
          14 => '理塘县',  
          15 => '巴塘县',  
          16 => '乡城县',  
          17 => '稻城县',  
          18 => '得荣县',  
        ),  
      ),  
      21 =>   
      array (  
        'city_name' => '凉山彝族自治州',  
        'area' =>   
        array (  
          1 => '西昌市',  
          2 => '木里藏族自治县',  
          3 => '盐源县',  
          4 => '德昌县',  
          5 => '会理县',  
          6 => '会东县',  
          7 => '宁南县',  
          8 => '普格县',  
          9 => '布拖县',  
          10 => '金阳县',  
          11 => '昭觉县',  
          12 => '喜德县',  
          13 => '冕宁县',  
          14 => '越西县',  
          15 => '甘洛县',  
          16 => '美姑县',  
          17 => '雷波县',  
        ),  
      ),  
    ),  
  ),  
  24 =>   
  array (  
    'province_name' => '贵州省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '贵阳市',  
        'area' =>   
        array (  
          1 => '南明区',  
          2 => '云岩区',  
          3 => '花溪区',  
          4 => '乌当区',  
          5 => '白云区',  
          6 => '小河区',  
          7 => '开阳县',  
          8 => '息烽县',  
          9 => '修文县',  
          10 => '清镇市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '六盘水市',  
        'area' =>   
        array (  
          1 => '钟山区',  
          2 => '六枝特区',  
          3 => '水城县',  
          4 => '盘　县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '遵义市',  
        'area' =>   
        array (  
          1 => '红花岗区',  
          2 => '汇川区',  
          3 => '遵义县',  
          4 => '桐梓县',  
          5 => '绥阳县',  
          6 => '正安县',  
          7 => '道真仡佬族苗族自治县',  
          8 => '务川仡佬族苗族自治县',  
          9 => '凤冈县',  
          10 => '湄潭县',  
          11 => '余庆县',  
          12 => '习水县',  
          13 => '赤水市',  
          14 => '仁怀市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '安顺市',  
        'area' =>   
        array (  
          1 => '西秀区',  
          2 => '平坝县',  
          3 => '普定县',  
          4 => '镇宁布依族苗族自治县',  
          5 => '关岭布依族苗族自治县',  
          6 => '紫云苗族布依族自治县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '铜仁地区',  
        'area' =>   
        array (  
          1 => '铜仁市',  
          2 => '江口县',  
          3 => '玉屏侗族自治县',  
          4 => '石阡县',  
          5 => '思南县',  
          6 => '印江土家族苗族自治县',  
          7 => '德江县',  
          8 => '沿河土家族自治县',  
          9 => '松桃苗族自治县',  
          10 => '万山特区',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '黔西南布依族苗族自治州',  
        'area' =>   
        array (  
          1 => '兴义市',  
          2 => '兴仁县',  
          3 => '普安县',  
          4 => '晴隆县',  
          5 => '贞丰县',  
          6 => '望谟县',  
          7 => '册亨县',  
          8 => '安龙县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '毕节地区',  
        'area' =>   
        array (  
          1 => '毕节市',  
          2 => '大方县',  
          3 => '黔西县',  
          4 => '金沙县',  
          5 => '织金县',  
          6 => '纳雍县',  
          7 => '威宁彝族回族苗族自治县',  
          8 => '赫章县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '黔东南苗族侗族自治州',  
        'area' =>   
        array (  
          1 => '凯里市',  
          2 => '黄平县',  
          3 => '施秉县',  
          4 => '三穗县',  
          5 => '镇远县',  
          6 => '岑巩县',  
          7 => '天柱县',  
          8 => '锦屏县',  
          9 => '剑河县',  
          10 => '台江县',  
          11 => '黎平县',  
          12 => '榕江县',  
          13 => '从江县',  
          14 => '雷山县',  
          15 => '麻江县',  
          16 => '丹寨县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '黔南布依族苗族自治州',  
        'area' =>   
        array (  
          1 => '都匀市',  
          2 => '福泉市',  
          3 => '荔波县',  
          4 => '贵定县',  
          5 => '瓮安县',  
          6 => '独山县',  
          7 => '平塘县',  
          8 => '罗甸县',  
          9 => '长顺县',  
          10 => '龙里县',  
          11 => '惠水县',  
          12 => '三都水族自治县',  
        ),  
      ),  
    ),  
  ),  
  25 =>   
  array (  
    'province_name' => '云南省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '昆明市',  
        'area' =>   
        array (  
          1 => '五华区',  
          2 => '盘龙区',  
          3 => '官渡区',  
          4 => '西山区',  
          5 => '东川区',  
          6 => '呈贡县',  
          7 => '晋宁县',  
          8 => '富民县',  
          9 => '宜良县',  
          10 => '石林彝族自治县',  
          11 => '嵩明县',  
          12 => '禄劝彝族苗族自治县',  
          13 => '寻甸回族彝族自治县',  
          14 => '安宁市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '曲靖市',  
        'area' =>   
        array (  
          1 => '麒麟区',  
          2 => '马龙县',  
          3 => '陆良县',  
          4 => '师宗县',  
          5 => '罗平县',  
          6 => '富源县',  
          7 => '会泽县',  
          8 => '沾益县',  
          9 => '宣威市',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '玉溪市',  
        'area' =>   
        array (  
          1 => '红塔区',  
          2 => '江川县',  
          3 => '澄江县',  
          4 => '通海县',  
          5 => '华宁县',  
          6 => '易门县',  
          7 => '峨山彝族自治县',  
          8 => '新平彝族傣族自治县',  
          9 => '元江哈尼族彝族傣族自治县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '保山市',  
        'area' =>   
        array (  
          1 => '隆阳区',  
          2 => '施甸县',  
          3 => '腾冲县',  
          4 => '龙陵县',  
          5 => '昌宁县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '昭通市',  
        'area' =>   
        array (  
          1 => '昭阳区',  
          2 => '鲁甸县',  
          3 => '巧家县',  
          4 => '盐津县',  
          5 => '大关县',  
          6 => '永善县',  
          7 => '绥江县',  
          8 => '镇雄县',  
          9 => '彝良县',  
          10 => '威信县',  
          11 => '水富县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '丽江市',  
        'area' =>   
        array (  
          1 => '古城区',  
          2 => '玉龙纳西族自治县',  
          3 => '永胜县',  
          4 => '华坪县',  
          5 => '宁蒗彝族自治县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '思茅市',  
        'area' =>   
        array (  
          1 => '翠云区',  
          2 => '普洱哈尼族彝族自治县',  
          3 => '墨江哈尼族自治县',  
          4 => '景东彝族自治县',  
          5 => '景谷傣族彝族自治县',  
          6 => '镇沅彝族哈尼族拉祜族自治县',  
          7 => '江城哈尼族彝族自治县',  
          8 => '孟连傣族拉祜族佤族自治县',  
          9 => '澜沧拉祜族自治县',  
          10 => '西盟佤族自治县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '临沧市',  
        'area' =>   
        array (  
          1 => '临翔区',  
          2 => '凤庆县',  
          3 => '云　县',  
          4 => '永德县',  
          5 => '镇康县',  
          6 => '双江拉祜族佤族布朗族傣族自治县',  
          7 => '耿马傣族佤族自治县',  
          8 => '沧源佤族自治县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '楚雄彝族自治州',  
        'area' =>   
        array (  
          1 => '楚雄市',  
          2 => '双柏县',  
          3 => '牟定县',  
          4 => '南华县',  
          5 => '姚安县',  
          6 => '大姚县',  
          7 => '永仁县',  
          8 => '元谋县',  
          9 => '武定县',  
          10 => '禄丰县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '红河哈尼族彝族自治州',  
        'area' =>   
        array (  
          1 => '个旧市',  
          2 => '开远市',  
          3 => '蒙自县',  
          4 => '屏边苗族自治县',  
          5 => '建水县',  
          6 => '石屏县',  
          7 => '弥勒县',  
          8 => '泸西县',  
          9 => '元阳县',  
          10 => '红河县',  
          11 => '金平苗族瑶族傣族自治县',  
          12 => '绿春县',  
          13 => '河口瑶族自治县',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '文山壮族苗族自治州',  
        'area' =>   
        array (  
          1 => '文山县',  
          2 => '砚山县',  
          3 => '西畴县',  
          4 => '麻栗坡县',  
          5 => '马关县',  
          6 => '丘北县',  
          7 => '广南县',  
          8 => '富宁县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '西双版纳傣族自治州',  
        'area' =>   
        array (  
          1 => '景洪市',  
          2 => '勐海县',  
          3 => '勐腊县',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '大理白族自治州',  
        'area' =>   
        array (  
          1 => '大理市',  
          2 => '漾濞彝族自治县',  
          3 => '祥云县',  
          4 => '宾川县',  
          5 => '弥渡县',  
          6 => '南涧彝族自治县',  
          7 => '巍山彝族回族自治县',  
          8 => '永平县',  
          9 => '云龙县',  
          10 => '洱源县',  
          11 => '剑川县',  
          12 => '鹤庆县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '德宏傣族景颇族自治州',  
        'area' =>   
        array (  
          1 => '瑞丽市',  
          2 => '潞西市',  
          3 => '梁河县',  
          4 => '盈江县',  
          5 => '陇川县',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '怒江傈僳族自治州',  
        'area' =>   
        array (  
          1 => '泸水县',  
          2 => '福贡县',  
          3 => '贡山独龙族怒族自治县',  
          4 => '兰坪白族普米族自治县',  
        ),  
      ),  
      16 =>   
      array (  
        'city_name' => '迪庆藏族自治州',  
        'area' =>   
        array (  
          1 => '香格里拉县',  
          2 => '德钦县',  
          3 => '维西傈僳族自治县',  
        ),  
      ),  
    ),  
  ),  
  26 =>   
  array (  
    'province_name' => '西藏自治区',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '拉萨市',  
        'area' =>   
        array (  
          1 => '城关区',  
          2 => '林周县',  
          3 => '当雄县',  
          4 => '尼木县',  
          5 => '曲水县',  
          6 => '堆龙德庆县',  
          7 => '达孜县',  
          8 => '墨竹工卡县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '昌都地区',  
        'area' =>   
        array (  
          1 => '昌都县',  
          2 => '江达县',  
          3 => '贡觉县',  
          4 => '类乌齐县',  
          5 => '丁青县',  
          6 => '察雅县',  
          7 => '八宿县',  
          8 => '左贡县',  
          9 => '芒康县',  
          10 => '洛隆县',  
          11 => '边坝县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '山南地区',  
        'area' =>   
        array (  
          1 => '乃东县',  
          2 => '扎囊县',  
          3 => '贡嘎县',  
          4 => '桑日县',  
          5 => '琼结县',  
          6 => '曲松县',  
          7 => '措美县',  
          8 => '洛扎县',  
          9 => '加查县',  
          10 => '隆子县',  
          11 => '错那县',  
          12 => '浪卡子县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '日喀则地区',  
        'area' =>   
        array (  
          1 => '日喀则市',  
          2 => '南木林县',  
          3 => '江孜县',  
          4 => '定日县',  
          5 => '萨迦县',  
          6 => '拉孜县',  
          7 => '昂仁县',  
          8 => '谢通门县',  
          9 => '白朗县',  
          10 => '仁布县',  
          11 => '康马县',  
          12 => '定结县',  
          13 => '仲巴县',  
          14 => '亚东县',  
          15 => '吉隆县',  
          16 => '聂拉木县',  
          17 => '萨嘎县',  
          18 => '岗巴县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '那曲地区',  
        'area' =>   
        array (  
          1 => '那曲县',  
          2 => '嘉黎县',  
          3 => '比如县',  
          4 => '聂荣县',  
          5 => '安多县',  
          6 => '申扎县',  
          7 => '索　县',  
          8 => '班戈县',  
          9 => '巴青县',  
          10 => '尼玛县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '阿里地区',  
        'area' =>   
        array (  
          1 => '普兰县',  
          2 => '札达县',  
          3 => '噶尔县',  
          4 => '日土县',  
          5 => '革吉县',  
          6 => '改则县',  
          7 => '措勤县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '林芝地区',  
        'area' =>   
        array (  
          1 => '林芝县',  
          2 => '工布江达县',  
          3 => '米林县',  
          4 => '墨脱县',  
          5 => '波密县',  
          6 => '察隅县',  
          7 => '朗　县',  
        ),  
      ),  
    ),  
  ),  
  27 =>   
  array (  
    'province_name' => '陕西省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '西安市',  
        'area' =>   
        array (  
          1 => '新城区',  
          2 => '碑林区',  
          3 => '莲湖区',  
          4 => '灞桥区',  
          5 => '未央区',  
          6 => '雁塔区',  
          7 => '阎良区',  
          8 => '临潼区',  
          9 => '长安区',  
          10 => '蓝田县',  
          11 => '周至县',  
          12 => '户　县',  
          13 => '高陵县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '铜川市',  
        'area' =>   
        array (  
          1 => '王益区',  
          2 => '印台区',  
          3 => '耀州区',  
          4 => '宜君县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '宝鸡市',  
        'area' =>   
        array (  
          1 => '渭滨区',  
          2 => '金台区',  
          3 => '陈仓区',  
          4 => '凤翔县',  
          5 => '岐山县',  
          6 => '扶风县',  
          7 => '眉　县',  
          8 => '陇　县',  
          9 => '千阳县',  
          10 => '麟游县',  
          11 => '凤　县',  
          12 => '太白县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '咸阳市',  
        'area' =>   
        array (  
          1 => '秦都区',  
          2 => '杨凌区',  
          3 => '渭城区',  
          4 => '三原县',  
          5 => '泾阳县',  
          6 => '乾　县',  
          7 => '礼泉县',  
          8 => '永寿县',  
          9 => '彬　县',  
          10 => '长武县',  
          11 => '旬邑县',  
          12 => '淳化县',  
          13 => '武功县',  
          14 => '兴平市',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '渭南市',  
        'area' =>   
        array (  
          1 => '临渭区',  
          2 => '华　县',  
          3 => '潼关县',  
          4 => '大荔县',  
          5 => '合阳县',  
          6 => '澄城县',  
          7 => '蒲城县',  
          8 => '白水县',  
          9 => '富平县',  
          10 => '韩城市',  
          11 => '华阴市',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '延安市',  
        'area' =>   
        array (  
          1 => '宝塔区',  
          2 => '延长县',  
          3 => '延川县',  
          4 => '子长县',  
          5 => '安塞县',  
          6 => '志丹县',  
          7 => '吴旗县',  
          8 => '甘泉县',  
          9 => '富　县',  
          10 => '洛川县',  
          11 => '宜川县',  
          12 => '黄龙县',  
          13 => '黄陵县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '汉中市',  
        'area' =>   
        array (  
          1 => '汉台区',  
          2 => '南郑县',  
          3 => '城固县',  
          4 => '洋　县',  
          5 => '西乡县',  
          6 => '勉　县',  
          7 => '宁强县',  
          8 => '略阳县',  
          9 => '镇巴县',  
          10 => '留坝县',  
          11 => '佛坪县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '榆林市',  
        'area' =>   
        array (  
          1 => '榆阳区',  
          2 => '神木县',  
          3 => '府谷县',  
          4 => '横山县',  
          5 => '靖边县',  
          6 => '定边县',  
          7 => '绥德县',  
          8 => '米脂县',  
          9 => '佳　县',  
          10 => '吴堡县',  
          11 => '清涧县',  
          12 => '子洲县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '安康市',  
        'area' =>   
        array (  
          1 => '汉滨区',  
          2 => '汉阴县',  
          3 => '石泉县',  
          4 => '宁陕县',  
          5 => '紫阳县',  
          6 => '岚皋县',  
          7 => '平利县',  
          8 => '镇坪县',  
          9 => '旬阳县',  
          10 => '白河县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '商洛市',  
        'area' =>   
        array (  
          1 => '商州区',  
          2 => '洛南县',  
          3 => '丹凤县',  
          4 => '商南县',  
          5 => '山阳县',  
          6 => '镇安县',  
          7 => '柞水县',  
        ),  
      ),  
    ),  
  ),  
  28 =>   
  array (  
    'province_name' => '甘肃省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '兰州市',  
        'area' =>   
        array (  
          1 => '城关区',  
          2 => '七里河区',  
          3 => '西固区',  
          4 => '安宁区',  
          5 => '红古区',  
          6 => '永登县',  
          7 => '皋兰县',  
          8 => '榆中县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '嘉峪关市',  
      ),  
      3 =>   
      array (  
        'city_name' => '金昌市',  
        'area' =>   
        array (  
          1 => '金川区',  
          2 => '永昌县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '白银市',  
        'area' =>   
        array (  
          1 => '白银区',  
          2 => '平川区',  
          3 => '靖远县',  
          4 => '会宁县',  
          5 => '景泰县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '天水市',  
        'area' =>   
        array (  
          1 => '秦城区',  
          2 => '北道区',  
          3 => '清水县',  
          4 => '秦安县',  
          5 => '甘谷县',  
          6 => '武山县',  
          7 => '张家川回族自治县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '武威市',  
        'area' =>   
        array (  
          1 => '凉州区',  
          2 => '民勤县',  
          3 => '古浪县',  
          4 => '天祝藏族自治县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '张掖市',  
        'area' =>   
        array (  
          1 => '甘州区',  
          2 => '肃南裕固族自治县',  
          3 => '民乐县',  
          4 => '临泽县',  
          5 => '高台县',  
          6 => '山丹县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '平凉市',  
        'area' =>   
        array (  
          1 => '崆峒区',  
          2 => '泾川县',  
          3 => '灵台县',  
          4 => '崇信县',  
          5 => '华亭县',  
          6 => '庄浪县',  
          7 => '静宁县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '酒泉市',  
        'area' =>   
        array (  
          1 => '肃州区',  
          2 => '金塔县',  
          3 => '安西县',  
          4 => '肃北蒙古族自治县',  
          5 => '阿克塞哈萨克族自治县',  
          6 => '玉门市',  
          7 => '敦煌市',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '庆阳市',  
        'area' =>   
        array (  
          1 => '西峰区',  
          2 => '庆城县',  
          3 => '环　县',  
          4 => '华池县',  
          5 => '合水县',  
          6 => '正宁县',  
          7 => '宁　县',  
          8 => '镇原县',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '定西市',  
        'area' =>   
        array (  
          1 => '安定区',  
          2 => '通渭县',  
          3 => '陇西县',  
          4 => '渭源县',  
          5 => '临洮县',  
          6 => '漳　县',  
          7 => '岷　县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '陇南市',  
        'area' =>   
        array (  
          1 => '武都区',  
          2 => '成　县',  
          3 => '文　县',  
          4 => '宕昌县',  
          5 => '康　县',  
          6 => '西和县',  
          7 => '礼　县',  
          8 => '徽　县',  
          9 => '两当县',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '临夏回族自治州',  
        'area' =>   
        array (  
          1 => '临夏市',  
          2 => '临夏县',  
          3 => '康乐县',  
          4 => '永靖县',  
          5 => '广河县',  
          6 => '和政县',  
          7 => '东乡族自治县',  
          8 => '积石山保安族东乡族撒拉族自治县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '甘南藏族自治州',  
        'area' =>   
        array (  
          1 => '合作市',  
          2 => '临潭县',  
          3 => '卓尼县',  
          4 => '舟曲县',  
          5 => '迭部县',  
          6 => '玛曲县',  
          7 => '碌曲县',  
          8 => '夏河县',  
        ),  
      ),  
    ),  
  ),  
  29 =>   
  array (  
    'province_name' => '青海省',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '西宁市',  
        'area' =>   
        array (  
          1 => '城东区',  
          2 => '城中区',  
          3 => '城西区',  
          4 => '城北区',  
          5 => '大通回族土族自治县',  
          6 => '湟中县',  
          7 => '湟源县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '海东地区',  
        'area' =>   
        array (  
          1 => '平安县',  
          2 => '民和回族土族自治县',  
          3 => '乐都县',  
          4 => '互助土族自治县',  
          5 => '化隆回族自治县',  
          6 => '循化撒拉族自治县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '海北藏族自治州',  
        'area' =>   
        array (  
          1 => '门源回族自治县',  
          2 => '祁连县',  
          3 => '海晏县',  
          4 => '刚察县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '黄南藏族自治州',  
        'area' =>   
        array (  
          1 => '同仁县',  
          2 => '尖扎县',  
          3 => '泽库县',  
          4 => '河南蒙古族自治县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '海南藏族自治州',  
        'area' =>   
        array (  
          1 => '共和县',  
          2 => '同德县',  
          3 => '贵德县',  
          4 => '兴海县',  
          5 => '贵南县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '果洛藏族自治州',  
        'area' =>   
        array (  
          1 => '玛沁县',  
          2 => '班玛县',  
          3 => '甘德县',  
          4 => '达日县',  
          5 => '久治县',  
          6 => '玛多县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '玉树藏族自治州',  
        'area' =>   
        array (  
          1 => '玉树县',  
          2 => '杂多县',  
          3 => '称多县',  
          4 => '治多县',  
          5 => '囊谦县',  
          6 => '曲麻莱县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '海西蒙古族藏族自治州',  
        'area' =>   
        array (  
          1 => '格尔木市',  
          2 => '德令哈市',  
          3 => '乌兰县',  
          4 => '都兰县',  
          5 => '天峻县',  
        ),  
      ),  
    ),  
  ),  
  30 =>   
  array (  
    'province_name' => '宁夏回族自治区',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '银川市',  
        'area' =>   
        array (  
          1 => '兴庆区',  
          2 => '西夏区',  
          3 => '金凤区',  
          4 => '永宁县',  
          5 => '贺兰县',  
          6 => '灵武市',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '石嘴山市',  
        'area' =>   
        array (  
          1 => '大武口区',  
          2 => '惠农区',  
          3 => '平罗县',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '吴忠市',  
        'area' =>   
        array (  
          1 => '利通区',  
          2 => '盐池县',  
          3 => '同心县',  
          4 => '青铜峡市',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '固原市',  
        'area' =>   
        array (  
          1 => '原州区',  
          2 => '西吉县',  
          3 => '隆德县',  
          4 => '泾源县',  
          5 => '彭阳县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '中卫市',  
        'area' =>   
        array (  
          1 => '沙坡头区',  
          2 => '中宁县',  
          3 => '海原县',  
        ),  
      ),  
    ),  
  ),  
  31 =>   
  array (  
    'province_name' => '新疆维吾尔自治区',  
    'city' =>   
    array (  
      1 =>   
      array (  
        'city_name' => '乌鲁木齐市',  
        'area' =>   
        array (  
          1 => '天山区',  
          2 => '沙依巴克区',  
          3 => '新市区',  
          4 => '水磨沟区',  
          5 => '头屯河区',  
          6 => '达坂城区',  
          7 => '东山区',  
          8 => '乌鲁木齐县',  
        ),  
      ),  
      2 =>   
      array (  
        'city_name' => '克拉玛依市',  
        'area' =>   
        array (  
          1 => '独山子区',  
          2 => '克拉玛依区',  
          3 => '白碱滩区',  
          4 => '乌尔禾区',  
        ),  
      ),  
      3 =>   
      array (  
        'city_name' => '吐鲁番地区',  
        'area' =>   
        array (  
          1 => '吐鲁番市',  
          2 => '鄯善县',  
          3 => '托克逊县',  
        ),  
      ),  
      4 =>   
      array (  
        'city_name' => '哈密地区',  
        'area' =>   
        array (  
          1 => '哈密市',  
          2 => '巴里坤哈萨克自治县',  
          3 => '伊吾县',  
        ),  
      ),  
      5 =>   
      array (  
        'city_name' => '昌吉回族自治州',  
        'area' =>   
        array (  
          1 => '昌吉市',  
          2 => '阜康市',  
          3 => '米泉市',  
          4 => '呼图壁县',  
          5 => '玛纳斯县',  
          6 => '奇台县',  
          7 => '吉木萨尔县',  
          8 => '木垒哈萨克自治县',  
        ),  
      ),  
      6 =>   
      array (  
        'city_name' => '博尔塔拉蒙古自治州',  
        'area' =>   
        array (  
          1 => '博乐市',  
          2 => '精河县',  
          3 => '温泉县',  
        ),  
      ),  
      7 =>   
      array (  
        'city_name' => '巴音郭楞蒙古自治州',  
        'area' =>   
        array (  
          1 => '库尔勒市',  
          2 => '轮台县',  
          3 => '尉犁县',  
          4 => '若羌县',  
          5 => '且末县',  
          6 => '焉耆回族自治县',  
          7 => '和静县',  
          8 => '和硕县',  
          9 => '博湖县',  
        ),  
      ),  
      8 =>   
      array (  
        'city_name' => '阿克苏地区',  
        'area' =>   
        array (  
          1 => '阿克苏市',  
          2 => '温宿县',  
          3 => '库车县',  
          4 => '沙雅县',  
          5 => '新和县',  
          6 => '拜城县',  
          7 => '乌什县',  
          8 => '阿瓦提县',  
          9 => '柯坪县',  
        ),  
      ),  
      9 =>   
      array (  
        'city_name' => '克孜勒苏柯尔克孜自治州',  
        'area' =>   
        array (  
          1 => '阿图什市',  
          2 => '阿克陶县',  
          3 => '阿合奇县',  
          4 => '乌恰县',  
        ),  
      ),  
      10 =>   
      array (  
        'city_name' => '喀什地区',  
        'area' =>   
        array (  
          1 => '喀什市',  
          2 => '疏附县',  
          3 => '疏勒县',  
          4 => '英吉沙县',  
          5 => '泽普县',  
          6 => '莎车县',  
          7 => '叶城县',  
          8 => '麦盖提县',  
          9 => '岳普湖县',  
          10 => '伽师县',  
          11 => '巴楚县',  
          12 => '塔什库尔干塔吉克自治县',  
        ),  
      ),  
      11 =>   
      array (  
        'city_name' => '和田地区',  
        'area' =>   
        array (  
          1 => '和田市',  
          2 => '和田县',  
          3 => '墨玉县',  
          4 => '皮山县',  
          5 => '洛浦县',  
          6 => '策勒县',  
          7 => '于田县',  
          8 => '民丰县',  
        ),  
      ),  
      12 =>   
      array (  
        'city_name' => '伊犁哈萨克自治州',  
        'area' =>   
        array (  
          1 => '伊宁市',  
          2 => '奎屯市',  
          3 => '伊宁县',  
          4 => '察布查尔锡伯自治县',  
          5 => '霍城县',  
          6 => '巩留县',  
          7 => '新源县',  
          8 => '昭苏县',  
          9 => '特克斯县',  
          10 => '尼勒克县',  
        ),  
      ),  
      13 =>   
      array (  
        'city_name' => '塔城地区',  
        'area' =>   
        array (  
          1 => '塔城市',  
          2 => '乌苏市',  
          3 => '额敏县',  
          4 => '沙湾县',  
          5 => '托里县',  
          6 => '裕民县',  
          7 => '和布克赛尔蒙古自治县',  
        ),  
      ),  
      14 =>   
      array (  
        'city_name' => '阿勒泰地区',  
        'area' =>   
        array (  
          1 => '阿勒泰市',  
          2 => '布尔津县',  
          3 => '富蕴县',  
          4 => '福海县',  
          5 => '哈巴河县',  
          6 => '青河县',  
          7 => '吉木乃县',  
        ),  
      ),  
      15 =>   
      array (  
        'city_name' => '省直辖行政单位',  
        'area' =>   
        array (  
          1 => '石河子市',  
          2 => '阿拉尔市',  
          3 => '图木舒克市',  
          4 => '五家渠市',  
        ),  
      ),  
    ),  
  ),  
  32 =>   
  array (  
    'province_name' => '台湾省',  
  ),  
  33 =>   
  array (  
    'province_name' => '香港特别行政区',  
  ),  
  34 =>   
  array (  
    'province_name' => '澳门特别行政区',  
  ),  
		);  
		
		echo json_encode( $all_city);exit;
	}

	public function pay()
	{
		$postdata = html_filter_array( $_REQUEST );
		
		if( $postdata['action'] == 'get_pay_param' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			//查询用户信息
			$userinfo = $this->Common->get_one( $this->user_table, array('id'=>$postdata['uid']) );
			//查询活动信息
			$data = $this->Common->get_one( $this->event_table, array('id' => $postdata['info_id']));
			//查询支付信息
			$payinfo = $this->Common->get_one( 'gr_payment',array('id'=>$postdata['id']));
			
			$path = dirname(dirname(__FILE__));
			
			include_once($path . "/libraries/wx_pay/lib/WxPay.Api.php");
			include_once($path . "/libraries/wx_pay/WxPay.JsApiPay.php");
	
			$tools = new JsApiPay();
			$openId = $tools->GetOpenid();//$userinfo['wx_openid'] ? $userinfo['wx_openid'] : $tools->GetOpenid();		
			
			//②、统一下单
			$input = new WxPayUnifiedOrder();
			$input->SetBody($this->config->item('site_name').'-活动报名');
			//$input->SetAttach($userinfo['id']);
			$input->SetOut_trade_no($payinfo['out_trade_no']);
			$input->SetTotal_fee($payinfo['money'] * 100);
			//$input->SetTime_start(date("YmdHis"));
			//$input->SetTime_expire(date("YmdHis", time() + 600));
			//$input->SetGoods_tag($postdata['info_id']);
			//$notify_url = base_url() . 'api/pay_notify';
			//$input->SetNotify_url($notify_url);
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openId);
	//print_r($input);exit;		
			$order = WxPayApi::unifiedOrder($input);	
log_message('error','GET PAY PARAM ORDER:'.serialize($order));		
			$param = $tools->GetJsApiParameters($order);
						
			$return_arr['status'] = 1;
			$return_arr['msg'] = 'success';		
			$return_arr['data'] = $param;
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($restur_arr);
		exit;
       // $viewdata['jsApiParameters'] = $tools->GetJsApiParameters($order);			
	}


	public function pay_notify()
	{
		$path = dirname(dirname(__FILE__));
        include_once($path. "/libraries/wx_pay/lib/WxPay.Api.php");
        include_once($path . "/libraries/wx_pay/lib/WxPay.Notify.php");
        include_once($path. "/libraries/wx_pay/WxPay.JsApiPay.php");

        //使用通用通知接口

        $xml = file_get_contents('php://input', 'r');
        log_message('error', "callback for weixin get data xml: $xml");
        $result = WxPayResults::Init($xml);

        if ($result['return_code'] === 'SUCCESS')
		{
            $out_trade_no = $result['out_trade_no'];
			//更新订单支付状态
			$where = array(
				'out_trade_no' => $out_trade_no,
				);
			$res = $this->Common->update( 'gr_payment',$where,array('status'=>1,'finish_time'=>time()) );
			log_message('error','PAY RESULT UPDATE RES:'.$res);
			//报名支付成功后 发送通知
			$payinfo = $this->Common->get_one( 'gr_payment', $where );
			$userinfo = $this->Common->get_one( $this->user_table, array('id' => $payinfo['uid']) );
			$info = $this->Common->get_one( $this->event_table, array('id' => $payinfo['info_id'] ) );
			$this->_send_signup_msg( $userinfo['nickname'],$userinfo['telephone'],$info );
			//更新报名状态
			$where = array(
				'uid' => $payinfo['uid'],
				'info_id' => $payinfo['info_id'],
				);
			$this->Common->update( $this->join_table, $where, array('if_accept'=>1,'accept_time'=>time()));
            exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
        }	
			
	}
	
	private function _log_integral( $uid, $integral, $reason )
	{
		$insert['uid'] = $uid;
		$insert['jifen'] = $integral;
		$insert['reason'] = $reason;
		$insert['ctime'] = time();
		
		$res = $this->Common->add( $this->integral_table, $insert );
		return $res;
	}

	private function _send_signup_msg( $username,$mobile, $info )
	{	
		$title = $info['en_title'] ? $info['en_title'] : $info['title'];
		
		//$title = mb_strlen($title) > 20 ? mb_substr($title,0,20).'...' : $title;
		$address = $info['en_address'] ? $info['en_address'] : $info['address'];
		
		$msg = array(
			'title' => $title,
			'time' => ymd($info['start_time']),
			'address' => $address,
			);
		$template_code = 'SMS_52330084';				
		$appkey = $this->alidayu_app_id;
		$secretKey = $this->alidayu_app_secrect;
		
		$this->load->library('Alidayu');				
		$c = new TopClient;
		
		$c->appkey = $appkey;
		$c->secretKey = $secretKey;
		$req = new AlibabaAliqinFcSmsNumSendRequest;
		$req->setExtend("123456");
		$req->setSmsType("normal");
		$req->setSmsFreeSignName($this->alidayu_sms_sign);
		//$req->setSmsParam("{\"code\":\"1234\",\"product\":\"alidayu\"}");
		$params_str = json_encode($msg);
		$req->setSmsParam($params_str);
		$req->setRecNum($mobile);
		$req->setSmsTemplateCode($template_code);		
		$resp = $c->execute($req);
			
		if($resp->code == 0) 
		{			
			return 1;						
		}		
		else
		{
			log_message('error','发送报名通知失败：'.json_encode($msg)."  原因：".serialize($resp) );
			return 0;
		}
	}
	
	public function test_reg_sms()
	{
		$username = 'y_xiaoting';
		$mobile = '15603673429';
		$info['start_time'] = '1488528399';
		$info['address'] = '北京';
		
		$res = $this->_send_signup_msg($username, $mobile,$info);
		echo $res;
	}

	//检测用户是否报名活动 
	//参数 action ,uid ,info_id
	public function check_signup()
	{
		$postdata = html_filter_array($_REQUEST);
		
		if( $postdata['action'] == 'check_signup' )
		{
			$postdata['uid'] = ($postdata['uid'] != $_SESSION['uid'])&& !empty($_SESSION['uid']) ? $_SESSION['uid'] : $postdata['uid'];
			$where = array(
				'uid' => $postdata['uid'],
				'info_id' => $postdata['info_id'],
				);
			$res = $this->Common->get_one( $this->join_table, $where );
			if( $res )
			{
				$return_arr['status'] = 1;
				$return_arr['msg'] = '已报名此活动';
			}
			else
			{
				$return_arr['status'] = 0;
				$return_arr['msg'] = '尚未报名此活动';
			}
		}
		else
		{
			$return_arr['status'] = 0;
			$return_arr['msg'] = 'action error';
		}
		echo json_encode($return_arr);
		exit;
	}


	 
}