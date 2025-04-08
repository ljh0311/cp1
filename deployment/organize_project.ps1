# Create new directory structure
$directories = @(
    "src/controllers", "src/models", "src/views", "src/services", "src/middleware",
    "public/css", "public/js", "public/images", "public/uploads",
    "config",
    "database/migrations", "database/seeds",
    "tests/unit", "tests/integration", "tests/e2e",
    "resources/views", "resources/lang", "resources/assets",
    "storage/logs", "storage/cache", "storage/sessions", "storage/app",
    "docs/api", "docs/deployment", "docs/development"
)

foreach ($dir in $directories) {
    New-Item -ItemType Directory -Force -Path $dir
    Write-Host "Created directory: $dir"
}

# Move files to appropriate directories
# Controllers
Move-Item -Path "*.php" -Destination "src/controllers/" -ErrorAction SilentlyContinue

# Static assets
Move-Item -Path "css/*" -Destination "public/css/" -ErrorAction SilentlyContinue
Move-Item -Path "js/*" -Destination "public/js/" -ErrorAction SilentlyContinue
Move-Item -Path "images/*" -Destination "public/images/" -ErrorAction SilentlyContinue

# Configuration
Move-Item -Path "config.php" -Destination "config/" -ErrorAction SilentlyContinue
Move-Item -Path "php.ini" -Destination "config/" -ErrorAction SilentlyContinue

# Database
Move-Item -Path "database/*.sql" -Destination "database/migrations/" -ErrorAction SilentlyContinue

# Documentation
Move-Item -Path "docs/*" -Destination "docs/deployment/" -ErrorAction SilentlyContinue
Move-Item -Path "DEPLOYMENT.md" -Destination "docs/deployment/" -ErrorAction SilentlyContinue

# Create necessary .gitkeep files
$gitkeepFiles = @(
    "public/uploads/.gitkeep",
    "storage/logs/.gitkeep",
    "storage/cache/.gitkeep",
    "storage/sessions/.gitkeep"
)

foreach ($file in $gitkeepFiles) {
    New-Item -ItemType File -Force -Path $file
    Write-Host "Created .gitkeep file: $file"
}

Write-Host "Project structure reorganized successfully!" 