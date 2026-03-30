#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
FRONTEND_DIR="$SCRIPT_DIR/frontend"
PUBLIC_DIR="$SCRIPT_DIR/public"

echo "==> [1/3] Construyendo frontend..."
cd "$FRONTEND_DIR"
npm run build

echo "==> [2/3] Limpiando assets anteriores en public/..."
cd "$PUBLIC_DIR"
rm -rf assets/
rm -f sw.js workbox-*.js manifest.webmanifest
rm -f android-chrome-*.png favicon.png favicon1.png favicon.ico
rm -f index.html registerSW.js

echo "==> [3/3] Copiando dist/ a public/..."
cp -r "$FRONTEND_DIR/dist/"* "$PUBLIC_DIR/"

echo ""
echo "Rebuild completado. Sube public/ al servidor via FileZilla."
