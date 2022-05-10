@extends('auth.layout.login-master')
@section('login-content')
<div class="app-container">
    <div class="h-100">
        <div class="h-100 no-gutters row">
            <div class="h-100 d-flex bg-white justify-content-center align-items-center col-md-12 col-lg-12"
            style="background-image: url('../assets/theme/assets/images/sidebar/login.jpg'); background-size: cover;"  
            >
                <div class="mx-auto app-login-box col-sm-12 col-md-10 col-lg-6"  style=" padding-left:10% !important;  " >                    
                    <h3>Set New Password </h3>
                    <p>
                        Please set your new password.
                    </p>
                        
                    <div class="app-logo text-center"><img src="{{ asset('assets/images/logo.png')}}" /> </div>
                    <div>
                        <form class="form-login" action="{{ url('auth/forget/password/'.$user_info->id.'/verify') }}" method="post" style="background: #f7eddf; padding: 10px; border-radius: 8px; opacity: .9" method="post">
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                            @if($errors->count() > 0 )
                                <div class="alert alert-danger btn-squared">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <h6>The following errors have occurred:</h6>
                                    <ul>
                                        @foreach( $errors->all() as $message )
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(Session::has('message'))
                                <div class="alert alert-success btn-squared" role="alert">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    {{ Session::get('message') }}
                                </div>
                            @endif
                            @if(Session::has('errormessage'))
                                <div class="alert alert-danger btn-squared" role="alert">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    {{ Session::get('errormessage') }}
                                </div>
                            @endif
    
                            <fieldset>
                                <input type="hidden" name="user_id" value="{{isset($user_info->id)?$user_info->id:''}}">
                                <input type="hidden" name="token" value="{{isset($user_info->id)?$user_info->id:''}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <div class="form-group form-actions">
                                <span class="input-icon">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                          <div class="input-group-text"><i class="fa fa-lock"></i></div>
                                        </div>
                                    <input type="password" class="form-control lock" name="password" placeholder="New Password">
                                    </div>
                                </span>
                            </div>
    
                            <div class="form-group form-actions">
                                <span class="input-icon">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                          <div class="input-group-text"><i class="fa fa-lock"></i></div>
                                        </div>
                                    <input type="password" class="form-control lock" name="confirm_password" placeholder="Confirm New Password">
                                    </div>
                                </span>
                            </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success pull-right btn-squared" style="background-color: #591a0b; border-color: #792d16;">
                                        Confirm <i class="fa fa-arrow-circle-right"></i>
                                    </button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
