import pkg from 'whatsapp-web.js';
import qrcode from 'qrcode';
import { Sequelize, DataTypes, Op } from 'sequelize';
import express from 'express';
import net from 'net';
import { Client as SSHClient } from 'ssh2';
import ClientSFTP from 'ssh2-sftp-client';

const { Client, MessageMedia, LocalAuth } = pkg;

/* ==================================================
   GLOBAL STATE
================================================== */

const app = express();
const PORT = 3000;

let isAuthenticated = false;
let messageLoopStarted = false;
let sendingNow = false;
let isRestarting = false;
let isReady = false;
let lastMessageSentAt = 0;
/* ==================================================
   DB & SSH CONFIG
================================================== */

const DB_NAME = 'campuswikidb';
const DB_USER = 'campuswikiuser';
const DB_PASS = 'je4pru8xZuhQ';
const DB_HOST = '10.192.2.3';
const DB_PORT = 3306;

const SSH_HOST = 'cl0100-cl0062.wp-cl.clients.n99p-r1.com';
const SSH_PORT = 13002;
const SSH_USER = 'amitbookdepot';
const SSH_PASSWORD = 'do3HmqKsd4qb';
const LOCAL_TUNNEL_PORT = 33071;

const QR_REMOTE_PATH =
  '/home/amitbookdepot/staging.amitbookdepot.com_public_html/public/admin/qr/whatsapp_qr.png';

/* ==================================================
   SSH TUNNEL
================================================== */


async function startSshTunnel() {
  return new Promise((resolve, reject) => {
    const ssh = new SSHClient();

    ssh.on('ready', () => {
      net.createServer(socket => {
        ssh.forwardOut(
          socket.remoteAddress || '127.0.0.1',
          socket.remotePort || 0,
          DB_HOST,
          DB_PORT,
          (err, stream) => {
            if (err) return socket.destroy();
            socket.pipe(stream).pipe(socket);
          }
        );
      }).listen(LOCAL_TUNNEL_PORT, '127.0.0.1', resolve);
    });

    ssh.on('error', reject);
    ssh.connect({
      host: SSH_HOST,
      port: SSH_PORT,
      username: SSH_USER,
      password: SSH_PASSWORD,
    });
  });
}

await startSshTunnel();

/* ==================================================
   SEQUELIZE
================================================== */

const sequelize = new Sequelize(DB_NAME, DB_USER, DB_PASS, {
  host: '127.0.0.1',
  port: LOCAL_TUNNEL_PORT,
  dialect: 'mysql',
  logging: false,
  timezone: '+05:30',
});

await sequelize.authenticate();
console.log('✅ DB connected');

/* ==================================================
   MODEL
================================================== */

const WhatsappMessage = sequelize.define(
  'WhatsappMessage',
  {
    id: { type: DataTypes.INTEGER, primaryKey: true },
    number: DataTypes.STRING,
    message: DataTypes.TEXT,
    caption: DataTypes.TEXT,
    image: DataTypes.STRING,
    status: DataTypes.STRING,
    time_gap_from_previous_message: DataTypes.INTEGER,
    start_time: DataTypes.DATE,
    start_pausing_time: DataTypes.TIME,
    end_pausing_time: DataTypes.TIME,  
    updated_at: DataTypes.DATE,
  },
  { tableName: 'whatsapp_messages', timestamps: false }
);

/* ==================================================
   HELPERS
================================================== */

const sleep = ms => new Promise(r => setTimeout(r, ms));

function normalizeNumber(num) {
  num = num.replace(/\D/g, '');
  if (!num.startsWith('91')) num = '91' + num;
  return `${num}@c.us`;
}

/* ==================================================
   WHATSAPP CLIENT (STABLE CONFIG)
================================================== */

const client = new Client({
  authStrategy: new LocalAuth({
    dataPath: './whatsapp-session',
  }),
  puppeteer: {
    headless: "new",
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
    ],
  },
  restartOnAuthFail: true
});

/* ==================================================
   QR HANDLING
================================================== */

