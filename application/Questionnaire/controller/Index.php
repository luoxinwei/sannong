<?php

namespace app\Questionnaire\controller;

use app\Questionnaire\model\questionnaire;
use \think\Controller;
use \think\Request;
use think\Db;
use think\Model;
class index extends Controller
{


    //调查问卷视图
    public function index(){
        return view ('index');

//        $view = new  View();
//        return $view->fetch('index');
    }

    //添加问卷
    public function add(){
    	//接受前端post数据
    	$request = Request::instance()->post();
        echo "<pre>";
        // var_dump($request);

        //问卷名插入数据处理
        $wenjuan['name']=$request['wenjuan']['name'];
        $wenjuan['con']=$request['wenjuan']['con'];
        $uid=$request['wenjuan']['uid'];
        // var_dump($wenjuan);
        //问卷名执行插入
        $tzid = Db::name('questionnaire_name')->insertGetId($wenjuan);
        // var_dump($tzid);
        if($tzid){
        	//题库表插入数据处理
	        foreach ($request['form'] as $k=>$v){
	        	
	        	//文本框题插入
	        	if($v['type']==1){
	        		$tiku['type']='文本';
	        		$tiku['order_id']=$v['num'];
	        		$tiku['title']=$v['title'];
	        		$tiku['con']=$v['con'];
	        		$tiku['uid']=$uid;
	        		$tiku['tzid']=$tzid;
	        		$tiku['name']=$tzid.'_'.$v['num'];;
	        		$res = Db::table('question_database')->insert($tiku);
	        		// var_dump($tiku);
	        	//单选框插入处理
	        	}elseif($v['type']==2){
	        		$tiku['type']='单选';
	        		$tiku['order_id']=$v['num'];
	        		$tiku['title']=$v['title'];
	        		$tiku['con']=$v['con'];
	        		$tiku['uid']=$uid;
	        		$tiku['tzid']=$tzid;
	        		$tiku['name']=$tzid.'_'.$v['num'];
	        		// var_dump($tiku);
	        		$tid = Db::table('question_database')->insertGetId($tiku);
	        		

	        		// var_dump($tid);
	        		foreach($v['option'] as $kvo => $vo){
	        			// dump($vo);
	        			foreach ($vo as $kv => $vs) {  
							$list[$kv][$kvo]= $vs;
							$list[$kv]['tid']=$tid;
	        			}
	        			
	        		}
	        		 // var_dump($list);;
	        		// $list['tid']=$tid;
	        		// var_dump($list);
	        		$res = Db::name('question_option')->insertAll($list);
	        	
	        	//多选框插入处理	
	        	}elseif($v['type']==3){
	        		$tiku['type']='多选';
	        		$tiku['order_id']=$v['num'];
	        		$tiku['title']=$v['title'];
	        		$tiku['con']=$v['con'];
	        		$tiku['uid']=$uid;
	        		$tiku['tzid']=$tzid;
	        		$tiku['name']=$tzid.'_'.$v['num'];
	        		$tid = Db::table('question_database')->insertGetId($tiku);
	        		

	        		// var_dump($tid);
	        		foreach($v['option'] as $kvo => $vo){
	        			// dump($vo);
	        			foreach ($vo as $kv => $vs) {  
							$list[$kv][$kvo]= $vs;
							$list[$kv]['tid']=$tid;
	        			}
	        			
	        		}
	        		 
	        		$res = Db::name('question_option')->insertAll($list);
	        	}
	        }

        }else{
        	echo '问卷名插入失败';
        }

        
    }

