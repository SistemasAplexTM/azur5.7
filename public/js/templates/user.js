$(document).ready(function () {
  //  
});

$(window).load(function() {
    $('#tbl-user').DataTable({
        ajax: 'user/all',
        columns: [
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {
                sortable: false,
                "render": function (data, type, full, meta) {
                    var params = [
                        full.id, 
                        "'"+full.name+"'", 
                        "'"+full.email+"'"
                    ];
                    var btn_edit =  "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                    var btn_delete = " <a onclick=\"eliminar(" + full.id + ","+true+")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                    return btn_edit + btn_delete;
                }
            }
        ]
    });
});

function edit(id,name,email){
    var data ={
        id:id,
        name: name,
        email: email
    };
    objVue.edit(data);
}

var objVue = new Vue({
    el: '#user',
    mounted: function(){
        const dict = {
          custom: {
            name: {
              required: 'El nombre es obligatorio.'
            },
            email: {
              required: 'El correo es obligatorio.'
            },
            password: {
              required: 'La contraseña es obligatoria.'
            },
            password_confirm: {
              required: 'La confirmación de la contraseña es obligatoria.',
              confirmed: 'Las contraseñas no coinciden.'
            }
          }
        };
        this.$validator.localize('es', dict);
    },
    data:{
        name: null,
        email: null,
        password: null,
        password_confirm: null,
        editar: 0,
        mostrar_password: true
    },
    methods:{
        resetForm: function(){
            this.id = null;
            this.email = null;
            this.name = null;
            this.password = null;
            this.password_confirm = null;
            this.editar = 0;
            this.mostrar_password = true;
            this.errors.clear();
        },
        rollBackDelete: function(data){
            var urlRestaurar = 'user/restaurar/' + data.id;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-user');
            });
        },
        delete: function(data){
            axios.delete('user/' + data.id).then(response => {
                refreshTable('tbl-user');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function(){
            const isUnique = (value) => {
                return axios.post('user/validarUsername',{'name' : value}).then((response) => {
                    // Notice that we return an object containing both a valid property and a data property.
                    return {
                        valid: response.data.valid,
                        data: {
                            message: response.data.message
                        }
                    };
                });
            };
            const isEmailUnique = (value) => {
                return axios.post('user/validar',{'email' : value}).then((response) => {
                    // Notice that we return an object containing both a valid property and a data property.
                    return {
                        valid: response.data.valid,
                        data: {
                            message: response.data.message
                        }
                    };
                });
            };
            // The messages getter may also accept a third parameter that includes the data we returned earlier.
            this.$validator.extend('unique', {
                validate: isUnique,
                getMessage: (field, params, data) => {
                    return data.message;
                }
            });
            this.$validator.extend('uniques', {
                validate: isEmailUnique,
                getMessage: (field, params, data) => {
                    return data.message;
                }
            });
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('user',{
                        'name' : this.name,
                        'email' : this.email,
                        'password' : this.password,
                        'password_confirm' : this.password_confirm
                    }).then(function(response){
                        if(response.data['code'] == 200){
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            me.resetForm();
                            refreshTable('tbl-user');
                        }else{
                            toastr.warning(response.data['error']);
                            toastr.options.closeButton = true;
                        }
                    }).catch(function(error){
                        console.log(error);
                        toastr.error("Error. - " + error, {timeOut: 50000});
                    });
                }else{
                    console.log(errors);
                    toastr.warning('Error en la validacion');
                }
            }).catch(function(error) {
                toastr.warning('Error al intentar registrar.');
            });
        },
        update: function(){
            this.$validator.validateAll(['name', 'email']).then((result) => {
                if (result) {
                    var me = this;
                    axios.put('user/' + this.id,{
                        'name' : this.name,
                        'email' : this.email
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-user');
                        } else {
                            toastr.warning(response.data['error']);
                            toastr.options.closeButton = true;
                            console.log(response.data);
                        }
                    }).catch(function (error) {
                        console.log(error);
                        toastr.error("Error. - " + error, {timeOut: 50000});
                    });
                }
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error al intentar registrar.');
            });
        },
        edit: function(data){
            this.id = data['id'];
            this.name = data['name'];
            this.email = data['email'];
            
            this.editar = 1;
            this.mostrar_password = false;
        },
        cancel: function(){
            var me = this;
            me.resetForm();
        },
    },
});