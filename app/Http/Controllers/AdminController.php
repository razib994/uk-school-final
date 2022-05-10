<?php

namespace App\Http\Controllers;

use App\Jobs\UsersRegistrationConfirmationMailJob;
use App\Models\School;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Session;
use DB;
use \App\Models\User;
use App\Models\UserGroup;
use App\Models\UserGroupMember;
use App\Models\UserGroupPermission;
use App\Models\WebAction;
use \App\Models\Course;
use \App\Models\Student;
use App\Traits\HasPermission;

class AdminController extends Controller
{
	use HasPermission;

    public function __construct(Request $request)
    {
        $this->page_title   = $request->route()->getName();
        $description        = \Request::route()->getAction();
        $this->page_desc    = isset($description['desc']) ? $description['desc'] : $this->page_title;
		if($request->type   != "Admin" && $request->type != "School"){
			return redirect()->route('Dashboard');
		}
	}

	public function index()
    {
        $page_title = $this->page_title;
		$data['module_name']= "Dashboard";
		$data['sub_module']	= "";
		$data['studentName']	= "";
		$admin_user_id 		= Auth::user()->id;
		$userType 			= Auth::user()->type;

		if($userType == 'Student'){
			return  redirect('portal');
		}
//        $userlog = UserLog::where('user_id', Auth::user()->id)->get();
//        $to = 0;
//        foreach ($userlog as $u){
//            $a = strtotime($u->total_time);
//            $to = $to + strtotime(date('H:i:s',$a));
//        }
//        dd(date('H:i:s',$to));

		if($userType == 'School'){
            $userSchoolId 		= Auth::user()->school_id;
			$school	  		    = School::find($userSchoolId);
			$data['school_name']=$school->name;
		}
        $lastrow = \Illuminate\Support\Facades\DB::table('notices')->latest()->first();
        return view('admin.dashbord', array('page_title'=>$page_title, 'data'=>$data, 'last'=>$lastrow));
    }

	public function welcome(){
		$page_title = $this->page_title;
		$data['module_name']= "Dashboard";
		$data['sub_module']	= "";
		$data['studentName']	= "";
		$admin_user_id 		= Auth::user()->id;
		$userType 			= Auth::user()->type;
        return view('welcome', $data);
	}

	//admin users management
	public function adminUserManagement(Request $request){
		$userType = $request->type;
		$data['page_title']    = $this->page_title;
		$data['module_name']   = "Users";
		$data['sub_module']	   = "$userType Users";
		$data['user_type']	   = $userType;
		//action permissions
		$admin_user_id 		   = Auth::user()->id;
		$add_action_id 	   	   = 2;
        $add_permission 	   = $this->PermissionHasOrNot($admin_user_id,$add_action_id );

		$data['actions']['add_permission']= $add_permission;
        return view('admin.index', $data);
	}

