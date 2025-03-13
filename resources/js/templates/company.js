var objVue = new Vue({
    el: "#company",
    mounted: function() {
        const dict = {
            custom: {
                name: {
                    required: "El nombre es obligatorio."
                },
                nit: {
                    required: "El NIT es obligatorio."
                },
                address: {
                    required: "La dirección es obligatoria."
                },
                phone: {
                    required: "El teléfono es obligatorio."
                }
            }
        };
        this.$validator.localize("es", dict);
    },
    data: {
        id: null,
        name: null,
        nit: null,
        address: null,
        phone: null,
        logo: null,
        sourceLogo: null,
        editar: 0
    },
    methods: {
        getDataCompany() {
            axios
                .get("companies/getCompanyById/1")
                .then(response => {
                    console.log(response.data);
                    // si la respuesta es mayor a 0, significa que hay datos
                    if (response.data.code == 200) {
                        const data = response.data.data;
                        this.editar = 1;
                        this.id = data.id;
                        this.name = data.name;
                        this.nit = data.nit;
                        this.address = data.address;
                        this.phone = data.phone;
                        this.sourceLogo = data.logo;
                    } else {
                        // si no hay datos, se muestra un mensaje de error
                        toastr.error("No se encontraron datos");
                        toastr.options.closeButton = true;
                    }
                })
                .catch(error => {
                    const errorResponse = error.response.data;
                    toastr.error(errorResponse.error);
                    toastr.options.closeButton = true;
                });
        },
        handleLogoUpload(event) {
            this.logo = event.target.files[0];
        },
        store: function() {
            me = this;
            this.$validator
                .validateAll()
                .then(result => {
                    if (result) {
                        let formData = new FormData(); // Create FormData object
                        formData.append("name", me.name);
                        formData.append("nit", me.nit);
                        formData.append("address", me.address);
                        formData.append("phone", me.phone);
                        if (me.logo) {
                            formData.append("logo", me.logo, me.logo.name); // Append image file
                        }
                        axios
                            .post("companies", formData)
                            .then(function(response) {
                                if (response.data["code"] == 200) {
                                    toastr.success(
                                        "Registro creado correctamente."
                                    );
                                    toastr.options.closeButton = true;
                                    me.getDataCompany();
                                } else {
                                    console.log(response.data);
                                    toastr.warning(response.data["error"]);
                                    toastr.options.closeButton = true;
                                }
                            })
                            .catch(function(error) {
                                if (
                                    error.response &&
                                    error.response.status === 422
                                ) {
                                    console.log(
                                        "Errores de validación:",
                                        error.response.data.error
                                    );
                                    toastr.error(
                                        "Error de validación. Revisa la consola para más detalles.",
                                        { timeOut: 50000 }
                                    );
                                } else {
                                    console.log(error);
                                    toastr.error("Error. - " + error, {
                                        timeOut: 50000
                                    });
                                }
                            });
                    } else {
                        toastr.warning("Error en la validación");
                    }
                })
                .catch(function(error) {
                    console.log(error.response.data.errors);
                    toastr.warning("Error al intentar registrar.");
                });
        },
        update: function() {
            var me = this;
            this.$validator
                .validateAll()
                .then(result => {
                    if (result) {
                        var formData = new FormData();
                        formData.append("_method", "PUT"); // ¡Spoofing!
                        formData.append("name", me.name);
                        formData.append("nit", me.nit);
                        formData.append("address", me.address);
                        formData.append("phone", me.phone);

                        if (me.logo) {
                            formData.append("logo", me.logo, me.logo.name);
                        }
                        axios
                            .post("companies/" + me.id, formData)
                            .then(function(response) {
                                if (response.data["code"] == 200) {
                                    toastr.success(
                                        "Registro actualizado correctamente"
                                    );
                                    toastr.options.closeButton = true;
                                    me.getDataCompany();
                                } else {
                                    toastr.warning(response.data["error"]);
                                    toastr.options.closeButton = true;
                                }
                            })
                            .catch(function(error) {
                                console.log(error);
                                toastr.error("Error. - " + error, {
                                    timeOut: 50000
                                });
                            });
                    }
                })
                .catch(function(error) {
                    toastr.warning("Error al intentar actualizar.");
                });
        }
    },
    mounted: function() {
        this.getDataCompany();
    }
});
