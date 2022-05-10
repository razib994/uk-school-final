<?php

namespace App\Http\Controllers;
//later i will use use App\Traits\HasRoleAndPermission;
use App\Models\WebAction;
use Illuminate\Http\Request;
use Validator;
use Session;
use DB;
use \App\Models\System;
use \App\Models\Setting;
use App\Models\Menu;
use App\ModelsWebAction;
use App\Models\UserGroup;
use App\Models\UserGroupPermission;
use Auth;
use App\Traits\HasPermission;

class SettingController extends Controller
{
	use HasPermission;

    public function __construct(Request $request)
    {
        $this->page_title = $request->route()->getName();
        $description = \Request::route()->getAction();
        $this->page_desc = isset($description['desc']) ? $description['desc'] : $this->page_title;
    }

	//General Setting
	public function generalSetting(){
		$data['page_title'] 	= $this->page_title;
		$data['module_name']	= "Settings";
		$data['sub_module']		= "General Setting";

		$data['setting'] 		= Setting::first();
		// action permissions
        $admin_user_id  		= Auth::user()->id;
        $add_action_id  		= 15;// update action id
        $add_permisiion 		= $this->PermissionHasOrNot($admin_user_id,$add_action_id );
        $data['actions']['update_permisiion']= $add_permisiion;

		return view('setting.general_setting',$data);
	}

	//Update General  Setting
	public function generalSettingUpdate(Request $request){
		$rule = [
            'company_name' 		=> 'Required|max:100',
            'short_name' 		=> 'Required|max:15',
            'site_name' 		=> 'Required',
            'admin_email' 		=> 'Required|email',
            'admin_mobile' 		=> 'Required',
            'logo_image' 		=> 'mimes:jpeg,jpg,png,svg'
        ];

        $validation = Validator::make($request->all(), $rule);
        if ($validation->fails()) {
			$return['result'] = "0";
			$return['errors'] = $validation->errors();
			return json_encode($return);
        }
		else{
			try{
				DB::beginTransaction();
				$data = Setting::first();
				$settings_data = [
					'company_name'	=>$request->company_name,
					'short_name'	=>$request->short_name,
					'site_name'		=>$request->site_name,
					'admin_email'	=>$request->admin_email,
					'admin_mobile'	=>$request->admin_mobile,
					'site_url'		=>$request->site_url,
					'admin_url'		=>$request->admin_url,
					'address'		=>$request->address,
				];

				//if logo Image provided
				$logo_image = $request->file('logo');
				if (isset($logo_image)){
					$image_name 				= $logo_image->getClientOriginalName();
					if($image_name != $data->logo){
						$upload_path 				= 'assets/images/admin-upload/';
						$success					= $logo_image->move($upload_path,$image_name);
						$settings_data['logo'] 		= $image_name;
					}
				}
				$data->update($settings_data);
				//dd($settings_data);
				DB::commit();
				$return['result'] = "1";
				return json_encode($return);
			}
			catch (\Exception $e){
				DB::rollback();
				$return['result'] 	= "0";
				$return['message'][] = "Faild to save";
				return json_encode($return);
			}
		}
	}


	public function moduleManagement(){
		$data['page_title'] = $this->page_title;
		$data['module_name']= "Cpanel";
		$data['sub_module']	= "Menus/Moduls";
		$data['parentmenus']= Menu::where('parent_id','0')->get();
		// action permissions
        $admin_user_id  	= Auth::user()->id;
        $add_action_id  	= 8;// Module Management
        $add_permisiion 	= $this->PermissionHasOrNot($admin_user_id,$add_action_id );
        $data['actions']['add_permisiion']= $add_permisiion;
		return view('setting.manage_module',$data);
	}


