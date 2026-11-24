<?php
/**
 * Validaciones del lado del servidor (PHP).
 * Se aplican aunque el formulario ya se haya validado con JavaScript.
 */
class Validador
{
    private $errores = [];

    public function requerido($campo, $valor, $etiqueta)
    {
        if (trim((string) $valor) === '') {
            $this->errores[$campo] = "El campo {$etiqueta} es obligatorio.";
        }
        return $this;
    }

    public function correo($campo, $valor)
    {
        if ($valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[$campo] = 'El correo no tiene un formato válido.';
        }
        return $this;
    }

    public function telefono($campo, $valor)
    {
        if ($valor !== '' && !preg_match('/^[67]\d{7}$/', $valor)) {
            $this->errores[$campo] = 'El teléfono debe tener 8 dígitos y empezar con 6 o 7.';
        }
        return $this;
    }

    public function longitudMinima($campo, $valor, $min, $etiqueta)
    {
        if ($valor !== '' && mb_strlen($valor) < $min) {
            $this->errores[$campo] = "{$etiqueta} debe tener al menos {$min} caracteres.";
        }
        return $this;
    }

    public function montoPositivo($campo, $valor, $etiqueta)
    {
        if (!is_numeric($valor) || (float) $valor <= 0) {
            $this->errores[$campo] = "{$etiqueta} debe ser un número mayor a cero.";
        }
        return $this;
    }

    public function fechaFutura($campo, $valor)
    {
        $fecha = DateTime::createFromFormat('Y-m-d', (string) $valor);
        if (!$fecha || $fecha->format('Y-m-d') <= date('Y-m-d')) {
            $this->errores[$campo] = 'La fecha de entrega debe ser posterior a hoy.';
        }
        return $this;
    }

    public function esValido()
    {
        return empty($this->errores);
    }

    public function errores()
    {
        return $this->errores;
    }
}
