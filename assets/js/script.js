function tampilkanPopup() {
  Swal.fire({
    title: 'Tata Cara Pembayaran',
    html: `
      <div style="text-align: left; font-size: 15px;">
        <div style="text-align: center; margin-bottom: 15px;">
          <img src="assets/img/bri.png" alt="Logo BRI" style="width: 250px;">
        </div>
        <p>1. Transfer ke <strong>BRI : 498501014052536</strong> (a.n. IPA MUSDALIPA)</p>
        <p>2. Nominal <strong class="blink">Rp15.000/bulan</strong></p>
        <p>3. Kirim bukti via WhatsApp</p>
        <div style="margin-top: 15px; text-align: center;">
          <a href="https://wa.me/6282399430312?text=Halo%2C+saya+sudah+melakukan+pembayaran"
             target="_blank"
             class="swal2-confirm-btn-custom">
            <img src="https://img.icons8.com/color/48/000000/whatsapp.png" alt="WA" style="width:20px; vertical-align:middle;">
            <span style="margin-left: 8px;">Konfirmasi via WhatsApp</span>
          </a>
        </div>
      </div>
    `,
    icon: 'info',
    showConfirmButton: false,
    showCancelButton: true,
    cancelButtonText: 'Tutup',
    cancelButtonColor: '#d33'
  });
}

