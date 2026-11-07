import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const playwrightModule = process.env.PLAYWRIGHT_MODULE
    ?? path.join(process.env.USERPROFILE, '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright', 'index.mjs');
const { chromium } = await import(pathToFileURL(playwrightModule).href);

const chromePath = process.env.CHROME_PATH
    ?? path.join(process.env.LOCALAPPDATA, 'Google', 'Chrome', 'Application', 'chrome.exe');
const outputDirectory = path.resolve('storage/app/brand-logo-layout');
const logoPath = '/images/main-logo-desktop-svg.svg';
const cases = [
    { name: 'support-desktop', url: '/support/omc-crm', selector: '.brand img', width: 1280, height: 720 },
    { name: 'support-mobile', url: '/support/omc-crm', selector: '.brand img', width: 390, height: 720 },
    { name: 'app-desktop', url: '/app', selector: '.brand img', width: 1280, height: 720 },
    { name: 'app-mobile', url: '/app', selector: '.brand img', width: 390, height: 720 },
];

await fs.mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({ executablePath: chromePath, headless: true });
const results = [];

try {
    for (const testCase of cases) {
        const page = await browser.newPage({ viewport: { width: testCase.width, height: testCase.height } });
        await page.goto(`http://127.0.0.1:8000${testCase.url}`, { waitUntil: 'networkidle' });
        await page.locator('#sitePreloader').waitFor({ state: 'detached', timeout: 5000 }).catch(async () => {
            await page.locator('#sitePreloader').evaluate(element => element.remove()).catch(() => {});
        });

        const logo = page.locator(testCase.selector).first();
        await logo.waitFor({ state: 'visible' });

        const geometry = await logo.evaluate(element => {
            const bounds = element.getBoundingClientRect();

            return {
                src: element.getAttribute('src'),
                width: bounds.width,
                height: bounds.height,
                naturalWidth: element.naturalWidth,
                naturalHeight: element.naturalHeight,
            };
        });

        assert.ok(geometry.src?.includes(logoPath), `${testCase.name}: unexpected logo source`);
        assert.ok(geometry.width > 0 && geometry.height > 0, `${testCase.name}: logo is not visible`);
        assert.ok(geometry.naturalWidth > 0 && geometry.naturalHeight > 0, `${testCase.name}: logo did not load`);

        const renderedRatio = geometry.width / geometry.height;
        const naturalRatio = geometry.naturalWidth / geometry.naturalHeight;
        assert.ok(
            Math.abs(renderedRatio - naturalRatio) / naturalRatio < 0.03,
            `${testCase.name}: logo is stretched (${renderedRatio} vs ${naturalRatio})`,
        );

        await page.screenshot({
            path: path.join(outputDirectory, `${testCase.name}.png`),
            fullPage: false,
        });

        results.push({ name: testCase.name, ...geometry, renderedRatio, naturalRatio });
        await page.close();
    }
} finally {
    await browser.close();
}

await fs.writeFile(
    path.join(outputDirectory, 'verification.json'),
    `${JSON.stringify(results, null, 2)}\n`,
    'utf8',
);

process.stdout.write(`${JSON.stringify(results, null, 2)}\n`);
