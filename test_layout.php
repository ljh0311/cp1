<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    Test Alert - If you can see this, basic styling is working
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Test Card</h5>
                        <p class="card-text">If you can see this card with proper styling, Bootstrap is working.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <i class="fas fa-check"></i> If you can see this icon, Font Awesome is working
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('JavaScript is working');
    </script>
</body>
</html> 