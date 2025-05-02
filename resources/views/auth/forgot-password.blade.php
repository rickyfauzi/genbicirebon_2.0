@extends('layouts.app')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<div class="login-right">
    <div class="login-right-wrap">
        <h1 class="text-center">Lupa Password Anda ?</h1>
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

        <form action="{{route ('password.email')}}" method="POST">
            @csrf
            <div class="card-body px-2">
                <p class="card-text py-1">
                    Masukkan alamat email Anda dan kami akan mengirimi Anda email berisi instruksi untuk mengatur ulang kata sandi Anda.
                </p>
                <div class="form-outline">
                    <label class="form-label" for="typeEmail">Masukan Email Anda :</label>
                    <input type="email" name="email" id="email" class="form-control my-3" />
                </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">Minta Reset password</button>
            </div>
        </form>
       
    </div>
</div>

@endsection
