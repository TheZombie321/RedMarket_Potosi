import { test, expect } from '@playwright/test'

const BASE = process.env.URL || 'https://redmarketpotosi-production.up.railway.app'
const ADMIN = { email: 'admin@redmarket.com', pass: 'password123' }
const CLIENTE = { email: 'cliente@ejemplo.com', pass: 'password123' }
const REPARTIDOR = { email: 'repartidor@redmarket.com', pass: 'password123' }

test.describe('RedMarket — Demo Completa', () => {

  test('01 - Homepage', async ({ page }) => {
    await page.goto(BASE)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/01-homepage.png', fullPage: true })
  })

  test('02 - Catálogo', async ({ page }) => {
    await page.goto(`${BASE}/tienda`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/02-catalogo.png', fullPage: true })
  })

  test('03 - Detalle producto', async ({ page }) => {
    await page.goto(`${BASE}/tienda`)
    await page.waitForTimeout(1500)
    const link = page.locator('a[href^="/producto/"]').first()
    await link.click()
    await page.waitForTimeout(1500)
    await page.screenshot({ path: 'screenshots/03-detalle.png', fullPage: true })
  })

  test('04 - Login cliente', async ({ page }) => {
    await page.goto(`${BASE}/login`)
    await page.fill('input[name="email"]', CLIENTE.email)
    await page.fill('input[name="password"]', CLIENTE.pass)
    await page.click('button[type="submit"]')
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/04-login-cliente.png', fullPage: true })
  })

  test('05 - Perfil', async ({ page }) => {
    await page.goto(`${BASE}/perfil`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/05-perfil.png', fullPage: true })
  })

  test('06 - Carrito', async ({ page }) => {
    await page.goto(`${BASE}/tienda`)
    await page.waitForTimeout(1500)
    const btn = page.locator('button:has-text("Añadir")').first()
    if (await btn.isVisible()) {
      await btn.click()
      await page.waitForTimeout(500)
    }
    await page.goto(`${BASE}/carrito`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/06-carrito.png', fullPage: true })
  })

  test('07 - Tracking', async ({ page }) => {
    await page.goto(`${BASE}/tracking`)
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/07-tracking.png', fullPage: true })
  })

  test('08 - Login repartidor', async ({ page }) => {
    await page.goto(`${BASE}/login`)
    await page.fill('input[name="email"]', REPARTIDOR.email)
    await page.fill('input[name="password"]', REPARTIDOR.pass)
    await page.click('button[type="submit"]')
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/08-login-repartidor.png', fullPage: true })
  })

  test('09 - Repartidor', async ({ page }) => {
    await page.goto(`${BASE}/repartidor`)
    await page.waitForTimeout(3000)
    await page.screenshot({ path: 'screenshots/09-repartidor.png', fullPage: true })
  })

  test('10 - Login admin', async ({ page }) => {
    await page.goto(`${BASE}/login`)
    await page.fill('input[name="email"]', ADMIN.email)
    await page.fill('input[name="password"]', ADMIN.pass)
    await page.click('button[type="submit"]')
    await page.waitForTimeout(2000)
    await page.screenshot({ path: 'screenshots/10-login-admin.png', fullPage: true })
  })

  test('11 - Admin', async ({ page }) => {
    await page.goto(`${BASE}/admin`)
    await page.waitForTimeout(3000)
    await page.screenshot({ path: 'screenshots/11-admin.png', fullPage: true })
  })

  test('12 - Register', async ({ page }) => {
    await page.goto(`${BASE}/register`)
    await page.waitForTimeout(1500)
    await page.screenshot({ path: 'screenshots/12-registro.png', fullPage: true })
  })

})
