# 🎰 SlotMania - Situs Slot Online

Situs slot online modern dengan desain responsif menggunakan Tailwind CSS.

## 🚀 Fitur

- ✨ Desain modern dengan Tailwind CSS
- 🎮 Demo slot machine interaktif
- 📱 Responsive design (mobile-friendly)
- 🎨 Animasi dan efek visual menarik
- 🔔 Sistem notifikasi kemenangan
- 🎯 Interface yang user-friendly

## 🛠️ Teknologi

- HTML5
- Tailwind CSS
- JavaScript (ES6+)
- Font Awesome Icons
- Live Server untuk development

## 📦 Instalasi

1. Clone atau download project ini
2. Install dependencies:
   ```bash
   npm install
   ```

## 🏃‍♂️ Menjalankan Project

### Cara 1: Menggunakan script start.sh
```bash
./start.sh
```

### Cara 2: Manual
```bash
# Build CSS
npx tailwindcss -i ./src/input.css -o ./dist/output.css

# Start server
npx live-server --port=3000
```

### Cara 3: Watch mode untuk development
```bash
# Terminal 1: Watch CSS changes
npm run build-css

# Terminal 2: Start server
npm run dev
```

## 🎮 Cara Bermain Demo Slot

1. Klik tombol "SPIN" atau tekan spacebar
2. Tunggu reel berhenti berputar
3. Lihat kombinasi symbol untuk menang:
   - 💎💎💎 = Jackpot! (1000x bet)
   - 👑👑👑 = Big Win! (500x bet)
   - ⭐⭐⭐ = Great! (300x bet)
   - Dan kombinasi lainnya...

## 📁 Struktur Project

```
slot-website/
├── index.html          # Halaman utama
├── package.json        # Dependencies
├── tailwind.config.js  # Konfigurasi Tailwind
├── start.sh           # Script untuk start server
├── src/
│   ├── input.css      # Tailwind CSS input
│   └── slot.js        # JavaScript logic
└── dist/
    └── output.css     # Compiled CSS
```

## 🎨 Kustomisasi

### Mengubah warna tema:
Edit file `tailwind.config.js` untuk menambah warna custom:
```javascript
colors: {
  'slot-gold': '#FFD700',
  'slot-red': '#DC143C',
  // tambah warna lainnya...
}
```

### Menambah symbol slot:
Edit array symbols di `src/slot.js`:
```javascript
this.symbols = ['🍒', '🍋', '🔔', '💎', '👑', '🍀', '⭐', '💰'];
```

## 📱 Responsive Design

Website ini fully responsive dan optimal di:
- 📱 Mobile phones
- 📱 Tablets
- 💻 Desktop
- 🖥️ Large screens

## 🤝 Contributing

1. Fork project ini
2. Buat feature branch
3. Commit changes
4. Push ke branch
5. Buat Pull Request

## 📄 License

MIT License - bebas digunakan untuk project pribadi maupun komersial.

## ⚠️ Disclaimer

Project ini dibuat untuk tujuan educational dan portfolio. Tidak dimaksudkan untuk gambling yang sesungguhnya.

---

**Happy Coding! 🎰✨**