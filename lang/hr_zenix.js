import pkg from 'whatsapp-web.js';
import qrcode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { Sequelize, DataTypes, Op } from 'sequelize';
import express from 'express';
import ClientSFTP from 'ssh2-sftp-client';

const { Client, MessageMedia, LocalAuth } = pkg;

const app = express();
process.on('uncaughtException', (err) => {
  console.error('🔥 Uncaught Exception:', err && err.stack ? err.stack : err);
});
process.on('unhandledRejection', (reason, p) => {
  console.error('🔥 Unhandled Rejection at:', p, 'reason:', reason && reason.stack ? reason.stack : reason);
});

const PORT = process.env.PORT ? parseInt(process.env.PORT) : 3001;

// ---------- Config (use env vars where possible) ----------
const SFTP_HOST = process.env.SFTP_HOST || 'cl0007-cl0015.wp-cl.clients.n99p-r1.com';
const SFTP_PORT = process.env.SFTP_PORT ? parseInt(process.env.SFTP_PORT) : 13011;
const SFTP_USER = process.env.SFTP_USER || 'hrzenix';
const SFTP_PASSWORD = process.env.SFTP_PASSWORD || 'iq12ypXUpajx';
const REMOTE_QR_DIR = process.env.REMOTE_QR_DIR || '/home/hrzenix/hrzenix.com_public_html/public/admin/whatsapp-qr';

const DB_NAME = process.env.DB_NAME || 'saashrzenix';
const DB_USER = process.env.DB_USER || 'saashrzenix';
const DB_PASS = process.env.DB_PASS || 'gZnk2Zklxf5t';
const DB_HOST = process.env.DB_HOST || 'cl0007-cl0015.wp-cl.clients.n99p-r1.com';
const DB_PORT = process.env.DB_PORT ? parseInt(process.env.DB_PORT) : 3306;

// Puppeteer executable (change if necessary)
const PUPPETEER_EXEC_PATH = process.env.PUPPETEER_EXEC_PATH || '/usr/bin/chromium-browser';

// ---------- Globals ----------
const clients = new Map(); // organization_id => WhatsApp client
let isLoopRunning = false;
let isQrLoopRunning = false;

// Track QR + readiness per organization
// lastQrMap: orgId => { qr: string, ts: number }
const lastQrMap = new Map();
const readyMap = new Map();    // orgId => boolean

// Track init locks and backoff
const initLocks = new Map();   // orgId => boolean
const backoffMap = new Map();  // orgId => ms

// ---------- Database (Sequelize) ----------
const sequelize = new Sequelize(DB_NAME, DB_USER, DB_PASS, {
  host: DB_HOST,
  port: DB_PORT,
  dialect: 'mysql',
  logging: false,
  timezone: '+05:30',
  dialectOptions: { useUTC: false },
});

// Models
const Organization = sequelize.define(
  'Organization',
  {
    id: { type: DataTypes.INTEGER, primaryKey: true },
    name: DataTypes.STRING,
    email: DataTypes.STRING,
  },
  { tableName: 'organizations', timestamps: false }
);

const WhatsappMessage = sequelize.define(
  'WhatsappMessage',
  {
    id: { type: DataTypes.INTEGER, primaryKey: true },
    organization_id: DataTypes.INTEGER,
    number: DataTypes.STRING,
    message: DataTypes.TEXT,
    caption: DataTypes.TEXT,
    image: DataTypes.STRING,
    status: DataTypes.ENUM('draft', 'pending', 'sent', 'failed', 'cancelled'),
    time_gap_from_previous_message: DataTypes.INTEGER,
    created_at: DataTypes.DATE,
    updated_at: DataTypes.DATE,
    start_time: DataTypes.DATE,
    start_pausing_time: DataTypes.TIME,
    end_pausing_time: DataTypes.TIME,  
  },
  { tableName: 'whatsapp_messages', timestamps: false }
);

// ---------- Helpers ----------
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

function normalizeNumber(number) {
  if (!number) return '';
  let num = `${number}`.replace(/\D/g, '');
  if (num.length === 0) return '';
  // if number already begins with country code '91' (India) keep it
  if (!num.startsWith('91')) num = '91' + num;
  return `${num}@c.us`;
}

