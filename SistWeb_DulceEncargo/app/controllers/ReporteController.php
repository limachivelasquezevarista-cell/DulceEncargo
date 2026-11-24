<?php
/**
 * RF08 - Reporte de pedidos en PDF con la librería FPDF. Solo administrador.
 * FPDF funciona con PHP 5.6 y no necesita Composer: va copiada en app/libs/fpdf/
 */
class ReporteController extends Controller
{
    public function __construct()
    {
        $this->requiereRol(Auth::ADMIN);
    }

    /** GET /reportes  -> formulario con rango de fechas */
    public function index()
    {
        $this->vista('reportes/index', ['titulo' => 'Reportes']);
    }

    /** GET /reportes/ventas?desde=YYYY-MM-DD&hasta=YYYY-MM-DD  -> abre el PDF */
    public function ventas()
    {
        $desde = isset($_GET['desde']) ? $_GET['desde'] : '';
        $hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
        if (!$this->esFecha($desde) || !$this->esFecha($hasta) || $hasta < $desde) {
            $this->mensaje('danger', 'Elige un rango de fechas válido.');
            $this->redirigir('reportes');
        }

        $datos   = (new Pedido())->reportePedidos($desde, $hasta);
        $negocio = (new Configuracion())->obtener('nombre_negocio', APP_NAME);

        require_once APP_PATH . '/libs/fpdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Encabezado
        // Encabezado con logo
        $pdf->SetFillColor(155, 28, 74);
        $pdf->Rect(0, 0, 216, 4, 'F');
        $logo = dirname(APP_PATH) . '/public/img/logo.jpg';
        $x = 12;
        if (file_exists($logo)) {
            $pdf->Image($logo, 12, 9, 26, 26);   // logo de 26 x 26 mm
            $x = 42;                             // el texto empieza a la derecha del logo
        }
        $pdf->SetXY($x, 11);
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(194, 37, 92);
        $pdf->Cell(0, 9, $this->t($negocio), 0, 2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(138, 90, 74);
        $pdf->Cell(0, 4, $this->t('TORTAS HECHAS CON AMOR · SACABA, COCHABAMBA'), 0, 2);
        $telefono = (new Configuracion())->obtener('telefono_negocio', '');
        if ($telefono !== '') {
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 4, $this->t('WhatsApp: ' . $telefono), 0, 2);
        }
        $pdf->SetY(38);
        $pdf->SetDrawColor(240, 180, 203);
        $pdf->Line(12, 37, 204, 37);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(0);
        $pdf->Cell(0, 7, $this->t('Reporte de pedidos'), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(90);
        $pdf->Cell(0, 6, $this->t('Fecha de entrega: del ' . fecha($desde) . ' al ' . fecha($hasta)
            . '   ·   Generado el ' . date('d/m/Y H:i')), 0, 1);
        $pdf->Ln(4);

        // Resumen
        $pdf->SetTextColor(0);
        $resumen = [
            ['Pedidos', count($datos['pedidos'])],
            ['Valor de pedidos', bs($datos['total'])],
        ];
        $y = $pdf->GetY();
        $pdf->SetFillColor(253, 232, 239);
        foreach ($resumen as $i => $r) {
            $pdf->SetXY(12 + $i * 96, $y);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(94, 6, $this->t($r[0]), 0, 2, 'C', true);   // 2 = la siguiente celda va debajo
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(94, 8, $this->t($r[1]), 0, 0, 'C', true);
        }
        $pdf->SetY($y + 20);

        // Tabla de pedidos
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 7, $this->t('Detalle de pedidos'), 0, 1);
        $columnas = [['N°', 15, 'C'], ['Entrega', 27, 'C'], ['Cliente', 47, 'L'], ['Producto', 51, 'L'],
                     ['Estado', 30, 'L'], ['Total', 22, 'R']];
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(155, 28, 74);
        $pdf->SetTextColor(255);
        foreach ($columnas as $c) {
            $pdf->Cell($c[1], 7, $this->t($c[0]), 0, 0, $c[2], true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 8.5);
        $relleno = false;
        foreach ($datos['pedidos'] as $p) {
            $pdf->SetFillColor(250, 245, 247);
            $fila = ['#' . $p['id_pedido'], fecha($p['fecha_entrega']), $p['cliente'], $p['producto'],
                     nombreEstado($p['estado']), number_format($p['total'], 2, ',', '.')];
            foreach ($columnas as $i => $c) {
                $pdf->Cell($c[1], 6.5, $this->recortar($pdf, $this->t($fila[$i]), $c[1] - 2), 0, 0, $c[2], $relleno);
            }
            $pdf->Ln();
            $relleno = !$relleno;
        }
        if (!$datos['pedidos']) {
            $pdf->Cell(0, 8, $this->t('No hay pedidos en ese rango de fechas.'), 0, 1, 'C');
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetDrawColor(155, 28, 74);
        $pdf->Cell(170, 7, $this->t('TOTALES (Bs)'), 'T', 0, 'R');
        $pdf->Cell(22, 7, number_format($datos['total'], 2, ',', '.'), 'T', 1, 'R');

        // Productos más pedidos
        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 7, $this->t('Productos más pedidos'), 0, 1);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(253, 232, 239);
        $pdf->Cell(110, 7, 'Producto', 0, 0, 'L', true);
        $pdf->Cell(30, 7, 'Cantidad', 0, 0, 'C', true);
        $pdf->Cell(40, 7, 'Importe (Bs)', 0, 1, 'R', true);
        $pdf->SetFont('Arial', '', 9);
        foreach ($datos['top'] as $t) {
            $pdf->Cell(110, 6.5, $this->t($t['nombre']), 'B', 0);
            $pdf->Cell(30, 6.5, (int) $t['cantidad'], 'B', 0, 'C');
            $pdf->Cell(40, 6.5, number_format($t['importe'], 2, ',', '.'), 'B', 1, 'R');
        }

        $pdf->Output('I', 'reporte_pedidos_' . $desde . '_' . $hasta . '.pdf');
        exit;
    }

    /** Convierte el texto de UTF-8 a la codificación que usa FPDF (acentos y ñ). */
    private function t($texto)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $texto);
    }

    /** Corta un texto para que entre en el ancho de la celda. */
    private function recortar($pdf, $texto, $ancho)
    {
        if ($pdf->GetStringWidth($texto) <= $ancho) {
            return $texto;
        }
        while ($texto !== '' && $pdf->GetStringWidth($texto . '...') > $ancho) {
            $texto = substr($texto, 0, -1);
        }
        return $texto . '...';
    }

    private function esFecha($valor)
    {
        $f = DateTime::createFromFormat('Y-m-d', (string) $valor);
        return $f && $f->format('Y-m-d') === $valor;
    }
}
