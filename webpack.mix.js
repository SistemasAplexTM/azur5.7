let mix = require('laravel-mix');

mix.scripts([
	/*-- Scripts de la plantilla --*/
	'resources/js/jquery/jquery-2.1.1.js',
	'resources/js/bootstrap/bootstrap.min.js',
	'resources/js/bootstrap/bootstrap-editable.min.js',
	'resources/js/plugins/metisMenu/jquery.metisMenu.js',
	'resources/js/plugins/slimscroll/jquery.slimscroll.min.js',
	'resources/js/inspinia.js',
	'resources/js/plugins/pace/pace.min.js',
	// termina inclusiones plantilla requeridas
	'resources/js/plugins/toastr/toastr.min.js',
	'resources/js/plugins/dataTables/datatables.min.js',
	'resources/js/plugins/ladda/spin.min.js',
	'resources/js/plugins/ladda/ladda.min.js',
	'resources/js/plugins/ladda/ladda.jquery.min.js',
	'resources/js/plugins/fullcalendar/moment.min.js',
	'resources/js/plugins/datapicker/bootstrap-datepicker.js',
	'resources/js/plugins/daterangepicker/daterangepicker.js',
	'resources/js/plugins/sweetalert2/sweetalert2.min.js',
	], 'public/js/plantilla.js');

mix.styles([
	/*-- Estilos de la plantilla --*/
	'resources/css/bootstrap/bootstrap.min.css',
	'resources/css/bootstrap/bootstrap-editable.css',
	'resources/css/plugins/toastr/toastr.min.css',
	'resources/css/font-awesome/css/font-awesome.min.css',
	'resources/css/animate.css',
	'resources/css/style.css',
	// termina inclusiones plantilla requeridas
	'resources/css/plugins/dataTables/datatables.min.css',
	'resources/css/plugins/jasny/jasny-bootstrap.min.css',
	'resources/css/plugins/sweetalert2/sweetalert2.min.css',
	'resources/css/plugins/ladda/ladda-themeless.min.css',
	'resources/css/plugins/datapicker/datepicker3.css',
	'resources/css/plugins/daterangepicker/daterangepicker-bs3.css',
	], 'public/css/plantilla.css');

mix.js('resources/js/app.js' , 'public/js');
mix.scripts(['resources/js/main.js'] , 'public/js/main.js');
mix.styles(['resources/css/main.css'] , 'public/css/main.css');

mix.copyDirectory('resources/js/templates', 'public/js/templates');
mix.copyDirectory('resources/img', 'public/img');

mix.copyDirectory('resources/fonts', 'public/fonts');
mix.copyDirectory('resources/css/font-awesome', 'public/css/font-awesome');
mix.copyDirectory('resources/css/patterns', 'public/css/patterns');