function ckeditorHtmlToWhatsApp(html) {
  if (!html) return '';
  let text = html;
  text = text.replace(/<(\/?)(strong|b)>/gi, '*');
  text = text.replace(/<(\/?)(em|i)>/gi, '_');
  text = text.replace(/<(\/?)(del|s)>/gi, '~');
  text = text.replace(/<(\/?)code>/gi, '`');
  text = text.replace(/<br\s*\/?>/gi, '\n');
  text = text.replace(/<\/?p>/gi, '\n');
  text = text.replace(/<[^>]+>/g, '');
  text = text
    .replace(/&nbsp;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&lt;/gi, '<')
    .replace(/&gt;/gi, '>')
    .replace(/&quot;/gi, '"')
    .replace(/&#39;/gi, "'");
  text = text.replace(/\n{2,}/g, '\n').trim();
  return text;
}

function startClientHealthCheckLoop() {
  setInterval(async () => {
    try {
      for (const [orgId, client] of clients.entries()) {
        // only check non-ready clients less often — ready ones may still become dead, so check all
        try {
          // lightweight call: getChats is inexpensive; will throw on closed session
          await client.getChats(); 
          // optionally: log debug once in a while
        } catch (err) {
          const msg = String(err && err.message ? err.message : err);
          console.error(`🔍 Health check failed for org ${orgId}: ${msg}`);
          if (/Target closed|Session closed|Page crashed|No such session/i.test(msg)) {
            try { await resetClientForOrg(orgId); } catch (e) { console.error(e); }
          }
        }
      }
    } catch (err) {
      console.error('❌ Error in client health check loop:', err.message || err);
    }
  }, 5 * 60 * 1000); // check every 5 minutes
}

// ---------- SFTP Helpers ----------
async function uploadQrToRemote(organizationId, qrImageBuffer) {
  const remoteFilePath = `${REMOTE_QR_DIR}/qr_org_${organizationId}.png`;
  const sftp = new ClientSFTP();
  try {
    await sftp.connect({
      host: SFTP_HOST,
      port: SFTP_PORT,
      username: SFTP_USER,
      password: SFTP_PASSWORD,
    });

    // Attempt to put file (overwrite)
    await sftp.put(qrImageBuffer, remoteFilePath);
    console.log(`🚀 QR uploaded to remote: ${remoteFilePath}`);
    console.log(`🌐 Public URL: https://hrzenix.com/admin/whatsapp-qr/qr_org_${organizationId}.png`);
    return true;
  } catch (err) {
    console.error(`❌ Failed to upload QR for org ${organizationId}: ${err.message}`);
    return false;
  } finally {
    try { await sftp.end(); } catch (_) {}
  }
}

async function deleteRemoteQr(organizationId) {
  const remoteFilePath = `${REMOTE_QR_DIR}/qr_org_${organizationId}.png`;
  const sftp = new ClientSFTP();
  try {
    await sftp.connect({
      host: SFTP_HOST,
      port: SFTP_PORT,
      username: SFTP_USER,
      password: SFTP_PASSWORD,
    });

    const exists = await sftp.exists(remoteFilePath);
    if (!exists) {
      // nothing to delete
      return;
    }

    try {
      await sftp.delete(remoteFilePath);
      console.log(`🗑️ Remote QR deleted for org ${organizationId}: ${remoteFilePath}`);
    } catch (err) {
      console.error(`⚠️ Remote QR delete failed for org ${organizationId}: ${err.message}`);
    }
  } catch (err) {
    console.error(`❌ Failed to connect for remote QR delete (org ${organizationId}): ${err.message}`);
  } finally {
    try { await sftp.end(); } catch (_) {}
  }
}

async function resetClientForOrg(organizationId) {
  console.log(`♻️ Resetting WhatsApp client for org ${organizationId} due to browser/session error...`);
  readyMap.set(organizationId, false);
  lastQrMap.delete(organizationId);

  try {
    await deleteRemoteQr(organizationId);
  } catch (err) {
    console.error(`❌ Error deleting remote QR in resetClientForOrg (org ${organizationId}): ${err.message}`);
  }

  const existing = clients.get(organizationId);
  if (existing) {
    try {
      await existing.destroy();
    } catch (err) {
      console.error(`⚠️ Error destroying existing client in resetClientForOrg (org ${organizationId}): ${err.message}`);
    }
    clients.delete(organizationId);
  }

  const next = Math.min((backoffMap.get(organizationId) || 1000) * 2, 5 * 60 * 1000);
  backoffMap.set(organizationId, next);
  setTimeout(() => initWhatsAppClient(organizationId), next);
}

// ---------- WhatsApp Client Init (robust) ----------
async function initWhatsAppClient(organizationId) {
  // prevent overlapping inits
  if (initLocks.get(organizationId)) {
    console.log(`Init already in progress for org ${organizationId}, skipping duplicate init.`);
    return;
  }
  initLocks.set(organizationId, true);

  // backoff
  const backoff = backoffMap.get(organizationId) || 0;
  if (backoff > 0) {
    console.log(`Waiting backoff ${backoff}ms before init for org ${organizationId}`);
    await sleep(backoff);
  }

  try {
    readyMap.set(organizationId, false);
    lastQrMap.delete(organizationId);

    const client = new Client({
      authStrategy: new LocalAuth({ clientId: `org_${organizationId}` }),
      puppeteer: {
        executablePath: PUPPETEER_EXEC_PATH,
        headless: true,
        args: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-gpu',
          '--disable-extensions',
          '--disable-background-networking',
          '--disable-sync',
          '--disable-translate',
          '--disable-features=site-per-process',
          '--disable-web-security'
        ],
        timeout: 60000
      },
      restartOnAuthFail: true,
    });

    await client.initialize();

    client.on('qr', async (qr) => {
      console.log(`⚠️ Organization ${organizationId} requires QR scan`);
      const prev = lastQrMap.get(organizationId);
      const now = Date.now();
      // if QR identical and recent, skip upload to avoid repeated writes
      if (prev && prev.qr === qr && now - prev.ts < 60 * 1000) {
        return;
      }
      lastQrMap.set(organizationId, { qr, ts: now });

      try {
        const qrImageBuffer = await qrcode.toBuffer(qr, { type: 'png' });
        await uploadQrToRemote(organizationId, qrImageBuffer);
      } catch (err) {
        console.error(`❌ Failed to create/upload QR buffer (org ${organizationId}): ${err.message}`);
      }
    });

    client.on('authenticated', async () => {
      console.log(`🔑 Organization ${organizationId} authenticated`);
      readyMap.set(organizationId, true);
      try { await deleteRemoteQr(organizationId); } catch (err) { console.error(err); }
    });

    client.on('ready', async () => {
      console.log(`✅ WhatsApp ready for organization ${organizationId}`);
      readyMap.set(organizationId, true);
      backoffMap.set(organizationId, 0);
    });

    client.on('auth_failure', async (msg) => {
      console.error(`🔐 auth_failure for org ${organizationId}: ${msg}`);
      readyMap.set(organizationId, false);
      lastQrMap.delete(organizationId);
      try { await deleteRemoteQr(organizationId); } catch (err) { console.error(err); }
      try { await client.destroy(); } catch (_) {}
      clients.delete(organizationId);
      const next = Math.min((backoffMap.get(organizationId) || 1000) * 2, 5 * 60 * 1000);
      backoffMap.set(organizationId, next);
      setTimeout(() => initWhatsAppClient(organizationId), next);
    });

    client.on('auth_expired', async () => {
      console.error(`⌛ auth_expired for org ${organizationId}`);
      readyMap.set(organizationId, false);
      lastQrMap.delete(organizationId);
      try { await deleteRemoteQr(organizationId); } catch (err) { console.error(err); }
      try { await client.destroy(); } catch (_) {}
      clients.delete(organizationId);
      const next = Math.min((backoffMap.get(organizationId) || 1000) * 2, 5 * 60 * 1000);
      backoffMap.set(organizationId, next);
      setTimeout(() => initWhatsAppClient(organizationId), next);
    });

    client.on('disconnected', async (reason) => {
      console.log(`⚠️ WhatsApp disconnected for org ${organizationId}: ${reason}`);
      readyMap.set(organizationId, false);
      lastQrMap.delete(organizationId);
      try { await deleteRemoteQr(organizationId); } catch (err) { console.error(err); }
      try { await client.destroy(); } catch (err) { console.warn(err.message); }
      clients.delete(organizationId);
      const next = Math.min((backoffMap.get(organizationId) || 1000) * 2 + 1000, 5 * 60 * 1000);
      backoffMap.set(organizationId, next);
      setTimeout(() => initWhatsAppClient(organizationId), next);
    });
    client.on('error', async (err) => {
      const msg = String(err && err.message ? err.message : err || '');
      console.error(`💥 Client error for org ${organizationId}:`, msg);

      if (/Target closed|Session closed|Most likely the page has been closed/i.test(msg)) {
        // Browser session is dead → reset client
        try {
          await resetClientForOrg(organizationId);
        } catch (e) {
          console.error(`❌ Error calling resetClientForOrg from client error handler (org ${organizationId}): ${e.message}`);
        }
      }
    });


    // client.initialize();
    clients.set(organizationId, client);
  } catch (err) {
    console.error(`❌ Failed to init client for org ${organizationId}: ${err.message}`);
    const next = Math.min((backoffMap.get(organizationId) || 1000) * 2, 5 * 60 * 1000);
    backoffMap.set(organizationId, next);
    setTimeout(() => initWhatsAppClient(organizationId), next);
  } finally {
    initLocks.set(organizationId, false);
  }
}

