#!/bin/bash
set -e
echo "=== RedMarket — Demo E2E ==="
echo ""

if [ ! -d "node_modules" ]; then
  echo "Instalando Playwright..."
  npm init -y > /dev/null 2>&1
  npm install @playwright/test
fi

if ! npx playwright install chromium 2>&1 | tail -1; then
  echo "Instalando Chromium..."
  npx playwright install chromium
fi

mkdir -p screenshots

echo ""
echo "Ejecutando 12 pruebas contra Railway..."
echo "URL: https://redmarketpotosi-production.up.railway.app"
echo ""

npx playwright test --config=e2e/playwright.config.ts

echo ""
echo "=== Listo ==="
echo "Reporte HTML: test-report/index.html"
echo "Screenshots:  screenshots/"
echo ""
echo "Para ver el reporte:"
echo "  npx playwright show-report test-report"
