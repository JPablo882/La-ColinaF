<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MotoqueroController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\TmpPedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\EspecialController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\LlamadaController;
use App\Http\Controllers\AvisoNavegacionController;
use Illuminate\Http\Request;
use App\Http\Controllers\MotoqueroUbicacionController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\Contabilidad\ConfirmacionVentaController;
use App\Http\Controllers\Contabilidad\MovimientoContableController;
use App\Http\Controllers\DespachoRepartidorController;
use App\Http\Controllers\NotificacionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin');
});

Auth::routes(['register' => false]);

Route::get('/home', [AdminController::class, 'index'])->name('home')->middleware('auth');
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');

// ---------------- CONFIGURACIONES ----------------
Route::get('/admin/configuraciones', [ConfiguracionController::class, 'index'])->name('admin.configuracion.index')->middleware('auth', 'can:admin.configuracion.index');
Route::post('/admin/configuraciones/create', [ConfiguracionController::class, 'store'])->name('admin.configuracion.store')->middleware('auth', 'can:admin.configuracion.store');

// ---------------- ROLES ----------------
Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index')->middleware('auth', 'can:admin.roles.index');
Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('admin.roles.create')->middleware('auth', 'can:admin.roles.create');
Route::post('/admin/roles/create', [RoleController::class, 'store'])->name('admin.roles.store')->middleware('auth', 'can:admin.roles.store');
Route::get('/admin/roles/{id}/permisos', [RoleController::class, 'permisos'])->name('admin.roles.permisos')->middleware('auth', 'can:admin.roles.permisos');
Route::post('/admin/roles/permisos/{id}', [RoleController::class, 'update_permisos'])->name('admin.roles.update_permisos')->middleware('auth', 'can:admin.roles.update_permisos');
Route::get('/admin/roles/{id}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit')->middleware('auth', 'can:admin.roles.edit');
Route::put('/admin/roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update')->middleware('auth', 'can:admin.roles.update');
Route::delete('/admin/roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy')->middleware('auth', 'can:admin.roles.destroy');

// ---------------- USUARIOS ----------------
Route::get('/admin/usuarios', [UserController::class, 'index'])->name('admin.usuarios.index')->middleware('auth', 'can:admin.usuarios.index');
Route::get('/admin/usuarios/create', [UserController::class, 'create'])->name('admin.usuarios.create')->middleware('auth', 'can:admin.usuarios.create');
Route::post('/admin/usuarios/create', [UserController::class, 'store'])->name('admin.usuarios.store')->middleware('auth', 'can:admin.usuarios.store');
Route::get('/admin/usuarios/{id}', [UserController::class, 'show'])->name('admin.usuarios.show')->middleware('auth', 'can:admin.usuarios.show');
Route::get('/admin/usuarios/{id}/edit', [UserController::class, 'edit'])->name('admin.usuarios.edit')->middleware('auth', 'can:admin.usuarios.edit');
Route::put('/admin/usuarios/{id}', [UserController::class, 'update'])->name('admin.usuarios.update')->middleware('auth', 'can:admin.usuarios.update');
Route::delete('/admin/usuarios/{id}', [UserController::class, 'destroy'])->name('admin.usuarios.destroy')->middleware('auth', 'can:admin.usuarios.destroy');

// ---------------- MOTOQUEROS ----------------
Route::get('/admin/motoqueros', [MotoqueroController::class, 'index'])->name('admin.motoqueros.index')->middleware('auth', 'can:admin.motoqueros.index');
Route::get('/admin/motoqueros/create', [MotoqueroController::class, 'create'])->name('admin.motoqueros.create')->middleware('auth', 'can:admin.motoqueros.create');
Route::post('/admin/motoqueros/create', [MotoqueroController::class, 'store'])->name('admin.motoqueros.store')->middleware('auth', 'can:admin.motoqueros.store');
Route::get('/admin/motoqueros/{id}', [MotoqueroController::class, 'show'])->name('admin.motoqueros.show')->middleware('auth', 'can:admin.motoqueros.show');
Route::get('/admin/motoqueros/{id}/edit', [MotoqueroController::class, 'edit'])->name('admin.motoqueros.edit')->middleware('auth', 'can:admin.motoqueros.edit');
Route::put('/admin/motoqueros/{id}', [MotoqueroController::class, 'update'])->name('admin.motoqueros.update')->middleware('auth', 'can:admin.motoqueros.update');
Route::delete('/admin/motoqueros/{id}', [MotoqueroController::class, 'destroy'])->name('admin.motoqueros.destroy')->middleware('auth', 'can:admin.motoqueros.destroy');

