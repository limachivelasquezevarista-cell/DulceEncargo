<?php
/**
 * RF01 - Panel según rol. Cada rol ve un panel distinto al iniciar sesión.
 */
class PanelController extends Controller
{
    /** GET /panel  -> redirige al panel que corresponde al rol */
    public function index()
    {
        $this->requiereSesion();
        Auth::esAdmin() ? $this->admin() : $this->cliente();
    }

    /** Panel del administrador: resumen del día y accesos a los módulos. */
    private function admin()
    {
        $pedidos = new Pedido();
        $this->vista('admin/panel', [
            'titulo'  => 'Panel de administración',
            'resumen'  => $pedidos->resumenPanel(),
            'proximos' => $pedidos->proximasEntregas(5),
        ]);
    }

    /** Panel del cliente: accesos al catálogo y a sus pedidos. */
    private function cliente()
    {
        $this->vista('cliente/panel', [
            'titulo'  => 'Mi panel',
            'pedidos' => (new Pedido())->listarPorCliente(Auth::id()),
        ]);
    }
}
