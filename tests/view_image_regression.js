const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');

// Set PLAYWRIGHT_MODULE and BROWSER_BIN when using an externally installed browser.
const source = process.env.VIEW_IMAGE_SCRIPT || path.join(__dirname, '../assets/vendor/view-image.min.js');

async function main() {
    const browser = await chromium.launch({
        headless: true,
        ...(process.env.BROWSER_BIN ? { executablePath: process.env.BROWSER_BIN } : {})
    });

    try {
        const page = await browser.newPage();
        await page.setContent('<div class="prose" view-image></div>');
        const sources = await page.evaluate(() => {
            const svg = 'data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%222%22%20height=%222%22%3E%3C/svg%3E';
            // These quotes are image data; HTML parsing would turn them into an attribute.
            const urls = [svg + '%3C!--" data-regression-injected="true" --%3E', svg + '#second', svg + '#third'];
            return urls.map((url) => {
                const image = document.createElement('img');
                image.src = url;
                document.querySelector('.prose').appendChild(image);
                return image.src;
            });
        });
        await page.addScriptTag({ content: fs.readFileSync(source, 'utf8') });
        await page.evaluate(() => window.ViewImage.init('.prose img'));

        async function expectImage(index) {
            await page.waitForFunction(() => {
                const image = document.querySelector('.view-image-lead__in img');
                return Boolean(image);
            });
            const actual = await page.evaluate(() => {
                const image = document.querySelector('.view-image-lead img');
                return {
                    attributes: image.getAttributeNames().sort(),
                    count: document.querySelectorAll('.view-image-lead img').length,
                    index: document.querySelector('.view-image-index').textContent,
                    alt: image.alt,
                    src: image.src
                };
            });
            assert.deepEqual(actual.attributes, ['alt', 'no-view', 'src']);
            assert.equal(actual.count, 1);
            assert.equal(actual.index, String(index + 1));
            assert.equal(actual.alt, 'ViewImage');
            assert.equal(actual.src, sources[index]);
            await page.waitForFunction(() => document.querySelector('.view-image-lead img').naturalWidth > 0);
        }

        await page.locator('.prose img').first().click();
        await expectImage(0);
        await page.locator('.view-image-tools__flip-next').click();
        await expectImage(1);
        await page.keyboard.press('ArrowRight');
        await expectImage(2);
        await page.keyboard.press('ArrowLeft');
        await expectImage(1);
        await page.locator('.view-image-tools__flip-prev').click();
        await expectImage(0);
        await page.locator('.view-image-tools__flip-prev').click();
        await expectImage(2);
        await page.locator('.view-image-tools__flip-next').click();
        await expectImage(0);
        await page.keyboard.press('Escape');
        await page.waitForSelector('.view-image', { state: 'detached' });

        await page.locator('.prose img').nth(1).click();
        await expectImage(1);
        await page.locator('.view-image-tools .view-image-close').click();
        await page.waitForSelector('.view-image', { state: 'detached' });
        console.log('ViewImage regression tests passed (quoted image data, navigation, close and reopen).');
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