// ---------------- TARIFAS ----------------
Route::get('/admin/tarifas', [TarifaController::class, 'index'])->name('admin.tarifas.index')->middleware('auth', 'can:admin.tarifas.index');
Route::get('/admin/tarifas/create', [TarifaController::class, 'create'])->name('admin.tarifas.create')->middleware('auth', 'can:admin.tarifas.create');
Route::post('/admin/tarifas/create', [TarifaController::class, 'store'])->name('admin.tarifas.store')->middleware('auth', 'can:admin.tarifas.store');
Route::get('/admin/tarifas/{id}', [TarifaController::class, 'show'])->name('admin.tarifas.show')->middleware('auth', 'can:admin.tarifas.show');
Route::get('/admin/tarifas/{id}/edit', [TarifaController::class, 'edit'])->name('admin.tarifas.edit')->middleware('auth', 'can:admin.tarifas.edit');
Route::put('/admin/tarifas/{id}', [TarifaController::class, 'update'])->name('admin.tarifas.update')->middleware('auth', 'can:admin.tarifas.update');
Route::delete('/admin/tarifas/{id}', [TarifaController::class, 'destroy'])->name('admin.tarifas.destroy')->middleware('auth', 'can:admin.tarifas.destroy');

// ---------------- CLIENTES ----------------
Route::get('/admin/clientes', [ClienteController::class, 'index'])->name('admin.clientes.index')->middleware('auth', 'can:admin.clientes.index');
Route::get('/admin/clientes/create', [ClienteController::class, 'create'])->name('admin.clientes.create')->middleware('auth', 'can:admin.clientes.create');
Route::post('/admin/clientes/create', [ClienteController::class, 'store'])->name('admin.clientes.store')->middleware('auth', 'can:admin.clientes.store');
Route::get('/admin/clientes/{id}', [ClienteController::class, 'show'])->name('admin.clientes.show')->middleware('auth', 'can:admin.clientes.show');
Route::get('/admin/clientes/{id}/edit', [ClienteController::class, 'edit'])->name('admin.clientes.edit')->middleware('auth', 'can:admin.clientes.edit');
Route::put('/admin/clientes/{id}', [ClienteController::class, 'update'])->name('admin.clientes.update')->middleware('auth', 'can:admin.clientes.update');
Route::delete('/admin/clientes/{id}', [ClienteController::class, 'destroy'])->name('admin.clientes.destroy')->middleware('auth', 'can:admin.clientes.destroy');

Route::post('/admin/clientes/coords', [ClienteController::class, 'obtenerCoords'])->name('admin.clientes.coords')->middleware('auth', 'can:admin.clientes.create');

// ---------------- PEDIDOS ----------------
Route::get('/admin/pedidos', [PedidoController::class, 'index'])
    ->name('admin.pedidos.index')
    ->middleware('auth', 'can:admin.pedidos.index');

Route::get('/admin/pedidos/create', [PedidoController::class, 'create'])
    ->name('admin.pedidos.create')
    ->middleware('auth', 'can:admin.pedidos.create');

Route::post('/admin/pedidos/store', [PedidoController::class, 'store'])
    ->name('admin.pedidos.store')
    ->middleware('auth', 'can:admin.pedidos.store');

Route::get('/admin/pedidos/asignar_motoquero', [PedidoController::class, 'asignar_motoquero'])
    ->name('admin.pedidos.asignar_motoquero')
    ->middleware('auth', 'can:admin.pedidos.asignar_motoquero');



