const { MessageMedia, Client, LocalAuth } = require("whatsapp-web.js");
const QRCode = require("qrcode");
const schedule = require("node-schedule");
const fs = require("fs");
const path = require("path");
const axios = require("axios");
const mime = require("mime-types");
const dotenv = require('dotenv');
const express = require("express");
const app = express();
app.use(express.json());
dotenv.config({ path: path.resolve(__dirname, '../.env') });

// ==================== CONFIG ====================
const API_BASE = `${process.env.APP_URL}/api`;
console.log(API_BASE);
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
            file_path: null,
        });
        console.log("✅ Pesan masuk disimpan ke histories");
        saveLog("Incoming message saved to histories.");
    } catch (err) {
        console.error("❌ Gagal simpan pesan masuk: ", err.message);
        saveLog(`Failed to save incoming message: ${err.message}`);
    }
});

app.post("/bot/logout", async (req, res) => {
  console.log("📩 Received logout signal from Laravel...");

  try {
    if (!client) {
      console.warn("⚠️ No active WhatsApp client found.");
      return res.status(400).json({
        success: false,
        message: "No active WhatsApp session to log out from.",
      });
    }

    await client.logout();
    console.log("✅ WhatsApp bot logged out successfully.");
    console.log("🔁 QR code will regenerate automatically on next connection attempt.");

    return res.json({
      success: true,
      message: "Bot logged out successfully. QR will regenerate on reconnect.",
    });
  } catch (err) {
    console.error("❌ Logout failed:", err);
    return res.status(500).json({
      success: false,
      error: err.message || "Unknown error during logout.",
    });
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
                    file_path: null,
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
                    file_path: null,
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

        for (const item of schedules) {
            console.log(`📌 Jadwal: ${item.scheduler_name} @ ${item.schedule_time}`);
            saveLog(`Schedule: ${item.scheduler_name} @ ${item.schedule_time}`);

            const [hour, minute] = item.schedule_time.split(":");
            const rule = `${minute} ${hour} * * *`;

            // Reset job lama jika ada
            if (schedule.scheduledJobs[item.scheduler_name]) {
                schedule.scheduledJobs[item.scheduler_name].cancel();
                console.log(`♻️ Job ${item.scheduler_name} direset`);
                saveLog(`Job ${item.scheduler_name} reset`);
            }

            // Jadwalkan job baru
            schedule.scheduleJob(item.scheduler_name, rule, async () => {
                console.log(`🚀 Eksekusi schedule: ${item.scheduler_name}`);
                saveLog(`Execute schedule: ${item.scheduler_name}`);

                try {
                    const categories = item.categories || [];
                    for (const category of categories) {
                        const catId = category.id;

                        // Ambil kontak per kategori
                        const contactsRes = await axios.get(`${API_BASE}/contacts/by-category/${catId}`);
                        const contacts = contactsRes.data;

                        // Persiapkan media jika ada file
                        let media = null;
                        let caption = item.message || "";

                        if (item.file_path) {
                            try {
                                const fileUrl = `${API_BASE.replace("/api", "")}/storage/${item.file_path}`;
                                const mimeType = mime.lookup(item.file_path) || "application/octet-stream";

                                console.log(`📎 File terdeteksi: ${item.file_path} (${mimeType})`);
                                saveLog(`Detected file: ${item.file_path} (${mimeType})`);

                                // Ambil file dari URL
                                const fileRes = await axios.get(fileUrl, { responseType: "arraybuffer" });
                                const base64File = Buffer.from(fileRes.data, "binary").toString("base64");

                                // Buat MessageMedia
                                media = new MessageMedia(mimeType, base64File, item.file_path.split("/").pop());

                                for (const contact of contacts) {
                                    const number = contact.phone_number + "@c.us";

                                    if (mimeType.startsWith("image/") || mimeType.startsWith("video/")) {
                                        // Kirim gambar/video dengan caption biasa
                                        await client.sendMessage(number, media, { caption });
                                        console.log(`✅ Media (${mimeType}) dikirim ke ${contact.phone_number}`);
                                        saveLog(`Media sent to ${contact.phone_number}`);
                                    } else {
                                        // Kirim dokumen dengan caption
                                        await client.sendMessage(number, media, {
                                            sendMediaAsDocument: true,
                                            caption: caption,
                                        });
                                        console.log(`📄 Dokumen (${mimeType}) dengan caption dikirim ke ${contact.phone_number}`);
                                        saveLog(`Document with caption sent to ${contact.phone_number}`);
                                    }


                                    // Simpan ke histories
                                    try {
                                        await axios.post(`${API_BASE}/histories`, {
                                            contact_number: contact.phone_number,
                                            message: caption,
                                            direction: "out",
                                            status: "sent",
                                            is_read: true,
                                            file_path: item.file_path || null,
                                        });
                                    } catch (histErr) {
                                        console.error("❌ Gagal simpan ke histories:", histErr.message);
                                        saveLog(`Failed to save history: ${histErr.message}`);
                                    }
                                }
                            } catch (fileErr) {
                                console.error("❌ Gagal mengirim file:", fileErr.message);
                                saveLog(`Failed to send file: ${fileErr.message}`);
                            }
                        } else {
                            // Kalau tidak ada file, kirim pesan teks biasa
                            for (const contact of contacts) {
                                const number = contact.phone_number + "@c.us";
                                await safeSend(number, caption);
                            }
                        }
                    }
                } catch (err) {
                    console.error("❌ Error menjalankan schedule:", err.message);
                    saveLog(`Error executing schedule: ${err.message}`);
                }
            });
        }
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

async function handleShutdown(signal) {
    console.log(`⚠️ Program terminated (${signal}), sending disconnected status...`);
    saveLog(`Program terminated (${signal}), sending disconnected status...`);

    try {
        // 1. Ubah status bot menjadi disconnected
        bot_status = "disconnected";
        await sendBotStatus();
        console.log("🔔 Bot status set to disconnected before exit");
        saveLog("Bot status set to disconnected before exit");

        // 2. Destroy client terlebih dahulu untuk release semua file locks
        if (client) {
            try {
                await client.destroy();
                console.log("🔌 WhatsApp client destroyed successfully");
                saveLog("WhatsApp client destroyed successfully");
            } catch (destroyErr) {
                console.error("⚠️ Error destroying client:", destroyErr.message);
                saveLog(`Error destroying client: ${destroyErr.message}`);
            }
        }

        // 3. Tunggu sebentar agar semua file locks benar-benar terlepas
        await new Promise(resolve => setTimeout(resolve, 2000));

        // 4. Hapus folder session dengan path yang BENAR
        const sessionPath = path.join(process.cwd(), "sessions"); // Perbaikan: hapus "../"
        
        if (fs.existsSync(sessionPath)) {
            // Coba hapus dengan retry mechanism
            let deleteSuccess = false;
            let attempts = 0;
            const maxAttempts = 3;

            while (!deleteSuccess && attempts < maxAttempts) {
                attempts++;
                try {
                    fs.rmSync(sessionPath, { recursive: true, force: true });
                    deleteSuccess = true;
                    console.log("🧹 Session folder deleted successfully");
                    saveLog("Session folder deleted successfully");
                } catch (rmErr) {
                    console.error(`⚠️ Attempt ${attempts} to delete sessions failed:`, rmErr.message);
                    saveLog(`Delete attempt ${attempts} failed: ${rmErr.message}`);
                    
                    if (attempts < maxAttempts) {
                        // Tunggu sebentar sebelum retry
                        await new Promise(resolve => setTimeout(resolve, 1000));
                    }
                }
            }

            if (!deleteSuccess) {
                console.error("❌ Failed to delete sessions folder after multiple attempts");
                saveLog("Failed to delete sessions folder after multiple attempts");
            }
        } else {
            console.log("ℹ️ Sessions folder not found, skipping deletion");
            saveLog("Sessions folder not found");
        }

        console.log("✅ Cleanup finished, shutting down gracefully...");
        saveLog("Cleanup finished, shutting down gracefully...");

        // Tunggu agar semua logs tersimpan
        await new Promise(resolve => setTimeout(resolve, 1000));
        process.exit(0);

    } catch (err) {
        console.error("❌ Failed during shutdown:", err.message);
        saveLog(`Shutdown error: ${err.message}`);
        
        // Tetap coba exit meskipun ada error
        setTimeout(() => process.exit(1), 1000);
    }
}

// Event handlers
process.on("SIGINT", () => handleShutdown("SIGINT"));
process.on("SIGTERM", () => handleShutdown("SIGTERM"));
process.on("uncaughtException", (err) => {
    console.error("💥 Uncaught exception:", err);
    saveLog(`Uncaught exception: ${err.message}`);
    handleShutdown("uncaughtException");
});

// Tambahan: Handle unhandled promise rejections
process.on("unhandledRejection", (reason, promise) => {
    console.error("💥 Unhandled Rejection at:", promise, "reason:", reason);
    saveLog(`Unhandled rejection: ${reason}`);
    handleShutdown("unhandledRejection");
});