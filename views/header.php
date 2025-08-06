<?php
require_once __DIR__ . "/../lib/security/SecurityHelper.php";
require_once __DIR__ . "/../lib/backend.php";

$userProfile = null;
$profileImage = null;
if (isAuthenticated()) {
  try {
    $userId = $_SESSION["user_id"] ?? null;
    if ($userId) {
      $user = $userRepository->findById($userId);
      $profileImage = $user["profile_image"] ?? null;
    }
  } catch (Exception $e) {
    error_log("Failed to retrieve user profile: " . $e->getMessage());
  }
}

// Define a default title
$title = "Sistema de Agendamento Médico";
// You can set a specific title for each page by defining a $pageTitle variable before including the header
if (isset($pageTitle)) {
  $title = $pageTitle . " - " . $title;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Sistema de Agendamento Médico</h1>
        <nav>
            <?php if (isAuthenticated()): ?>
                <a href="/index.php" class="<?= $currentPage === "index"
                  ? "active"
                  : "" ?>">Início</a>
                <a href="/views/appointments.php" class="<?= $currentPage ===
                "appointments"
                  ? "active"
                  : "" ?>">Consultas</a>
                <a href="/views/doctors.php" class="<?= $currentPage ===
                "doctors"
                  ? "active"
                  : "" ?>">Médicos</a>
                <a href="/views/patients.php" class="<?= $currentPage ===
                "patients"
                  ? "active"
                  : "" ?>">Pacientes</a>
                <a href="/views/logout.php">Sair</a>
        </nav>
        <div class="user-profile" onclick="openProfileModal()">
            <?php if ($profileImage && !empty($profileImage)): ?>
                <img src="<?= htmlspecialchars(
                  $profileImage,
                ) ?>" alt="Profile" class="profile-image">
            <?php else: ?>
                <img src="/assets/images/default-avatar.svg" alt="Profile" class="profile-image">
            <?php endif; ?>
            <span class="username"><?= htmlspecialchars(
              $_SESSION["username"] ?? "",
            ) ?></span>
        </div>
            <?php else: ?>
                <a href="/views/login.php" class="<?= $currentPage === "login"
                  ? "active"
                  : "" ?>">Entrar</a>
                <a href="/views/signup.php" class="<?= $currentPage === "signup"
                  ? "active"
                  : "" ?>">Cadastrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Profile Modal -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeProfileModal()">&times;</span>
        <div class="profile-modal-header">
            <?php if ($profileImage && !empty($profileImage)): ?>
                <img src="<?= htmlspecialchars(
                  $profileImage,
                ) ?>" alt="Profile" class="modal-profile-image">
            <?php else: ?>
                <img src="/assets/images/default-avatar.svg" alt="Profile" class="modal-profile-image">
            <?php endif; ?>
            <div class="camera-icon" onclick="openImageModal()">
                <img src="/assets/images/profile-image-edit-icon.svg" alt="Editar imagem de perfil" style="width:20px;height:20px;">
            </div>
        </div>
        <h3><?= htmlspecialchars($_SESSION["username"] ?? "") ?></h3>
        <div class="profile-actions">
            <a href="/views/profile.php" class="btn btn-primary">Editar Perfil</a>
        </div>
    </div>
</div>

<!-- Image Upload Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content image-modal-content">
        <div class="modal-header">
            <h3>Alterar Foto de Perfil</h3>
            <span class="close" onclick="closeImageModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="imageUploadForm" enctype="multipart/form-data">
                <div class="upload-section" id="uploadSection">
                    <label for="profileImageInput" class="file-upload-label" id="uploadLabel">
                        <div class="upload-icon">📁</div>
                        <span>Escolher imagem ou arrastar aqui</span>
                        <small>JPG, PNG ou GIF até 2MB</small>
                    </label>
                    <input type="file" id="profileImageInput" accept="image/*" onchange="previewImage(this)" style="display: none;">
                </div>
                <div id="imagePreview" class="image-preview-container"></div>
                <div class="image-actions">
                    <button type="button" onclick="closeImageModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="button" onclick="uploadImage()" class="btn btn-primary" id="saveImageBtn" disabled>Salvar</button>
                    <?php if ($profileImage && !empty($profileImage)): ?>
                        <button type="button" onclick="deleteImage()" class="btn btn-danger">Remover Atual</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openProfileModal() {
    document.getElementById('profileModal').style.display = 'block';
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

function openImageModal() {
    document.getElementById('imageModal').style.display = 'block';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const saveBtn = document.getElementById('saveImageBtn');

    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            alert('Arquivo muito grande. Máximo 2MB permitido.');
            input.value = '';
            preview.innerHTML = '';
            saveBtn.disabled = true;
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="preview-wrapper">
                    <img src="${e.target.result}" class="preview-image">
                    <div class="preview-info">
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">${(file.size / 1024).toFixed(1)} KB</span>
                    </div>
                </div>
            `;
            saveBtn.disabled = false;
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
        saveBtn.disabled = true;
    }
}

// Drag and drop functionality
function setupDragAndDrop() {
    const uploadSection = document.getElementById('uploadSection');
    const uploadLabel = document.getElementById('uploadLabel');
    const fileInput = document.getElementById('profileImageInput');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadSection.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadSection.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadSection.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        uploadLabel.classList.add('drag-over');
    }

    function unhighlight(e) {
        uploadLabel.classList.remove('drag-over');
    }

    uploadSection.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            previewImage(fileInput);
        }
    }
}

// Initialize drag and drop when modal opens
function openImageModal() {
    document.getElementById('imageModal').style.display = 'block';
    setupDragAndDrop();
}

function uploadImage() {
    const fileInput = document.getElementById('profileImageInput');
    const saveBtn = document.getElementById('saveImageBtn');

    if (!fileInput.files[0]) {
        alert('Selecione uma imagem');
        return;
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Salvando...';

    const formData = new FormData();
    formData.append('profile_image', fileInput.files[0]);
    formData.append('action', 'upload');

    fetch('/views/upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Imagem atualizada com sucesso!');
            closeImageModal();
            location.reload();
        } else {
            alert(data.message || 'Erro ao fazer upload da imagem');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Salvar';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro na requisição');
        saveBtn.disabled = false;
        saveBtn.textContent = 'Salvar';
    });
}

function deleteImage() {
    if (!confirm('Tem certeza que deseja remover a foto de perfil?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');

    fetch('/views/upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Imagem removida com sucesso!');
            closeImageModal();
            location.reload();
        } else {
            alert(data.message || 'Erro ao remover imagem');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro na requisição');
    });
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('click', function(event) {
        const profileModal = document.getElementById('profileModal');
        const imageModal = document.getElementById('imageModal');

        if (event.target === profileModal) {
            closeProfileModal();
        }
        if (event.target === imageModal) {
            closeImageModal();
        }
    });
});
</script>
