import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const playwrightModule = process.env.PLAYWRIGHT_MODULE
    ?? path.join(process.env.USERPROFILE, '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright', 'index.mjs');
const { chromium } = await import(pathToFileURL(playwrightModule).href);

const outputDirectory = path.resolve('storage/app/holiday-logo-previews');
const baseUrl = process.env.HOLIDAY_PREVIEW_BASE_URL ?? 'http://127.0.0.1:8000';
const chromePath = process.env.CHROME_PATH
    ?? path.join(process.env.LOCALAPPDATA, 'Google', 'Chrome', 'Application', 'chrome.exe');
const defaultThemes = [
    'new-year',
    'christmas',
    'easter',
    'vyshyvanka',
    'independence',
    'youth',
    'st-nicholas',
    'valentines',
    'first-day-spring',
    'first-day-summer',
    'first-day-autumn',
    'first-day-winter',
];
const themes = process.env.HOLIDAY_PREVIEW_THEMES
    ? process.env.HOLIDAY_PREVIEW_THEMES.split(',').map(theme => theme.trim()).filter(Boolean)
    : defaultThemes;

await fs.mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({
    executablePath: chromePath,
    headless: true,
});

try {
    const gallery = await browser.newPage({ viewport: { width: 1440, height: 1100 } });
    await gallery.goto(`${baseUrl}/holiday-logo-preview`, { waitUntil: 'domcontentloaded' });
    await gallery.locator('.preview img').first().waitFor({ state: 'visible' });
    await gallery.waitForTimeout(700);
    await gallery.screenshot({
        path: path.join(outputDirectory, 'all-holiday-logos.png'),
        fullPage: true,
    });

    const results = [];

    for (const theme of themes) {
        for (const viewport of [
            { name: 'desktop', width: 1440, height: 280, captureHeight: 230 },
            { name: 'mobile', width: 390, height: 300, captureHeight: 190 },
            { name: 'mobile-dark', width: 390, height: 300, captureHeight: 190, a11yTheme: 'dark' },
        ]) {
            const page = await browser.newPage({ viewport: { width: viewport.width, height: viewport.height } });
            if (viewport.a11yTheme) {
                await page.addInitScript(theme => {
                    window.localStorage.setItem('omc-accessibility-settings', JSON.stringify({ font: 'normal', theme }));
                }, viewport.a11yTheme);
            }
            await page.goto(`${baseUrl}/?holiday-preview=${theme}`, { waitUntil: 'domcontentloaded' });
            await page.locator('#siteLogo').waitFor({ state: 'visible' });
            await page.waitForTimeout(600);
            await page.locator('#sitePreloader').evaluate(element => element.remove()).catch(() => {});
            await page.screenshot({
                path: path.join(outputDirectory, `${theme}-${viewport.name}-header.png`),
                clip: { x: 0, y: 0, width: viewport.width, height: viewport.captureHeight },
            });

            const result = await page.evaluate(({ viewportName, viewportWidth, a11yTheme }) => {
                const logo = document.querySelector('#siteLogo');
                const wrapper = document.querySelector('[data-holiday-theme]');
                const header = document.querySelector('#header');
                const bounds = logo?.getBoundingClientRect();

                return {
                    viewport: viewportName,
                    a11yTheme: a11yTheme ?? 'normal',
                    theme: wrapper?.getAttribute('data-holiday-theme'),
                    src: logo?.getAttribute('src'),
                    displayedWidth: bounds?.width,
                    displayedHeight: bounds?.height,
                    fullyInsideViewport: Boolean(bounds && bounds.left >= 0 && bounds.right <= viewportWidth && bounds.top >= 0),
                    logoBackground: logo ? getComputedStyle(logo).backgroundColor : null,
                    headerBackground: header ? getComputedStyle(header).backgroundColor : null,
                };
            }, { viewportName: viewport.name, viewportWidth: viewport.width, a11yTheme: viewport.a11yTheme });

            if (result.theme !== theme || !result.fullyInsideViewport) {
                throw new Error(`${theme}/${viewport.name}: invalid header logo geometry`);
            }

            if (viewport.a11yTheme === 'dark'
                && (result.logoBackground !== 'rgb(255, 255, 255)' || result.headerBackground !== 'rgb(0, 0, 0)')) {
                throw new Error(`${theme}/${viewport.name}: dark-mode contrast backing is missing`);
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
