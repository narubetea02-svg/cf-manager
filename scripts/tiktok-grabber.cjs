const { chromium } = require('playwright');
const http = require('http');

const API_BASE = process.env.API_URL || 'http://127.0.0.1:8080';
const API_KEY = process.env.API_KEY || 'cfshops-grabber-key-2026';

const CODE_PATTERN = /^[a-zA-Z]{1,2}\d{2,4}$/;
const POLL_INTERVAL = 3000;
const BROWSER_CLOSE_IDLE = 5 * 60 * 1000;

async function getStreams() {
  return new Promise((resolve, reject) => {
    http.get(`${API_BASE}/api/grabber/streams`, { headers: { 'X-API-Key': API_KEY } }, res => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => { try { resolve(JSON.parse(data).streams || []); } catch(e) { resolve([]); } });
    }).on('error', reject);
  });
}

async function submitOrder(stream, code, rawComment, username) {
  return new Promise((resolve, reject) => {
    const body = JSON.stringify({
      shop_id: stream.shop_id,
      stream_id: stream.id,
      code,
      raw_comment: rawComment,
      username,
      platform: 'tiktok',
    });
    const req = http.request(`${API_BASE}/api/grabber/orders/tiktok-code`, {
      method: 'POST',
      headers: { 'X-API-Key': API_KEY, 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(body) },
    }, res => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => resolve(JSON.parse(data)));
    });
    req.on('error', reject);
    req.write(body);
    req.end();
  });
}

async function monitorStream(page, stream) {
  const url = stream.live_url;
  console.log(`[${stream.shop_name}] Monitoring: ${url}`);
  
  try {
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
    
    let seenCodes = new Set();
    let idleSince = Date.now();

    while (true) {
      try {
        const comments = await page.evaluate(() => {
          const items = document.querySelectorAll('[data-e2e="comment-item"]');
          return Array.from(items).map(el => {
            const userEl = el.querySelector('[data-e2e="comment-username"]');
            const textEl = el.querySelector('[data-e2e="comment-text"]');
            return {
              username: userEl?.textContent?.trim() || '',
              text: textEl?.textContent?.trim() || el.textContent?.trim() || '',
            };
          }).reverse();
        });

        for (const c of comments) {
          const match = c.text.match(CODE_PATTERN);
          if (match && !seenCodes.has(c.text)) {
            seenCodes.add(c.text);
            console.log(`🎯 [${stream.shop_name}] Found code: ${c.text} from @${c.username}`);
            
            try {
              const result = await submitOrder(stream, c.text, c.text, c.username);
              console.log(`   ✅ Order created: ${result.order?.id || 'ok'}`);
            } catch(e) {
              console.error(`   ❌ Submit failed: ${e.message}`);
            }
          }
        }

        if (comments.length > 0) {
          idleSince = Date.now();
        } else if (Date.now() - idleSince > BROWSER_CLOSE_IDLE) {
          console.log(`[${stream.shop_name}] Idle timeout — closing`);
          break;
        }

        await new Promise(r => setTimeout(r, POLL_INTERVAL));
      } catch (e) {
        console.error(`[${stream.shop_name}] Error: ${e.message}`);
        await new Promise(r => setTimeout(r, 5000));
      }
    }
  } catch(e) {
    console.error(`[${stream.shop_name}] Failed to load: ${e.message}`);
  }
}

async function main() {
  console.log('🚀 TikTok Grabber Service starting...');
  console.log(`   API: ${API_BASE}`);
  
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
  });

  process.on('SIGINT', async () => { console.log('\nShutting down...'); await browser.close(); process.exit(0); });
  process.on('SIGTERM', async () => { console.log('\nShutting down...'); await browser.close(); process.exit(0); });

  while (true) {
    try {
      const streams = await getStreams();
      
      if (streams.length === 0) {
        console.log('⏳ No active streams — waiting...');
        await new Promise(r => setTimeout(r, 15000));
        continue;
      }

      console.log(`📡 Active streams: ${streams.length}`);
      
      for (const stream of streams) {
        const page = await browser.newPage();
        monitorStream(page, stream).catch(e => console.error(`Monitor error: ${e.message}`));
      }

      await new Promise(r => setTimeout(r, 30000));
    } catch(e) {
      console.error(`❌ Loop error: ${e.message}`);
      await new Promise(r => setTimeout(r, 10000));
    }
  }
}

main().catch(console.error);
