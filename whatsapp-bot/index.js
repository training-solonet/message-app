const { Client, LocalAuth } = require("whatsapp-web.js");
const QRCode = require("qrcode");
const schedule = require("node-schedule");
const fs = require("fs");
const axios = require("axios");

// ==================== CONFIG ====================
const API_BASE = "http://message-app.test/api"; // Ganti dengan URL Laravel kamu
const BOT_ID = "whatsapp-bot"; // ID unik client
const sessionsDir = "./sessions";

// ==================== VARIABEL STATUS ====================
let isConnected = false;
let reconnecting = false;
const MAX_RECONNECT_ATTEMPTS = 10;
let reconnectAttempts = 0;
let bot_status = "disconnected"; // connected / disconnected

// Pastikan folder sessions ada
if (!fs.existsSync(sessionsDir)) {
    fs.mkdirSync(sessionsDir, { recursive: true });
    console.log("📁 Sessions directory created");
    saveLog("Sessions directory created.");
}

// ==================== INIT CLIENT ====================
const client = new Client({
    authStrategy: new LocalAuth({
        clientId: BOT_ID,
        dataPath: sessionsDir,
    }),
    puppeteer: {
        headless: true,
        args: ["--no-sandbox", "--disable-setuid-sandbox"],
    },
    takeoverOnConflict: true,
    takeoverTimeoutMs: 30000,
    restartOnAuthFail: true,
    keepAliveIntervalMs: 30000,
    keepAliveRequired: true,
});

// ==================== EVENT HANDLER ====================

client.on("qr", async (qr) => {
    console.log("📱 QR diterima, mengirim ke Laravel...");
    saveLog("QR received, sending to Laravel.");

    const qrImage = await QRCode.toDataURL(qr);

    try {
        await axios.post(`${API_BASE}/whatsapp/qr`, { qr: qrImage });
        console.log("✅ QR terkirim ke Laravel");
        saveLog("QR sent to Laravel.");
    } catch (err) {
        console.error("❌ Gagal kirim QR:", err.message);
        saveLog(`Failed to send QR: ${err.message}`);
    }
});

client.on("authenticated", () => {
    console.log("✅ Authentication successful");
    saveLog("Authentication successful.");
    reconnectAttempts = 0;
});

client.on("auth_failure", (msg) => {
    console.log("❌ Authentication failed:", msg);
    saveLog(`Authentication failed: ${msg}`);
});

client.on("ready", async () => {
    console.log("✅ WhatsApp Bot siap!");
    bot_status = "connected";
    await sendBotStatus();
    saveLog("WhatsApp Bot is ready.");
    isConnected = true;

    const botNumber = client.info.wid.user + "@c.us";
    console.log("🤖 Nomor bot:", botNumber);
    saveLog(`Bot number: ${botNumber}`);

    try {
        await axios.post(`${API_BASE}/whatsapp/bot-info`, {
            number: client.info.wid.user,
            name: client.info.pushname || "Unknown",
        });
        console.log("✅ Info bot terkirim ke Laravel");
        saveLog(`Bot info sent to Laravel.`);
    } catch (err) {
        console.error("❌ Gagal kirim info bot:", err.message);
        saveLog(`Failed to send bot info`);
    }

    try {
        await axios.post(`${API_BASE}/whatsapp/qr`, { qr: null });
        console.log("🧹 QR dihapus karena sudah login");
        saveLog(`QR deleted because user logged in already.`);
    } catch (err) {
        console.error("❌ Gagal hapus QR:", err.message);
        saveLog(`Failed to delete QR: ${err.message}`);
    }

    keepSessionAlive();
    await loadSchedules();
});