    //以问卷名形式查询
    public function find(){
    	
    	echo '<pre>';
    	$request = Request::instance();
//    	var_dump($request->post());
    	$url = $request->pathinfo();
    	$table = explode('/',$url)[3];
    	// dump($str);

	   $paperID = Db::table('questionnaire_name') -> where("name = '".$table."'") -> find();
	   $question = Db::table('question_database') -> where('tzid = '.$paperID['id']) -> select();
	   // var_dump($question);
	   foreach ($question as $key => $value) {

	   		$options = Db::table('question_option') -> where ('tid ='.$value['id']) ->select();
	   		// var_dump($options);
	   		$question[$key]['options'] = $options;
	   		// var_dump($question);
	   	}
   		$list['name'] = $paperID['name'];
   		$list['con'] = $paperID['con'];

   		$list['question'] = $question;


   		$que_str = '';
	   	foreach($list['question'] as $k=>$v){
	   	   		if($v['type']=='文本'){
	   			 $que_str .= $v['order_id'].'.'.$v['title'].'-'.$v['con']."<br><input type='text' name='".$v['name']."'><br><br>";
	   		}elseif($v['type']=='单选'){
	   			$que_str .= '<br><br>'.$v['order_id'].'.'.$v['title'].'-'.$v['con'].'<br>';
	   			foreach($v['options'] as $k1 => $v1){
	   				$que_str .= $v1['option']."<input type='radio' name='".$v['name']."' value='".$v1['option']."'>".$v1['value'].'&nbsp&nbsp';
	   			}
	   			// echo $v['order_id'].'.'.$v['title'].'<br>'.$v[]."<input type='text' name=''>";
	   		}elseif($v['type']=='多选'){
	   			$que_str .= '<br><br>'.$v['order_id'].'.'.$v['title'].'-'.$v['con'].'<br>';
	   			foreach($v['options'] as $k1 => $v1){
	   				$que_str .= $v1['option']."<input type='checkbox' name='".$v['name']."[]' value='".$v1['option']."'>".$v1['value'].'&nbsp&nbsp&nbsp';
	   			}
	   		}
	   	}
		var_dump($que_str);
	   return view('chaxun',['data'=>$list,'que_str' => $que_str]);
	}

	//答案表插入
	public function  insert(){
		$request = Request::instance();
		echo '<pre>';
//		var_dump($request->post());
		//答案表插入数据处理
		foreach($request->post() as $k=>$v){
//			var_dump($k.'==>'.$v);
			$res = Db::table('question_database')-> where("name = '".$k."'")->find();

			//多选项答案表插入处理
			if(is_array($v)){
				$an['tid'] = $res['id'];
				$an['uid'] =3;
				$an['answer']=implode('&',$v);
				$res = Db::table('question_answer')->insert($an);
			//单个答案表插入处理
			}else{
				$an['tid'] = $res['id'];
				$an['uid'] =3;
				$an['answer']=$v;
				$res = Db::table('question_answer')->insert($an);
			}
//
		}


	}

	//查看数据
	public function cat(){
		echo '<pre>';
		$table = '测试调查问卷';
		$res = Db::table('questionnaire_name')->where("name = '".$table."'")->find();
		$res1 = Db::table('question_database')->where("tzid = '".$res['id']."'")->select();
		$tid='';
		$cat='';
		$final='';
		$cat2='';
		foreach ($res1 as $k=>$v){
			$tid[] .= $v['id'];

			$res2 = Db::table('question_answer')->where("tid = '".$v['id']."'")->select();
//			var_dump($res2);
			foreach($res2 as $ko =>$vo){
				$cat[$ko][$k]=$vo;
			}
		}
//		var_dump($cat);
		foreach ($cat as $k1=>$v1){
//			dump($v1);
			foreach($v1 as $k2=>$v2){
				$cat2[$k2][$k1]=$v2['answer'];
			}
		}

		foreach($cat2 as $k3 => $v3){

			foreach($v3 as $k4=>$v4){
				$final[$k4][$k3] = $v4;
			}
		}
		dump($final);
//		foreach($v['option'] as $kvo => $vo){
//			// dump($vo);
//			foreach ($vo as $kv => $vs) {
//				$list[$kv][$kvo]= $vs;
//				$list[$kv]['tid']=$tid;
//			}

	}

	//数据统计



}

//查询带选项的问题	

