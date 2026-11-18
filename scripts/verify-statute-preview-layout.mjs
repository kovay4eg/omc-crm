import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const playwrightModule = process.env.PLAYWRIGHT_MODULE
    ?? path.join(process.env.USERPROFILE, '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright', 'index.mjs');
const { chromium } = await import(pathToFileURL(playwrightModule).href);

const chromePath = process.env.CHROME_PATH
    ?? path.join(process.env.LOCALAPPDATA, 'Google', 'Chrome', 'Application', 'chrome.exe');
const baseUrl = (process.env.BASE_URL ?? 'http://127.0.0.1:8124').replace(/\/$/, '');
const previewPath = '/documents/statute-701f31e4c1f3143a-a4-v1.pdf';
const previewVersion = 'DA56599E122D8070';
const previewSha256 = 'DA56599E122D807063A842E599FD45CF60A9326227326BD61313951A65D8D1CF';
const previewUrl = `${baseUrl}${previewPath}?v=${previewVersion}`;
const outputDirectory = path.resolve('storage/app/statute-preview-layout');

await fs.mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({ executablePath: chromePath, headless: true });
const results = [];

async function preparePage(context) {
    const page = await context.newPage();
    await page.goto(`${baseUrl}/#statut-section`, { waitUntil: 'networkidle' });
    await page.locator('#sitePreloader').waitFor({ state: 'detached', timeout: 5000 }).catch(async () => {
        await page.locator('#sitePreloader').evaluate(element => element.remove()).catch(() => {});
    });
    await page.locator('#statut-section').scrollIntoViewIfNeeded();
    await page.waitForTimeout(250);

    return page;
}

try {
    const response = await fetch(previewUrl);
    assert.equal(response.status, 200, 'preview PDF must return HTTP 200');
    assert.match(response.headers.get('content-type') ?? '', /application\/pdf/i);
    const previewBytes = Buffer.from(await response.arrayBuffer());
    assert.equal(previewBytes.subarray(0, 5).toString(), '%PDF-');
    assert.equal(
        crypto.createHash('sha256').update(previewBytes).digest('hex').toUpperCase(),
        previewSha256,
    );

    const mobileContext = await browser.newContext({
        viewport: { width: 390, height: 844 },
        isMobile: true,
        hasTouch: true,
    });
    const mobilePage = await preparePage(mobileContext);
    const mobileState = await mobilePage.evaluate(expectedPreviewUrl => {
        const desktop = document.querySelector('.statut-desktop-preview');
        const mobile = document.querySelector('.statut-mobile-preview');
        const link = document.querySelector('.statut-mobile-btn');
        const bounds = link.getBoundingClientRect();

        return {
            desktopDisplay: getComputedStyle(desktop).display,
            mobileDisplay: getComputedStyle(mobile).display,
            href: link.href,
            expectedPreviewUrl,
            opacity: Number.parseFloat(getComputedStyle(link).opacity),
            width: bounds.width,
            height: bounds.height,
        };
    }, previewUrl);
    assert.equal(mobileState.desktopDisplay, 'none');
    assert.equal(mobileState.mobileDisplay, 'block');
    assert.equal(mobileState.href, previewUrl);
    assert.ok(mobileState.opacity >= 0.99);
    assert.ok(mobileState.width >= 44 && mobileState.height >= 44);
    await mobilePage.locator('#statut-section').screenshot({
        path: path.join(outputDirectory, 'mobile-390.png'),
    });
    results.push({ viewport: 'mobile-390', ...mobileState });
    await mobileContext.close();

    const tabletContext = await browser.newContext({
        viewport: { width: 834, height: 1194 },
        isMobile: true,
        hasTouch: true,
        userAgent: 'Mozilla/5.0 (iPad; CPU OS 17_7 like Mac OS X) AppleWebKit/605.1.15 Version/17.7 Mobile/15E148 Safari/604.1',
    });
    const tabletPage = await preparePage(tabletContext);
    const tabletState = await tabletPage.evaluate(expectedPreviewUrl => {
        const desktop = document.querySelector('.statut-desktop-preview');
        const mobile = document.querySelector('.statut-mobile-preview');
        const button = document.querySelector('#openStatuteBtn');
        const frame = document.querySelector('#statutFrame');
        const style = getComputedStyle(button);
        const matrix = new DOMMatrixReadOnly(style.transform === 'none' ? undefined : style.transform);

        return {
            desktopDisplay: getComputedStyle(desktop).display,
            mobileDisplay: getComputedStyle(mobile).display,
            frameSrc: frame.src,
            expectedFrameSrc: `${expectedPreviewUrl}#view=FitH&toolbar=1&navpanes=0`,
            buttonOpacity: Number.parseFloat(style.opacity),
            buttonTranslateY: matrix.m42,
        };
    }, previewUrl);
    assert.equal(tabletState.desktopDisplay, 'block');
    assert.equal(tabletState.mobileDisplay, 'none');
    assert.equal(tabletState.frameSrc, tabletState.expectedFrameSrc);
    assert.ok(tabletState.buttonOpacity >= 0.99);
    assert.ok(Math.abs(tabletState.buttonTranslateY) < 0.1);

    await tabletPage.locator('#openStatuteBtn').click();
    await tabletPage.waitForTimeout(400);
    const activatedState = await tabletPage.evaluate(() => ({
        overlayActive: document.querySelector('#statutOverlay').classList.contains('active'),
        overlayPointerEvents: getComputedStyle(document.querySelector('#statutOverlay')).pointerEvents,
        frameBlurred: document.querySelector('#statutFrame').classList.contains('blurred'),
        frameFilter: getComputedStyle(document.querySelector('#statutFrame')).filter,
    }));
    assert.equal(activatedState.overlayActive, true);
    assert.equal(activatedState.overlayPointerEvents, 'none');
    assert.equal(activatedState.frameBlurred, false);
    assert.equal(activatedState.frameFilter, 'none');
    await tabletPage.locator('#statut-section').screenshot({
        path: path.join(outputDirectory, 'ipad-834.png'),
    });
    results.push({ viewport: 'ipad-834', ...tabletState, ...activatedState });
    await tabletContext.close();
} finally {
    await browser.close();
}

await fs.writeFile(
    path.join(outputDirectory, 'verification.json'),
    `${JSON.stringify(results, null, 2)}\n`,
    'utf8',
);

process.stdout.write(`${JSON.stringify(results, null, 2)}\n`);
