import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const playwrightModule = process.env.PLAYWRIGHT_MODULE
    ?? path.join(process.env.USERPROFILE, '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright', 'index.mjs');
const { chromium } = await import(pathToFileURL(playwrightModule).href);

const outputDirectory = path.resolve('storage/app/holiday-logo-previews');
const chromePath = process.env.CHROME_PATH
    ?? path.join(process.env.LOCALAPPDATA, 'Google', 'Chrome', 'Application', 'chrome.exe');
const themes = ['new-year', 'christmas', 'easter', 'vyshyvanka', 'independence', 'youth'];

await fs.mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({
    executablePath: chromePath,
    headless: true,
});

try {
    const gallery = await browser.newPage({ viewport: { width: 1440, height: 1100 } });
    await gallery.goto('http://127.0.0.1:8000/holiday-logo-preview', { waitUntil: 'networkidle' });
    await gallery.screenshot({
        path: path.join(outputDirectory, 'all-holiday-logos.png'),
        fullPage: true,
    });

    const results = [];

    for (const theme of themes) {
        for (const viewport of [
            { name: 'desktop', width: 1440, height: 280, captureHeight: 230 },
            { name: 'mobile', width: 390, height: 300, captureHeight: 190 },
        ]) {
            const page = await browser.newPage({ viewport: { width: viewport.width, height: viewport.height } });
            await page.goto(`http://127.0.0.1:8000/?holiday-preview=${theme}`, { waitUntil: 'networkidle' });
            await page.locator('#siteLogo').waitFor({ state: 'visible' });
            await page.locator('#sitePreloader').waitFor({ state: 'detached', timeout: 5000 }).catch(async () => {
                await page.locator('#sitePreloader').evaluate(element => element.remove()).catch(() => {});
            });
            await page.screenshot({
                path: path.join(outputDirectory, `${theme}-${viewport.name}-header.png`),
                clip: { x: 0, y: 0, width: viewport.width, height: viewport.captureHeight },
            });

            const result = await page.evaluate(({ viewportName, viewportWidth }) => {
                const logo = document.querySelector('#siteLogo');
                const wrapper = document.querySelector('[data-holiday-theme]');
                const bounds = logo?.getBoundingClientRect();

                return {
                    viewport: viewportName,
                    theme: wrapper?.getAttribute('data-holiday-theme'),
                    src: logo?.getAttribute('src'),
                    displayedWidth: bounds?.width,
                    displayedHeight: bounds?.height,
                    fullyInsideViewport: Boolean(bounds && bounds.left >= 0 && bounds.right <= viewportWidth && bounds.top >= 0),
                };
            }, { viewportName: viewport.name, viewportWidth: viewport.width });

            if (result.theme !== theme || !result.fullyInsideViewport) {
                throw new Error(`${theme}/${viewport.name}: invalid header logo geometry`);
            }

            results.push(result);
            await page.close();
        }
    }

    await fs.writeFile(
        path.join(outputDirectory, 'verification.json'),
        `${JSON.stringify(results, null, 2)}\n`,
        'utf8',
    );

    process.stdout.write(`${JSON.stringify(results, null, 2)}\n`);
} finally {
    await browser.close();
}
