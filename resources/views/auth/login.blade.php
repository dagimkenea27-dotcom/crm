@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">G</div>
            <div class="login-logo-text">Gojo Shop</div>
        </div>
        <h1 class="login-title">Welcome back</h1>
        <p class="login-sub">Sign in to your CRM account</p>

        @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input id="email" name="email" type="email" class="form-control"
                    value="{{ old('email') }}" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control"
                    autocomplete="current-password" required>
            </div>
            <div style="margin-top:20px">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:12px">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
