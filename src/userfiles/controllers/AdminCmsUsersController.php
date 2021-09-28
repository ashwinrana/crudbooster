<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Session;
use Request;
use CRUDbooster;
use crocodicstudio\crudbooster\controllers\CBController;

class AdminCmsUsersController extends CBController {


    public function cbInit() {
        # START CONFIGURATION DO NOT REMOVE THIS LINE
        $this->table               = 'cms_users';
        $this->primary_key         = 'id';
        $this->title_field         = "name";
        $this->button_action_style = 'button_icon';
        $this->button_import 	   = FALSE;
        $this->button_export 	   = FALSE;
        $this->limit = "20";
        $this->orderby = "id,desc";
        $this->show_numbering = TRUE;
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = array();
        $this->col[] = array("label"=>"Name","name"=>"name");
        $this->col[] = array("label"=>"Email","name"=>"email");
        $this->col[] = array("label"=>"Privilege","name"=>"id_cms_privileges","join"=>"cms_privileges,name");
        $this->col[] = array("label"=>"Photo","name"=>"photo","image"=>1);
        $this->col[] = ["label" => "Status", "name" => "status", "callback_php" => '($row->status=="1")?"<span class=\"label label-success\">Active</span>":"<span class=\"label label-danger\">Inactive</span>"'];
        # END COLUMNS DO NOT REMOVE THIS LINE

        # START FORM DO NOT REMOVE THIS LINE
        $this->form = array();
        $this->form[] = array("label"=>"Name","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3');
        $this->form[] = array("label"=>"Email","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId());
        $this->form[] = array("label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px",'validation'=>'image|max:1000','resize_width'=>90,'resize_height'=>90);
        $this->form[] = array("label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name",'required'=>true);
        // $this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change");
        $this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change","validation" => "min:6|confirmed");
        $this->form[] = array("label"=>"Password Confirmation","name"=>"password_confirmation","type"=>"password","help"=>"Please leave empty if not change","validation" => "min:6");
        $this->form[] = ["label" => "Status", "name" => "status", "type" => "radio", "required" => true, 'dataenum' => '1|Active;0|Inactive', 'value' => '1'];
        # END FORM DO NOT REMOVE THIS LINE
        $this->sub_module = array();
        $this->addaction = array();
        $this->addaction[] = ['label' => 'Active', 'url' => CRUDBooster::mainpath('set-status/0/[id]'), 'icon' => 'fa fa-check', 'color' => 'success', 'showIf' => "[status] == '1'", 'confirmation' => true];
        $this->addaction[] = ['label' => 'Inactive', 'url' => CRUDBooster::mainpath('set-status/1/[id]'), 'icon' => 'fa fa-ban', 'color' => 'danger', 'showIf' => "[status] == '0'", 'confirmation' => true];
        $this->table_row_color = array();
        $this->table_row_color[] = ['condition' => "[status] == 0", "color" => "danger"];
    }

    public function getProfile() {

        $this->button_addmore = FALSE;
        $this->button_cancel  = FALSE;
        $this->button_show    = FALSE;
        $this->button_add     = FALSE;
        $this->button_delete  = FALSE;
        $this->hide_form 	  = ['id_cms_privileges'];

        $data['page_title'] = cbLang("label_button_profile");
        $data['row']        = CRUDBooster::first('cms_users',CRUDBooster::myId());

        return $this->view('crudbooster::default.form',$data);
    }
    public function hook_before_edit(&$postdata,$id) {
        unset($postdata['password_confirmation']);
    }
    public function hook_before_add(&$postdata) {
        /* check password and confirm password blank */
        if ($postdata['password'] == null || $postdata['password_confirmation'] == null) {
            if ($postdata['password'] == null)
                return CRUDBooster::redirectBack('Password field can not be empty !');
            elseif ($postdata['password_confirmation'] == null)
                return CRUDBooster::redirectBack('Confirm password  field can not be empty !');
            else
                return CRUDBooster::redirectBack('Password and confirm password  field can not be empty !');
        }
        unset($postdata['password_confirmation']);
    }
    public function hook_query_index(&$query)
    {
        $query
            ->where([
                ['cms_users.id', '<>', CRUDBooster::myId()]
            ]);
    }
    public function getSetStatus($status, $id)
    {
        //find user
        $user = DB::table('cms_users')->where('id', $id)->first();
        if ($user->status == 1) {
            $existingStatus = 'Active';
            $newStatus = 'Inactive';
        } else {
            $existingStatus = 'Inactive';
            $newStatus = 'Active';
        }
        DB::table('cms_users')->where('id', $id)
            ->update(['status' => $status]);

        //create logs
        CRUDBooster::insertLog(trans("crudbooster.user_status", ['email' => $user->email, 'existing_status' => $existingStatus, 'new_status' => $newStatus, 'ip' => \Request::ip()]));
        //This will redirect back and gives a message
        CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "The status  has been updated !", "info");
    }
}