	//admin users list
	public function adminList(Request $request){
		$userType = 'Admin';
		$admin_user_id 		= Auth::user()->id;
		$edit_action_id 	= ($request->type=='Admin')?3:36; // Admin/Student User edit
		$delete_action_id 	= ($request->type=='Admin')?4:37; // Admin/Student User delete
		$edit_permisiion 	= $this->PermissionHasOrNot($admin_user_id,$edit_action_id);
		$delete_permisiion 	= $this->PermissionHasOrNot($admin_user_id,$delete_action_id);

		$imagePath = asset('assets/images/user/admin');
		$adminUser 	= User::Select('user_profile_image', 'id', DB::raw('CONCAT(first_name," ", ifnull(last_name,"")) as name'),  'email','user_name', 'status')
							->where('type','=',$userType)
							->orderBy('created_at','desc')
							->get();
		$return_arr = array();
		foreach($adminUser as $user){
			$groups =  DB::table('user_group_members as ugm')
						->leftJoin('user_groups as ug', 'ugm.group_id', '=', 'ug.id')
						->select(DB::raw('group_concat("", ug.group_name, "") AS group_name'))
						->where('ugm.user_id', $user->id)
						->where('ugm.status', 1)
						->get();
			$user['groups_name'] = $groups[0]->group_name;

			if ($user->user_profile_image!="" || $user->user_profile_image!=null) {
				$user['user_profile_image'] = '<img height="40" width="50" src="'.$imagePath.'/'.$user->user_profile_image.'" alt="image" />';
			}else{
				$user['user_profile_image'] = '<img height="40" width="50" src="'.$imagePath.'/no-user-image.png'.'" alt="image" />';
			}

			if($user->status == 0){			$user['status']="<button class='btn btn-xs btn-danger' disabled>In-active</button>";}
			else if($user->status == 1){	$user['status']="<button class='btn btn-xs btn-success' disabled>Active</button>";}

			$user['actions']		=" <button title='View' onclick='admin_user_view(".$user->id.")' id='view_" . $user->id . "' class='btn btn-xs btn-info btn-hover-shine admin-user-view' ><i class='lnr-eye'></i></button>";
			if($edit_permisiion>0){
				$user['actions'] 	.=" <button title='Edit' onclick='admin_user_edit(".$user->id.")' id=edit_" . $user->id . " class='btn btn-xs btn-hover-shine  btn-primary' ><i class='lnr-pencil'></i></button>";
			}
			if ($delete_permisiion > 0) {
					$user['actions'] .=" <button title='Delete' onclick='delete_admin_user(".$user->id.")' id='delete_" . $user->id . "' class='btn btn-xs btn-hover-shine btn-danger'><i class='fa fa-trash'></i></button>";
			}
			$return_arr[] = $user;
		}
		return json_encode(array('data'=>$return_arr));
	}

	//Admin User Entry And Update
	public function adminCreateOrEdit(Request $request){

        $admin_user_id      = \Illuminate\Support\Facades\Auth::user()->id;
        $add_permission     = $this->PermissionHasOrNot($admin_user_id, 2);
        $update_permission  = $this->PermissionHasOrNot($admin_user_id, 3);

        $request->request->add(['type'                  => 'Admin']);
        $request->request->add(['user_group_type_id'    => 1]);

        if(!is_null($request->id) && $request->id != "" && $update_permission) {
            $response_data = User::editUser($request->all(), $request->id);
            if($response_data==false){
                $return['result'] = 0;
                $return['message'] = "Super Admin Update Failed!";
                $response_data = json_encode($return);
            };
        }
        else if($add_permission) {
            $response_data = User::createUser($request->all()); // new entry
            if($response_data==false){
                $return['result'] = 0;
                $return['message'] = "Super Admin Save Failed!";
                $response_data = json_encode($return);
            }
        } else {
            $return['result'] = 0;
            $return['message'] = "You are not authorized to insert a User";
            $response_data = json_encode($return);
        }
        return $response_data;
	}

	//Admin user delete
	public function adminDestroy($id){
		User::where('id',$id)->update(['status'=>0]);
		return json_encode(array(
			"deleteMessage"=>"Deleted Successful"
		));
	}

	//Admin User View
	public function adminUserView($id){
		$user 	= User::find($id);
		$groups = DB::table('user_group_members as ugm')
					->leftJoin('user_groups as ug', 'ugm.group_id', '=', 'ug.id')
					->select(DB::raw('group_concat("", ug.group_name, "") AS group_name'))
					->where('ugm.user_id', $id)
					->where('ugm.status', 1)
					->get();
		return json_encode(array(
			'user'=>$user,
			'groups'=>$groups,
		));
	}

