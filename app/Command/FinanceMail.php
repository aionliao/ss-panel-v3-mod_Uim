<?php


namespace App\Command;

use App\Models\User;
use App\Models\Ann;
use App\Models\Code;
use App\Services\Config;
use App\Services\Mail;
use App\Services\Analytics;
use App\Utils\Telegram;
use App\Utils\Tools;

use Ozdemir\Datatables\Datatables;
use App\Utils\DatatablesHelper;

class FinanceMail
{
    public static function sendFinanceMail_day()
    {
		$datatables = new Datatables(new DatatablesHelper());
        $datatables->query(
		'select code.number,code.userid,code.usedatetime from code
		where TO_DAYS(NOW()) - TO_DAYS(code.usedatetime) = 1 and code.type = -1 and code.isused= 1');
		$text_json=$datatables->generate();
        $text_array=json_decode($text_json,true);
        $codes=$text_array['data'];
        $text_html='<table border=1><tr><td>金额</td><td>用户ID</td><td>用户名</td><td>充值时间</td>';
		$income_count=0;
		$income_total=0.00;
		foreach($codes as $code){
			$text_html.='<tr>';
			$text_html.='<td>'.$code['number'].'</td>';
			$text_html.='<td>'.$code['userid'].'</td>';
			$user=User::find($code['userid']);
			$text_html.='<td>'.$user->user_name.'</td>';
			$text_html.='<td>'.$code['usedatetime'].'</td>';
			$text_html.='</tr>';
			$income_count+=1;
			$income_total+=$code['number'];
		}
		$text_html.='</table>';
		$text_html.='<br>昨日总收入笔数：'.$income_count.'<br>昨日总收入金额：'.$income_total;

        $adminUser = User::where("is_admin", "=", "1")->get();
        foreach ($adminUser as $user) {
			echo "Send offline mail to user: ".$user->id;
			$subject = Config::get('appName')."-财务日报";
			$to = $user->email;
			$title='财务日报';
			$text = $text_html;
			try {
			Mail::send($to, $subject, 'news/finance.tpl', [
			"user" => $user,"title"=>$title,"text" => $text
			], [
			]);
			} catch (Exception $e) {
			echo $e->getMessage();
			}
		}
        
		if (Config::get("finance_public")=="true") {
			$sts = new Analytics();    
			Telegram::Send(
				"新鲜出炉的财务日报~".PHP_EOL.
				"昨日总收入笔数:".$sts->getTodayCheckinUser().PHP_EOL.
				"昨日总收入金额:".Tools::flowAutoShow($lastday_total).PHP_EOL.
				"凌晨也在努力工作~"
			);
		}
    }

	public static function sendFinanceMail_week()
	{
		$datatables = new Datatables(new DatatablesHelper());
        $datatables->query(
		'select code.number from code
		where yearweek(date_format(code.usedatetime,\'%Y-%m-%d\')) = yearweek(now())-1 and code.isused= 1');
		//每周的第一天是周日，因此统计周日～周六的七天
		$text_json=$datatables->generate();
        $text_array=json_decode($text_json,true);
        $codes=$text_array['data'];
        $text_html='';
		$income_count=0;
		$income_total=0.00;
		foreach($codes as $code){
			$income_count+=1;
			$income_total+=$code['number'];
		}
		$text_html.='<br>上周总收入笔数：'.$income_count.'<br>上周总收入金额：'.$income_total;

        $adminUser = User::where("is_admin", "=", "1")->get();
        foreach ($adminUser as $user) {
			echo "Send offline mail to user: ".$user->id;
			$subject = Config::get('appName').'-财务周报';
			$to = $user->email;
			$title='财务周报';
			$text = $text_html;
			try {
			Mail::send($to, $subject, 'news/finance.tpl', [
			"user" => $user,"title"=>$title,"text" => $text
			], [
			]);
			} catch (Exception $e) {
			echo $e->getMessage();
			}
		}
        
		if (Config::get("finance_public")=="true") {
			$sts = new Analytics();    
			Telegram::Send(
				"新鲜出炉的财务周报~".PHP_EOL.
				"上周总收入笔数:".$sts->getTodayCheckinUser().PHP_EOL.
				"上周总收入金额:".Tools::flowAutoShow($lastday_total).PHP_EOL.
				"周末也在努力工作~"
			);
		}
	}

	public static function sendFinanceMail_month()
	{
		$datatables = new Datatables(new DatatablesHelper());
        $datatables->query(
		'select code.number from code
		where date_format(code.usedatetime,\'%Y-%m\')=date_format(date_sub(curdate(), interval 1 month),\'%Y-%m\') and code.type = -1 and code.isused= 1');
		$text_json=$datatables->generate();
        $text_array=json_decode($text_json,true);
        $codes=$text_array['data'];
        $text_html='';
		$income_count=0;
		$income_total=0.00;
		foreach($codes as $code){
			$income_count+=1;
			$income_total+=$code['number'];
		}
		$text_html.='<br>上月总收入笔数：'.$income_count.'<br>上月总收入金额：'.$income_total;

        $adminUser = User::where("is_admin", "=", "1")->get();
        foreach ($adminUser as $user) {
			echo "Send offline mail to user: ".$user->id;
			$subject = Config::get('appName').'-财务月报';
			$to = $user->email;
			$title='财务月报';
			$text = $text_html;
			try {
			Mail::send($to, $subject, 'news/finance.tpl', [
			"user" => $user,"title"=>$title,"text" => $text
			], [
			]);
			} catch (Exception $e) {
			echo $e->getMessage();
			}
		}
        
		if (Config::get("finance_public")=="true") {
			$sts = new Analytics();    
			Telegram::Send(
				"新鲜出炉的财务月报~".PHP_EOL.
				"上月总收入笔数:".$sts->getTodayCheckinUser().PHP_EOL.
				"上月总收入金额:".Tools::flowAutoShow($lastday_total).PHP_EOL.
				"月初也在努力工作~"
			);
		}
	}
}
