<?php
session_start();
require '../conexion.php';

// Solo admin puede acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = 'success';

// PROCESAR ACCIONES - SOLO SI SE ENVÍA EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    
    // 1. CAMBIAR CONTRASEÑA
    if ($accion === 'cambiar_password' && isset($_POST['id']) && isset($_POST['password'])) {
        $id = $_POST['id'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            $mensaje = "La contraseña no puede estar vacía.";
            $tipo_mensaje = 'danger';
        } elseif ($password !== $confirm_password) {
            $mensaje = "Las contraseñas no coinciden.";
            $tipo_mensaje = 'danger';
        } elseif (strlen($password) < 6) {
            $mensaje = "La contraseña debe tener al menos 6 caracteres.";
            $tipo_mensaje = 'danger';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $stmt->execute([
                    ':password' => $hash,
                    ':id' => $id
                ]);
                $mensaje = "Contraseña modificada correctamente.";
                $tipo_mensaje = 'success';
            } catch (PDOException $e) {
                $mensaje = "Error al cambiar la contraseña: " . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // 2. CAMBIAR ROL - CORREGIDO para usar los valores del ENUM
    if ($accion === 'cambiar_rol' && isset($_POST['id']) && isset($_POST['rol'])) {
        $id = $_POST['id'];
        $nuevo_rol = $_POST['rol'];
        
        // Verificar que el rol sea válido (solo los que existen en el ENUM)
        $roles_validos = ['operador', 'despachador', 'admin'];
        if (!in_array($nuevo_rol, $roles_validos)) {
            $mensaje = "Rol no válido. Los roles permitidos son: " . implode(', ', $roles_validos);
            $tipo_mensaje = 'danger';
        } elseif ($id == $_SESSION['usuario_id']) {
            $mensaje = "No puedes cambiar tu propio rol.";
            $tipo_mensaje = 'danger';
        } else {
            try {
                // Verificar que el usuario existe
                $stmt = $pdo->prepare("SELECT id, rol FROM usuarios WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$usuario) {
                    $mensaje = "El usuario no existe.";
                    $tipo_mensaje = 'danger';
                } else {
                    // Evitar que se quite el único admin
                    if ($usuario['rol'] == 'admin' && $nuevo_rol != 'admin') {
                        // Verificar cuántos admins hay
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin' AND status = 1");
                        $total_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        
                        if ($total_admins <= 1) {
                            $mensaje = "No puedes cambiar el rol del último administrador. Debe haber al menos un administrador activo.";
                            $tipo_mensaje = 'danger';
                        } else {
                            $stmt = $pdo->prepare("UPDATE usuarios SET rol = :rol WHERE id = :id");
                            $stmt->execute([
                                ':rol' => $nuevo_rol,
                                ':id' => $id
                            ]);
                            $mensaje = "Rol cambiado exitosamente de '{$usuario['rol']}' a '{$nuevo_rol}'.";
                            $tipo_mensaje = 'success';
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE usuarios SET rol = :rol WHERE id = :id");
                        $stmt->execute([
                            ':rol' => $nuevo_rol,
                            ':id' => $id
                        ]);
                        $mensaje = "Rol cambiado exitosamente de '{$usuario['rol']}' a '{$nuevo_rol}'.";
                        $tipo_mensaje = 'success';
                    }
                }
            } catch (PDOException $e) {
                $mensaje = "Error al cambiar el rol: " . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // 3. DESACTIVAR USUARIO (cambiar status a 0)
    if ($accion === 'desactivar' && isset($_POST['id_eliminar'])) {
        $id = $_POST['id_eliminar'];
        
        if ($id == $_SESSION['usuario_id']) {
            $mensaje = "No puedes desactivar tu propia cuenta.";
            $tipo_mensaje = 'danger';
        } else {
            try {
                // Verificar que el usuario existe y está activo
                $stmt = $pdo->prepare("SELECT id, status FROM usuarios WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$usuario) {
                    $mensaje = "El usuario no existe.";
                    $tipo_mensaje = 'danger';
                } elseif ($usuario['status'] == 0) {
                    $mensaje = "El usuario ya está desactivado.";
                    $tipo_mensaje = 'warning';
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET status = 0 WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                    $mensaje = "Usuario desactivado correctamente. Ya no podrá iniciar sesión.";
                    $tipo_mensaje = 'success';
                }
            } catch (PDOException $e) {
                $mensaje = "Error al desactivar el usuario: " . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // 4. REACTIVAR USUARIO (cambiar status a 1)
    if ($accion === 'reactivar' && isset($_POST['id_reactivar'])) {
        $id = $_POST['id_reactivar'];
        
        try {
            // Verificar que el usuario existe y está inactivo
            $stmt = $pdo->prepare("SELECT id, status FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                $mensaje = "El usuario no existe.";
                $tipo_mensaje = 'danger';
            } elseif ($usuario['status'] == 1) {
                $mensaje = "El usuario ya está activo.";
                $tipo_mensaje = 'warning';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET status = 1 WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mensaje = "Usuario reactivado correctamente. Ya puede iniciar sesión.";
                $tipo_mensaje = 'success';
            }
        } catch (PDOException $e) {
            $mensaje = "Error al reactivar el usuario: " . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

// Obtener todos los usuarios (incluyendo status)
$stmtUsuarios = $pdo->query("SELECT id, nombre, usuario, rol, status FROM usuarios ORDER BY nombre");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestionar Usuarios - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f6f8; }
.main-content { margin-left: 240px; padding: 22px; }
.header { background: #fff; padding: 12px 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 18px; }
.header h3 { margin: 0; font-size: 1.15rem; color: #333; }
.table-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
.alert-custom { margin-bottom: 18px; }
.table th { background-color: #f8f9fa; }
.usuario-inactivo { opacity: 0.6; background-color: #f8f9fa; }
.badge-status { padding: 5px 10px; border-radius: 20px; }
.btn-accion { margin: 2px; position: relative; }
.btn-reset-password { 
    background-color: #6c757d; 
    color: white;
    border: none;
}
.btn-reset-password:hover { 
    background-color: #5a6268; 
    color: white;
}
.btn-cambiar-rol {
    background-color: #0d6efd;
    color: white;
    border: none;
}
.btn-cambiar-rol:hover {
    background-color: #0b5ed7;
    color: white;
}
/* Tooltip personalizado */
.tooltip-inner {
    max-width: 250px;
    padding: 8px 12px;
    font-size: 13px;
}
/* Leyenda de iconos */
.leyenda-iconos {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #dee2e6;
}
.leyenda-iconos .item {
    display: inline-block;
    margin-right: 20px;
    font-size: 13px;
}
.leyenda-iconos .item i {
    margin-right: 5px;
}
/* Estilos para badges de roles */
.badge-rol-operador { background-color: #6c757d; color: white; }
.badge-rol-despachador { background-color: #17a2b8; color: white; }
.badge-rol-admin { background-color: #dc3545; color: white; }
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="header">
        <h3><i class="bi bi-people"></i> Gestionar Usuarios</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>

    <!-- Mostrar mensajes -->
    <?php if($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-custom alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $tipo_mensaje == 'success' ? 'check-circle' : ($tipo_mensaje == 'warning' ? 'exclamation-triangle' : 'exclamation-circle') ?>"></i>
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Lista de Usuarios con todas las acciones -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Lista de Usuarios</h5>
            <span class="badge bg-secondary">Total: <?= count($usuarios) ?></span>
        </div>
        
        <!-- LEYENDA DE ICONOS -->
        <div class="leyenda-iconos">
            <span class="item"><i class="bi bi-key text-secondary"></i> <strong>Reset Password</strong> - Cambiar contraseña</span>
            <span class="item"><i class="bi bi-arrow-repeat text-primary"></i> <strong>Cambiar Rol</strong> - Cambiar rol (operador/despachador/admin)</span>
            <span class="item"><i class="bi bi-person-x text-warning"></i> <strong>Desactivar</strong> - Bloquear acceso</span>
            <span class="item"><i class="bi bi-person-check text-success"></i> <strong>Reactivar</strong> - Permitir acceso</span>
            <span class="item"><i class="bi bi-person text-info"></i> <strong>Tú</strong> - Tu propia cuenta</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $u): ?>
                        <tr class="<?= $u['status'] == 0 ? 'usuario-inactivo' : '' ?>">
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td>
                                <?php 
                                // Determinar clase CSS según el rol
                                $clase_rol = 'badge-rol-' . $u['rol'];
                                ?>
                                <span class="badge <?= $clase_rol ?>">
                                    <?= htmlspecialchars($u['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($u['status'] == 1): ?>
                                    <span class="badge-status bg-success text-white">
                                        <i class="bi bi-check-circle"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge-status bg-danger text-white">
                                        <i class="bi bi-x-circle"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                                    <!-- Botón Reset Password -->
                                    <button type="button" class="btn btn-sm btn-reset-password btn-accion" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalPassword"
                                            data-id="<?= $u['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre']) ?>"
                                            data-usuario="<?= htmlspecialchars($u['usuario']) ?>"
                                            data-bs-toggle="tooltip" 
                                            title="Cambiar contraseña de <?= htmlspecialchars($u['nombre']) ?>">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    
                                    <!-- Botón Cambiar Rol -->
                                    <button type="button" class="btn btn-sm btn-cambiar-rol btn-accion" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalCambiarRol"
                                            data-id="<?= $u['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre']) ?>"
                                            data-usuario="<?= htmlspecialchars($u['usuario']) ?>"
                                            data-rol="<?= $u['rol'] ?>"
                                            data-bs-toggle="tooltip" 
                                            title="Cambiar rol de <?= htmlspecialchars($u['nombre']) ?> (actual: <?= $u['rol'] ?>)">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    
                                    <?php if($u['status'] == 1): ?>
                                        <!-- Botón Desactivar -->
                                        <button type="button" class="btn btn-sm btn-warning btn-accion" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDesactivar"
                                                data-id="<?= $u['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($u['nombre']) ?>"
                                                data-bs-toggle="tooltip" 
                                                title="Desactivar a <?= htmlspecialchars($u['nombre']) ?> (no podrá iniciar sesión)">
                                            <i class="bi bi-person-x"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Botón Reactivar -->
                                        <button type="button" class="btn btn-sm btn-success btn-accion" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalReactivar"
                                                data-id="<?= $u['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($u['nombre']) ?>"
                                                data-bs-toggle="tooltip" 
                                                title="Reactivar a <?= htmlspecialchars($u['nombre']) ?> (podrá iniciar sesión nuevamente)">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-info" data-bs-toggle="tooltip" title="Esta es tu cuenta, no puedes modificarla">
                                        <i class="bi bi-person"></i> Tú
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: CAMBIAR CONTRASEÑA -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPasswordLabel">
                    <i class="bi bi-key"></i> Cambiar Contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="cambiar_password">
                    <input type="hidden" name="id" id="idUsuarioPassword" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <p class="fw-bold" id="nombreUsuarioPassword" style="color: #0d6efd;"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: CAMBIAR ROL - CORREGIDO -->
<div class="modal fade" id="modalCambiarRol" tabindex="-1" aria-labelledby="modalCambiarRolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarRolLabel">
                    <i class="bi bi-arrow-repeat"></i> Cambiar Rol de Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="cambiar_rol">
                    <input type="hidden" name="id" id="idUsuarioRol" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <p class="fw-bold" id="nombreUsuarioRol" style="color: #0d6efd;"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rol actual:</label>
                        <p><span class="badge bg-secondary" id="rolActualUsuario"></span></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rol" class="form-label">Nuevo Rol:</label>
                        <select name="rol" id="rol" class="form-select" required>
                            <option value="operador">Operador</option>
                            <option value="despachador">Despachador</option>
                            <option value="admin">Administrador</option>
                        </select>
                        <small class="text-muted">Cambiar el rol determinará los permisos del usuario</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Permisos por rol:</strong><br>
                        <span class="badge badge-rol-operador">Operador</span> - Gestión básica de turnos<br>
                        <span class="badge badge-rol-despachador">Despachador</span> - Gestión avanzada de turnos<br>
                        <span class="badge badge-rol-admin">Administrador</span> - Acceso total al sistema
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Cambiar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DESACTIVAR -->
<div class="modal fade" id="modalDesactivar" tabindex="-1" aria-labelledby="modalDesactivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDesactivarLabel">
                    <i class="bi bi-exclamation-triangle"></i> Desactivar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas <strong>desactivar</strong> al usuario <strong id="nombreUsuarioDesactivar"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Este usuario no podrá iniciar sesión hasta que sea reactivado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="accion" value="desactivar">
                    <input type="hidden" name="id_eliminar" id="idUsuarioDesactivar" value="">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-person-x"></i> Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REACTIVAR -->
<div class="modal fade" id="modalReactivar" tabindex="-1" aria-labelledby="modalReactivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReactivarLabel">
                    <i class="bi bi-check-circle"></i> Reactivar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas <strong>reactivar</strong> al usuario <strong id="nombreUsuarioReactivar"></strong>?</p>
                <div class="alert alert-success">
                    <i class="bi bi-info-circle"></i> Este usuario podrá iniciar sesión nuevamente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="accion" value="reactivar">
                    <input type="hidden" name="id_reactivar" id="idUsuarioReactivar" value="">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-check"></i> Reactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        // Activar todos los tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Pasar datos al modal de cambio de contraseña
        var modalPassword = document.getElementById('modalPassword');
        modalPassword.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            var usuario = button.getAttribute('data-usuario');
            
            document.getElementById('idUsuarioPassword').value = id;
            document.getElementById('nombreUsuarioPassword').innerHTML = 
                '<i class="bi bi-person"></i> ' + nombre + ' (<strong>' + usuario + '</strong>)';
        });
        
        // Pasar datos al modal de cambio de rol
        var modalCambiarRol = document.getElementById('modalCambiarRol');
        modalCambiarRol.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            var usuario = button.getAttribute('data-usuario');
            var rol = button.getAttribute('data-rol');
            
            document.getElementById('idUsuarioRol').value = id;
            document.getElementById('nombreUsuarioRol').innerHTML = 
                '<i class="bi bi-person"></i> ' + nombre + ' (<strong>' + usuario + '</strong>)';
            document.getElementById('rolActualUsuario').textContent = rol;
            
            // Preseleccionar el rol actual en el select
            document.getElementById('rol').value = rol;
        });
        
        // Pasar datos al modal de desactivar
        var modalDesactivar = document.getElementById('modalDesactivar');
        modalDesactivar.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            
            document.getElementById('idUsuarioDesactivar').value = id;
            document.getElementById('nombreUsuarioDesactivar').textContent = nombre;
        });
        
        // Pasar datos al modal de reactivar
        var modalReactivar = document.getElementById('modalReactivar');
        modalReactivar.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            
            document.getElementById('idUsuarioReactivar').value = id;
            document.getElementById('nombreUsuarioReactivar').textContent = nombre;
        });

        // Validar que las contraseñas coincidan antes de enviar
        document.querySelector('#modalPassword form').addEventListener('submit', function(e) {
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor, verifica.');
            }
        });
    });
</script>

</body>
</html>