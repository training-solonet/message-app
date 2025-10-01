<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class ManageController extends Controller
{
    private $pidFile;

    public function __construct()
    {
        $this->pidFile = storage_path('bot.pid');
    }

    public function index()
    {
        $status = 'stopped';
        $pid = null;

        if (File::exists($this->pidFile)) {
            $pid = trim(File::get($this->pidFile));
            $running = false;

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Cek proses Windows
                $output = [];
                exec("tasklist /FI \"PID eq $pid\" /NH", $output);
                // Jika baris output ada dan tidak kosong, berarti proses berjalan
                $running = isset($output[0]) && !empty($output[0]) && stripos($output[0], (string)$pid) !== false;
            } else {
                // Linux/Mac: posix_kill dengan signal 0 untuk cek keberadaan proses
                $running = posix_kill((int)$pid, 0);
            }

            if ($running) {
                $status = 'running';
            } else {
                $status = 'stopped';
                File::delete($this->pidFile);
                $pid = null;
            }
        }

        $schedules = Schedule::all();
        $contacts = Contact::all();

        return view('manage', compact('status', 'pid', 'schedules', 'contacts'));
    }

    public function startBot()
    {
        if (File::exists($this->pidFile)) {
            return redirect()->route('manage.index')->with('message', '⚠️ Bot sudah berjalan.');
        }

        try {
            $process = new Process(['node', 'index.js'], base_path('whatsapp-bot'));
            $process->disableOutput();
            $process->start();

            // Tunggu sebentar agar proses benar-benar berjalan
            usleep(500000); // 0.5 detik

            $pid = $process->getPid();

            if (!$pid) {
                throw new \Exception("Gagal mendapatkan PID proses Node.");
            }

            File::put($this->pidFile, $pid);

            return redirect()->route('manage.index')->with('message', '✅ Bot berhasil dijalankan.');
        } catch (\Exception $e) {
            return redirect()->route('manage.index')->with('message', '❌ Gagal menjalankan bot: ' . $e->getMessage());
        }
    }

    public function stopBot()
    {
        if (!File::exists($this->pidFile)) {
            return redirect()->route('manage.index')->with('message', '⚠️ Bot tidak berjalan.');
        }

        $pid = trim(File::get($this->pidFile));

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec("taskkill /F /PID $pid 2>NUL");
            } else {
                exec("kill -9 $pid 2>/dev/null");
            }

            File::delete($this->pidFile);

            return redirect()->route('manage.index')->with('message', '🛑 Bot berhasil dihentikan.');
        } catch (\Exception $e) {
            return redirect()->route('manage.index')->with('message', '❌ Gagal menghentikan bot: ' . $e->getMessage());
        }
    }
}
