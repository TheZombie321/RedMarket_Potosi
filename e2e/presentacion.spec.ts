import { test, expect } from '@playwright/test'

// ─── CONFIG ───
const BASE = process.env.URL || 'https://redmarketpotosi-production.up.railway.app'
const ADMIN = { email: 'admin@redmarket.com', pass: 'password123', name: 'Admin RedMarket' }
const CLIENTE = { email: 'cliente@ejemplo.com', pass: 'password123', name: 'Cliente Potosí' }
const REPARTIDOR = { email: 'repartidor@redmarket.com', pass: 'password123', name: 'Repartidor Pérez' }

test.describe('RedMarket — Presentación', () => {

  test('01 - Homepage sin auth', async ({ page }) => {
    await page.goto(BASE)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/01-homepage.png', fullPage: true })
    // Ver que se ven secciones principales
    await expect(page.locator('text=Categorías').first()).toBeVisible()
    await expect(page.locator('text=Ofertas especiales').first()).toBeVisible()
  })

  test('02 - Catálogo de productos', async ({ page }) => {
    await page.goto(`${BASE}/tienda`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/02-catalogo.png', fullPage: true })
    await expect(page.locator('.grid > a, .grid > div').first()).toBeVisible()
  })

  test('03 - Detalle de producto', async ({ page }) => {
    await page.goto(`${BASE}/tienda`)
    await page.waitForTimeout(1500)
    // Click en primer producto
    const primerProducto = page.locator('a[href^="/producto/"], .grid a').first()
    await primerProducto.click()
    await page.waitForTimeout(1500)
    await page.screenshot({ path: 'screenshots/03-detalle-producto.png', fullPage: true })
  })

  test('04 - Login como cliente', async ({ page }) => {
    await page.goto(`${BASE}/login`)
    await page.fill('input[type="email"], input[name="email"]', CLIENTE.email)
    await page.fill('input[type="password"], input[name="password"]', CLIENTE.pass)
    await page.click('button[type="submit"]')
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/04-login-cliente.png', fullPage: true })
  })

  test('05 - Carrito de compras', async ({ page }) => {
    // Ir a catalogo y agregar producto
    await page.goto(`${BASE}/tienda`)
    await page.waitForTimeout(1500)
    // Buscar boton Añadir
    const btnAnadir = page.locator('button:has-text("Añadir"), button:has-text("+"), .añadir-btn').first()
    if (await btnAnadir.isVisible()) {
      await btnAnadir.click()
      await page.waitForTimeout(500)
    }
    await page.goto(`${BASE}/carrito`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/05-carrito.png', fullPage: true })
  })

  test('06 - Perfil del cliente', async ({ page }) => {
    await page.goto(`${BASE}/perfil`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/06-perfil.png', fullPage: true })
  })

  test('07 - Tracking de pedido', async ({ page }) => {
    await page.goto(`${BASE}/tracking`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/07-tracking.png', fullPage: true })
  })

  test('08 - Login como repartidor', async ({ page }) => {
    await page.goto(`${BASE}/login`)
    await page.fill('input[type="email"], input[name="email"]', REPARTIDOR.email)
    await page.fill('input[type="password"], input[name="password"]', REPARTIDOR.pass)
    await page.click('button[type="submit"]')
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/08-login-repartidor.png', fullPage: true })
  })

  test('09 - Vista repartidor', async ({ page }) => {
    await page.goto(`${BASE}/repartidor`)
    await page.waitForTimeout(3000)
    await page.screenshot({ path: 'screenshots/09-repartidor-disponibles.png', fullPage: true })
    // Cambiar a pestaña Mis Entregas
    const tabEntregas = page.locator('button:has-text("Mis Entregas")')
    if (await tabEntregas.isVisible()) {
      await tabEntregas.click()
      await page.waitForTimeout(1000)
      await page.screenshot({ path: 'screenshots/10-repartidor-entregas.png', fullPage: true })
    }
  })

  test('11 - Login como admin', async ({ page }) => {
    await page.goto(`${BASE}/login`)
    await page.fill('input[type="email"], input[name="email"]', ADMIN.email)
    await page.fill('input[type="password"], input[name="password"]', ADMIN.pass)
    await page.click('button[type="submit"]')
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/11-login-admin.png', fullPage: true })
  })

  test('12 - Panel admin', async ({ page }) => {
    await page.goto(`${BASE}/admin`)
    await page.waitForTimeout(3000)
    await page.screenshot({ path: 'screenshots/12-admin-dashboard.png', fullPage: true })
    // Click en tabs si existen
    for (const tabName of ['Productos', 'Pedidos', 'Usuarios']) {
      const tab = page.locator(`button:has-text("${tabName}"), a:has-text("${tabName}")`).first()
      if (await tab.isVisible()) {
        await tab.click()
        await page.waitForTimeout(1000)
        await page.screenshot({ path: `screenshots/12-admin-${tabName.toLowerCase()}.png`, fullPage: true })
      }
    }
  })

  test('13 - Registro de usuario', async ({ page }) => {
    await page.goto(`${BASE}/register`)
    await page.waitForTimeout(1500)
    await page.screenshot({ path: 'screenshots/13-registro.png', fullPage: true })
  })

})
