<?php
namespace app\Questionnaire\controller;

use app\Questionnaire\model\questionnaire;
use \think\Controller;
use \think\Request;
use think\Db;
use think\Model;

class question extends Controller{
    public function index(){
//        echo 123;
        return view ('wenjuan');

    }
    public function test(){
        $request = Request::instance();
        var_dump($request->post());
        return view('test');
    }

//  题库表问题插入
     public function add(){
        echo '<pre>';
        $request = Request::instance();
//        var_dump($request->post());
//        exit;
        //问卷名插入数据处理
        //        var_dump($request->post()['wenjuan']);
        $wenjuan['name']=$request->post()['wenjuan']['name'];
        $wenjuan['con']=$request->post()['wenjuan']['con'];
//        $uid=$request->post()['wenjuan']['uid'];
        //         var_dump($wenjuan);
        //问卷名执行插入
        $tzid = Db::name('questionnaire_name')->insertGetId($wenjuan);
        //        题库表数据插入处理
        $tiku='';


        $list = $request->post()['form'];
        foreach($list as $k=>$v){
            if($v['type'] == '矩阵填空'){
                $tiku['con'] = $v['con'];
                $tiku['uid'] = '2';
                $tiku['tzid'] = $tzid;
                $tiku['type'] = $v['type'];

                if(count($v['title'])>1){
                    $tiku['title'] = implode('*',$v['title']);
                }
//                dump($tiku);
                //题库表插入操作
                $tid = Db::table('question_database')->insertGetId($tiku);
                $res = Db::table('question_database')->where('id',$tid)->update(['name'=>$tid]);
                if($res){
                    echo '成功';
                }else{
                    echo '失败';
                }
            }elseif($v['type']== '矩阵单选'|| $v['type']== '矩阵多选' ){
//                dump($v);
                $tiku['con'] = $v['con'];
                $tiku['uid'] = '2';
                $tiku['tzid'] = $tzid;
                $tiku['type'] = $v['type'];
                if(count($v['title'])>1){
                    $tiku['title'] = implode('*',$v['title']);
                }
//                dump($tiku);
                $tid = Db::table('question_database')->insertGetId($tiku);
                $res = Db::table('question_database')->where('id',$tid)->update(['name'=>$tid]);
                $option = '';

                foreach($v['value'] as $k1=>$v1){
                    $option['tid']=$tid;
                    $option['value']=$v1;
                    $id = Db::table('question_option')->insertGetId($option);
                }
            }elseif( $v['type']== '单选' ||  $v['type']== '多选'){
                $tiku['con'] = $v['con'];
                $tiku['uid'] = '2';
                $tiku['tzid'] =  $tzid;
                $tiku['type'] = $v['type'];
                $tiku['title'] = $v['title'];

//                dump($tiku);
                $tid = Db::table('question_database')->insertGetId($tiku);
                $res = Db::table('question_database')->where('id',$tid)->update(['name'=>$tid]);
                $option = '';

                foreach($v['value'] as $k1=>$v1){
                    $option['tid']=$tid;
                    $option['value']=$v1;
                    $id = Db::table('question_option')->insertGetId($option);
                }
            }

        }

    }

    public function find(){

        echo '<pre>';
        $request = Request::instance();
//    	var_dump($request->post());

        $url = $request->pathinfo();
        $table = explode('/',$url)[3];
        $final = '';
        $radio='';
        $checkbox='';
        $tiankong='';
        $jzradio='';
        $str = '';
        $paperID = Db::table('questionnaire_name') -> where("name = '".$table."'") -> find();
        $question = Db::table('question_database') -> where('tzid = '.$paperID['id']) -> select();

//        dump($question);
        foreach ($question as $k => $v) {
//            dump($v);
            $option=array();
            if($v['type']=='矩阵单选'){
                $res = Db::table('question_option')->where("tid ='".$v['id']."'")->select();
//                dump($res);
                foreach($res as $k1 => $v1){
                    $option[$v['title']][]= $v1['value'];
                }
//                dump($option);
                foreach($option as $k1=>$v1){
                    dump($v1);
                    $title = explode('*',$k1);
//                    dump($title);
                    foreach($title as $k2 =>$v2){
//                        dump($k2);
                        if($k2=='0'){
                            $jzradio .= $title[$k2];
                            $str .= $title[$k2];

                        }else{
                            $options=array();
                            $jzradio.= $v2."<input type='radio' name='opt".'_260'."' value='".$k2."'>";

//                            foreach($v1 as $ko=>$vo){
////                                dump($title[$k2]);
//                                 $options[] .= "".$vo."<input type='radio'>";
//
//                                $jzradio .= "".$title[$k2]."".$vo."<input type='radio' name='".$v['name']."' value='".$ko."'>";
//                            }
//                           var_dump($options);
//                            foreach($options as $k3=>$v3){
//                                dump($v3);
//                                $jzradio .=
//                            }
//                            $jzradio .= "".$title[$k1]."<input type='radio' name='".$v['name']."' value='".$k2."'>";
                        }
                    }
                     }
                 }


            if($v['type']=='单选' ){
                $res = Db::table('question_option')->where("tid ='".$v['id']."'")->select();;
                foreach($res as $k1 => $v1){
                    $option[$v['title']][]= $v1['value'];
                }
                foreach($option as $k2=>$v2){
                    $radio.="<div>".$k2."</div>";
                    $str.="<div>".$k2."</div>";
                    foreach($v2 as $k3=>$v3){
                        $radio .= "".$v3."<input  type='radio' name='".$v['name']."' value='".$k2."'>";

                    }
                }
            }elseif($v['type']=='多选'){
                $res = Db::table('question_option')->where("tid ='".$v['id']."'")->select();

                foreach($res as $k1 => $v1) {
                    $option[$v['title']][] = $v1['value'];
                }
                foreach($option as $k2=>$v2){
                    $checkbox.="<div>".$k2."</div>";
                    $str.="<div>".$k2."</div>";
                    foreach($v2 as $k3=>$v3){
                        $checkbox .= "".$v3."<input  type='checkbox' name='".$v['name']."' value='".$k2."'>";

                    }
                }
            }elseif($v['type']=='矩阵填空'){
                $title = explode('*',$v['title']);
                foreach($title as $ko =>$vo){
                    if($ko=='0'){
                        $tiankong .= $title[$ko];
                        $str .= $title[$ko];
                    }else{
                        $tiankong.= "".$title[$ko]."<input type='text' name='".$v['name']."' >";

                    }
                }
            }
        }
//        dump($jzradio);
        print_r($jzradio);
//        var_dump($final);
//        return view('find',['list'=> $str]);



    }
}
