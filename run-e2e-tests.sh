#!/bin/bash
# Script para instalar y ejecutar pruebas E2E con Playwright

echo "=== Instalando Playwright ==="
npm init -y > /dev/null 2>&1
npm install @playwright/test

echo "=== Instalando navegadores (Chromium) ==="
npx playwright install chromium

echo "=== Creando directorio de screenshots ==="
mkdir -p screenshots

echo "=== Ejecutando pruebas contra Railway ==="
echo "URL: https://redmarketpotosi-production.up.railway.app"
npx playwright test --config=e2e/playwright.config.ts

echo ""
echo "=== Reporte generado ==="
echo "Abrí test-report/index.html en tu navegador para ver los resultados"
echo "Capturas de pantalla en: screenshots/"