	//Admin User Data for edit and get group
	public function adminUserEdit(Request $request, $id){
		$user_id = $id;
		$data = User::find($user_id);
		$userType = ($request->type == 'Admin')?1:2;
		$user_group_member_details = DB::table('user_groups as ug')
									->leftJoin('user_group_members as ugm','ug.id','=','ugm.group_id')
									->where('ug.type',$userType)
									->where('ugm.user_id',$user_id)
									->select('ug.id as id','ug.group_name as group_name','ugm.user_id as user_id','ugm.status as status')
									->get();
		return json_encode(array(
			"data"=>$data,
			"user_group_member_details"=>$user_group_member_details
		));
	}

	//Admin User Groups
	public function admin_user_groups(){
		$data['page_title'] = $this->page_title;
		$data['module_name']= "Settings";
		$data['sub_module']	= "Admin User Groups";
		// action permissions
        $admin_user_id  = Auth::user()->id;
        $add_action_id  = 17;
        $add_permisiion = $this->PermissionHasOrNot($admin_user_id,$add_action_id );
        $data['actions']['add_permisiion']= $add_permisiion;

        return view('admin.admin_groups', $data);
	}

	function loadUserGroups(Request $request){
		$admin_user_id  = Auth::user()->id;
		$groups 		= UserGroup::get();

		return json_encode(array('data'=>$groups));
	}