// ---------- QR Refresh Loop ----------
function startQrRefreshLoop() {
  if (isQrLoopRunning) return;
  isQrLoopRunning = true;

  setInterval(async () => {
    try {
      for (const [organizationId, client] of clients.entries()) {
        const isReady = readyMap.get(organizationId) === true;
        if (isReady) continue;

        const last = lastQrMap.get(organizationId);
        if (!last || !last.qr) continue;

        // Only refresh if QR hasn't been uploaded for a while (e.g., 20s)
        const age = Date.now() - (last.ts || 0);
        if (age < 20 * 1000) continue;

        console.log(`🔄 Refreshing QR for org ${organizationId} (not logged in yet)...`);
        try {
          const qrImageBuffer = await qrcode.toBuffer(last.qr, { type: 'png' });
          await uploadQrToRemote(organizationId, qrImageBuffer);
          lastQrMap.set(organizationId, { qr: last.qr, ts: Date.now() });
        } catch (err) {
          console.error(`❌ Failed to refresh QR for org ${organizationId}: ${err.message}`);
        }
      }
    } catch (err) {
      console.error('❌ Error in QR refresh loop:', err.message);
    }
  }, 25 * 1000); // 25 seconds cadence
}

// ---------- Send Message (safe checks) ----------
async function sendWhatsAppMessage(msg) {
  try {
    const client = clients.get(msg.organization_id);
    const isReady = readyMap.get(msg.organization_id) === true;
    if (!client || !isReady) {
      console.log(`🚫 Client not ready for org ${msg.organization_id}. Deferring send for ${msg.number}`);
      // Keep as 'draft' so the message loop will retry later.
      try { await msg.update({ status: 'draft', updated_at: new Date() }); } catch (_) {}
      return;
    }

    const chatId = normalizeNumber(msg.number);
    if (!chatId) {
      console.error(`❌ Invalid number for message id ${msg.id}`);
      try { await msg.update({ status: 'failed', updated_at: new Date() }); } catch (_) {}
      return;
    }

    await msg.update({ status: 'pending', updated_at: new Date() });

    const formattedMessage = ckeditorHtmlToWhatsApp(msg.message);
    const captionText = msg.caption ? ckeditorHtmlToWhatsApp(msg.caption) : '';

    if (msg.image) {
      const imageUrl = `https://hrzenix.com/assets/whatsapp/${msg.image}`;
      try {
        const media = await fetchMediaWithTimeout(imageUrl, 15000);
        await client.sendMessage(chatId, media, {
          caption: captionText || formattedMessage,
        });
      } catch (err) {
        console.error(`⚠️ Failed to send image: ${err.message}. Falling back to text.`);
        await client.sendMessage(chatId, formattedMessage);
      }
    } else {
      await client.sendMessage(chatId, formattedMessage);
    }

    await msg.update({ status: 'sent', updated_at: new Date() });
    console.log(`✅ Message sent by Organization ${msg.organization_id} to ${msg.number}`);
  } catch (err) {
    console.error(`❌ Failed to send to ${msg.number}: ${err.message}`);

    const msgText = String(err && err.message ? err.message : err);
    const isSessionClosed =
      /Target closed|Session closed|Most likely the page has been closed/i.test(msgText);

    if (isSessionClosed) {
      // Browser/tab died. Mark message back to draft, reset client, and retry later.
      try {
        await msg.update({ status: 'draft', updated_at: new Date() });
      } catch (_) {}
      try {
        await resetClientForOrg(msg.organization_id);
      } catch (e) {
        console.error(`❌ Error in resetClientForOrg for org ${msg.organization_id}: ${e.message}`);
      }
    } else {
      // Other errors: mark failed
      try {
        await msg.update({ status: 'failed', updated_at: new Date() });
      } catch (_) {}
    }
  }
}