Route::post('/admin/pedidos/asignar-motoquero-multiple', [PedidoController::class, 'asignarMotoqueroMultiple'])
    ->name('admin.pedidos.asignar_motoquero_multiple')
    ->middleware('auth', 'can:admin.pedidos.asignar_motoquero');    


Route::post('/admin/pedidos/mover-por-asignar', [PedidoController::class, 'moverPorAsignar'])
    ->name('admin.pedidos.mover_por_asignar')
    ->middleware('can:admin.pedidos.asignar_motoquero');


Route::post('/admin/pedidos/ordenar', [PedidoController::class, 'actualizarOrden'])
    ->name('admin.pedidos.ordenar')
    ->middleware('can:admin.pedidos.asignar_motoquero');    


Route::get('/admin/pedidos/cambiar_motoquero', [PedidoController::class, 'cambiar_motoquero'])
    ->name('admin.pedidos.cambiar_motoquero')
    ->middleware('auth', 'can:admin.pedidos.cambiar_motoquero');

Route::delete('/admin/pedidos/{id}', [PedidoController::class, 'destroy'])
    ->name('admin.pedidos.destroy')
    ->middleware('auth', 'can:admin.pedidos.destroy');

Route::get('/admin/pedidos/cancelar_pedido', [PedidoController::class, 'cancelar_pedido'])
    ->name('admin.pedidos.cancelar_pedido')
    ->middleware('auth', 'can:admin.pedidos.cancelar_pedido');

Route::get('/admin/pedidos/motoquero/{id}', [PedidoController::class, 'ver_pedidos_motoquero'])
    ->name('admin.pedidos.ver_pedidos_motoquero')
    ->middleware('auth', 'can:admin.pedidos.ver_pedidos_motoquero');

Route::post('/admin/pedidos/motoquero/{id}/tomar_pedido', [PedidoController::class, 'tomar_pedido'])
    ->name('admin.pedidos.tomar_pedido')
    ->middleware('auth', 'can:admin.pedidos.tomar_pedido');

Route::post('/admin/pedidos/motoquero/{id}/rechazar_pedido', [PedidoController::class, 'rechazar_pedido'])
    ->name('admin.pedidos.rechazar_pedido')
    ->middleware('auth', 'can:admin.pedidos.rechazar_pedido');

Route::post('/admin/pedidos/motoquero/{id}/finalizar_pedido', [PedidoController::class, 'finalizar_pedido'])
    ->name('admin.pedidos.finalizar_pedido')
    ->middleware('auth', 'can:admin.pedidos.finalizar_pedido');

Route::get('/admin/pedidos/{id}/editar', [PedidoController::class, 'editarPedido'])->middleware('auth', 'can:admin.pedidos.create');
Route::post('/admin/pedidos/actualizar-edicion',[PedidoController::class, 'actualizarEdicion'])->middleware('auth', 'can:admin.pedidos.create');

// ---------------- REPORTE DE VENTAS POR MOTOQUERO ----------------
// Usamos GET, con permiso de administrador
Route::get('/admin/pedidos/reporte-motoquero', [PedidoController::class, 'reporte_motoquero'])
    ->name('admin.pedidos.reporte_motoquero')
    ->middleware('auth', 'can:admin.pedidos.reporte_motoquero');

// ---------------- PEDIDOS TEMPORALES ----------------
Route::post('/admin/pedidos/tmp/create', [TmpPedidoController::class, 'store'])->name('admin.pedidos_tmp.store')->middleware('auth', 'can:admin.pedidos_tmp.store');
Route::put('/admin/pedidos/tmp/{id}', [TmpPedidoController::class, 'update'])->name('admin.pedidos_tmp.update')->middleware('auth', 'can:admin.pedidos_tmp.update');
Route::delete('/admin/pedidos/tmp/{id}', [TmpPedidoController::class, 'destroy'])->name('admin.pedidos_tmp.destroy')->middleware('auth', 'can:admin.pedidos_tmp.destroy');

