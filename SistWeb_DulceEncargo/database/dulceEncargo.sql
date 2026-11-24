-- =====================================================================
-- DulceEncargo
-- Sistema web de gestión de pedidos personalizados para emprendimientos
-- de repostería en el municipio de Sacaba, Cochabamba
-- Script de base de datos (MySQL 5.6+ / MariaDB 10.1+, probado también en versiones actuales)
-- Normalizada hasta la segunda forma normal (2FN)
-- =====================================================================

DROP DATABASE IF EXISTS dulceencargo;
CREATE DATABASE dulceencargo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dulceencargo;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. ROLES: los dos roles del sistema (administrador y cliente)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id_rol      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(30)      NOT NULL,
    descripcion VARCHAR(150)     NULL,
    CONSTRAINT pk_roles PRIMARY KEY (id_rol),
    CONSTRAINT uq_roles_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. USUARIOS: administrador (repostero) y clientes
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    id_rol      TINYINT UNSIGNED NOT NULL,
    nombres     VARCHAR(60)      NOT NULL,
    apellidos   VARCHAR(60)      NOT NULL,
    correo      VARCHAR(100)     NOT NULL,
    telefono    VARCHAR(15)      NOT NULL,
    contrasena  VARCHAR(255)     NOT NULL,          -- hash generado con password_hash()
    estado      TINYINT(1)       NOT NULL DEFAULT 1, -- 1 = activo, 0 = desactivado
    creado_en   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_usuarios PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuarios_correo UNIQUE (correo),
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol)
        REFERENCES roles (id_rol) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. CATEGORIAS: tortas, postres, bocaditos...
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id_categoria SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(50)       NOT NULL,
    estado       TINYINT(1)        NOT NULL DEFAULT 1,
    CONSTRAINT pk_categorias PRIMARY KEY (id_categoria),
    CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. PRODUCTOS: catálogo base con su precio sin personalizar
