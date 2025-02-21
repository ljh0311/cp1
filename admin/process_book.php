<?php
session_start();
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $db = DatabaseManager::getInstance();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            // Handle file upload
            $target_dir = "../uploads/books/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file = $_FILES['image'];
            $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $target_file = $target_dir . uniqid() . '.' . $imageFileType;

            // Check file type
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.');
            }

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $image_url = str_replace('../', '', $target_file);
                
                $stmt = $db->query(
                    "INSERT INTO books (title, author, price, image_url, description, category, featured) 
                     VALUES (:title, :author, :price, :image_url, :description, :category, :featured)",
                    [
                        ':title' => $_POST['title'],
                        ':author' => $_POST['author'],
                        ':price' => $_POST['price'],
                        ':image_url' => $image_url,
                        ':description' => $_POST['description'],
                        ':category' => $_POST['category'],
                        ':featured' => isset($_POST['featured']) ? 1 : 0
                    ]
                );

                header('Location: admin_dashboard.php?success=1');
                exit;
            } else {
                throw new Exception('Failed to upload file.');
            }
            break;

        case 'edit':
            $data = [
                ':book_id' => $_POST['book_id'],
                ':title' => $_POST['title'],
                ':author' => $_POST['author'],
                ':price' => $_POST['price'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':featured' => isset($_POST['featured']) ? 1 : 0
            ];

            // Handle image update if new image is uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "../uploads/books/";
                $file = $_FILES['image'];
                $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $target_file = $target_dir . uniqid() . '.' . $imageFileType;

                if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception('Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.');
                }

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $data[':image_url'] = str_replace('../', '', $target_file);
                    
                    // Delete old image
                    $old_image = $db->query("SELECT image_url FROM books WHERE book_id = :id", [':id' => $_POST['book_id']]);
                    $old_image_path = $old_image->fetch()['image_url'];
                    if ($old_image_path && file_exists('../' . $old_image_path)) {
                        unlink('../' . $old_image_path);
                    }
                }
            }

            $sql = "UPDATE books SET 
                    title = :title, 
                    author = :author, 
                    price = :price, 
                    description = :description, 
                    category = :category, 
                    featured = :featured";
            
            if (isset($data[':image_url'])) {
                $sql .= ", image_url = :image_url";
            }
            
            $sql .= " WHERE book_id = :book_id";

            $db->query($sql, $data);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            // Get image path before deleting
            $result = $db->query("SELECT image_url FROM books WHERE book_id = :id", [':id' => $_POST['book_id']]);
            $image_path = $result->fetch()['image_url'];

            // Delete the book
            $db->query("DELETE FROM books WHERE book_id = :id", [':id' => $_POST['book_id']]);

            // Delete the image file
            if ($image_path && file_exists('../' . $image_path)) {
                unlink('../' . $image_path);
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            break;

        case 'toggle_featured':
            $db->query(
                "UPDATE books SET featured = :featured WHERE book_id = :id",
                [':featured' => $_POST['featured'], ':id' => $_POST['book_id']]
            );

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 