// ---------------- PRODUCTOS ----------------
Route::get('/admin/productos', [ProductoController::class, 'index'])->name('admin.productos.index')->middleware('auth', 'can:admin.productos.index');
Route::get('/admin/productos/create', [ProductoController::class, 'create'])->name('admin.productos.create')->middleware('auth', 'can:admin.productos.create');
Route::post('/admin/productos/create', [ProductoController::class, 'store'])->name('admin.productos.store')->middleware('auth', 'can:admin.productos.store');
Route::get('/admin/productos/{id}', [ProductoController::class, 'show'])->name('admin.productos.show')->middleware('auth', 'can:admin.productos.show');
Route::get('/admin/productos/{id}/edit', [ProductoController::class, 'edit'])->name('admin.productos.edit')->middleware('auth', 'can:admin.productos.edit');
Route::put('/admin/productos/{id}', [ProductoController::class, 'update'])->name('admin.productos.update')->middleware('auth', 'can:admin.productos.update');
Route::delete('/admin/productos/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy')->middleware('auth', 'can:admin.productos.destroy');




// Rutas para precios especiales
    Route::get('admin/especiales', [EspecialController::class,'index'])->name('admin.especiales.index')->middleware('auth', 'can:admin.especiales.index');
    Route::get('admin/especiales/create', [EspecialController::class,'create'])->name('admin.especiales.create')->middleware('auth', 'can:admin.especiales.create');
    Route::post('admin/especiales', [EspecialController::class,'store'])->name('admin.especiales.store')->middleware('auth', 'can:admin.especiales.create');
    Route::get('admin/especiales/{cliente}/edit', [EspecialController::class,'edit'])->name('admin.especiales.edit')->middleware('auth', 'can:admin.especiales.edit');
    Route::put('admin/especiales/{cliente}', [EspecialController::class,'update'])->name('admin.especiales.update')->middleware('auth', 'can:admin.especiales.create');
    Route::delete('admin/especiales/{cliente}', [EspecialController::class,'destroy'])->name('admin.especiales.destroy')->middleware('auth', 'can:admin.especiales.destroy');

    Route::get('admin/webhook-whatsapp', [WhatsappWebhookController::class, 'verify']);
    Route::post('admin/webhook-whatsapp', [WhatsappWebhookController::class, 'receive']);


    Route::post('admin/solicitar-llamada', [LlamadaController::class, 'solicitar'])->name('solicitar.llamada')->middleware('auth');

    Route::get('admin/llamadas/poll', [LlamadaController::class, 'poll'])->name('llamadas.poll')->middleware('auth');

    Route::post('admin/llamadas/{llamada}/atender', [LlamadaController::class, 'atender'])->name('llamadas.atender')->middleware('auth');

    Route::post('admin/llamadas/{llamada}/cerrar', [LlamadaController::class, 'cerrar'])->name('llamadas.cerrar')->middleware('auth');

    Route::get('admin/motoquero/llamadas/check', [LlamadaController::class, 'checkMotoquero'])->name('llamadas.checkMotoquero')->middleware('auth');

    // ADMIN — Poll
Route::get('admin/avisos-navegacion/poll', [AvisoNavegacionController::class, 'poll'])->name('avisos.navegacion.poll')->middleware('auth');

// ADMIN — Atender
Route::post('admin/avisos-navegacion/{id}/atender', [AvisoNavegacionController::class, 'atender'])->name('avisos.navegacion.atender')->middleware('auth');

// ADMIN — Cerrar
Route::post('admin/avisos-navegacion/{id}/cerrar', [AvisoNavegacionController::class, 'cerrar'])->name('avisos.navegacion.cerrar')->middleware('auth');


Route::post('admin/avisos-navegacion/crear', [AvisoNavegacionController::class, 'crear'])->middleware('auth');



Route::post('admin/pedidos/{pedido}/avisar', [App\Http\Controllers\PedidoController::class, 'avisarMotoquero'])->name('admin.pedidos.avisar')->middleware('auth');

