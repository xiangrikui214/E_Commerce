<?php

namespace App\Http\Controllers\Service;

use App\Tool\Validate\ValidateCode;
use App\Http\Controllers\Controller;
use App\Tool\SMS\SendTemplateSMS;
use App\Entity\TempPhone;
use App\Entity\TempEmail;
use Illuminate\Http\Request;
use App\Models\M3Result;
use App\Entity\Member;


class ValidateController extends Controller
{
	//生成图片验证码
    public function create(Request $request){
        $ValidateCode = new ValidateCode;
        $request->session()->put('validate_code',$ValidateCode->getCode());
        return $ValidateCode->doimg();
    }
    //发送短信验证码
    public function sendSMS(Request $request){
    	$m3_result = new M3Result;

    	$phone = $request->input('phone','');
    	if($phone == ''){
    		$m3_result->status = 1;
    		$m3_result->message = '手机号不能为空';
    		return $m3_result->toJson();
    	}
    	//生成六位短信验证码
    	$sendTemplateSMS = new SendTemplateSMS;
    	$code = '';
    	$charset = '1234567890';
    	$_len = strlen($charset) - 1;
        for ($i = 0;$i < 6;++$i) {
            $code .= $charset[mt_rand(0, $_len)];
        }
        //发送短信验证码到手机
		$m3_result = $sendTemplateSMS->sendTemplateSMS($phone, array($code,60),1);
		if($m3_result->status == 0){
			//将手机号和验证码存入数据库
            $tempPhone = TempPhone::where('phone', $phone)->first();
            if($tempPhone == null) {
                $tempPhone = new TempPhone;
            }

			$tempPhone->phone = $phone;
			$tempPhone->code = $code;
			$tempPhone->deadline = date('Y-m-d H-i-s', time()+60*60);
			$tempPhone->save();
		}
		return $m3_result->toJson();
    }

    //验证邮箱
    public function validateEmail(Request $request){
        $member_id = $request->input('member_id','');
        $code = $request->input('code','');
        if($member_id == '' || $code == ''){
            return '验证异常';
        }
        $tempEmail = TempEmail::where('member_id', $member_id)->first();
        if($tempEmail == null){
            return '验证异常';
        }
        if($tempEmail->code == $code){
            if(time() > strtotime($tempEmail->deadline)){
                return '该链接已失效';
            }
            $member = Member::find($member_id);
            $member->active = 1;//对应用户的active置为1,表示已激活
            $member->save();
            return redirect('/login');//重定向到登录界面
        }
    }
}