async function uploadQR(qr) {
  const buffer = await qrcode.toBuffer(qr);
  const sftp = new ClientSFTP();
  await sftp.connect({
    host: SSH_HOST,
    port: SSH_PORT,
    username: SSH_USER,
    password: SSH_PASSWORD,
  });
  await sftp.put(buffer, QR_REMOTE_PATH);
  await sftp.end();
}

async function deleteQR() {
  const sftp = new ClientSFTP();
  try {
    await sftp.connect({
      host: SSH_HOST,
      port: SSH_PORT,
      username: SSH_USER,
      password: SSH_PASSWORD,
    });
    await sftp.delete(QR_REMOTE_PATH);
  } catch {}
  finally {
    try { await sftp.end(); } catch {}
  }
}

/* ==================================================
   EVENTS
================================================== */

client.on('qr', async qr => {
  console.log('📸 QR generated');
  await uploadQR(qr);
});

client.on('authenticated', async () => {
  console.log('🔑 Authenticated');
  isAuthenticated = true;
  await deleteQR();
  safeStartMessageLoop();
});

client.on('ready', () => {
  console.log('✅ WhatsApp fully ready');
  isReady = true;
});

/* ==================================================
   SAFE LOOP START
================================================== */

async function safeStartMessageLoop() {
  if (messageLoopStarted) return;
  messageLoopStarted = true;

  console.log('🔥 Message loop started');
  await sleep(3000);

  startMessageLoop();
}

/* ==================================================
   SEND MESSAGE
================================================== */

async function sendWhatsAppMessage(msg) {
  if (sendingNow || !isAuthenticated) return;
  if (!isReady) {
    console.log('⚠️ Client not ready');
    return;
  }
  sendingNow = true;

  try {
    const chatId = normalizeNumber(msg.number);

    let isRegistered = true;
    try {
      isRegistered = await client.isRegisteredUser(chatId);
    } catch {
      console.log('⚠️ Skipping registration check');
    }


    // ✅ CRITICAL FIX (No LID error)
    if (!isRegistered) {
      console.log('❌ Number not on WhatsApp:', msg.number);
      await msg.update({ status: 'failed', updated_at: new Date() });

      sendingNow = false; // ✅ FIX
      return;
    }

    // ✅ Explicitly load chat
    //const chat = await client.getChatById(chatId);

    // await msg.update({ status: 'pending', updated_at: new Date() });

    // if (msg.image) {
    //   const media = await MessageMedia.fromUrl(
    //     `https://staging.amitbookdepot.com/assets/whatsapp/${msg.image}`
    //   );
    //   const caption = htmlToWhatsapp(msg.caption || msg.message);

    //   await chat.sendMessage(media, { caption });


    // } else {
    //   const cleanMessage = htmlToWhatsapp(msg.message);
    //   await chat.sendMessage(cleanMessage);
    // }

    await msg.update({ status: 'pending', updated_at: new Date() });

    if (msg.image) {
      const media = await MessageMedia.fromUrl(
        `https://staging.amitbookdepot.com/assets/whatsapp/${msg.image}`
      );

      const caption = htmlToWhatsapp(msg.caption || msg.message);

      // await client.sendMessage(chatId, media, { caption });
      await safeSend(chatId, media, { caption });

    } else {
      const cleanMessage = htmlToWhatsapp(msg.message);

      // await client.sendMessage(chatId, cleanMessage);
      await safeSend(chatId, cleanMessage);
    }

    await msg.update({ status: 'sent', updated_at: new Date() });
    console.log('✅ Sent to', msg.number);

  } catch (e) {
    console.error('❌ Send failed:', e.message);
    await msg.update({ status: 'failed', updated_at: new Date() });
  } finally {
    sendingNow = false;
    await sleep(3000);
  }
}


/* ==================================================
   MAIN LOOP
================================================== */

