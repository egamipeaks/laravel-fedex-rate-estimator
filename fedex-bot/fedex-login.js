const path = require('path');
const fsPromises = require('fs').promises;
const puppeteer = require('puppeteer-extra');
const {exec} = require('child_process');
const util = require('util');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const {createLogger, transports, format} = require('winston');

const execPromise = util.promisify(exec);

const USERNAME = process.env.FEDEX_USERNAME;
const PASSWORD = process.env.FEDEX_PASSWORD;
const DOWNLOAD_PATH = process.env.DOWNLOAD_PATH;
const LOG_PATH = process.env.LOG_PATH;

const CONFIG = {
	downloadPath: DOWNLOAD_PATH,
	logPath: LOG_PATH,
	loginUrl: 'https://www.fedex.com/secure-login/en-us/#/credentials',
	trackingUrl: 'https://www.fedex.com/wtrk/fedextracking/',
	timeouts: {
		navigation: 30000,
		download: 30000,
		wait: 1000,
		exec: 60000,
	},
};

const logger = createLogger({
	level: 'info',
	format: format.combine(format.timestamp(), format.json()),
	transports: [
		new transports.File({filename: path.join(CONFIG.logPath, 'puppeteer.log')}),
		new transports.Console(),
	],
});

puppeteer.use(StealthPlugin());

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function retry(fn, retries = 3, delay = 1000) {
	for (let i = 0; i < retries; i++) {
		try {
			return await fn();
		} catch (error) {
			if (i === retries - 1) throw error;
			logger.warn(`Retry ${i + 1}/${retries} failed: ${error.message}`);
			await wait(delay * Math.pow(2, i));
		}
	}
}

async function validateEnv() {
	const required = ['FEDEX_USERNAME', 'FEDEX_PASSWORD'];
	const missing = required.filter((key) => !process.env[key]);
	if (missing.length > 0) {
		throw new Error(`Missing environment variables: ${missing.join(', ')}`);
	}
}

// Move existing CSV files to the archive folder if present
async function archiveExistingCSV(downloadPath) {
	try {
		const files = await fsPromises.readdir(downloadPath);
		const csvFiles = files.filter((file) => file.endsWith('.csv'));
		if (csvFiles.length === 0) {
			logger.info('No CSV files to archive.');
			return;
		}

		const archivePath = path.join(downloadPath, 'archive');
		await fsPromises.mkdir(archivePath, {recursive: true});

		for (const file of csvFiles) {
			const timestamp = new Date().toISOString().replace(/:/g, '-');
			const oldPath = path.join(downloadPath, file);
			const newPath = path.join(archivePath, `${timestamp}_${file}`);
			await fsPromises.rename(oldPath, newPath);
			logger.info(`Archived ${file} to ${newPath}`);
		}
	} catch (error) {
		throw new Error(`Failed to archive CSV files: ${error.message}`);
	}
}

async function waitForDownload(downloadPath, timeout) {
	const start = Date.now();
	while (Date.now() - start < timeout) {
		try {
			const files = await fsPromises.readdir(downloadPath);
			const csvFiles = files.filter((f) => f.endsWith('.csv'));
			if (csvFiles.length > 0) return csvFiles[0];
		} catch (error) {
			throw new Error(`Failed to read download directory: ${error.message}`);
		}
		await wait(500);
	}
	throw new Error('CSV download timeout');
}


(async () => {
	try {
		await validateEnv();

		await fsPromises.mkdir(CONFIG.downloadPath, {recursive: true});
		await fsPromises.mkdir(CONFIG.logPath, {recursive: true});

		await archiveExistingCSV(CONFIG.downloadPath);

		const browser = await puppeteer.launch({
			headless: true,
			defaultViewport: {width: 1280, height: 720},
			args: [
				'--no-sandbox',
				'--disable-setuid-sandbox',
				'--disable-dev-shm-usage',
				'--disable-accelerated-2d-canvas',
				'--disable-gpu',
				'--window-size=1280,720',
			],
		});

		try {
			const page = await browser.newPage();

			await page.setUserAgent(
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
			);

			// Enable downloads
			const client = await page.target().createCDPSession();
			await client.send('Page.setDownloadBehavior', {
				behavior: 'allow',
				downloadPath: CONFIG.downloadPath,
			});

			logger.info('Logging in to FedEx...');

			// Login
			await retry(() =>
				page.goto(CONFIG.loginUrl, {waitUntil: 'domcontentloaded', timeout: CONFIG.timeouts.navigation})
			);

			await page.waitForSelector('#username', {timeout: CONFIG.timeouts.navigation});
			await page.waitForSelector('#password', {timeout: CONFIG.timeouts.navigation});

			logger.info('Entering username...');

			await page.evaluate((username) => {
				const input = document.querySelector('#username');
				input.focus();
				input.value = username;
				input.dispatchEvent(new Event('input', {bubbles: true}));
				input.dispatchEvent(new Event('change', {bubbles: true}));
			}, USERNAME);

			await wait(1000);

			logger.info("Entering password...");

			await page.evaluate((password) => {
				const input = document.querySelector('#password');
				input.focus();
				input.value = password;
				input.dispatchEvent(new Event('input', {bubbles: true}));
				input.dispatchEvent(new Event('change', {bubbles: true}));
			}, PASSWORD);

			await wait(1000);

			logger.info("Clicking login button...");

			const loginButton = await page.waitForSelector('#login_button', {visible: true});

			await Promise.all([
				loginButton.click(),
				page.waitForNavigation({waitUntil: 'networkidle0', timeout: 60000}),
			]);

			logger.info('🔐 Logged in! Navigating to tracking page...');
			await wait(10000); // You may refine this with more specific wait methods later

			await retry(() =>
				page.goto(CONFIG.trackingUrl, {waitUntil: 'domcontentloaded', timeout: CONFIG.timeouts.navigation})
			);

			logger.info("Navigating to the export page...");

			await page.waitForSelector('#exportTab', {visible: true, timeout: 30000});

			const exportTab = await page.$('#exportTab');

			if (!exportTab) {
				throw new Error('Export tab not found');
			}

			await exportTab.click();

			await page.waitForSelector('#dialogExport', {visible: true});

			// Select export options
			await page.$$eval('input[name="shipment"]', (inputs) => {
				inputs.forEach((input) => {
					if (input.value === 'allShipments') input.click();
				});
			});

			await page.$$eval('input[name="content"]', (inputs) => {
				inputs.forEach((input) => {
					if (input.value === 'allAvailableColumns') input.click();
				});
			});

			await page.$$eval('input[name="fileFormat"]', (inputs) => {
				inputs.forEach((input) => {
					if (input.value === 'CSV') input.click();
				});
			});

			await wait(1000);

			logger.info('🚀 Starting export...');
			await page.click('#removeView');

			logger.info(`⏳ Waiting for CSV download to appear in: ${CONFIG.downloadPath}`);

			await waitForDownload(CONFIG.downloadPath, CONFIG.timeouts.download);

			logger.info('✅ Download complete. Triggering Laravel pipeline...');

			await browser.close();

			logger.info('File created successfully');

		} catch (error) {
			logger.error(`Operation failed: ${error.message}`);
			await browser.close();
			process.exit(1);
		}
	} catch (error) {
		logger.error(`Fatal error: ${error.message}`);
		process.exit(1);
	}
})();
