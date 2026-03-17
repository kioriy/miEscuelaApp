#!/usr/bin/env bash
set -euo pipefail

# ==============================================================
# deploy.sh - Build miEscuelaApp para produccion
# Uso: bash deploy.sh
# ==============================================================

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
FRONTEND_DIR="$SCRIPT_DIR/frontend"
PUBLIC_DIR="$SCRIPT_DIR/public"

echo "==> [1/4] Instalando dependencias del frontend..."
cd "$FRONTEND_DIR"
npm ci

echo "==> [2/4] Construyendo frontend (mode=production)..."
npm run build

echo "==> [3/4] Limpiando assets anteriores en public/..."
# Eliminar assets del frontend anterior, preservando archivos de Laravel
cd "$PUBLIC_DIR"
rm -rf assets/
rm -f sw.js workbox-*.js manifest.webmanifest
rm -f android-chrome-*.png favicon.png favicon1.png favicon.ico

echo "==> [4/4] Copiando dist/ a public/..."
cp -r "$FRONTEND_DIR/dist/"* "$PUBLIC_DIR/"

# Verificar que .htaccess de Laravel sigue presente
if [ ! -f "$PUBLIC_DIR/.htaccess" ]; then
  echo "ADVERTENCIA: .htaccess no encontrado en public/. Laravel no funcionara correctamente."
  exit 1
fi

# Verificar que index.html existe
if [ ! -f "$PUBLIC_DIR/index.html" ]; then
  echo "ERROR: index.html no encontrado en public/ despues del deploy."
  exit 1
fi

echo ""
echo "Deploy completado exitosamente."
echo "Archivos en public/:"
ls -la "$PUBLIC_DIR/" | head -20
echo ""
echo "Recuerda en el servidor:"
echo "  1. php artisan config:cache"
echo "  2. php artisan route:cache"
echo "  3. php artisan view:clear"
echo "  4. Verificar APP_DEBUG=false en .env"