// ---------- Message Loop ----------
async function startMessageLoop() {
  if (isLoopRunning) return;
  isLoopRunning = true;

  while (true) {
    try {
      // Prefer messages for organizations with ready clients
      const readyOrgs = Array.from(readyMap.entries()).filter(([_, v]) => v).map(([id]) => id);
      let message = null;
      if (readyOrgs.length > 0) {
        message = await WhatsappMessage.findOne({
          where: {
            status: 'draft',
            start_time: { [Op.lte]: new Date() },
            organization_id: { [Op.in]: readyOrgs },
          },
          order: [['start_time', 'ASC']],
        });
      }

      if (!message) {
        // fallback: pick any draft (will be deferred if org not ready)
        message = await WhatsappMessage.findOne({
          where: { status: 'draft', start_time: { [Op.lte]: new Date() } },
          order: [['start_time', 'ASC']],
        });
      }

      if (!message) {
        await sleep(10000);
        continue;
      }

      //await sendWhatsAppMessage(message);
      if (isInPauseTime(message.start_pausing_time, message.end_pausing_time)) {
        console.log(`⏸️ Paused for org ${message.organization_id}`);
        await sleep(60000); // wait 1 min
        continue;
      }

      await sendWhatsAppMessage(message);

      await sleep(2000);
    } catch (err) {
      console.error('❌ Error in message loop:', err.message);
      await sleep(5005);
    }
  }
}

