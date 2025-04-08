# Move remaining directories to their proper locations
$moves = @{
    "admin/*" = "src/controllers/admin/"
    "inc/*" = "src/includes/"
    "cart/*" = "src/controllers/cart/"
    "tools/*" = "src/tools/"
    "logs/*" = "storage/logs/"
    "sessions/*" = "storage/sessions/"
    "uploads/*" = "public/uploads/"
    "css/*" = "public/css/"
    "js/*" = "public/js/"
    "images/*" = "public/images/"
}

# Create additional needed directories
New-Item -ItemType Directory -Force -Path "src/includes"
New-Item -ItemType Directory -Force -Path "src/tools"

foreach ($source in $moves.Keys) {
    $destination = $moves[$source]
    Write-Host "Moving files from $source to $destination"
    Move-Item -Path $source -Destination $destination -Force -ErrorAction SilentlyContinue
}

# Move configuration files
Move-Item -Path "server.bat" -Destination "deployment/" -ErrorAction SilentlyContinue

# Clean up empty directories
$dirsToRemove = @(
    "admin",
    "inc",
    "cart",
    "tools",
    "logs",
    "sessions",
    "uploads",
    "css",
    "js",
    "images"
)

foreach ($dir in $dirsToRemove) {
    if (Test-Path $dir) {
        Remove-Item -Path $dir -Recurse -ErrorAction SilentlyContinue
        Write-Host "Removed empty directory: $dir"
    }
}

Write-Host "Cleanup completed successfully!" 