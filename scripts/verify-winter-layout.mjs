import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const playwrightModule = process.env.PLAYWRIGHT_MODULE
    ?? path.join(process.env.USERPROFILE, '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright', 'index.mjs');
const { chromium } = await import(pathToFileURL(playwrightModule).href);

const outputDirectory = path.resolve('storage/app/winter-layout');
const chromePath = process.env.CHROME_PATH
    ?? path.join(process.env.LOCALAPPDATA, 'Google', 'Chrome', 'Application', 'chrome.exe');
const viewports = [
    { name: 'desktop-wide', width: 1920, height: 1080 },
    { name: 'desktop', width: 1280, height: 800 },
    { name: 'mobile', width: 390, height: 844 },
];

await fs.mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({ executablePath: chromePath, headless: true });
const results = [];

try {
    for (const viewport of viewports) {
        const page = await browser.newPage({ viewport });
        await page.goto(
            `http://127.0.0.1:8000/?holiday-preview=new-year&winter-layout-test=${viewport.width}`,
            { waitUntil: 'networkidle' },
        );
        await page.locator('#sitePreloader').waitFor({ state: 'detached', timeout: 5000 }).catch(async () => {
            await page.locator('#sitePreloader').evaluate(element => element.remove()).catch(() => {});
        });
        await page.waitForTimeout(250);

        const initial = await page.evaluate(() => {
            const rect = selector => {
                const element = document.querySelector(selector);
                if (!element) return null;
                const bounds = element.getBoundingClientRect();
                return {
                    top: bounds.top,
                    bottom: bounds.bottom,
                    height: bounds.height,
                };
            };

            const candidates = ['.banner-section', '.content-section']
                .map(selector => ({ selector, bounds: rect(selector) }))
                .filter(candidate => candidate.bounds && candidate.bounds.height > 1);

            return {
                header: rect('#header'),
                garland: rect('.omc-winter-garland'),
                clearance: rect('#omcHeaderClearance'),
                firstContent: candidates[0] ?? null,
                garlandPosition: getComputedStyle(document.querySelector('.omc-winter-garland')).position,
            };
        });

        assert.equal(initial.garlandPosition, 'absolute', `${viewport.name}: garland must scroll with the page`);
        assert.ok(initial.firstContent, `${viewport.name}: no visible content root found`);
        assert.ok(
            initial.firstContent.bounds.top >= initial.garland.bottom + 12,
            `${viewport.name}: content overlaps garland (${initial.firstContent.bounds.top} < ${initial.garland.bottom + 12})`,
        );

        await page.screenshot({
            path: path.join(outputDirectory, `${viewport.name}.png`),
            fullPage: false,
        });

        await page.evaluate(() => window.scrollTo(0, 400));
        await page.waitForTimeout(100);

        const scrolled = await page.evaluate(() => {
            const header = document.querySelector('#header').getBoundingClientRect();
            const garland = document.querySelector('.omc-winter-garland').getBoundingClientRect();

            return {
                headerBottom: header.bottom,
                garlandBottom: garland.bottom,
                scrollY: window.scrollY,
            };
        });

        assert.ok(scrolled.scrollY > 0, `${viewport.name}: page did not scroll`);
        assert.ok(
            scrolled.garlandBottom <= scrolled.headerBottom,
            `${viewport.name}: garland still covers scrolling content`,
        );

        results.push({ viewport, initial, scrolled });
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