Route::get('admin/motoquero/avisos', [App\Http\Controllers\PedidoController::class, 'obtenerAvisosMotoquero'])->name('admin.motoquero.avisos')->middleware('auth');




// 🔄 Estado de cambios del motoquero (polling inteligente)
Route::get('admin/motoquero/{motoquero}/estado',[PedidoController::class, 'estadoMotoquero'])->name('admin.motoquero.estado')->middleware('auth');

// 🔄 Panel HTML del motoquero (para refrescar solo su panel)
Route::get('admin/motoquero/{motoquero}/panel',[PedidoController::class, 'panelMotoquero'])->name('admin.motoquero.panel')->middleware('auth');

// Pedido de emergencia, control
Route::post('admin/pedidos/{pedido}/emergencia',[PedidoController::class, 'marcarEmergencia'])->name('admin.pedidos.emergencia')->middleware('auth');

Route::get('admin/motoquero/check-emergencia',[PedidoController::class, 'checkEmergencia'])->name('motoquero.check.emergencia')->middleware('auth');

Route::get('/admin/pedidos/mapa-por-asignar', [PedidoController::class, 'mapaPorAsignar'])->middleware('auth');



Route::post('admin/motoquero/ubicacion', [MotoqueroUbicacionController::class, 'store'])->name('admin.motoquero.ubicacion')->middleware('auth');

Route::get('/admin/pedidos/motoqueros/ubicaciones', [MotoqueroUbicacionController::class, 'ultimas'])->name('admin.pedidos.motoqueros.ubicaciones')->middleware('auth');

Route::get('/admin/pedidos/motoquero/{id}/recorrido', [MotoqueroUbicacionController::class, 'recorrido'])->name('admin.pedidos.motoquero.recorrido')->middleware('auth');




/* ===============================
   PROMOCIONES
   =============================== */

// Listar promociones
Route::get('admin/promociones', [PromocionController::class, 'index'])->name('admin.promociones.index')->middleware('auth', 'can:admin.promociones.index');

// Crear promoción (form)
Route::get('admin/promociones/crear', [PromocionController::class, 'create'])->name('admin.promociones.create')->middleware('auth', 'can:admin.promociones.create');

// Guardar promoción
Route::post('admin/promociones', [PromocionController::class, 'store'])->name('admin.promociones.store')->middleware('auth', 'can:admin.promociones.store');

// Editar promoción (form)
Route::get('admin/promociones/{id}/editar', [PromocionController::class, 'edit'])->name('admin.promociones.edit')->middleware('auth', 'can:admin.promociones.edit');

// Actualizar promoción
Route::put('admin/promociones/{id}', [PromocionController::class, 'update'])->name('admin.promociones.update')->middleware('auth', 'can:admin.promociones.update');

// Activar / desactivar promoción
Route::post('admin/promociones/{id}/toggle', [PromocionController::class, 'toggle'])->name('admin.promociones.toggle')->middleware('auth', 'can:admin.promociones.toggle');

// Eliminar promoción
Route::delete('admin/promociones/{id}', [PromocionController::class, 'destroy'])->name('admin.promociones.destroy')->middleware('auth', 'can:admin.promociones.destroy');


Route::get('admin/pedidos/precio-cliente/{cliente}/{producto}',[PedidoController::class, 'precioCliente'])->middleware('auth');


////////////////////////////////////////////////////
// Rutas para contabilidad y confirmacion de venta//
////////////////////////////////////////////////////


// Mostrar el formulario para confirmar la venta
Route::get('admin/contabilidad/confirmar-venta', [ConfirmacionVentaController::class, 'create'])->name('admin.contabilidad.confirmar_venta.create')->middleware('auth', 'can:admin.contabilidad.confirmar_venta.create');

// Guardar la confirmación de venta
Route::post('admin/contabilidad/confirmar-venta', [ConfirmacionVentaController::class, 'store'])->name('admin.contabilidad.confirmar_venta.store')->middleware('auth', 'can:admin.contabilidad.confirmar_venta.store');


