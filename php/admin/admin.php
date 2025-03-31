<?php
session_start();
// require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../api_data/get_top_songs.php';

define('SESSION_TIMEOUT', 1800);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
  header('Location: login.php');
  exit;
}

// Verificar si la sesión ha expirado
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
  session_unset();
  session_destroy();
  header('Location: /php/login.php?message=session_expired');
  exit;
}

// Actualizar actividad de la sesión
$_SESSION['last_activity'] = time();


$data = get_top_ten();

?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrador</title>
  <link rel="stylesheet" href="../../public/assets/css/admin_panel.css">
</head>

<body>
  <header>
    <h1>Panel de Administración</h1>
    <a href="logout.php">Cerrar sesión</a>
  </header>
  <main>
    <table>
      <thead>
        <th>Top</th>
        <th>Nombre</th>
        <th>Editar Preview</th>
      </thead>
      <tbody>
        <?php foreach ($data as $key => $value) {
          $top = $value['top'];
          $name = $value['name'];
          $id = $value['spotify_id'];
          $is_preview = $value['preview'];
          $path = $value['file_path'] ?? null;

          echo "<tr>
            <td class='td-top'>$top</td>
            <td class='td-name'>$name</td>
            <td class='td-btn'><button class='add-btn' onclick='show_upload_modal(\"$name\", \"$id\", \"$is_preview\")'>Editar</button></td>
        </tr>";
        } ?>

      </tbody>
    </table>
    <div id="modal-container">


      <div id="upload-modal">

        <div id="form-container">
          <h2 id="modal-title">Subir Canciones</h2>
          <form id="modal-form" action="http://localhost/growcode/web_austero/php/admin/admin_upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
              <label for="title">Título de la Canción:</label>
              <input type="text" id="title" name="title" placeholder="Título de la canción"
                value="" readonly>
            </div>
            <div id="preview-row" class="form-row">
              <label for="preview-player">Preview:</label>
              <audio id="preview-player" src="" type="audio/mpeg" controls>
              </audio>
            </div>
            <div class="form-row">

              <label id="file-label" for="song_file">Archivo MP3:</label>
              <input type="file" id="song_file" name="song_file" accept="audio/mp3" required>
            </div>
            <input type="hidden" id="spotify_id" name="spotify_id" value="">
            <input type="hidden" id="update" name="update" value="">
            <input type="submit" class="submit-btn" value="Subir canción">

            <button id="close-modal">Salir</button>
          </form>
        </div>
      </div>
    </div>

  </main>
  <footer>
    &copy; 2024 Mitomanía. Todos los derechos reservados.
  </footer>

  <div id="successModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <p id="modalMessage">jjgjggj</p>
    </div>
  </div>


  </main>

  <!-- Script para el Modal -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('successModal');
      const modalMessage = document.getElementById('modalMessage');
      const closeModal = document.querySelector('.modal-content .close');
      const form = document.querySelector('form');

      <?php if (!empty($_SESSION['upload_message'])): ?>
        modalMessage.textContent = "<?= htmlspecialchars($_SESSION['upload_message']); ?>";
        modal.style.display = 'block';
        <?php unset($_SESSION['upload_message']); // Limpiar mensaje 
        ?>
      <?php endif; ?>

      closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
        form.reset(); // Limpiar el formulario
      });

      window.addEventListener('click', (event) => {
        if (event.target === modal) {
          modal.style.display = 'none';
          form.reset(); // Limpiar el formulario
        }
      });
    });

    <?php
    $path_list = "const path_list={";
    foreach ($data as $value) {
      if ($value['preview']) {
        $path_list .= "'" . $value['spotify_id'] . "': \"" . $value['file_path'] . "\",";
      }
    }
    $path_list .= "}";
    echo $path_list;
    ?>

    function show_upload_modal(song_name, id, preview) {
      const title = document.getElementById('title')
      const spotify_id = document.getElementById('spotify_id')
      const player = document.getElementById('preview-player')
      const file_label = document.getElementById('file-label')
      const preview_row = document.getElementById('preview-row')
      const update = document.getElementById('update')
      title.value = song_name
      spotify_id.value = id
      if (preview) {
        preview_row.style.display = "block"
        player.src = path_list[id]
        // player.controls = true
        file_label.innerHTML = "Elegir otro archivo:"
        update.value = "1"
      } else {
        preview_row.style.display = "none"
        // player.controls = false
        update.value = ""
        file_label.innerHTML = "Agregar preview:"
      }
      const upload_modal = document.getElementById('modal-container')
      upload_modal.style.display = 'block'
    }
    // show_upload_modal('Mi canción', '', '12')

    const colse_modal = document.getElementById('close-modal')
    const upload_modal = document.getElementById('modal-container')
    const player = document.getElementById('preview-player')
    colse_modal.addEventListener('click', function(e) {
      upload_modal.style.display = 'none'
      player.pause()
    })
    window.addEventListener('click', (event) => {
      if (event.target === upload_modal) {
        upload_modal.style.display = 'none'
        player.pause()
      }
    });
  </script>
</body>

</html>