	public function ajaxMenuList(){
		$admin_user_id 		= Auth::user()->id;
		$edit_action_id 	= 10;
		$delete_action_id 	= 11;
		$edit_permisiion 	= $this->PermissionHasOrNot($admin_user_id,$edit_action_id);
		$delete_permisiion 	= $this->PermissionHasOrNot($admin_user_id,$delete_action_id);

		$mesuList = Menu::Select('module_name', 'id',  'menu_title',  'parent_id', 'menu_url','menu_icon_class','status')->where('status','1')->orderBy('created_at','desc')->get();

		$return_arr = array();
		foreach($mesuList as $menu){
			$menu['status']=($menu->status == 1)?"<button class='btn btn-sm btn-success' disabled>Active</button>":"<button class='btn btn-xs btn-success' disabled>In-active</button>";
			$menu['actions'] = "";

			if($edit_permisiion>0){
				$menu['actions'] .="<button onclick='moduleEdit(".$menu->id.")' id=edit_" . $menu->id . "  class='btn btn-xs btn-hover-shine  btn-primary module-edit'><i class='lnr-pencil'></i></button>";
			}
			if ($delete_permisiion>0) {
				$menu['actions'] .=" <button onclick='moduleDelete(".$menu->id.")' id='delete_" . $menu->id . "' class='btn btn-xs btn-hover-shine btn-danger' ><i class='fa fa-trash'></i></button>";
			}
			$return_arr[] = $menu;
		}
		return json_encode(array('data'=>$return_arr));
	}

	//getting parent menu
	public function getParentMenu(){
		$parent_menu = Menu::Select('module_name')->where('parent_id','0')->orderBy('id', 'desc')->get();
		return json_encode(array('data'=>$parent_menu));
	}

	//Module Entry
	public function moduleEntry(Request $request){
		$rule = [
            'module_name' 	=> 'Required|max:50',
            'menu_title' 	=> 'Required|max:50',
        ];

        $validation = Validator::make($request->all(), $rule);
        if ($validation->fails()) {
			$return['result'] = "0";
			$return['errors'] = $validation->errors();
			return json_encode($return);
        }
		else{
			try{
				$menu_url 	= ($request->menu_url=="")?"":$request->menu_url;
				$parent_id 	= ($request->parent_id=="")?0:$request->parent_id;

				DB::beginTransaction();
				$menu_data = [
					'module_name'		=> $request->module_name,
					'menu_title'		=> $request->menu_title,
					'parent_id'			=> $request->parent_id,
					'menu_url'			=> $menu_url,
					'menu_icon_class'	=> $request->menu_icon_class,
				];
				//dd($menu_data);
				if($request->edit_id !=""){
					$data = Menu::find($request->edit_id);
					$data->update($menu_data);
				}

				else{
					$response = Menu::create($menu_data);
				}

				DB::commit();
				$return['result'] = "1";
				return json_encode($return);
			}
			catch (\Exception $e){
				DB::rollback();
				$return['result'] 	= "0";
				$return['message'][] ="Faild to save";
				return json_encode($return);
			}
		}
	}


	//get data for update
	public function moduleEdit($id){
		 $data = Menu::Select('id','module_name','menu_title','menu_url','menu_icon_class','parent_id')->where('id',$id)->first();
		return json_encode($data);
	}


	//Module delete
	public function moduleDelete($id){
		$parent_menu = Menu::Select('module_name','parent_id')->where('id',$id)->first();
		if ($parent_menu['parent_id']=='0') {
			$sub_menus = Menu::where('parent_id',$id)->count();
			if($sub_menus>0){
				return json_encode(array(
					"parentmessage"=>"You cant not delete ".$parent_menu['module_name'].". It has Sub-menu",
				));
			}
			else{
				$actions = WebAction::where('module_id',$id)->count();
				if($actions==0){
					Menu::Select('id')->where('id',$id)->delete();
					return json_encode(array(
						"deleteMessage"=>"Delete Successful",
					));
				}
				else{
					return json_encode(array(
						"parentmessage"=>"You cant not delete ".$parent_menu['module_name'].". It has actions",
					));
				}
			}
		}else{
			$actions = WebAction::where('module_id',$id)->count();
			if($actions==0){
				Menu::Select('id')->where('id',$id)->delete();
				return json_encode(array(
					"deleteMessage"=>"Delete Successful",
				));
			}
			else{
				return json_encode(array(
					"parentmessage"=>"You cant not delete ".$parent_menu['module_name'].". It has actions",
				));
			}
		}
	}


