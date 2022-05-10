@extends('layout.master')
@section('content')
@section('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bils/profile.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.8d288f825d8dffbbe55e.css') }}">
<style type="text/css" media="screen">
    hr {
        margin: 0;
    }

</style>
@endsection
<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div>
                        <div class="page-title-head center-elem">
                            <span class="d-inline-block pr-2">
                                <i class="pe-7s-users icon-gradient bg-mean-fruit"></i>
                            </span>
                            <span class="d-inline-block">Profile Details</span>
                        </div>
                        <div class="page-title-subheading opacity-10">
                            <nav class="" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a>
                                            <i aria-hidden="true" class="fa fa-home"></i>&nbsp;Ace Learning
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{url('dashboard')}}">Dashboards</a>
                                    </li>
                                    <li class="active breadcrumb-item" aria-current="page">
                                        <a href="{{\Request::url()}}">
                                            {{isset($page_title) ? $page_title : ''}}
                                        </a>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!--<div class="page-title-actions">
					<button type="button" onclick='unitAdd()' title="Add a new Unit" data-placement="bottom" class="btn-shadow mr-3 btn btn-primary">
						<i class="fa fa-plus"></i>
						Add New Unit
					</button>
				</div>-->
            </div>
        </div>
        <ul class="tabs-animated-shadow tabs-animated nav">
            <li class="nav-item">
                <a role="tab" class="nav-link show active" id="my_profile_tab" data-toggle="tab" href="#my_profile_info_div" aria-selected="false">
                    <span>My Profile</span>
                </a>
            </li>
            <li class="nav-item" id="edit_profile_menu_tab">
                <a role="tab" class="nav-link show " data-toggle="tab" href="#edit_profile" id="edit_profile_tab" aria-selected="true">
                    <span>Edit Profile</span>
                </a>
            </li>
            <li class="nav-item" id="change_pass_menu_tab">
                <a role="tab" class="nav-link show" href="#change_pass" id="change_pass_tab" data-toggle="tab" aria-selected="false">
                    <span>Change Password</span>
                </a>
            </li>
            <li class="nav-item">
                <a role="tab" class="nav-link show " data-toggle="tab" href="#all_notification_div" id="notification_tab" aria-selected="true">
                    <span>Notification</span>
                </a>
            </li>
            @if(\Auth::user()->type =="Student")
            <li class="nav-item">
                <a role="tab" class="nav-link show " data-toggle="tab" href="#all_class_Information" id="class_tab" aria-selected="true">
                    <span>Class</span>
                </a>
            </li>
            @endif
        </ul>
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="tab-content">
                    <!--MESSAGE-->
                    <div class="col-md-12">
                        <div id="form_submit_error" class="text-center" style="display:none"></div>
                    </div>
                    <!--END MESSAGE-->

                    <div class="tab-pane show active" id="my_profile_info_div" role="tabpanel">
                        <div class="container portfolio">
                            <div class="bio-info">
                                @if(\Auth::user()->type =="Student")
                                @php
                                $user_id = \Auth::user()->id;
                                @endphp
                                <div class="row">
                                    <div class="col-md-6 col-xs-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="bio-image">
                                                    <img src="{{asset('assets/images/user/admin')}}/{{ ($user->user_profile_image=="")?'no-user-image.png':$user->user_profile_image }}" class="img-thumbnail user_profile_img">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6  col-xs-6">
                                        <table class="table table-condensed table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Personal Information</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-left"><b> {{$user->first_name.' '.$user->last_name}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left">
                                                        @if ($user->status==1)
                                                        <button disabled class='btn btn-xs btn-success'>Active</button>
                                                        @else
                                                        <button disabled class='btn btn-xs btn-danger'>In-Active</button></td>
                                                    @endif
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-phone'></i> &nbsp; &nbsp; {{$user->contact_no}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-envelope'></i> &nbsp;&nbsp; {{$user->email}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-user'></i> &nbsp;&nbsp;{{$user->user_name}}</td>
                                                </tr>

                                                <tr>
                                                    <td class="text-left">Details: {{$user->remarks}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>

                                        <br>
                                        <button class='btn btn-info' onclick='edit_profile()'>Edit Profile</button>
                                        <button class='btn btn-danger' onclick='change_password()'>Change Password</button>
                                    </div>
                                </div>



                                @elseif (\Auth::user()->type =="Teacher")

                                @php
                                $user_id = \Auth::user()->typable_id;
                                @endphp
                                <div class="row">
                                    <div class="col-md-2 col-xs-2">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="bio-image">
                                                    <img src="{{asset('assets/images/user/admin')}}/{{ ($user->user_profile_image=="")?'no-user-image.png':$user->user_profile_image }}" class="img-thumbnail user_profile_img">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4  col-xs-4">
                                        <table class="table table-condensed table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Personal Information</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-left"><b> {{$user->first_name.' '.$user->last_name}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left">
                                                        @if ($user->status==1)
                                                        <button disabled class='btn btn-xs btn-success'>Active</button>
                                                        @else
                                                        <button disabled class='btn btn-xs btn-danger'>In-Active</button></td>
                                                    @endif
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-phone'></i> {{ $user->contact_no}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-envelope'></i> {{ $user->email}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-user'></i> {{ $user->user_name}}</td>
                                                </tr>

                                                <tr>
                                                    <td class="text-left">Details: {{$user->remarks}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>

                                        <br>
                                        <button class='btn btn-info' onclick='edit_profile()'>Edit Profile</button>
                                        <button class='btn btn-danger' onclick='change_password()'>Change Password</button>
                                    </div>
                                    <div class="col-md-1  col-xs-1 ">
                                        <div class="vl "></div>
                                    </div>
                                    @php
                                    $user_id = \Auth::user()->typable_id;
                                     $teacher_subjects = App\Models\TeacherSubject::where('teacher_id', '=', $user_id) ->get();


                                   @endphp

                                    <div class="col-md-5  col-xs-5">

                                        <div class="card width">
                                            <div class="card-header">
                                                Subject List

                                            </div>
                                            <div class="card-body">

                                                <table class="table table-hover">
                                                    <thead>
                                                      <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">Key-Stage</th>
                                                        <th scope="col">Year</th>
                                                        <th scope="col">Subject</th>
                                                      </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($teacher_subjects as $key => $teacher_subjects)
                                                        @php
                                                            //  $id = $teacher_subjects->class_subject_id;
                                                             $class_subjects = App\Models\ClassSubject::where('id', '=',$teacher_subjects->class_subject_id) ->get();
                                                        @endphp
                                                    @foreach($class_subjects as $ke => $class_subjects)
                                                         <tr>
                                                            <th scope="row">{{++$key  }}</th>
                                                            <td>{{ $class_subjects->classes->KeyStages->name }}</td>
                                                           <td>{{ $class_subjects->classes->name }}</td>
                                                            <td>{{ $class_subjects->subjects->subject_name }}</td>

                                                      </tr>
                                                    @endforeach
                                                    @endforeach


                                                    </tbody>
                                                  </table>



                                            </div>
                                        </div>
                                    </div>
                                </div>


                                @else
                                <div class="row">
                                    <div class="col-md-6 col-xs-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="bio-image">
                                                    <img src="{{asset('assets/images/user/admin')}}/{{ ($user->user_profile_image=="")?'no-user-image.png':$user->user_profile_image }}" class="img-thumbnail user_profile_img">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6  col-xs-6">
                                        <table class="table table-condensed table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Personal Information</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-left"><b>{{$user->first_name.' '.$user->last_name}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left">
                                                        @if ($user->status==1)
                                                        <button disabled class='btn btn-xs btn-success'>Active</button>
                                                        @else
                                                        <button disabled class='btn btn-xs btn-danger'>In-Active</button></td>
                                                    @endif
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-phone'></i> {{$user->contact_no}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-envelope'></i> {{$user->email}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><i class='fa fa-user'></i> {{$user->user_name}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"> Details: {{$user->remarks}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>

                                        <br>
                                        <button class='btn btn-info' onclick='edit_profile()'>Edit Profile</button>
                                        <button class='btn btn-danger' onclick='change_password()'>Change Password</button>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane show " id="edit_profile" role="tabpanel">
                        <form id="my_profile_form" name="my_profile_form" enctype="multipart/form-data" class="form form-horizontal form-label-left">
                            @csrf
                            <div class="row">
                                <div class="col-md-9">
                                    <input type="hidden" name="edit_profile_id" id="edit_profile_id" value="{{$user->id}}">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="company_name" class="">First Name <span class="required">*</span></label>
                                                <input type="text" id="first_name" name="first_name" value="{{$user->first_name}}" required class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="short_name" class="">Last Name</label>
                                                <input type="text" id="last_name" name="last_name" value="{{$user->last_name}}" class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="company_name" class="">Contact No. <span class="required">*</span></label>
                                                <input type="text" id="contact_no" name="contact_no" value="{{$user->contact_no}}" required class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="short_name" class="">Email</label>
                                                <input type="email" id="email" name="email" required value="{{$user->email}}" class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="company_name" class="">Username<span class="required">*</span></label>
                                                <input type="text" id="user_name" required value="{{$user->user_name}}" name="user_name" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="company_name" class="">Address<span class="required">*</span></label>
                                                <input type="text" id="address" name="address" class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                    </div>

                                    <br />
                                    <div class="ln_solid"></div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <img src="{{asset('assets/images/user/admin')}}/{{ ($user->user_profile_image=="")?'no-user-image.png':$user->user_profile_image }}" width="70%" height="70%" class="img-thumbnail">


                                        <input type="file" name="user_profile_image" id="user_profile_image" value="">

                                </div>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="id" id="id">
                                <label class="control-label col-md-2 col-sm-2 col-xs-6"></label>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <button type="submit" id="update_profile_info" class="btn btn-success">Update</button>
                                    <button type="button" id="cancle_admin_update" class="btn btn-danger hidden">Cancle</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane show" id="change_pass" role="tabpanel">
                        <form id="change_password_form" autocomplete="off" name="change_password_form" enctype="multipart/form-data" class="form form-horizontal form-label-left">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="hidden" name="change_pass_id" id="change_pass_id" value="{{$user->id}}">
{{--                                    <div class="form-row">--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="position-relative form-group">--}}
{{--                                                <label for="company_name" class="">Current Password <span class="required">*</span></label>--}}
{{--                                                <input type="password" id="current_password" name="current_password" class="form-control col-lg-12" />--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="short_name" class="">New Password</label>
                                                <input type="password" id="new_password" name="new_password" class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="short_name" class="">Confirm Password</label>
                                                <input type="password" id="confirm_password" name="confirm_password" class="form-control col-lg-12" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-12" style="display: inline;">
                                        <button type="submit" id="update_password" class="btn btn-success">Update Password</button>
                                        <button type="button" id="cancle_admin_update" class="btn btn-danger hidden">Cancle</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                    <div class="tab-pane show" id="all_notification_div" role="tabpanel">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <table class="table table-bordered table-hover notification_table" id="notification_table" style="width:100% !important">
                                    <thead>
                                        <tr>
                                            <th>Message</th>
                                            <th class="text-center" width="100">Date</th>
                                            <th class="text-center" width='80'>Status</th>
                                            <th class="text-center" width='80'>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



                    <div class="tab-pane tabs-animation fade" id="all_class_Information" role="tabpanel">
                        <div class="container portfolio">
                            <div class="bio-info">
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        @php
                                        $user_id = \Auth::user()->typable_id;
                                        $student_class = App\Models\StudentClass::where('student_id', '=', $user_id) ->get();
                                        @endphp


                                <div id="accordion">
                                    @foreach ( $student_class as $key=> $student_class )
                                    <div class="card ">
                                        @php
                                        $class_name = App\Models\Classe::where('id', '=', $student_class->class_id) ->get();


                                        @endphp
                                        @foreach( $class_name as $class_name)
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne{{ $class_name->id }}" aria-expanded="true" aria-controls="collapseOne">
                                                    {{ $class_name->KeyStages->name }} --> {{ $class_name->name }}
                                                </button>
                                            </h5>
                                        </div>


                                        <div id="collapseOne{{ $class_name->id }}" class="collapse {{$key  == 0 ? 'show' : '' }}" aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
                                                @php
                                                $class_subject = App\Models\ClassSubject::where('class_id', '=', $class_name->id) ->get();
                                                @endphp



                                                Subject List :

                                                        <ul>
                                                            @foreach ( $class_subject as $class_subject)
                                                            <li>{{ $class_subject->subjects->subject_name }}</li>
                                                            @endforeach
                                                        </ul>

                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


            </div>






        </div>
    </div>
</div>
</div>
</div>
</div>

@endsection

@section('JScript')

<script src="{{ asset('assets/js/page-js/admin/profile.js')}}"></script>
<script src="{{ asset('assets/js/main.8d288f825d8dffbbe55e.js') }}" @endsection

