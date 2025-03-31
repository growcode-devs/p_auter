<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['song_file'])) {
    $title = $_POST['title'];
    $song_id = $_POST['spotify_id'];
    $file = $_FILES['song_file'];
    $upload_dir = __DIR__ . '/../../uploads/';
    $allowed_extensions = ['mp3'];

    // Inicializar mensaje vacío
    $_SESSION['upload_message'] = '';

    // Validar extensión del archivo
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['upload_message'] = "Error: Solo se permiten archivos MP3.";
        header('Location: admin.php');
        exit;
    }

    // Validar tamaño del archivo (máximo 5 MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['upload_message'] = "Error: El archivo es demasiado grande (máximo 5 MB).";
        header('Location: admin.php');
        exit;
    }

    // Generar un nombre único para el archivo
    $file_name = $song_id . '-' . basename($file['name']);
    $file_path = $upload_dir . $file_name;

    //Eliminar el existente cuando es actualización
    if ($_POST['update']) {
        $directorio = __DIR__ . '/../uploads/';
        $archivos = scandir($directorio);

        foreach ($archivos as $archivo) {
            // echo $archivo . "<br>";
            if (str_contains($archivo, $song_id)) {
                $archivo_a_eliminar = $directorio . $archivo;


                if (file_exists($archivo_a_eliminar)) {
                    if (!unlink($archivo_a_eliminar)) {
                        $_SESSION['upload_message'] = "Error: No se pudo eliminar el archivo existente";
                        header('Location: admin.php');
                        exit;
                    }
                }
                break;
            }
        }
    }

    // Mover el archivo al directorio de uploads
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $_SESSION['upload_message'] = "Error: No se pudo subir el archivo.";
        header('Location: admin.php');
        exit;
    }

    if ($_POST['update']) {
        // Guardar información en la base de datos
        $stmt = $mysqli->prepare("UPDATE songs SET file_path = ? WHERE spotify_id = ?");
        $stmt->bind_param('ss',  $file_name, $song_id);
        if ($stmt->execute()) {
            $_SESSION['upload_message'] = "Canción actualizada exitosamente.";
        } else {
            $_SESSION['upload_message'] = "Error al guardar en la base de datos: " . $stmt->error;
        }
    } else {
        $stmt = $mysqli->prepare("INSERT INTO songs (title, file_path, spotify_id) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $title, $file_name, $song_id);
        if ($stmt->execute()) {
            $_SESSION['upload_message'] = "Canción subida exitosamente.";
        } else {
            $_SESSION['upload_message'] = "Error al guardar en la base de datos: " . $stmt->error;
        }
    }
    $stmt->close();
}

$mysqli->close();
header('Location: admin.php');
exit;
