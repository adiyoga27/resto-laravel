@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="card border-0">
    <div class="card-body login-card-body p-4">
        <div class="text-center mb-3">
            <div class="mb-2" style="font-size:3rem;">🍽️</div>
            <h5 class="font-weight-bold" style="color:#1e1b4b;">{{ config('app.name', 'Resto') }}</h5>
            <p class="text-muted small">Sign in ke akun Anda</p>
        </div>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
                <div class="input-group-append"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
            </div>
            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
            <div class="input-group mb-4">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <div class="input-group-append"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
            </div>
            @error('password')<small class="text-danger">{{ $message }}</small>@enderror
            <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold">Sign In</button>
        </form>
    </div>
</div>
@endsection
