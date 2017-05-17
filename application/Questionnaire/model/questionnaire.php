<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/9
 * Time: 10:25
 */

namespace app\Questionnaire\model;

use think\Model;
use think\Db;
class questionnaire extends Model{

    //数据库增加题组
    public function add($data){
//        $res = Db::table('user')->get();

        $res = Db::table('questionnaire_name')->insert($data);
        $id = Db::table('questionnaire_name')->getLastInsID();
        if($id){
            return true;
        }else{
            return false;
        }
    }

    //数据库查询所有数据
    public function getall($table){
        $res = Db::table($table)->select();
        return $res;
    }

    //插入单条数据
    public function insert($table,$data){

    }



}