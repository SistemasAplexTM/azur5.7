<div  id="navbar">
    <div class="row border-bottom">
        <nav class="navbar navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header" style="width: 60%;">
                <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                <span class="minimalize-styl-2" style="font-size: 15px;font-weight: bold;color: red;">
                    {{-- DEMO --}}
                </span>
            </div>

            <ul class="nav navbar-top-links navbar-right">
                <!--NOTIFICACIONES-->
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell" data-toggle='tooltip' data-placement='left' title='Notificaciones'></i>  
                    </a>

                    <ul class="dropdown-menu dropdown-alerts">
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-user-circle fa-fw"></i>JHONNYS
                                    <span class="pull-right text-muted small">MENSAJE</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <div style="text-align: center; font-weight: bold;">
                                    No hay registros
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="text-center link-block">
                                <a href="#">
                                    <strong>Ver Todas las alertas</strong>
                                    <i class="fa fa-angle-double-right"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class="fa fa-sign-out"></i> Log out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </li>
                <li>
                    
                </li>
            </ul>

        </nav>
    </div>
</div>
