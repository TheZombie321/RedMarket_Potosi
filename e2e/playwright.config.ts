import { defineConfig } from '@playwright/test'

export default defineConfig({
  testDir: './e2e',
  timeout: 60000,
  expect: { timeout: 10000 },
  fullyParallel: false,
  retries: 1,
  reporter: [['html', { outputFolder: 'test-report' }]],
  use: {
    baseURL: process.env.URL || 'https://redmarketpotosi-production.up.railway.app',
    viewport: { width: 1280, height: 900 },
    actionTimeout: 15000,
    screenshot: 'on',
    video: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],
})