Route::get('admin/contabilidad/historial_cierres', [ConfirmacionVentaController::class, 'historial'])->name('admin.contabilidad.historial_cierres')->middleware('auth', 'can:admin.contabilidad.historial_cierres');




Route::get('admin/contabilidad/movimientos', [MovimientoContableController::class, 'index'])->name('admin.contabilidad.movimientos.index')->middleware('auth', 'can:admin.contabilidad.movimientos.index');

Route::get('admin/contabilidad/movimientos/create', [MovimientoContableController::class, 'create'])->name('admin.contabilidad.movimientos.create')->middleware('auth', 'can:admin.contabilidad.movimientos.create');

Route::post('admin/contabilidad/movimientos', [MovimientoContableController::class, 'store'])->name('admin.contabilidad.movimientos.store')->middleware('auth', 'can:admin.contabilidad.movimientos.store');

Route::get('admin/contabilidad/movimientos/resumen', [MovimientoContableController::class, 'resumen'])->name('admin.contabilidad.movimientos.resumen')->middleware('auth', 'can:admin.contabilidad.movimientos.resumen');


Route::post('admin/contabilidad/movimientos/registrar-gasto-fijo', [MovimientoContableController::class, 'registrarGastoFijo'])->name('admin.contabilidad.movimientos.registrarGastoFijo')->middleware('auth', 'can:admin.contabilidad.gastos-fijos.store');




    // Gastos fijos (desde la misma vista)
Route::post('admin/contabilidad/gastos-fijos', [MovimientoContableController::class, 'storeGastoFijo'])->name('admin.contabilidad.gastos-fijos.store')->middleware('auth', 'can:admin.contabilidad.gastos-fijos.store');

Route::post('admin/contabilidad/gastos-fijos/{id}', [MovimientoContableController::class, 'updateGastoFijo'])->name('admin.contabilidad.gastos-fijos.update')->middleware('auth', 'can:admin.contabilidad.gastos-fijos.store');

Route::patch('admin/contabilidad/gastos-fijos/{id}/toggle', [MovimientoContableController::class, 'toggleGastoFijo'])->name('admin.contabilidad.gastos-fijos.toggle')->middleware('auth', 'can:admin.contabilidad.gastos-fijos.store');



Route::post('admin/despachos', [DespachoRepartidorController::class, 'store'])->name('admin.despachos.store')->middleware('auth');


Route::post('/admin/clientes/validar', [ClienteController::class, 'validarCampo'])->name('admin.clientes.validar')->middleware('auth');

Route::post('admin/clientes/{cliente}/imagen',  [ClienteController::class, 'guardarImagen'])->name('admin.clientes.imagen')->middleware('auth');

Route::post('admin/clientes/buscar-por-celular',[ClienteController::class, 'buscarPorCelular'])->name('admin.clientes.buscarCelular')->middleware('auth');



Route::get('/admin/notificaciones', [NotificacionController::class, 'index'])->name('admin.notificaciones.index')->middleware('auth');

Route::post('/admin/notificaciones', [NotificacionController::class, 'update'])->name('admin.notificaciones.update')->middleware('auth');

Route::post('/admin/clientes/{cliente}/toggle-notificacion', [App\Http\Controllers\ClienteController::class, 'toggleNotificacion'])->middleware('auth');

Route::post('/admin/pedidos/{pedido}/toggle-notificacion', [App\Http\Controllers\PedidoController::class, 'toggleNotificacion'])->middleware('auth');


Route::get('/admin/pedidos/mapa-asignados', [PedidoController::class,'mapaAsignados'])->middleware('auth');

Route::get('/admin/pedidos/{id}/metodo-pago', [PedidoController::class,'obtenerMetodoPago'])->middleware('auth');

Route::post('/admin/pedidos/{id}/actualizar-entrega', [PedidoController::class,'actualizarEntrega'])->middleware('auth');