client.on("disconnected", async (reason) => {
    console.log("⚠️ Client terputus:", reason);
    bot_status = "disconnected";
    await sendBotStatus();
    saveLog(`Client disconnected: ${reason}`);
    isConnected = false;

    if (!reconnecting && reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
        reconnecting = true;
        reconnectAttempts++;
        console.log(`🔄 Reconnect dalam 10 detik... (Percobaan ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
        saveLog(`Reconnect in 10 seconds... (Attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);

        try {
            await client.destroy();
        } catch (err) {
            console.error("❌ Error destroy client:", err.message);
            saveLog(`Error destroy client: ${err.message}`);
        }

        setTimeout(async () => {
            try {
                await client.initialize();
                reconnecting = false;
            } catch (err) {
                console.error("❌ Reconnect gagal:", err.message);
                saveLog(`Failed to reconnect: ${err.message}`);
                reconnecting = false;
            }
        }, 10000);
    }
});

client.on("message", async (msg) => {
    console.log(`📩 Pesan masuk dari ${msg.from}: ${msg.body}`);
    saveLog(`Incoming message from ${msg.from}: ${msg.body}`);

    const phoneNumber = msg.from.replace(/@c\.us$/, "");

    try {
        await axios.post(`${API_BASE}/histories`, {
            contact_number: phoneNumber,
            message: msg.body,
            direction: "in",
            status: "sent",
            is_read: false,
        });
        console.log("✅ Pesan masuk disimpan ke histories");
        saveLog("Incoming message saved to histories.");
    } catch (err) {
        console.error("❌ Gagal simpan pesan masuk: ", err.message);
        saveLog(`Failed to save incoming message: ${err.message}`);
    }
});

// ==================== FUNCTIONS ====================

function keepSessionAlive() {
    setInterval(async () => {
        if (isConnected) {
            try {
                await client.sendPresenceAvailable();
                console.log("🔄 Keep-alive ping sent -", new Date().toLocaleTimeString());
                saveLog(`Keep-alive ping sent -${new Date().toLocaleTimeString()}`);
            } catch (error) {
                console.log("❌ Keep-alive failed:", error.message);
                saveLog(`Keep-alive failed: ${error.message}`);
            }
        }
    }, 60000);
}

function checkConnection() {
    if (!isConnected) {
        console.log("⚠️ Bot tidak terhubung, menunggu reconnect...");
        saveLog("Bot is not connected, waiting for reconnect.");
        return false;
    }
    return true;
}

async function safeSend(number, message, retries = 3) {
    for (let attempt = 1; attempt <= retries; attempt++) {
        if (!checkConnection()) {
            console.log(`❌ Percobaan ${attempt} gagal - Client tidak terhubung`);
            saveLog(`Attempt ${attempt} failed - Client not connected`);
            if (attempt < retries) await new Promise((r) => setTimeout(r, 10000));
            continue;
        }

        try {
            await client.sendMessage(number, message);
            console.log("✅ Pesan terkirim ke " + number);
            saveLog(`Message sent to ${number}`);

            const contactNumber = number.replace(/@c\.us$/, "");

            try {
                await axios.post(`${API_BASE}/histories`, {
                    contact_number: contactNumber,
                    message: message,
                    direction: "out",
                    status: "sent",
                    is_read: true,
                });
                console.log("✅ Pesan keluar disimpan ke histories");
                saveLog("Outgoing message saved to histories");
            } catch (err) {
                console.error("❌ Gagal simpan pesan keluar: ", err.message);
                saveLog(`Failed to save outgoing message: ${err.message}`);
            }

            return true;
        } catch (err) {
            console.error(`❌ Percobaan ${attempt} gagal:`, err.message);
            saveLog(`Attempt ${attempt} failed: ${err.message}`);

            try {
                const contactNumber = number.replace(/@c\.us$/, "");
                await axios.post(`${API_BASE}/histories`, {
                    contact_number: contactNumber,
                    message: message,
                    direction: "out",
                    status: "failed",
                    is_read: true,
                });
                console.log("⚠️ Pesan gagal disimpan ke histories dengan status failed");
                saveLog("Failed message saved to histories");
            } catch (saveErr) {
                console.error("❌ Gagal simpan pesan gagal: ", saveErr.message);
                saveLog(`Failed to save failed message: ${saveErr.message}`);
            }

            if (attempt < retries) {
                console.log("⏳ Coba lagi dalam 5 detik...");
                saveLog("Try again in 5 seconds.");
                await new Promise((resolve) => setTimeout(resolve, 5000));
            }
        }
    }
    return false;
}

async function sendBotStatus() {
    try {
        await axios.post(`${API_BASE}/whatsapp/bot-status`, { status: bot_status });
        console.log(`🔔 Bot status sent: ${bot_status}`);
        saveLog(`Bot status sent: ${bot_status}`);
    } catch (err) {
        console.error("❌ Gagal kirim bot_status:", err.message);
        saveLog(`Failed to send bot_status: ${err.message}`);
    }
}

async function saveLog(message) {
    try {
        await axios.post(`${API_BASE}/logs`, { message });
    } catch (err) {
        console.error("❌ Gagal simpan log:", err.message);
    }
}

// ==================== PERUBAHAN DI SINI ====================
async function loadSchedules() {
    console.log("📡 Memuat jadwal dari Laravel...");
    saveLog(`Loading schedules from Laravel.`);

    try {
        const res = await axios.get(`${API_BASE}/schedules`);
        const schedules = res.data;

        schedules.forEach((item) => {
            console.log(`📌 Jadwal: ${item.scheduler_name} @ ${item.schedule_time}`);
            saveLog(`Schedule: ${item.scheduler_name} @ ${item.schedule_time}`);

            const [hour, minute] = item.schedule_time.split(":");
            const rule = `${minute} ${hour} * * *`;

            // Reset jika sudah ada job dengan nama sama
            if (schedule.scheduledJobs[item.scheduler_name]) {
                schedule.scheduledJobs[item.scheduler_name].cancel();
                console.log(`♻️ Job ${item.scheduler_name} direset`);
                saveLog(`Job ${item.scheduler_name} reset`);
            }

            schedule.scheduleJob(item.scheduler_name, rule, async () => {
                console.log(`🚀 Eksekusi schedule: ${item.scheduler_name}`);
                saveLog(`Execute schedule: ${item.scheduler_name}`);

                try {
                    // Ambil kategori dari pivot contact_schedules (item.categories)
                    const categories = item.categories || [];
                    for (const category of categories) {
                        const catId = category.id;

                        // Ambil semua kontak dari kategori tersebut
                        const contactsRes = await axios.get(`${API_BASE}/contacts/by-category/${catId}`);
                        const contacts = contactsRes.data;

                        for (const contact of contacts) {
                            const number = contact.phone_number + "@c.us";
                            const success = await safeSend(number, item.message);

                            if (success) {
                                console.log(`✅ Message sent to ${contact.phone_number}`);
                                saveLog(`✅ Message to ${contact.phone_number} sent successfully.`);
                            } else {
                                console.log(`❌ Failed to send to ${contact.phone_number}`);
                                saveLog(`❌ Message to ${contact.phone_number} failed to send.`);
                            }
                        }
                    }
                } catch (err) {
                    console.error("❌ Error menjalankan schedule:", err.message);
                    saveLog(`Error executing schedule: ${err.message}`);
                }
            });
        });
    } catch (err) {
        console.error("❌ Gagal load schedules:", err.message);
        saveLog(`Failed to load schedules: ${err.message}`);
    }
}

// ==================== START BOT ====================
client.initialize().catch(async (error) => {
    console.error("❌ Gagal inisialisasi client:", error);
    saveLog(`Failed to initiate client: ${error}`);
    process.exit(1);
});

setInterval(() => {
    loadSchedules();
}, 60000);

setInterval(() => {
    sendBotStatus();
}, 30000);