	//Web Aciton Management Start Here

	//Get web action page
	public function webActionManagement(){
		$data['page_title'] 	= $this->page_title;
		$data['module_name']	= "Cpanel";
		$data['sub_module']		= "Actions";
		$data['setting'] 		= Setting::first();
		$data['parentmenus'] 	= Menu::where('parent_id','0')->get();
		$data['menus'] 			= Menu::all();
		// action permissions
        $admin_user_id  		= Auth::user()->id;
        $add_action_id  		= 5; // Action Management
        $add_permisiion 		= $this->PermissionHasOrNot($admin_user_id,$add_action_id );
        $data['actions']['add_permisiion']= $add_permisiion;
		return view('setting.web_action_management',$data);
	}

	//get module name and id
	public function getModuleName(){
		$data = Menu::Select('id','module_name')->where('parent_id','0')->get();
		return json_encode(array('data'=>$data));
	}

	//Web Action Entry And Update
	public function webActionEntry(Request $request){
		$rule = [
            'module_name' 	=> 'Required',
            'activity_name' => 'Required|max:50',
        ];

        $validation = Validator::make($request->all(), $rule);
        if ($validation->fails()) {
			$return['result'] = "0";
			$return['message'] = $validation->errors();
			return json_encode($return);
        }
		else{
			try{
				DB::beginTransaction();
				$status 	= ($request->is_active)?1:0;
				$action_data = [
					'activity_name'	=>$request->activity_name,
					'module_id'		=>$request->module_name,
					'status'		=>$status,
				];
				//dd($action_data);

				if($request->is_menu != ""){
					$action_data['is_menu']=$request->is_menu;
				}

				if($request->edit_id !=""){
					$data = WebAction::find($request->edit_id);
					$data->update($action_data);
				}
				else{
					$response = WebAction::create($action_data);
					//get action id
					$action_id = $response->id;
					//get group id
					$group_id = UserGroup::Select('id')->get();
					// insert a row in user_group_permissions for each group of the action id
					foreach ($group_id as $group_id) {
						$status = ($group_id['id']==1)?1:0;
						$user_group_permissions 			= new UserGroupPermission();
						$user_group_permissions->group_id	= $group_id['id'];
						$user_group_permissions->action_id	= $action_id;
						$user_group_permissions->status		= $status;
						$user_group_permissions->save();
					}
				}
				DB::commit();
				$return['result'] = "1";
				return json_encode($return);
			}
			catch (\Exception $e){
				DB::rollback();
				$return['result'] = "0";
				$return['message'][] ="Faild to save";
				return json_encode($return);
			}
		}
	}

	//Web Action List
	public function webActionList(){
		$admin_user_id 		= Auth::user()->id;
		$edit_action_id 	= 7;//action edit
		$edit_permisiion 	= $this->PermissionHasOrNot($admin_user_id,$edit_action_id);

		$webActionList = WebAction::Select('actions.id as id','actions.activity_name as activity_name','actions.status as status','menus.module_name as module_name')
			->leftJoin('menus', 'actions.module_id', '=', 'menus.id')
			//->where('actions.status','1')
			->orderBy('actions.id','desc')
			->get();

		$return_arr = array();
		foreach($webActionList as $webActionList){
			$webActionList['status']=($webActionList->status == 1)?"<button class='btn btn-xs btn-success' disabled>Active</button>":"<button class='btn btn-sm btn-warning' disabled>In-active</button>";
			if($edit_permisiion>0){
				$webActionList['actions']="<button title='Edit' onclick='web_action_edit(".$webActionList->id.")' id=edit_" . $webActionList->id . " class='btn btn-xs btn-hover-shine  btn-primary module-edit'><i class='lnr-pencil'></i></button>";
			}
			else{
				$webActionList['actions']="";
			}
			$return_arr[] = $webActionList;
		}
		return json_encode(array('data'=>$return_arr));

	}

	//Web Action Edit
	public function web_action_edit($id){
		$data = WebAction::Select('id','activity_name','module_id','is_menu','status')->where('id',$id)->first();
		return json_encode($data);
	}


}

