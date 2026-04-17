#!/usr/bin/env node
/**
 * Scripted dashboard walk-through for the README demo GIF.
 *
 * Drives Chromium through the Periscope pages with Playwright's video
 * recorder, then converts the resulting .webm into an optimized GIF.
 *
 * Setup (once):
 *   cd packages/periscope/scripts
 *   npm install playwright
 *   npx playwright install chromium
 *
 * Run (from the package root):
 *   PERISCOPE_URL=https://queue-monitor.test/periscope ./scripts/capture-demo.mjs
 *
 * Output: docs/screenshots/demo.gif
 */

import { chromium } from 'playwright';
import { spawnSync } from 'node:child_process';
import { mkdtempSync, mkdirSync, readdirSync, renameSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const BASE = (process.env.PERISCOPE_URL || 'http://localhost:8000/periscope').replace(/\/$/, '');
const OUTPUT = resolve(ROOT, process.env.PERISCOPE_GIF_OUT || 'docs/screenshots/demo.gif');
const WIDTH = Number(process.env.PERISCOPE_GIF_WIDTH || 1280);
const HEIGHT = Number(process.env.PERISCOPE_GIF_HEIGHT || 720);
const FPS = Number(process.env.PERISCOPE_GIF_FPS || 20);

// Pages to visit — each tuple is [path, dwellMs, optional interaction function].
const steps = [
    ['/', 5000],
    ['/queues', 3500],
    ['/jobs', 4000],
    ['/exceptions', 4000, async (page) => {
        const row = page.locator('table a.text-rose-300').first();
        if (await row.count()) await row.click();
    }],
    ['/failed', 4000],
    ['/batches', 4000],
    ['/performance', 3500],
    ['/workers', 3000],
];

const run = (cmd, args, opts = {}) => {
    const result = spawnSync(cmd, args, { stdio: 'inherit', ...opts });
    if (result.status !== 0) {
        throw new Error(`${cmd} exited with ${result.status}`);
    }
};

const which = (bin) => spawnSync('which', [bin]).status === 0;

(async () => {
    for (const bin of ['ffmpeg', 'gifski']) {
        if (!which(bin)) {
            console.error(`${bin} not found — brew install ${bin}`);
            process.exit(1);
        }
    }

    const workDir = mkdtempSync(join(tmpdir(), 'periscope-demo-'));
    console.log(`→ Recording to ${workDir}`);

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: WIDTH, height: HEIGHT },
        recordVideo: { dir: workDir, size: { width: WIDTH, height: HEIGHT } },
    });
    const page = await context.newPage();

    try {
        for (const [path, dwell, interaction] of steps) {
            console.log(`  ${path}`);
            await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle', timeout: 15_000 });
            if (interaction) {
                try {
                    await interaction(page);
                    await page.waitForTimeout(1200);
                } catch {}
            }
            await page.waitForTimeout(dwell);
        }
    } finally {
        await context.close();
        await browser.close();
    }

    const webm = readdirSync(workDir).find((f) => f.endsWith('.webm'));
    if (!webm) throw new Error('No video produced');

    const sourcePath = join(workDir, webm);
    const framesDir = join(workDir, 'frames');
    mkdirSync(framesDir);

    console.log(`→ Extracting frames @ ${FPS}fps`);
    run('ffmpeg', [
        '-y', '-i', sourcePath,
        '-vf', `fps=${FPS},scale=${WIDTH}:-2:flags=lanczos`,
        '-loglevel', 'error',
        join(framesDir, 'frame-%04d.png'),
    ]);

    mkdirSync(dirname(OUTPUT), { recursive: true });
    console.log(`→ Encoding GIF`);
    const frames = readdirSync(framesDir).map((f) => join(framesDir, f)).sort();
    run('gifski', [
        '--fps', String(FPS),
        '--width', String(WIDTH),
        '--quality', '85',
        '-o', OUTPUT,
        ...frames,
    ]);

    rmSync(workDir, { recursive: true, force: true });

    const size = spawnSync('du', ['-h', OUTPUT]).stdout.toString().trim().split('\t')[0];
    console.log(`✓ ${OUTPUT} (${size})`);
})().catch((err) => {
    console.error(err);
    process.exit(1);
});
