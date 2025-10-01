const { Client, LocalAuth } = require("whatsapp-web.js");
const QRCode = require("qrcode");
const schedule = require("node-schedule");
const fs = require("fs");
const path = require("path");
const axios = require("axios");

// ==================== CONFIG ====================
const API_BASE = "http://message-app.test/api"; // ganti dengan URL Laravel kamu
const BOT_ID = "whatsapp-bot"; // ID unik client
const sessionsDir = "./sessions";

// ==================== VARIABEL STATUS ====================
let isConnected = false;
let reconnecting = false;
const MAX_RECONNECT_ATTEMPTS = 10;
let reconnectAttempts = 0;

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

// QR untuk login
client.on("qr", async (qr) => {
    console.log("📱 QR diterima, mengirim ke Laravel...");
    saveLog("QR received, sending to Laravel.");

    // Convert ke base64 PNG
    const qrImage = await QRCode.toDataURL(qr);

    // Kirim ke Laravel
    try {
        await axios.post(`${API_BASE}/whatsapp/qr`, { qr: qrImage });
        console.log("✅ QR terkirim ke Laravel");
        saveLog("QR sent to Laravel.");
    } catch (err) {
        let error = err.message;
        console.error("❌ Gagal kirim QR:", err.message);
        saveLog(`Failed to send QR: ${error}`);
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
    saveLog("WhatsApp Bot is ready.");
    isConnected = true;

    // Ambil nomor bot
    const botNumber = client.info.wid.user + "@c.us";
    console.log("🤖 Nomor bot:", botNumber);
    saveLog(`Bot number: ${botNumber}`)

    // Kirim ke Laravel
    try {
        await axios.post(`${API_BASE}/whatsapp/bot-info`, {
            number: client.info.wid.user, // nomor tanpa @c.us
            name: client.info.pushname || "Unknown",
        });
        console.log("✅ Info bot terkirim ke Laravel");
        saveLog(`Bot info sent to Laravel.`);
    } catch (err) {
        console.error("❌ Gagal kirim info bot:", err.message);
        saveLog(`Failed to send bot info`);
    }

    // Hapus QR dari Laravel kalau sudah login
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
    saveLog(`Client disconnected: ${reason}`);
    isConnected = false;

    if (!reconnecting && reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
        reconnecting = true;
        reconnectAttempts++;
        console.log(
            `🔄 Reconnect dalam 10 detik... (Percobaan ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`
        );
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
    
    try{
        await axios.post(`${API_BASE}/histories`, {
            contact_number: phoneNumber,
            message: msg.body,
            direction: "in",
        });
        console.log("✅ Pesan masuk disimpan ke histories");
        saveLog("Incoming message saved to histories.");
    } catch (err){
        console.error("❌ Gagal simpan pesan masuk: ", err.message);
        saveLog(`Failed to save incoming message: ${err.message}`);
    }
});

// ==================== FUNCTIONS ====================

// Keep alive
function keepSessionAlive() {
    setInterval(async () => {
        if (isConnected) {
            try {
                let time = new Date().toLocaleTimeString();
                await client.sendPresenceAvailable();
                console.log("🔄 Keep-alive ping sent -", new Date().toLocaleTimeString());
                saveLog(`Keep-alive ping sent -${time}`);
            } catch (error) {
                console.log("❌ Keep-alive failed:", error.message);
                saveLog(`Keep-alive failed: ${error.message}`);
            }
        }
    }, 60000);
}

// Cek koneksi
function checkConnection() {
    if (!isConnected) {
        console.log("⚠️ Bot tidak terhubung, menunggu reconnect...");
        saveLog("Bot is not connected, waiting for reconnect.");
        return false;
    }
    return true;
}

// Kirim pesan dengan retry
async function safeSend(number, message, retries = 3) {
    for (let attempt = 1; attempt <= retries; attempt++) {
        if (!checkConnection()) {
            console.log(`❌ Percobaan ${attempt} gagal - Client tidak terhubung`);
            saveLog(`Attempt ${attempt} failed - Client not connected`);
            if (attempt < retries)
                await new Promise((resolve) => setTimeout(resolve, 10000));
            continue;
        }

        try {
            await client.sendMessage(number, message);
            console.log("✅ Pesan terkirim ke " + number);
            saveLog(`Message sent to ${number}`);

            contactNumber = number.replace(/@c\.us$/, "");

            try{
                await axios.post(`${API_BASE}/histories`, {
                    contact_number: contactNumber,
                    message: message,
                    direction: "out",
                });
                console.log("✅ Pesan keluar disimpan ke histories");
                saveLog("Outgoing message saved to histories");
            } catch (err){
                console.error("❌ Gagal simpan pesan keluar: ", err.message);
                saveLog(`Failed to save outgoing message: ${err.message}`);
            }

            return true;
        } catch (err) {
            console.error(`❌ Percobaan ${attempt} gagal:`, err.message);
            saveLog(`Attempt ${attempt} failed: ${err.message}`);
            if (attempt < retries) {
                console.log("⏳ Coba lagi dalam 5 detik...");
                saveLog(`Try again in 5 seconds.`);
                await new Promise((resolve) => setTimeout(resolve, 5000));
            }
        }
    }
    return false;
}

async function saveLog(message) {
    try {
        await axios.post(`${API_BASE}/logs`, { message });
    } catch (err) {
        console.error("❌ Gagal simpan log:", err.message);
        saveLog(`Failed to save log: ${err.message}`);
    }
}

// Ambil jadwal dari Laravel
// Ambil jadwal dari Laravel
async function loadSchedules() {
    console.log("📡 Memuat jadwal dari Laravel...");
    saveLog(`Loading schedules from Laravel.`);

    try {
        const res = await axios.get(`${API_BASE}/schedules`);
        const schedules = res.data;

        schedules.forEach((item) => {
            console.log(
                `📌 Jadwal: ${item.scheduler_name} @ ${item.schedule_time} untuk ${item.contacts.length} kontak`
            );
            saveLog(`Schedule: ${item.scheduler_name} @ ${item.schedule_time} for ${item.contacts.length} contacts`);

            const [hour, minute] = item.schedule_time.split(":");
            const rule = `${minute} ${hour} * * *`;

            // Cancel job lama jika sudah ada
            if (schedule.scheduledJobs[item.scheduler_name]) {
                schedule.scheduledJobs[item.scheduler_name].cancel();
                console.log(`♻️ Job ${item.scheduler_name} direset`);
                saveLog(`Job ${item.scheduler_name} reset`);
            }

            // Daftarkan job baru dengan nama unik
            schedule.scheduleJob(item.scheduler_name, rule, async () => {
                for (const contact of item.contacts) {
                    let number = contact.phone_number + "@c.us";
                    let success = await safeSend(number, item.message);

                    if (success) {
                        console.log(`✅ Message sent to ${contact.phone_number}`);
                        saveLog(`✅ Message to ${contact.phone_number} sent successfully.`);
                    } else {
                        console.log(`❌ Failed to send to ${contact.phone_number}`);
                        saveLog(`❌ Message to ${contact.phone_number} failed to send.`);
                    }
                }
            });
        });
    } catch (err) {
        console.error("❌ Gagal load schedules:", err.message);
        saveLog(`Failed to load schedules: ${err.message}`);
    }
}

// Shutdown Chromium clean
async function shutdownChromium() {
    console.log("🛑 Mematikan Chromium...");
    saveLog(`Turning off Chromium.`);
    try {
        await client.destroy();
        console.log("✅ Chromium berhasil dimatikan");
        saveLog(`Chromium successfully turned off.`);
    } catch (error) {
        console.error("❌ Error shutdown:", error.message);
        saveLog(`Error shutdown: ${error.message}`);
        process.exit(1);
    }
}

// Handle signal shutdown
["SIGINT", "SIGTERM", "SIGQUIT", "SIGHUP"].forEach((signal) => {
    process.on(signal, async () => {
        console.log(`\n${signal} diterima, menghentikan bot...`);
        saveLog(`${signal} received, turning off the bot.`);
        try {
            await shutdownChromium();
            console.log("✅ Bot dihentikan clean");
            saveLog(`Bot stopped clean.`);
            process.exit(0);
        } catch (error) {
            console.error("❌ Gagal shutdown:", error);
            saveLog(`Failed to shutdown: ${error}`);
            process.exit(1);
        }
    });
});

// Exception handler
process.on("uncaughtException", async (error) => {
    console.error("❌ Uncaught Exception:", error);
    saveLog(`Uncaught Exception: ${error}`);
    try {
        await shutdownChromium();
    } finally {
        process.exit(1);
    }
});

process.on("unhandledRejection", async (reason, promise) => {
    console.error("❌ Unhandled Rejection:", reason);
    saveLog(`Unhandled Rejection: ${reason}`);
    try {
        await shutdownChromium();
    } finally {
        process.exit(1);
    }
});

// Watch sessions folder
fs.watch('.', (eventType, filename) => {
    if (filename === "sessions" && eventType === "rename") {
        // kalau sessions dihapus, langsung reset client
        if (!fs.existsSync(sessionsDir)) {
            console.log("⚠️ Sessions folder deleted! Forcing re-login...");
            saveLog("Sessions folder deleted, forcing QR regeneration...");

            forceRelogin();
        }
    }
});

async function forceRelogin() {
    try {
        await client.destroy();
        console.log("🧹 Old client destroyed");

        // Buat ulang folder sessions
        fs.mkdirSync(sessionsDir, { recursive: true });

        // Inisialisasi ulang client
        client.initialize();
        console.log("🔄 Client re-initialized, waiting for QR...");
        saveLog("Client re-initialized, waiting for QR...");
    } catch (error) {
        console.error("❌ Error during forceRelogin:", error.message);
        saveLog(`Error during forceRelogin: ${error.message}`);
    }
}

// ==================== START BOT ====================
client.initialize().catch(async (error) => {
    console.error("❌ Gagal inisialisasi client:", error);
    saveLog(`Failed to initiate client: ${error}`);
    process.exit(1);
});

process.on("exit", (code) => {
    console.log(`Process exiting with code: ${code}`);
    saveLog(`Process exiting with code: ${code}`);
});

setInterval(() => {
    loadSchedules();
}, 60000);