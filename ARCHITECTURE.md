# Mikhmon — Arsitektur & Aturan Sinkronisasi UI

## ⚠️ Pola Dual-Render (WAJIB DIBACA)

Mikhmon menggunakan pola **dual-render**: halaman dirender pertama kali oleh PHP,
lalu komponen tertentu **di-replace oleh AJAX** secara otomatis (berkala / on-event).

> **Aturan wajib:** Setiap perubahan UI **harus dilakukan di DUA tempat** —
> file render awal **DAN** file AJAX-nya. Jika hanya salah satu yang diubah,
> tampilan akan kembali ke versi lama setelah AJAX berjalan.

---

## Peta Komponen: Render Awal ↔ AJAX

### Dashboard (`/?session=...` atau `hotspot=dashboard`)

| Element ID | File Render Awal | File AJAX | Parameter | Frekuensi |
|---|---|---|---|---|
| `#r_1` | `dashboard/home.php` | `dashboard/aload.php` | `load=sysresource` | Interval (`$areload` detik) |
| `#r_2` | `dashboard/home.php` | `dashboard/aload.php` | `load=hotspot` | Interval |
| `#r_3` | `dashboard/home.php` | `dashboard/aload.php` | `load=logs` | Interval + load awal |
| `#r_4` | `dashboard/home.php` | `report/livereport.php` | — | Interval (65432 ms) |

### Hotspot Active

| Element ID | File Render Awal | File AJAX | Frekuensi |
|---|---|---|---|
| `#reloadHotspotActive` | halaman hotspot | `hotspot/hotspotactive.php` | Interval |

### Form Harga / Voucher

| Element ID | File Trigger | File AJAX | Trigger |
|---|---|---|---|
| `#GetValidPrice` | `hotspot/adduser.php` | `process/getvalidprice.php` | On change |
| `#GetValidPrice` | `hotspot/generateuser.php` | `process/getvalidprice.php` | On change |
| `#GetValidPrice` | `hotspot/listquickprint.php` | `process/getvalidprice.php` | On load |

---

## Bagaimana AJAX Dipanggil

Semua pemanggilan AJAX dashboard ada di **`index.php`** (sekitar baris 513–565):

```js
// Dashboard — dipanggil saat halaman dashboard dibuka
$("#r_3").load("./dashboard/aload.php?session=XXX&load=logs #r_3");

// Interval reload (setiap $areload detik)
var dashboard = setInterval(function() {
    $("#r_1").load("./dashboard/aload.php?session=XXX&load=sysresource #r_1");
    $("#r_2").load("./dashboard/aload.php?session=XXX&load=hotspot #r_2");
    $("#r_3").load("./dashboard/aload.php?session=XXX&load=logs #r_3");
}, interval1);

// Live report interval (setiap 65432 ms)
var livereport = setInterval(function() {
    $("#r_4").load("./report/livereport.php?session=XXX #r_4");
}, interval2);
```

---

## Struktur `dashboard/aload.php`

File ini menangani **3 section** berdasarkan parameter `?load=`:

```
load=sysresource  → render #r_1 (Date/Time, Board info, CPU/Memory/HDD)
load=hotspot      → render #r_2 (Hotspot active count, user count)
load=logs         → render #r_3 (Hotspot log table)
```

---

## Checklist Perubahan UI

Sebelum menyimpan perubahan UI, pastikan:

- [ ] Ubah file render awal (misal `dashboard/home.php`)
- [ ] Ubah file AJAX yang sesuai (misal `dashboard/aload.php` pada section yang relevan)
- [ ] Uji: buka halaman di browser → tunggu sampai interval AJAX selesai berjalan → pastikan tampilan tetap konsisten dan tidak kembali ke versi lama

---

## File-File Penting

| File | Fungsi |
|---|---|
| `index.php` | Entry point utama, penentu routing, dan pemanggilan AJAX (`$.load()`) |
| `dashboard/home.php` | Render awal seluruh isi dashboard |
| `dashboard/aload.php` | AJAX handler untuk auto-refresh data dashboard |
| `report/livereport.php` | AJAX handler untuk live report pendapatan/voucher |
| `hotspot/hotspotactive.php` | AJAX handler untuk tabel user aktif |
| `process/getvalidprice.php` | AJAX handler untuk fetch harga voucher berdasarkan profil |
| `isolir.php` | Halaman tampilan isolir pelanggan |
| `isolir-check.php` | Middleware cek status aktif/isolir via server billing |
| `status.json` | Fallback cache status lokal (jika billing server offline) |

---

## Catatan Isolir / Billing
- `isolir-check.php` dapat di-`require_once` di awal halaman yang ingin diproteksi.
- Di lingkungan lokal, file `status.json` dengan `"status": "aktif"` bertindak sebagai fallback saat billing server lokal tidak aktif.
