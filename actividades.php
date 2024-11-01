<?php
include('conexion.php');

// Si la cookie "usuario_logeado" esta vacia, lo devuelvo al index
if (empty($_COOKIE['usuario_logeado'])) {
  header('Location: index.php');
}

// Agarro el id de usuario de la cookie
$cookie = explode(";", $_COOKIE['usuario_logeado']);
$id_usuario = $cookie[1];

// Si esta el parametro "eliminar", borro el producto en base al id
if (isset($_GET['eliminar'])) {
  // Agarro el id_actividad del parametro de la url
  $id_actividad = $_GET["eliminar"];
  if (!is_numeric($id_actividad)) {
      // Si el id_actividad pasado por parametro no es un numero, lo llevo a las actividades sin el parametro
      header('Location: actividades.php');
  }
  // Ejecuto la query para borrar el producto en base al id pasado por parametro
  $query = "DELETE FROM actividades WHERE id = {$id_actividad}";
  $conexion->query($query);
  
  $accion = 'Actividad eliminada exitosamente.';
} else if (isset($_GET['accion'])) {
  $get_accion = $_GET['accion'];
  if ($get_accion == 'agrego') {
    $accion = 'Actividad creada exitosamente.';
  } else if ($get_accion == 'actualizo') {
    $accion = 'Actividad actualizada exitosamente.';
  }
}

$query = "SELECT es_jefe FROM usuarios WHERE id = '{$id_usuario}'";
$es_jefe = $conexion->query($query)->fetch_assoc()['es_jefe'];

if ($es_jefe) {
  $query = "SELECT  p.id id_proyecto,
                    p.nombre nombre_proyecto,
                    a.id,
                    a.nombre,
                    COALESCE(pa.estado, 0) estado,
                    pa.fecha_asociado,
                    pa.fecha_completado
            FROM actividades a
            LEFT JOIN proyecto_actividad pa ON a.id = pa.id_actividad
            LEFT JOIN proyectos p ON pa.id_proyecto = p.id
            ORDER BY a.id, pa.id_proyecto";
} else {
  $query = "SELECT  ua.id_proyecto,
                    p.nombre nombre_proyecto,
                    a.id,
                    a.nombre,
                    pa.estado,
                    pa.fecha_asociado,
                    pa.fecha_completado
            FROM usuario_actividad ua
            JOIN proyecto_actividad pa ON ua.id_actividad = pa.id_actividad AND ua.id_proyecto = pa.id_proyecto
            JOIN proyectos p ON pa.id_proyecto = p.id
            JOIN actividades a ON ua.id_actividad = a.id
            WHERE ua.id_usuario = {$id_usuario}
            ORDER BY a.id, p.id";
}
$actividades = $conexion->query($query);
$conexion->close();

$estados = [
  0 => "",
  1 => "Pendiente",
  2 => "En progreso",
  3 => "Completada"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <title>Actividades</title>
</head>
<body class="w-screen h-screen flex flex-col">
  <?php include('toast.php'); ?>
  <?php include('header.php'); ?>
  <div class="h-0 flex-1 w-full flex">
    <?php include('sidebar.php') ?>
    <div class="flex-1 overflow-y-auto">
      <div class="p-8">
        <?php include('actividades_content.php'); ?>
      </div>
    </div>
  </div>
</body>
</html>