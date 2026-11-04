import fs from 'node:fs/promises';
import path from 'node:path';
import { createRequire } from 'node:module';
import { pathToFileURL } from 'node:url';

const projectRoot = process.cwd();
const modulesRoot = process.env.CODEX_NODE_MODULES
    ?? path.join(process.env.USERPROFILE, '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules');
const require = createRequire(import.meta.url);
const sharp = require(path.join(modulesRoot, 'sharp'));
const { default: pixelmatch } = await import(pathToFileURL(path.join(modulesRoot, 'pixelmatch', 'index.js')).href);

const themes = ['new-year', 'christmas', 'easter', 'vyshyvanka', 'independence', 'youth'];
const cropDirectory = path.resolve(projectRoot, 'storage/app/holiday-reference-crops');
const outputDirectory = path.resolve(projectRoot, 'storage/app/holiday-logo-fidelity');
const svgDirectory = path.resolve(projectRoot, 'public/images/holiday/logos');
await fs.mkdir(outputDirectory, { recursive: true });

const results = {};

for (const theme of themes) {
    const referencePath = path.join(cropDirectory, `${theme}-transparent.png`);
    const svgPath = path.join(svgDirectory, `${theme}.svg`);
    const svgMarkup = await fs.readFile(svgPath, 'utf8');
    const forbidden = svgMarkup.match(/<image\b|data:image|base64|<text\b|<foreignObject\b/iu);
    if (forbidden) {
        throw new Error(`${theme}: non-vector SVG content: ${forbidden[0]}`);
    }

    const metadata = await sharp(referencePath).metadata();
    const width = metadata.width;
    const height = metadata.height;
    if (!width || !height) {
        throw new Error(`${theme}: cannot read reference dimensions`);
    }

    const reference = await sharp(referencePath)
        .flatten({ background: '#ffffff' })
        .ensureAlpha()
        .raw()
        .toBuffer();
    const renderedPng = await sharp(Buffer.from(svgMarkup))
        .resize(width, height, { fit: 'fill' })
        .flatten({ background: '#ffffff' })
        .png()
        .toBuffer();
    const rendered = await sharp(renderedPng)
        .ensureAlpha()
        .raw()
        .toBuffer();
    const diff = Buffer.alloc(width * height * 4);
    const mismatchedPixels = pixelmatch(reference, rendered, diff, width, height, {
        threshold: 0.1,
        includeAA: true,
        alpha: 0.55,
    });

    let absoluteError = 0;
    for (let index = 0; index < reference.length; index += 4) {
        absoluteError += Math.abs(reference[index] - rendered[index]);
        absoluteError += Math.abs(reference[index + 1] - rendered[index + 1]);
        absoluteError += Math.abs(reference[index + 2] - rendered[index + 2]);
    }

    const pixels = width * height;
    const mismatchRatio = mismatchedPixels / pixels;
    const normalizedMae = absoluteError / (pixels * 3 * 255);

    await sharp(referencePath).png().toFile(path.join(outputDirectory, `${theme}-reference.png`));
    await fs.writeFile(path.join(outputDirectory, `${theme}-rendered.png`), renderedPng);
    await sharp(diff, { raw: { width, height, channels: 4 } })
        .png()
        .toFile(path.join(outputDirectory, `${theme}-diff.png`));

    results[theme] = {
        width,
        height,
        svgBytes: Buffer.byteLength(svgMarkup),
        mismatchedPixels,
        mismatchRatio: Number(mismatchRatio.toFixed(6)),
        normalizedMae: Number(normalizedMae.toFixed(6)),
        trueVector: true,
    };
}

await fs.writeFile(
    path.join(outputDirectory, 'metrics.json'),
    `${JSON.stringify(results, null, 2)}\n`,
    'utf8',
);

console.log(JSON.stringify(results, null, 2));
