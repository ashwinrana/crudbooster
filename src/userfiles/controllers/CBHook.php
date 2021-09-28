<?php 
namespace App\Http\Controllers;

use DB;
use Session;
use Request;

class CBHook extends Controller {

	/*
	| --------------------------------------
	| Please note that you should re-login to see the session work
	| --------------------------------------
	|
	*/
	public function afterLogin() {
        $users = DB::table(config('crudbooster.USER_TABLE'))->where("id", Session::get('admin_id'))->first();
        Session::put('privilege_id', $users->id_cms_privileges);
	}
}