	//Entry Admin User Group And App User Grou
	public function adminGroupsEntryOrUpdate(Request $request){
		$rule = [
            'group_name' => 'Required|max:50',
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
				$status 	= ($request->is_active)?1:0;
				$data = [
					'group_name'=>$request->group_name,
					'type'		=>$request->type,
					'status'	=>$status,
				];
				if ($request->edit_id == '') {

					$response = UserGroup::create($data);
					// Get group id
					$group_id = $response->id;

					if ($request->type=='1') {
						// Get Admin User
						$admin_user_id = User::Select('id')->orderBy('id')->get();

						// Assign Admin user Group for all Admin user with status 0
						foreach($admin_user_id as $admin_user_id){
							$admin_user_group_member 			= new UserGroupMember();
							$admin_user_group_member->user_id 	= $admin_user_id['id'];
							$admin_user_group_member->group_id 	= $group_id;
							$admin_user_group_member->status 	= '0';
							$admin_user_group_member->save();
							//echo $admin_user_id;
						}

						// Get Action id
						$action_id = WebAction::Select('id')->get();
						//Save permission
						foreach ($action_id as $action_id) {
							$user_group_permissions = new UserGroupPermission();
							$user_group_permissions->group_id=$group_id;
							$user_group_permissions->action_id=$action_id['id'];
							$user_group_permissions->status='0';
							$user_group_permissions->save();
						}
					}
				}
				else{
					$data = UserGroup::find($request->edit_id);
					$data->group_name = $request->group_name;
					$data->type = $request->type;
					$data->update();
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

	//Admin Group show
	public function adminGroupsList(){
		$admin_user_id 				= Auth::user()->id;
		$edit_action_id 			= 18;
		$delete_action_id 			= 19;
		$give_permission_action_id 	= 20;
		$edit_permisiion  			= $this->PermissionHasOrNot($admin_user_id,$edit_action_id);
		$delete_permisiion			= $this->PermissionHasOrNot($admin_user_id,$delete_action_id);
		$give_permission 			= $this->PermissionHasOrNot($admin_user_id,$give_permission_action_id);

		$admin_group_list 	= UserGroup::Select('id', 'group_name', 'type','status')->orderBy('group_name')->get();
		$return_arr 		= array();
		foreach($admin_group_list as $admin_group_list){
			 if($admin_group_list->type == 1){$admin_group_list['type'] = "Admin User";} elseif($admin_group_list->type == 2){ $admin_group_list['type'] = "School User"; } elseif($admin_group_list->type == 3) {$admin_group_list['type'] = "Teacher User";} else {$admin_group_list['type']= "Student User";};
			$admin_group_list['status']	=($admin_group_list->status == 1)?"<button class='btn btn-xs btn-success' disabled>Active</button>":"<button class='btn btn-xs btn-danger' disabled>In-active</button>";

			$admin_group_list['actions'] = "";
			if($give_permission>0){
				$admin_group_list['actions'] .="<button title='Permission' onclick='group_permission(".$admin_group_list->id.")' id=permission_" . $admin_group_list->id . " class='btn btn-xs btn-hover-shine btn-warning' ><i class='fa fa-key'></i></button>";
			}
			if($edit_permisiion>0){
				$admin_group_list['actions'] .=" <button title='Edit' onclick='admin_group_edit(".$admin_group_list->id.")' id=edit_" . $admin_group_list->id . "  class='btn btn-xs btn-hover-shine  btn-primary' ><i class='lnr-pencil'></i></button>";
			}
			if ($delete_permisiion>0) {
				$admin_group_list['actions'] .=" <button title='Delete' onclick='admin_group_delete(".$admin_group_list->id.")' id='delete_" . $admin_group_list->id . "' class='btn btn-xs btn-hover-shine btn-danger'><i class='fa fa-trash'></i></button>";
			}

			$return_arr[] = $admin_group_list;
		}
		return json_encode(array('data'=>$return_arr));
	}

	//Admin Group Edit
	public function adminGroupEdit($id){
		$data = UserGroup::Select('id','group_name','type','status')->where('id',$id)->first();
		return json_encode($data);
	}

	//admin group delete
	public function adminGroupDelete($id){
		UserGroup::find($id)->delete();
		return json_encode(array(
			"deleteMessage"=>"Deleted Successful"
		));
	}

	public function load_user_groups(){
		$user_groups = UserGroup::Select('id','group_name')
			->where('status','1')
			->where('type','1')
			->orderBy('group_name')
			->get();
		return json_encode(array('data'=>$user_groups));
    }

    public function loadActionsForGroupPermission($id){
    	$group_id = $id;

    	$permission_details = DB::table('user_group_permissions as up')
    							->leftJoin('actions as wa', 'up.action_id', '=', 'wa.id')
    							->leftJoin('menus as m','wa.module_id','=','m.id')
    							->where('up.group_id',$group_id)
    							->where('wa.status','1')
								->orderBy('module_name','asc')
    							->select('up.*', 'wa.activity_name', 'm.module_name')
    							->get();

		return json_encode(array('data'=>$permission_details));
    }

    public function permissionActionEntryUpdate(Request $request){
		$permission_action = $request->input('permission_action');
		$group_id = $request->group_id;

		try{
			DB::beginTransaction();
			$data_for_permission_action_update = DB::table('user_group_permissions')
															->where('group_id',$group_id)
															->update(['status'=>'0']);
			if($permission_action!=""){
				foreach ($permission_action as $permission_action ) {

					if (isset($permission_action)) {
						$status = '1';
						$data_for_permission_action_update = DB::table('user_group_permissions')
															->where('group_id',$group_id)
															->where('action_id',$permission_action)
															->update(['status'=>$status]);
					}
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

//****************************Admin personal profile*******************************

    public function profileIndex(){
    	$data['page_title'] = $this->page_title;
		$id 				= Auth::user()->id;
		$user 				= User::find($id);

    	$data['user']		= $user;
		/*if($user->student_id){
			$studentCourses = Student::with('courses')->findOrFail($user->student_id);
			$courses 		= $studentCourse->courses;
		}
		$data['courses'] =$courses;*/
		return view('admin.profile_index',$data);
    }

    public function profileInfo(){
    	$id 			= Auth::user()->id;
    	$profile_info 	= User::get()->where('id',$id);
    	return  json_encode($profile_info);
    }

    public function updateProfile(Request $request){
		$rule = [
            'first_name' 		=> 'Required|max:220',
            'contact_no' 		=> 'Required|max:13',
            'email' 			=> 'Required|email',
            'user_profile_image'=> 'mimes:jpeg,jpg,png,svg'
        ];
        $validation = Validator::make($request->all(), $rule);
        if ($validation->fails()) {
			$return['result'] = "0";
			$return['message'] = $validation->errors();
			return json_encode($return);
        }
		else{
			if ($request->edit_profile_id != ''){
				#EmailCheck
	            $email_verification = User::where([['email',$request->email],['id', '!=', $request->edit_profile_id]])->first();
           		if(isset($email_verification->id)){
					$return['result'] 	= "0";
					$return['message'][] = $request->email." is already exists";
					return json_encode($return);
				}
			}
			try{
				DB::beginTransaction();

				$data = [
					'name'	=> $request->first_name,
					'contact_no'	=> $request->contact_no,
					'email'			=> $request->email,
					'remarks'		=> $request->remarks,
				];

				//if admin user Image provided
				$admin_image = $request->file('user_profile_image');
				if (isset($admin_image)){
					$image_name 				= time();
					$ext 						= $admin_image->getClientOriginalExtension();
					$image_full_name 			= $image_name.'.'.$ext;
					$upload_path 				= 'assets/images/user/admin/';
					$success					= $admin_image->move($upload_path,$image_full_name);
					$data['user_profile_image'] = $image_full_name;
				}

				$user 		= User::find($request->edit_profile_id);
				$old_image 	= $user->user_profile_image;
				if (isset($admin_image) && $old_image!="") {
					$delete_img = $upload_path.$old_image;
					//unlink($delete_img);
				}

				$user->update($data);

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

    public function updatePassword(Request $request){
        $rule = [
            //  'current_password' => 'Required|max:255',
            'new_password' => 'Required|max:255',
            'confirm_password' => 'Required|max:255',
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
                $id = $request->change_pass_id;
                $user = User::find($id);
                if ($request->new_password == $request->confirm_password) {
                    $user->password = bcrypt($request->new_password);
                    $user->update();
                    DB::commit();
                    $return['result'] = "1";
                    return json_encode($return);
                }else{
                    $return['result'] = "0";
                    $return['errors'][] ="Wrong Password..!";
                    return json_encode($return);
                }
            }
            catch (\Exception $e){
                DB::rollback();
                $return['result'] = "0";
                $return['errors'][] =$e." Faild to save";
                return json_encode($return);
            }
        }
    }

	public function updateNotification(){
		$user = Auth::user();
		$user->unreadNotifications->markAsRead();
	}

	public function notificationHome($page){
		$page_no= $page;
        $limit 	= 5;
        $start 	= ($page_no*$limit)-$limit;
        $end   	= $limit;

		$user = User::find(Auth::user()->id);
		$notifications = $user->notifications()->offset($start)->limit($end)->get();

		$data['totalUnreadNotifications']= count($user->unreadNotifications);
		$data['notifications']=$notifications;
		return json_encode($data);
	}

	public function notificationRead($sessionId){
		$user = User::find(Auth::user()->id);
		$user->unreadNotifications->markAsRead();
	}

	public function ajaxNotificationList(){
		$user = User::find(Auth::user()->id);
		$notifications = $user->notifications;

		$return_arr = array();
		foreach($notifications as $notification){
			$notification['status']	=(is_null($notification->read_at))?"<strong class='text-danger'>Un-read</strong>":"Read";
			$notification['type'] 	= $notification->notifiable_type;
			$notification['message']= (!is_null($notification->read_at))?$notification->data['Message']:"<strong class='text-danger'>".$notification->data['Message']."</strong>";
			$notification['date']	= date("Y-m-d h:m", strtotime($notification->created_at));
			if(is_null($notification->read_at))
				$notification['actions'] .="<button onclick='notificationSeen(".$notification->id.")' id=edit_" . $notification->id . "  class='btn btn-xs btn-hover-shine  btn-primary' ><i class='lnr-pencil'></i></button>";
            else
				$notification['actions'] .="";

			$return_arr[] 			= $notification;
		}
		return json_encode(array('data'=>$return_arr));
	}

}