-- ---------------------------------------------------------------------
CREATE TABLE productos (
    id_producto  INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    id_categoria SMALLINT UNSIGNED NOT NULL,
    nombre       VARCHAR(80)       NOT NULL,
    descripcion  VARCHAR(255)      NULL,
    precio_base  DECIMAL(10,2)     NOT NULL,
    foto         VARCHAR(150)      NULL,
    estado       TINYINT(1)        NOT NULL DEFAULT 1,
    creado_en    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_productos PRIMARY KEY (id_producto),
    CONSTRAINT fk_productos_categorias FOREIGN KEY (id_categoria)
        REFERENCES categorias (id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_productos_precio CHECK (precio_base > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. TIPOS_OPCION: tamaño, sabor, relleno, decoración
-- ---------------------------------------------------------------------
CREATE TABLE tipos_opcion (
    id_tipo     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(40)      NOT NULL,
    obligatorio TINYINT(1)       NOT NULL DEFAULT 1, -- el cliente debe elegir una opción de este tipo
    CONSTRAINT pk_tipos_opcion PRIMARY KEY (id_tipo),
    CONSTRAINT uq_tipos_opcion_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. OPCIONES: cada opción de personalización con su recargo
-- ---------------------------------------------------------------------
CREATE TABLE opciones (
    id_opcion INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    id_tipo   TINYINT UNSIGNED NOT NULL,
    nombre    VARCHAR(60)      NOT NULL,
    recargo   DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    estado    TINYINT(1)       NOT NULL DEFAULT 1,
    CONSTRAINT pk_opciones PRIMARY KEY (id_opcion),
    CONSTRAINT uq_opciones_tipo_nombre UNIQUE (id_tipo, nombre),
    CONSTRAINT fk_opciones_tipos FOREIGN KEY (id_tipo)
        REFERENCES tipos_opcion (id_tipo) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_opciones_recargo CHECK (recargo >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. CONFIGURACION: parámetros del negocio (límite de pedidos por día)
-- ---------------------------------------------------------------------
CREATE TABLE configuracion (
    clave       VARCHAR(50)  NOT NULL,
    valor       VARCHAR(100) NOT NULL,
    descripcion VARCHAR(150) NULL,
    CONSTRAINT pk_configuracion PRIMARY KEY (clave)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. PEDIDOS: cabecera del pedido del cliente
-- ---------------------------------------------------------------------
CREATE TABLE pedidos (
    id_pedido      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    id_usuario     INT UNSIGNED  NOT NULL,              -- cliente que hizo el pedido
    fecha_pedido   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega  DATE          NOT NULL,
    hora_entrega   TIME          NULL,
    direccion      VARCHAR(200)  NOT NULL,
    latitud        DECIMAL(10,7) NULL,                  -- obtenida con el mapa (Leaflet)
    longitud       DECIMAL(10,7) NULL,
    observaciones  VARCHAR(255)  NULL,
    total          DECIMAL(10,2) NOT NULL,              -- precio congelado al momento del pedido
    estado         ENUM('pendiente','confirmado','en_preparacion','entregado','cancelado')
                   NOT NULL DEFAULT 'pendiente',
    CONSTRAINT pk_pedidos PRIMARY KEY (id_pedido),
    CONSTRAINT fk_pedidos_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_pedidos_total CHECK (total > 0)
) ENGINE=InnoDB;

CREATE INDEX idx_pedidos_fecha_entrega ON pedidos (fecha_entrega);
CREATE INDEX idx_pedidos_estado        ON pedidos (estado);

-- ---------------------------------------------------------------------
-- 9. DETALLE_PEDIDO: productos incluidos en cada pedido
-- ---------------------------------------------------------------------
CREATE TABLE detalle_pedido (
    id_detalle      INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    id_pedido       INT UNSIGNED      NOT NULL,
    id_producto     INT UNSIGNED      NOT NULL,
    cantidad        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2)     NOT NULL,         -- precio base + recargos al momento del pedido
    dedicatoria     VARCHAR(120)      NULL,
    CONSTRAINT pk_detalle_pedido PRIMARY KEY (id_detalle),
    CONSTRAINT fk_detalle_pedidos FOREIGN KEY (id_pedido)
        REFERENCES pedidos (id_pedido) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_productos FOREIGN KEY (id_producto)
        REFERENCES productos (id_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_detalle_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. DETALLE_OPCION: opciones elegidas para cada producto del pedido
-- ---------------------------------------------------------------------
CREATE TABLE detalle_opcion (
    id_detalle INT UNSIGNED  NOT NULL,
    id_opcion  INT UNSIGNED  NOT NULL,
    recargo    DECIMAL(10,2) NOT NULL,                  -- recargo congelado al momento del pedido
    CONSTRAINT pk_detalle_opcion PRIMARY KEY (id_detalle, id_opcion),
    CONSTRAINT fk_detopc_detalle FOREIGN KEY (id_detalle)
        REFERENCES detalle_pedido (id_detalle) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detopc_opciones FOREIGN KEY (id_opcion)
        REFERENCES opciones (id_opcion) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- DATOS INICIALES
-- =====================================================================
INSERT INTO roles (nombre, descripcion) VALUES
('administrador', 'Repostero dueño del emprendimiento: gestiona catálogo, pedidos y reportes'),
('cliente',       'Persona que arma y consulta sus pedidos');

-- Contraseñas de prueba: Admin123 y Cliente123 (guardadas con password_hash)
INSERT INTO usuarios (id_rol, nombres, apellidos, correo, telefono, contrasena) VALUES
(1, 'Administrador', 'DulceEncargo', 'admin@dulceencargo.bo', '70000000',
 '$2y$10$yg4TAcCuvN0gV31d3Ai/8Oyf8VnqeRpAupciXDfiNQSNseocIadyi'),
(2, 'María', 'Rojas', 'maria@correo.com', '71234567',
 '$2y$10$utFEPeFTOQ5c/0Ki5OfjLOgnLujf1GjWb/tNgCs5jqFMluNTs9BGi');

INSERT INTO categorias (nombre) VALUES
('Tortas'), ('Postres'), ('Bocaditos');

--imagenes
INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, foto) VALUES
(1, 'Torta de chocolate',     'Bizcocho húmedo de chocolate con cobertura de ganache', 120.00, 'torta_chocolate.jpeg'),
(1, 'Torta tres leches',      'Bizcocho bañado en tres leches con merengue',           110.00, 'torta_tres_leches.jpeg'),
(1, 'Torta de vainilla',      'Bizcocho de vainilla con crema chantilly',               100.00, 'torta_vainilla.jpeg'),
(2, 'Cheesecake de frutilla', 'Base de galleta, crema de queso y salsa de frutilla',   90.00,  'cheesecake_frutilla.jpeg'),
(3, 'Caja de 25 alfajores',   'Alfajores de maicena rellenos de dulce de leche',       60.00,  'alfafores.jpeg');


INSERT INTO tipos_opcion (nombre, obligatorio) VALUES
('Tamaño', 1), ('Sabor', 1), ('Relleno', 1), ('Decoración', 0);

INSERT INTO opciones (id_tipo, nombre, recargo) VALUES
(1, '15 porciones', 0.00), (1, '25 porciones', 60.00), (1, '40 porciones', 140.00),
(2, 'Chocolate', 0.00), (2, 'Vainilla', 0.00), (2, 'Red velvet', 20.00),
(3, 'Dulce de leche', 0.00), (3, 'Frutilla', 15.00), (3, 'Moca', 15.00),
(4, 'Sencilla', 0.00), (4, 'Con flores de crema', 25.00), (4, 'Temática con fondant', 80.00);

INSERT INTO configuracion (clave, valor, descripcion) VALUES
('limite_pedidos_dia', '5',        'Cantidad máxima de pedidos que se aceptan por fecha de entrega'),
('nombre_negocio',     'DulceEncargo', 'Nombre que aparece en el sistema y en los reportes'),
('telefono_negocio',   '', 'Teléfono de contacto del emprendimiento');

-- Pedido de ejemplo para probar el panel y los reportes
INSERT INTO pedidos (id_usuario, fecha_entrega, hora_entrega, direccion, latitud, longitud, total, estado) VALUES
(2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '16:00:00', 'Av. Villazón km 6, Sacaba', -17.4040000, -66.0390000, 195.00, 'confirmado');

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, dedicatoria) VALUES
(1, 1, 1, 195.00, 'Feliz cumpleaños, Ana');

INSERT INTO detalle_opcion (id_detalle, id_opcion, recargo) VALUES
(1, 2, 60.00), (1, 4, 0.00), (1, 8, 15.00), (1, 10, 0.00);

