<?php 
namespace App\Helpers;
use Request;
use User;
use Illuminate\Support\Facades\DB;
use App\LogActivity as LogActivityModel;


class LogActivity
{


    public static function addToLog($subject)
    {
    	$log = [];
    	$log['subject'] = $subject;
    	$log['url'] = Request::fullUrl();
    	$log['method'] = Request::method();
    	$log['ip'] = Request::ip();
    	$log['agent'] = Request::header('user-agent');
    	$log['user_id'] = auth()->check() ? auth()->user()->id : 1;
        LogActivityModel::create($log);
    }


    public static function logActivityLists()
    {
        return DB::table('log_activities')
        ->join('users', 'log_activities.user_id', 'users.id')
        ->select('log_activities.*', 'users.name as usuario')
        ->orderBy('log_activities.id', 'DESC')
        ->get();
    }


}