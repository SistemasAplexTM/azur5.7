<!DOCTYPE html>
    <html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset('img/azur.ico') }}" type="image/x-icon">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login | Azur</title>

        <!-- Styles -->
        <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet">
        <link href="{{ asset('css/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
        <style>
                html, body {
                    background-color: #fff;
                    color: #636b6f;
                    font-family: sans-serif;
                    font-weight: 100;
                    height: 100vh;
                    margin: 0;
                    background-size: cover;
                }

                .full-height {
                    height: 100vh;
                }

                .flex-center {
                    align-items: center;
                    display: flex;
                    justify-content: center;
                }

                .position-ref {
                    position: relative;
                }

                .top-right {
                    position: absolute;
                    right: 10px;
                    top: 18px;
                }
                #formLogin{
                    padding: 30px;
                    background-color: rgba(226, 226, 226, 0.95);
                    border-radius: 10px;
                    color: black;

                }
                body{
                    background-image: url("img/cc.jpg");
                    background-repeat: no-repeat;
                    background-position: center top;
                }
            </style>
    </head>
    <body>
        <div class="bodylogin">
            <div class="middle-box text-center loginscreen animated fadeInDown">
                <div>
                    <div>
                        <img class="img-responsive img-thumbnail" id="logo" style="margin-bottom: 30px;width: 300px;border: none;background-color: transparent;" src="{{ asset('img/logo.png') }}">
                    </div>
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                        <div class="row" id="formLogin">
                            {{ csrf_field() }}
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <input id="email" type="email" placeholder="Correo" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <input id="password" type="password" placeholder="Contraseña" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="checkbox checkbox-success">
                                <input type="checkbox" name="remember" id="remember" aria-label="Single checkbox One" {{ old('remember') ? 'checked' : '' }}>
                                       <label for="remember">No cerrar sesión</label>
                            </div>
                            <div class="form-group" style="margin-top: 10px;">
                                <button type="submit" class="btn btn-primary block full-width m-b">
                                    Entrar
                                </button>
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                            <p class="m-t"> <small>Power by AplexTM &copy;</small> </p>
                            
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
        <!-- Scripts -->
       
        <script src="{{ asset('js/plantilla.js') }}"></script>
    </body>
</html>