async function startMessageLoop() {
  while (true) {
    try {
      const msg = await WhatsappMessage.findOne({
        where: {
          status: 'draft',
          start_time: { [Op.lte]: new Date() },
        },
        order: [['id', 'ASC']],
      });

      // if (msg) await sendWhatsAppMessage(msg);
      if (msg && isAuthenticated && isReady && !isRestarting) {

        // ⏳ GAP CONTROL
        const gapSeconds = msg.time_gap_from_previous_message || 0;
        const now = Date.now();

        if (lastMessageSentAt > 0) {
          const diff = (now - lastMessageSentAt) / 1000;

          if (diff < gapSeconds) {
            const waitTime = (gapSeconds - diff) * 1000;
            console.log(`⏳ Waiting ${Math.ceil(waitTime / 1000)} sec before next message`);
            await sleep(waitTime);
          }
        }

        if (isInPauseTime(msg.start_pausing_time, msg.end_pausing_time)) {
          console.log('⏸️ Paused due to time window');
          await sleep(8000);
          continue;
        }

        await sendWhatsAppMessage(msg);

        // ✅ update last sent time
        lastMessageSentAt = Date.now();
      }
      await sleep(8000);
    } catch (e) {
      console.error('Loop error:', e.message);
      await sleep(8000);
    }
  }
}

function htmlToWhatsapp(text) {
  if (!text) return '';

  let msg = text;

  // bold
  msg = msg.replace(/<(strong|b)>(.*?)<\/\1>/gi, '*$2*');

  // italic
  msg = msg.replace(/<(em|i)>(.*?)<\/\1>/gi, '_$2_');

  // strike
  msg = msg.replace(/<(del|s)>(.*?)<\/\1>/gi, '~$2~');

  // line breaks
  msg = msg.replace(/<br\s*\/?>/gi, '\n');
  msg = msg.replace(/<\/p>/gi, '\n');
  msg = msg.replace(/<p>/gi, '');

  // links → keep URL only
  msg = msg.replace(/<a[^>]+href=["']([^"']+)["'][^>]*>.*?<\/a>/gi, '$1');

  // remove remaining tags
  msg = msg.replace(/<[^>]*>/g, '');

  // cleanup
  msg = msg.replace(/\n{2,}/g, '\n').trim();

  return msg;
}

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


client.on('disconnected', async (reason) => {
  console.log('❌ Disconnected:', reason);

  isAuthenticated = false;
  isReady = false;
  messageLoopStarted = false;
  sendingNow = false;

  if (!isRestarting) {
    isRestarting = true;

    await client.destroy();

    setTimeout(() => {
      console.log('🔄 Reinitializing WhatsApp...');
      client.initialize();
      isRestarting = false;
    }, 5000);
  }
});

async function safeSend(chatId, content, options = {}) {
  if (isRestarting) {
    console.log('⏳ Skipping send, restarting...');
    return;
  }

  try {
    const state = await client.getState();
    console.log('📡 WA State:', state);

    if (state !== 'CONNECTED') {
      throw new Error('WhatsApp not connected');
    }

  } catch (err) {
    console.log('❌ WA session issue:', err.message);

    if (!isRestarting) {
      isRestarting = true;

      await client.destroy();
      await sleep(5000);
      await client.initialize();

      isRestarting = false;
    }

    throw new Error('Client restarted');
  }

  for (let i = 0; i < 2; i++) {
    try {
      return await client.sendMessage(chatId, content, options);
    } catch (err) {
      console.log(`⚠️ Send attempt ${i + 1} failed:`, err.message);

      if (err.message.includes('detached Frame')) {
        console.log('♻️ Frame crash → restarting...');

        if (!isRestarting) {
          isRestarting = true;

          await client.destroy();
          await sleep(5000);
          await client.initialize();

          isRestarting = false;
        }

        throw err;
      }

      if (i === 1) throw err;

      await sleep(3000);
    }
  }
}

client.on('change_state', state => {
  console.log('🔄 State changed:', state);

  if (state === 'CONFLICT' || state === 'UNLAUNCHED') {
    client.destroy();
    setTimeout(() => client.initialize(), 5000);
  }
});


/* ==================================================
   START
================================================== */

client.initialize();
app.listen(PORT, () => console.log(`🌍 Server running on ${PORT}`));
