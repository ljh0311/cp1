#!/bin/bash

# Create new directory structure
mkdir -p src/{controllers,models,views,services,middleware}
mkdir -p public/{css,js,images,uploads}
mkdir -p config
mkdir -p database/{migrations,seeds}
mkdir -p tests/{unit,integration,e2e}
mkdir -p resources/{views,lang,assets}
mkdir -p storage/{logs,cache,sessions,app}
mkdir -p docs/{api,deployment,development}

# Move files to appropriate directories
# Controllers
mv *.php src/controllers/ 2>/dev/null || true

# Static assets
mv css/* public/css/ 2>/dev/null || true
mv js/* public/js/ 2>/dev/null || true
mv images/* public/images/ 2>/dev/null || true

# Configuration
mv config.php config/ 2>/dev/null || true
mv php.ini config/ 2>/dev/null || true

# Database
mv database/*.sql database/migrations/ 2>/dev/null || true

# Documentation
mv docs/* docs/deployment/ 2>/dev/null || true
mv DEPLOYMENT.md docs/deployment/ 2>/dev/null || true
mv README.md ./ 2>/dev/null || true

# Create necessary .gitkeep files
touch public/uploads/.gitkeep
touch storage/logs/.gitkeep
touch storage/cache/.gitkeep
touch storage/sessions/.gitkeep

# Set proper permissions
chmod -R 755 public/
chmod -R 777 storage/
chmod -R 777 public/uploads/

echo "Project structure reorganized successfully!" 