<div class="modal fade bs-example" id="modalList" tabindex="" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" style="">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
            class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-copy"></i> Menus Registrados</h4>
      </div>
      <div class="modal-body">
        <form id="formList" class="form-horizontal" role="form" autocomplete="off">
          <div class="row">
            <button type="button" class="btn btn-success btn-xs pull-right" data-target="#modalCambio"
              data-toggle="modal"><i class="fa fa-refresh"></i> Cambio U.M Pedido</button>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li role="presentation" :class="{ 'active': cdi_menu }"><a href="#cdi" aria-controls="cdi" role="tab"
                  data-toggle="tab">CDI</a></li>
              <li role="presentation" :class="{ 'active': hcb_menu }"><a href="#hcb" aria-controls="hcb" role="tab"
                  data-toggle="tab">HCB</a></li>
              <li role="presentation" :class="{ 'active': hi_menu }"><a href="#hi" aria-controls="hi" role="tab"
                  data-toggle="tab">HI</a></li>
            </ul>
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane fade in" :class="{ 'active': cdi_menu }" id="cdi">
                <div class="col-lg-12" style="margin-top: 20px;">
                  <div class="table-responsive">
                    <table id="tbl-menus_cdi" class="table table-striped table-hover table-bordered"
                      style="width: 100%;">
                      <thead>
                        <tr>
                          <th>Nombre</th>
                          <th>Tipo UDS</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane fade in" :class="{ 'active': hcb_menu }" id="hcb">
                <div class="col-lg-12" style="margin-top: 20px;">
                  <div class="table-responsive">
                    <table id="tbl-menus_hcb" class="table table-striped table-hover table-bordered"
                      style="width: 100%;">
                      <thead>
                        <tr>
                          <th>Nombre</th>
                          <th>Tipo UDS</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane fade in" :class="{ 'active': hi_menu }" id="hi">
                <div class="col-lg-12" style="margin-top: 20px;">
                  <div class="table-responsive">
                    <table id="tbl-menus_hi" class="table table-striped table-hover table-bordered"
                      style="width: 100%;">
                      <thead>
                        <tr>
                          <th>Nombre</th>
                          <th>Tipo UDS</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
      </div>
    </div>
  </div>
</div>


{{-- MODAL COPIAR MENU --}}
<div class="modal fade bs-example" id="modalCopy" tabindex="" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" style="width: 40%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
            class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-copy"></i> Copiar al @{{ name_menu }} de
          @{{ name_uds }}</h4>
      </div>
      <div class="modal-body">
        <form id="formCopy" class="form-horizontal" role="form" autocomplete="off">
          <p>Selecciona el menu desde donde deseas copiar la información y reemplazarla por el menu seleccionado.</p>
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label for="produc_type_id" class="control-label gcore-label-top">Copiar desde:</label>
                <el-select v-model="tipo_uds_id" filterable placeholder="Seleccione" value-key="id">
                  <el-option v-for="item in tipo_us" :key="item.id" :label="item.name" :value="item">
                  </el-option>
                </el-select>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label for="remanencia_tipo_prod">El Menu:</label>
                <el-select v-model="menus_id" filterable placeholder="Seleccione" value-key="id">
                  <el-option v-for="item in menus" :key="item.id" :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
        <el-button type="primary" @click="copyMenu()" :loading="loading" size="small"><i class="fa fa-save"></i>
          @{{ coping }}</el-button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL CAMBIAR PEDIDO --}}
<div class="modal fade bs-example" id="modalCambio" tabindex="" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" style="width: 40%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
            class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-refresh"></i> Este cambio afectara a todos los
          detalles de todos los menus creados</h4>
      </div>
      <div class="modal-body">
        <form id="formChange" enctype="multipart/form-data" class="form-horizontal" role="form" autocomplete="off">
          <div class="row">
            <div class="col-lg-7">
              <div class="form-group">
                <label for="produc_type_id" class="control-label gcore-label-top">Producto:</label>
                <el-select v-model="product_id_change" clearable placeholder="Producto" value-key="id">
                  <el-option v-for="item in products" :key="item.id" :label="item.name" :value="item">
                  </el-option>
                </el-select>
                {{-- <v-select name="product_id_change" placeholder="Producto" v-model="product_id_change" label="name"
                  :options="products"></v-select> --}}
              </div>
            </div>
            <div class="col-lg-5">
              <div class="form-group">
                <label for="remanencia_tipo_prod">U.M Pedido</label>
                <el-input placeholder="Unidad medida final" v-model="um_pedido" clearable>
                </el-input>
                {{-- <input v-model="um_pedido" name="um_pedido" placeholder="Unidad medida final" class="form-control"
                  type="text" /> --}}
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
        <button type="button" class="btn btn-primary" @click="saveChage()"><i class="fa fa-save"></i> Cambiar</button>
      </div>
    </div>
  </div>
</div>