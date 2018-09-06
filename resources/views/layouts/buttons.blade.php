<div class="col-lg-12">
    <div class="form-group">
        <div class="col-sm-12 col-sm-offset-0 guardar">
            <button type="button" class="ladda-button btn btn-primary" @click.prevent="store()" v-if="editar==0">
                <i class="fa fa-save"></i> Guardar
            </button>
            <template v-else>
                <button type="button" class="ladda-button btn btn-warning" @click.prevent="update()">
                    <i class="fa fa-edit"></i> Actualizar
                </button>
                <button type="button" class="btn btn-white" @click.prevent="cancel()">
                    <i class="fa fa-remove"></i> Cancelar
                </button>
            </template>
        </div>
    </div>
</div>