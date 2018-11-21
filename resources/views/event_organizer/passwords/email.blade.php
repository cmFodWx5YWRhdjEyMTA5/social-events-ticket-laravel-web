@extends('common_pages.layouts')

@section('title')
    <title>Admin Reset Password - Fika</title>
@endsection

@section('content')
    <section class="material-half-bg">
      <div class="cover"></div>
    </section>
    <section class="login-content">
      <div class="logo">
        <h1>Reset Password</h1>
      </div>
      <div class="email-box">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif        
        <form class="forget-form" method="POST" action="{{ route('event_organizer_email_form_post') }}">
          {{ csrf_field() }}
          <h3 class="login-head">
            <i class="fa fa-lg fa-fw fa-lock"></i><br>
            Event Organizer
          </h3>
          <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <label class="control-label">EmailL</label>
            <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="Input your email address" required>
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
          </div>
          <div class="form-group btn-container">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-unlock fa-lg fa-fw"></i>RESET</button>
          </div>
          <div class="form-group mt-3">
            <p class="semibold-text mb-0"><a href="{{ route('event_organizer_login_form') }}" data-toggle="flip"><i class="fa fa-angle-left fa-fw"></i> Back to Login</a></p>
          </div>
        </form>
      </div>
    </section>
@endsection

<script type="text/javascript">
    $('.login-box').toggleClass('flipped');   
</script>
