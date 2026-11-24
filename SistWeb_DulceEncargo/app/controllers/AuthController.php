<?php
/**
 * RF01 - Autenticación: inicio de sesión, registro de clientes y cierre de sesión.
 */
class AuthController extends Controller
{
    private $usuarios;

    public function __construct()
    {
        $this->usuarios = new Usuario();
    }

    /** GET /auth/login */
    public function login()
    {
        if (Auth::usuario()) {
            $this->redirigir('panel');
        }
        $this->vista('auth/login', ['titulo' => 'Iniciar sesión'], false);
    }

    /** POST /auth/autenticar */
    public function autenticar()
    {
        $this->validarPost();
        $correo     = trim(isset($_POST['correo']) ? $_POST['correo'] : '');
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

        $v = (new Validador())
            ->requerido('correo', $correo, 'correo')
            ->correo('correo', $correo)
            ->requerido('contrasena', $contrasena, 'contraseña');

        $usuario = $v->esValido() ? $this->usuarios->autenticar($correo, $contrasena) : null;

        if (!$usuario) {
            $this->vista('auth/login', [
                'titulo' => 'Iniciar sesión',
                'error'  => $v->esValido() ? 'Correo o contraseña incorrectos.' : implode(' ', $v->errores()),
                'correo' => $correo,
            ], false);
            return;
        }

        Auth::login($usuario);
        $this->redirigir('panel');
    }

    /** GET /auth/registro */
    public function registro()
    {
        $this->vista('auth/registro', ['titulo' => 'Crear cuenta'], false);
    }

    /** POST /auth/registrar */
    public function registrar()
    {
        $this->validarPost();
        $datos = [
            'nombres'    => trim(isset($_POST['nombres']) ? $_POST['nombres'] : ''),
            'apellidos'  => trim(isset($_POST['apellidos']) ? $_POST['apellidos'] : ''),
            'correo'     => trim(isset($_POST['correo']) ? $_POST['correo'] : ''),
            'telefono'   => trim(isset($_POST['telefono']) ? $_POST['telefono'] : ''),
            'contrasena' => isset($_POST['contrasena']) ? $_POST['contrasena'] : '',
        ];

        $v = (new Validador())
            ->requerido('nombres', $datos['nombres'], 'nombres')
            ->requerido('apellidos', $datos['apellidos'], 'apellidos')
            ->requerido('correo', $datos['correo'], 'correo')
            ->correo('correo', $datos['correo'])
            ->requerido('telefono', $datos['telefono'], 'teléfono')
            ->telefono('telefono', $datos['telefono'])
            ->longitudMinima('contrasena', $datos['contrasena'], 8, 'La contraseña');

        $errores = $v->errores();
        if ($v->esValido() && $this->usuarios->existeCorreo($datos['correo'])) {
            $errores['correo'] = 'Ya existe una cuenta con ese correo.';
        }

        if ($errores) {
            $this->vista('auth/registro', ['titulo' => 'Crear cuenta', 'errores' => $errores, 'datos' => $datos], false);
            return;
        }

        $this->usuarios->registrarCliente($datos);
        $this->mensaje('success', 'Cuenta creada. Ya puedes iniciar sesión.');
        $this->redirigir('auth/login');
    }

    /** GET /auth/logout */
    public function logout()
    {
        Auth::logout();
        $this->redirigir('auth/login');
    }
}
