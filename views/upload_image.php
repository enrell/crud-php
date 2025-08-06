<?php
require_once __DIR__ . "/../lib/security/SecurityHelper.php";
require_once __DIR__ . "/../lib/backend.php";

requireAuth();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'image_path' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$action = $_POST['action'] ?? '';

if ($action === 'upload') {
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Erro no upload da imagem';
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['profile_image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        $response['message'] = 'Tipo de arquivo não permitido. Use JPEG, PNG ou GIF.';
        echo json_encode($response);
        exit;
    }

    if ($file['size'] > $maxSize) {
        $response['message'] = 'Arquivo muito grande. Máximo 5MB.';
        echo json_encode($response);
        exit;
    }

    // Create user directory
    $baseDir = dirname(__DIR__);
    $userDir = "{$baseDir}/public/{$userId}/profile_image";
    if (!file_exists($userDir)) {
        mkdir($userDir, 0755, true);
    }

    // Remove old images
    $oldImages = glob("{$userDir}/*");
    foreach ($oldImages as $oldImage) {
        if (is_file($oldImage)) {
            unlink($oldImage);
        }
    }

    // Save new image
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "image.{$extension}";
    $targetPath = "{$userDir}/{$filename}";
    $relativePath = "/public/{$userId}/profile_image/{$filename}";

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        try {
            $userRepository->updateProfileImage($userId, $relativePath);
            $response['success'] = true;
            $response['message'] = 'Imagem atualizada com sucesso!';
            $response['image_path'] = $relativePath;
        } catch (Exception $e) {
            $response['message'] = "Erro ao salvar no banco: {$e->getMessage()}";
        }
    } else {
        $response['message'] = 'Erro ao salvar a imagem';
    }

} elseif ($action === 'delete') {
    try {
        $user = $userRepository->findById($userId);
        $imagePath = $user['profile_image'];

        if ($imagePath) {
            $fullPath = dirname(__DIR__) . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $userRepository->deleteProfileImage($userId);
        $response['success'] = true;
        $response['message'] = 'Imagem removida com sucesso!';
    } catch (Exception $e) {
        $response['message'] = "Erro ao remover imagem: {$e->getMessage()}";
    }
}

echo json_encode($response);
