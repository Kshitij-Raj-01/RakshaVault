
# 🛡️ RakshaVault – Secure File Storage System


**RakshaVault** is a **high-security file storage system** designed with advanced features such as encryption, AI-generated alerts, steganography, and blockchain-based immutable logs. Built using **PHP and MySQL**, this project is tailor-made for showcasing in **Cybersecurity internships** and high-level government projects.

---

## ✨ Features

- 🔐 **Encrypted File Storage** with expiry control  
- 📩 **OTP & Device-based Login Shield**  
- 🔗 **Time-bound Secure Download Links**  
- 🧠 **AI-generated Access Alerts**  
- 👁️ **Suspicious Activity Detection (IP, Access Pattern)**  
- 🧬 **Blockchain-based Immutable Logs**  
- 🧾 **Steganography & Digital Signature Validation**  
- 📊 **Admin Dashboard** with Charts, Logs, and Controls  
- 🚨 **Emergency "Raksha Mode" Toggle for Crisis Situations**  
- 🧹 **Expired File Cleanup Utility**

---

## 🧰 Requirements

- PHP 7.4+  
- MySQL 5.7+  
- Apache or XAMPP/WAMP/LAMP  
- Composer (optional, for extra dependencies)  
- Chart.js (via CDN)  

---

## ⚙️ Installation & Setup

### 1. 📥 Clone the Repo

```bash
git clone https://github.com/your-username/RakshaVault.git
cd RakshaVault
```

### 2. 🛠️ Setup MySQL

- Create a database named `rakshavault`
- Import the provided SQL file (if any) or create required tables manually
- Update credentials in `config.php`:

```php
$host = 'localhost';
$db   = '';
$user = 'root';
$pass = '';
```

### 3. 🔑 Environment Variables (optional)

You can create a `.env` file for secret configuration (use a loader like `vlucas/phpdotenv`):

```dotenv
DB_HOST=localhost
DB_NAME=
DB_USER=root
DB_PASS=
ENCRYPTION_KEY=securekey
```

### 4. 🌐 Start Server

If using XAMPP/WAMP, place the project inside `htdocs/` and access via:

```
http://localhost/RakshaVault/
```

---

## 📋 Folder Structure

```
RakshaVault/
├── ai_alerts.php
├── cleanup_expired.php
├── config.php
├── dashboard.php
├── login.php
├── logout.php
├── suspicious_ips.php
├── toggle_raksha.php
├── uploads/
├── functions.php
└── README.md
```

---

## 🚀 How to Use

1. Register and login as admin
2. Upload classified files with optional expiry
3. Monitor logs, AI alerts, blockchain entries
4. Detect suspicious users and toggle Raksha mode in emergencies
5. Run cleanup when needed to remove expired files

---

## 📊 Admin Dashboard Preview

- **Real-time Chart** of File Uploads
- **Log Tables** for Access, Blockchain, Suspicious IPs
- **Raksha Mode Toggle Button**
- **AI Alert Feed**  
*(See `/dashboard.php`)*

---

## 🧠 AI Access Pattern Alerts

Access logs are fed into a lightweight AI model (logic-based initially) that detects:

- Multiple failed access attempts
- Unusual time-based patterns
- Multiple IPs from same user

---

## ⚠️ Raksha Mode

Emergency mode that restricts non-admin access, pauses uploads, and enables deep logging. Ideal for breach scenarios.

---

## 🧪 Security Highlights

- AES-256 encryption using `openssl_encrypt`
- Encrypted file links with expiry timestamps
- Blockchain-style file access hash chains
- Device-bound OTP login (planned)
- SHA-based digital signature & steganographic embedding

---

## 📦 Future Enhancements

- Integrate face-recognition & biometric login
- Progressive Web App version
- Full AI-based anomaly scoring engine
- Stealth decoy file uploads for honeypot detection

---

## 🤝 Contribution

Pull requests are welcome. If you have suggestions, feel free to [open an issue](https://github.com/your-username/RakshaVault/issues).

---

## 👨‍💻 Author

Made with 🩵 by [Your Name]  
`Cyber Security Student | Innovator | Dreamer`

---

## 📜 License

This project is licensed under the **MIT License**.  
You're free to use, modify and distribute with attribution.
