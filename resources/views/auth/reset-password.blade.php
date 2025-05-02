@extends('layouts.app')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<div class="login-right">
    <div class="login-right-wrap">
        <h1 class="text-center">Silahkan Memperbarui Password</h1>
    
       
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    $@foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session()->has('status'))
        <div class="alert alert-success">
                {{session()->get('status')}}
        </div>
        @endif

        <form action="{{route ('password.update')}}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{request()->token}}">
            <input type="hidden" name="email" value="{{request()->email}}">
            <div class="card-body px-2">
                <div class="form-group">
                    <label class="form-label" for="typeEmail">Password :</label>
                    <input type="password" name="password" id="password" class="form-control my-3" />
                    <span class="profile-views feather-eye toggle-password"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="typeEmail">Konfirmasi Password : </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control my-3" />
                    <span class="profile-views feather-eye toggle-password"></span>
                </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">Update Password</button>
            </div>
        </form>
       
    </div>
</div>

@endsection