// ---------- Helper: fetch media with timeout ----------
async function fetchMediaWithTimeout(url, timeoutMs = 15000) {
  // Race the fromUrl promise against a timeout
  return await Promise.race([
    MessageMedia.fromUrl(url, { unsafeMime: true }),
    new Promise((_, rej) => setTimeout(() => rej(new Error('Media fetch timeout')), timeoutMs))
  ]);
}

// ---------- Initialize All Organization Clients ----------
async function initAllOrganizationClients() {
  try {
    await sequelize.authenticate();
    console.log('🟢 DB Connected.');

    const organizations = await Organization.findAll();
    console.log(`🏢 Found ${organizations.length} organizations.`);

    for (const org of organizations) {
      initWhatsAppClient(org.id).catch((err) => {
        console.error(`❌ initWhatsAppClient error for org ${org.id}: ${err.message}`);
      });
    }

    startMessageLoop();
    startQrRefreshLoop();
    startClientHealthCheckLoop();
  } catch (err) {
    console.error('❌ Error initializing organization clients:', err.message);
    // retry init after a short delay
    setTimeout(initAllOrganizationClients, 5000);
  }
}

// ---------- Express Routes ----------
app.get('/', (req, res) => {
  res.send('✅ Multi-Organization WhatsApp Bot is running.');
});

// simple admin status (unauthenticated — lock down in production)
app.get('/admin/status', (req, res) => {
  try {
    const data = {
      server_time: new Date().toISOString(),
      total_clients: clients.size,
      orgs: [],
    };

    const orgIds = Array.from(new Set([
      ...Array.from(clients.keys()),
      ...Array.from(readyMap.keys()),
      ...Array.from(lastQrMap.keys()),
    ]));

    for (const id of orgIds) {
      const last = lastQrMap.get(id);
      data.orgs.push({
        organization_id: id,
        ready: readyMap.get(id) === true,
        has_client: clients.has(id),
        last_qr_ts: last ? new Date(last.ts).toISOString() : null,
      });
    }

    res.json(data);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// route to trigger re-init for an org (admin)
app.post('/admin/reinit/:orgId', express.json(), async (req, res) => {
  try {
    const orgId = parseInt(req.params.orgId);
    if (!orgId) return res.status(400).json({ error: 'Invalid orgId' });

    // destroy existing client if present
    const client = clients.get(orgId);
    if (client) {
      try { await client.destroy(); } catch (_) {}
      clients.delete(orgId);
    }
    readyMap.set(orgId, false);
    lastQrMap.delete(orgId);

    // start init
    setTimeout(() => initWhatsAppClient(orgId), 500);
    res.json({ ok: true, message: `reinit triggered for org ${orgId}` });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

function isInPauseTime(start, end) {
  if (!start || !end) return false;

  const now = new Date();

  const extractTime = (value) => {
    if (!value) return null;

    // ✅ If already Date object
    if (value instanceof Date) {
      return value.toTimeString().slice(0, 8);
    }

    // ✅ Convert to string
    const str = value.toString();

    // If datetime → extract time
    if (str.includes(' ')) {
      return str.split(' ')[1];
    }

    // If HH:MM → convert
    if (str.length === 5) {
      return str + ':00';
    }

    return str;
  };

  const currentTime = now.toTimeString().slice(0, 8);

  start = extractTime(start);
  end = extractTime(end);

  // normal case
  if (start < end) {
    return currentTime >= start && currentTime <= end;
  }

  // overnight case
  return currentTime >= start || currentTime <= end;
}

// ---------- Start Server ----------
app.listen(PORT, () => {
  console.log(`🌍 Server running at: http://0.0.0.0:${PORT}`);
  initAllOrganizationClients();
});
