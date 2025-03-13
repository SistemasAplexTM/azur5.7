<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                        <img alt="image" class="img-circle" id="imgProfile" style="width: 150px;"
                            src="{{ isset($company->logo) ? asset($company->logo) : asset('img/default-logo.png') }}" />

                    </span>
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold">
                                    @if (Auth::check())
                                        {{ Auth::user()->name }}
                                    @endif
                                </strong>
                            </span>
                            <span class="text-muted text-xs block">
                                Bienvenido
                                <b class="caret">
                                </b>
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li>
                            <a href="{{ route('home') }}">
                                <i class="fa fa-home">
                                </i>
                                Inicio
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-user">
                                </i>
                                Perfil
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="logo-element">
                    4plbox
                </div>
            </li>
            <li class="active" id="firstMenu">
                <a href="" style="background-color: #BA55D3; color: white;">
                    <i class="fa fa-clipboard">
                    </i>
                    <span class="nav-label">
                        Documentos
                    </span>
                    <span class="fa arrow">
                    </span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="{{ route('minuta.index') }}">
                            <spam class="fa fa-file-text">
                            </spam>
                            Minutas
                        </a>
                    </li>
                    {{-- @can('menus.index') --}}
                    <li>
                        <a href="{{ route('menus.index') }}">
                            <spam class="fa fa-list">
                            </spam>
                            Menus
                        </a>
                    </li>
                    {{-- @endcan --}}
                </ul>
            </li>
            <li class="active" id="firstMenu">
                <a href="" style="background-color:#f17720; color: white;">
                    <i class="fa fa-user-circle">
                    </i>
                    <span class="nav-label">
                        Clientes
                    </span>
                    <span class="fa arrow">
                    </span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="{{ route('clientes.index') }}">
                            <spam class="fa fa-user-circle">
                            </spam>
                            Clientes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('unidadServicio.index') }}">
                            <spam class="fa fa-home">
                            </spam>
                            Unidades de servicio
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tercero.index') }}">
                            <spam class="fa fa-users">
                            </spam>
                            Proveedores
                        </a>
                    </li>
                </ul>
            </li>
            <li class="active" id="firstMenu">
                <a href="" style="background-color: #1793dc; color: white;">
                    <i class="fa fa-file-text">
                    </i>
                    <span class="nav-label">
                        Informes
                    </span>
                    <span class="fa arrow">
                    </span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="#">
                            <spam class="fa fa-file-text">
                            </spam>
                            Inventario
                        </a>
                    </li>
                </ul>
            </li>
            <li class="active" id="firstMenu">
                <a href="" style="background-color: #017767; color: white;">
                    <i class="fa fa-cogs">
                    </i>
                    <span class="nav-label">
                        Administracion
                    </span>
                    <span class="fa arrow">
                    </span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="{{ route('product.index') }}">
                            <spam class="fa fa-cubes">
                            </spam>
                            Productos
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('administracion/presentacion') }}">
                            <spam class="fa fa-puzzle-piece">
                            </spam>
                            Presentaciones
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('administracion/unidad_de_medida') }}">
                            <spam class="fa fa-balance-scale">
                            </spam>
                            Unidad de medida
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('administracion/grupo_edad') }}">
                            <spam class="fa fa-child">
                            </spam>
                            Grupo de edades
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('administracion/tipo_unidad_servicio') }}">
                            <spam class="fa fa-home">
                            </spam>
                            Tipo US
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('administracion/tipo_producto') }}">
                            <spam class="fa fa-tags">
                            </spam>
                            Tipo producto
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logActivity.index') }}">
                            <spam class="fa fa-history">
                            </spam>
                            Logs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('companies.index') }}">
                            <spam class="fa fa-building">
                            </spam>
                            Empresa
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Escuchar eventos de actualización del logo (ej: desde Vue)
        window.Echo.channel('company-updates')
            .listen('LogoUpdated', (data) => {
                const imgProfile = document.getElementById('imgProfile');
                imgProfile.src = data.logo_url + '?t=' + Date.now(); // Evitar caché
            });
    });
    </script>
