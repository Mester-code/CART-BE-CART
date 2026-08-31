<div align="center">

# 🏦 سیستم پرداخت خودکار کارت به کارت

### مدیریت حرفهای پرداختهای کارتبهکارت با تایید آنی و شناسه یکتا

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.0.0-3498db?style=for-the-badge)]()
[![License](https://img.shields.io/badge/License-MIT-27ae60?style=for-the-badge)]()
[![Status](https://img.shields.io/badge/Status-Production%20Ready-e74c3c?style=for-the-badge)]()

</div>

---

## 📑 فهرست مطالب

- [✨ ویژگیها](#-ویژگیها)
- [📁 ساختار پروژه](#-ساختار-پروژه)
- [🚀 نصب و راهاندازی](#-نصب-و-راهاندازی)
- [📥 دریافت پیامک (add.php)](#-دریافت-پیامک-addphp)
- [🔌 API Reference](#-api-reference)
  - [create.php](#1-createphp--ساخت-تراکنش)
  - [add.php](#2-addphp--دریافت-پیامک)
  - [check.php](#3-checkphp--مچ-کردن-خودکار)
  - [verify.php](#4-verifyphp--بررسی-وضعیت)
  - [cleanup.php](#5-cleanupphp--آرشیو-شبانه)
- [⏰ Cron Job](#-cron-job)
- [🔐 امنیت](#-امنیت)
- [🔧 عیبیابی](#-عیبیابی)
- [❓ سوالات متداول](#-سوالات-متداول)

---

## ✨ ویژگیها

<table>
<tr>
<td width="50%">

### 🎯 هسته سیستم
- ⚡ **تایید آنی** پرداخت به محض واریز
- 🔢 **شناسه یکتا** (۱ تا ۹۹۹۹)
- 💳 **چرخش خودکار** بین چند کارت بانکی (فعلا کارت های بلوبانک)
- ⏰ **اعتبار ۶۰ دقیقهای** هر تراکنش

</td>
<td width="50%">

### 🛡️ امکانات جانبی
- 📦 **آرشیو خودکار** شبانه
- 🔐 **محافظت** با API Key
- 📱 **سازگار** با MacroDroid و SMS Forwarder
- 🌐 **پشتیبانی** از GET و POST

</td>
</tr>
</table>

---

## 📁 ساختار پروژه

```bash
project/
│
├── 📄 config.php          # تنظیمات مرکزی (API Key، کارتها)
├── 📄 create.php          # ساخت تراکنش جدید
├── 📄 add.php             # دریافت پیامک واریز (GET)
├── 📄 check.php           # مچ کردن خودکار تراکنشها
├── 📄 verify.php          # بررسی وضعیت تراکنش
├── 📄 cleanup.php         # آرشیو شبانه (Cron Job)
│
├── 🗃️ data.json           # تراکنشهای فعال
├── 🗃️ archive.json        # تراکنشهای آرشیو شده
├── 🗃️ messages.json       # پیامکهای دریافتی
│
└── 🔒 .htaccess           # محافظت فایلها
```

---

## 🚀 نصب و راهاندازی

### ۱️⃣ تنظیم `config.php`

```php
<?php
// 🔐 کلید API (حتماً تغییر دهید)
define('API_KEY', 'یک_کلید_تصادفی_طولانی_و_امن');

// 💳 اطلاعات کارتهای بانکی
define('CARDS', json_encode([
    [
        'number' => '6219-8610-xxxx-xxxx',
        'bank'   => 'بلو بانک',
        'owner'  => 'نام شما'
    ]
]));
```

### ۲️⃣ تنظیم دسترسیها

```bash
chmod 755 /path/to/project/
chmod 644 *.php *.json
```

### ۳️⃣ تولید API Key امن

```php
<?php
echo bin2hex(random_bytes(32));
// خروجی: a3f5c8e9d2b1f4a7c6e8d9b2a5f7c4e1d8b3a6f9c2e5d8b1a4f7c0e3d6b9a2
```

---

## 📥 دریافت پیامک (`add.php`)

> [!IMPORTANT]
> این endpoint از متد **GET** پشتیبانی میکند. پیامک را از طریق **MacroDroid** یا **SMS Forwarder** به صورت GET ارسال کنید.

### 📱 تنظیم MacroDroid

| مرحله | تنظیمات |
|-------|---------|
| **Trigger** | SMS Received → From: `BluBank` |
| **Action** | HTTP Request |
| **Method** | `GET` |
| **URL** | `/add.php?api_key=YOUR_API_KEY&sms_body=[sms_content]` |

### 📲 تنظیم SMS Forwarder

| مرحله | تنظیمات |
|-------|---------|
| **Filter** | Sender contains `BluBank` |
| **Forward to** | HTTP GET |
| **URL** | `/add.php?api_key=YOUR_API_KEY&sms_body=[message]` |

### 🔗 نمونه URL

```
/add.php?api_key=YOUR_API_KEY&sms_body=بلو%0Aواریز+پول%0A+محمدمهدی+عزیز،+100001+ریال+به+حساب+شما+نشست.%0A+موجودی:+19,200,871+ریال%0A۲۳:۱۸%0A۱۴۰۵.۰۶.۰۶
```

---

## 🔌 API Reference

### 1️⃣ `create.php` — ساخت تراکنش

<div align="center">

**Method:** `POST`

</div>

#### 📋 پارامترها

| پارامتر | نوع | الزامی | توضیحات |
|:--------|:----|:------:|:--------|
| `api_key` | `string` | ✅ | کلید API از config.php |
| `chat_id` | `string` | ✅ | آیدی کاربر (تلگرام یا سیستم) |
| `name` | `string` | ✅ | نام کاربر |
| `amount` | `integer` | ✅ | مبلغ (ریال یا تومان) |
| `type` | `string` | ❌ | `rial` یا `tmn` (پیشفرض: `rial`) |

#### 💻 نمونه کد

<details>
<summary><b>🐘 PHP</b></summary>

```php
<?php
$ch = curl_init('/create.php');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'api_key' => 'YOUR_API_KEY',
        'chat_id' => '123456789',
        'name'    => 'علی محمدی',
        'amount'  => 100000,
        'type'    => 'rial'
    ])
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['success']) {
    echo "کارت: " . $response['callback']['card_number'];
    echo "مبلغ: " . $response['callback']['amount_to_pay_rial'] . " ریال";
}
```

</details>

<details>
<summary><b>🐍 Python</b></summary>

```python
import requests

response = requests.post("/create.php", data={
    "api_key": "YOUR_API_KEY",
    "chat_id": "123456789",
    "name": "علی محمدی",
    "amount": 100000,
    "type": "rial"
})
data = response.json()

if data["success"]:
    print("کارت:", data["callback"]["card_number"])
    print("مبلغ:", data["callback"]["amount_to_pay_rial"])
```

</details>

<details>
<summary><b>🟨 JavaScript</b></summary>

```javascript
const formData = new URLSearchParams({
    api_key: 'YOUR_API_KEY',
    chat_id: '123456789',
    name: 'علی محمدی',
    amount: '100000',
    type: 'rial'
});

const res = await fetch('/create.php', {
    method: 'POST',
    body: formData
});
const data = await res.json();

if (data.success) {
    console.log('کارت:', data.callback.card_number);
    console.log('مبلغ:', data.callback.amount_to_pay_rial);
}
```

</details>

<details>
<summary><b>🔷 Go</b></summary>

```go
package main

import (
    "encoding/json"
    "fmt"
    "net/http"
    "net/url"
    "strings"
)

type CreateResponse struct {
    Success  bool `json:"success"`
    Callback struct {
        CardNumber      string `json:"card_number"`
        AmountToPayRial int    `json:"amount_to_pay_rial"`
        TrackingCode    int    `json:"tracking_code"`
    } `json:"callback"`
}

func main() {
    form := url.Values{}
    form.Set("api_key", "YOUR_API_KEY")
    form.Set("chat_id", "123456789")
    form.Set("name", "علی محمدی")
    form.Set("amount", "100000")
    form.Set("type", "rial")

    resp, _ := http.Post(
        "/create.php",
        "application/x-www-form-urlencoded",
        strings.NewReader(form.Encode()),
    )
    defer resp.Body.Close()

    var result CreateResponse
    json.NewDecoder(resp.Body).Decode(&result)

    fmt.Println("کارت:", result.Callback.CardNumber)
    fmt.Println("مبلغ:", result.Callback.AmountToPayRial)
}
```

</details>

#### ✅ پاسخ موفق

```json
{
  "success": true,
  "message": "تراکنش با موفقیت ایجاد شد",
  "callback": {
    "card_number": "6219-8610-xxxx-xxxx",
    "card_bank": "بلو بانک",
    "card_owner": "نام شما",
    "amount_to_pay_rial": 100001,
    "amount_to_pay_toman": 10000.1,
    "tracking_code": 1,
    "expires_at": "2026-08-31 15:30:00",
    "expires_in_minutes": 60
  }
}
```

---

### 2️⃣ `add.php` — دریافت پیامک

<div align="center">

**Method:** `GET`

</div>

#### 📋 پارامترها

| پارامتر | نوع | الزامی | توضیحات |
|:--------|:----|:------:|:--------|
| `api_key` | `string` | ✅ | کلید API |
| `sms_body` | `string` | ✅ | متن کامل پیامک بانکی |

#### 💻 نمونه کد

<details>
<summary><b>🐘 PHP</b></summary>

```php
<?php
$smsBody = "بلو\nواریز پول\n محمدمهدی عزیز، 100001 ریال به حساب شما نشست.\n موجودی: 19,200,871 ریال\n۲۳:۱۸\n۱۴۰۵.۰۶.۰۶";

$url = "/add.php?" . http_build_query([
    'api_key'  => 'YOUR_API_KEY',
    'sms_body' => $smsBody
]);

$response = json_decode(file_get_contents($url), true);
```

</details>

<details>
<summary><b>🐍 Python</b></summary>

```python
import requests

sms_body = """بلو
واریز پول
 محمدمهدی عزیز، 100001 ریال به حساب شما نشست.
 موجودی: 19,200,871 ریال
۲۳:۱۸
۱۴۰۵.۰۶.۰۶"""

response = requests.get("/add.php", params={
    "api_key": "YOUR_API_KEY",
    "sms_body": sms_body
})
print(response.json())
```

</details>

<details>
<summary><b>🟨 JavaScript</b></summary>

```javascript
const smsBody = `بلو
واریز پول
 محمدمهدی عزیز، 100001 ریال به حساب شما نشست.
 موجودی: 19,200,871 ریال
۲۳:۱۸
۱۴۰۵.۰۶.۰۶`;

const params = new URLSearchParams({
    api_key: 'YOUR_API_KEY',
    sms_body: smsBody
});

const res = await fetch(`/add.php?${params}`);
console.log(await res.json());
```

</details>

<details>
<summary><b>🔷 Go</b></summary>

```go
package main

import (
    "fmt"
    "net/http"
    "net/url"
)

func main() {
    params := url.Values{}
    params.Set("api_key", "YOUR_API_KEY")
    params.Set("sms_body", "بلو\nواریز پول\n محمدمهدی عزیز، 100001 ریال به حساب شما نشست.")

    resp, _ := http.Get("/add.php?" + params.Encode())
    defer resp.Body.Close()

    fmt.Println("Status:", resp.Status)
}
```

</details>

---

### 3️⃣ `check.php` — مچ کردن خودکار

<div align="center">

**Method:** `GET` • **بدون پارامتر** • **توسط Cron Job اجرا میشود**

</div>

```bash
curl /check.php
```

#### ✅ پاسخ موفق

```json
{
  "success": true,
  "message": "1 تراکنش با موفقیت مچ و تایید شد.",
  "details": [
    "تراکنش TX_1693500000_1234 کاربر علی محمدی با مبلغ 100001 ریال تایید شد."
  ]
}
```

---

### 4️⃣ `verify.php` — بررسی وضعیت

<div align="center">

**Method:** `GET`

</div>

#### 📋 پارامترها

| پارامتر | نوع | الزامی | توضیحات |
|:--------|:----|:------:|:--------|
| `api_key` | `string` | ✅ | کلید API |
| `hangt_id` | `string` | ✅ | شناسه یکتای تراکنش |

#### 💻 نمونه کد

<details>
<summary><b>🐘 PHP</b></summary>

```php
<?php
$url = "/verify.php?" . http_build_query([
    'api_key'  => 'YOUR_API_KEY',
    'hangt_id' => '1'
]);
$data = json_decode(file_get_contents($url), true);

echo $data['status']; // paid | pending | expired
```

</details>

<details>
<summary><b>🐍 Python</b></summary>

```python
import requests

data = requests.get("/verify.php", params={
    "api_key": "YOUR_API_KEY",
    "hangt_id": "1"
}).json()

print(data["status"])  # paid | pending | expired
```

</details>

<details>
<summary><b>🟨 JavaScript</b></summary>

```javascript
const params = new URLSearchParams({
    api_key: 'YOUR_API_KEY',
    hangt_id: '1'
});

const res = await fetch(`/verify.php?${params}`);
const data = await res.json();

console.log(data.status); // paid | pending | expired
```

</details>

<details>
<summary><b>🔷 Go</b></summary>

```go
package main

import (
    "encoding/json"
    "fmt"
    "net/http"
)

type VerifyResponse struct {
    Success bool   `json:"success"`
    Status  string `json:"status"`
    Message string `json:"message"`
}

func main() {
    resp, _ := http.Get("/verify.php?api_key=YOUR_API_KEY&hangt_id=1")
    defer resp.Body.Close()

    var result VerifyResponse
    json.NewDecoder(resp.Body).Decode(&result)

    fmt.Println("وضعیت:", result.Status)
}
```

</details>

#### 📊 وضعیتهای ممکن

| وضعیت | توضیحات |
|:------|:--------|
| `paid` | ✅ پرداخت با موفقیت انجام شده |
| `pending` | ⏳ در انتظار پرداخت |
| `expired` | ❌ زمان تراکنش منقضی شده (۶۰ دقیقه) |

---

### 5️⃣ `cleanup.php` — آرشیو شبانه

<div align="center">

**Method:** `GET` • **توسط Cron Job اجرا میشود**

</div>

```bash
curl "/cleanup.php?api_key=YOUR_API_KEY"
```

#### ✅ پاسخ موفق

```json
{
  "success": true,
  "message": "عملیات آرشیو با موفقیت انجام شد",
  "archived_count": 45,
  "remaining_active": 12,
  "reset_tracking_codes": false
}
```

---

## ⏰ Cron Job

### تنظیم در cPanel

```cron
# 🔄 مچ کردن خودکار - هر ۱ دقیقه
* * * * * php /home/username/public_html/check.php

# 🗑️ آرشیو شبانه - هر روز ساعت ۰۰:۰۰
0 0 * * * curl "/cleanup.php?api_key=YOUR_API_KEY"
```

> [!NOTE]
> مسیر فایل را با مسیر واقعی هاست خود جایگزین کنید.

---

## 🔐 امنیت

### محافظت فایلهای حساس

فایل `.htaccess` زیر را در ریشه پروژه قرار دهید:

```apache
# 🚫 محافظت از فایلهای JSON
<FilesMatch "\.(json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# 🔒 محافظت از فایل تنظیمات
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

# 🛡️ محدود کردن دسترسی به check.php (اختیاری)
<Files "check.php">
    Order deny,allow
    Deny from all
    Allow from 127.0.0.1
</Files>
```

### نکات امنیتی

- ✅ حتماً از **HTTPS** استفاده کنید
- ✅ API Key را **هر ۳ ماه** تغییر دهید
- ✅ فایلهای JSON را **هر شب بکاپ** بگیرید
- ✅ دسترسی نوشتن پوشه را محدود کنید (`chmod 755`)

---

## 🔧 عیبیابی

<table>
<thead>
<tr>
<th width="25%">❌ مشکل</th>
<th width="35%">🔍 علت</th>
<th width="40%">✅ راهحل</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>دسترسی غیرمجاز</code></td>
<td>API Key اشتباه یا ارسال نشده</td>
<td>مقدار <code>config.php</code> را بررسی کنید</td>
</tr>
<tr>
<td><code>تراکنش یافت نشد</code></td>
<td>شناسه اشتباه یا منقضی شده</td>
<td>از <code>debug_info</code> در پاسخ استفاده کنید</td>
</tr>
<tr>
<td><code>پیامک مچ نمیشود</code></td>
<td>مبلغ متفاوت یا تراکنش منقضی</td>
<td><code>data.json</code> و <code>messages.json</code> را بررسی کنید</td>
</tr>
<tr>
<td><code>فایل JSON خالی</code></td>
<td>عدم دسترسی نوشتن به پوشه</td>
<td><code>chmod 755</code> را روی پوشه اجرا کنید</td>
</tr>
<tr>
<td><code>Parse error</code></td>
<td>نسخه PHP قدیمی</td>
<td>PHP 7.4 یا بالاتر نصب کنید</td>
</tr>
</tbody>
</table>

---

## ❓ سوالات متداول

<details>
<summary><b>🔹 حداکثر چند تراکنش در روز میتوان داشت؟</b></summary>
<br>
با هر کارت بانکی <b>۹۹۹۹ تراکنش</b>. با چند کارت، این عدد ضربدر تعداد کارتها میشود.
</details>

<details>
<summary><b>🔹 آیا میتوانم از چند کارت بانکی استفاده کنم؟</b></summary>
<br>
بله، در <code>config.php</code> میتوانید چندین کارت تعریف کنید. سیستم به صورت خودکار وقتی یک کارت پر شد، به کارت بعدی میرود.
</details>

<details>
<summary><b>🔹 اگر کاربر مبلغ را اشتباه واریز کرد چه میشود؟</b></summary>
<br>
تراکنش مچ نمیشود و پس از <b>۶۰ دقیقه</b> منقضی میشود. کاربر باید دوباره درخواست دهد.
</details>

<details>
<summary><b>🔹 چگونه میتوانم به MySQL مهاجرت کنم؟</b></summary>
<br>
برای بیش از <b>۱۰۰۰ تراکنش روزانه</b>، پیشنهاد میکنیم به MySQL یا SQLite مهاجرت کنید.
</details>

<details>
<summary><b>🔹 آیا این سیستم قانونی است؟</b></summary>
<br>
استفاده از کارت به کارت شخصی برای کسبوکار ممکن است ریسکهای مالیاتی و بانکی داشته باشد. برای حجم بالا، از <b>درگاه پرداخت رسمی</b> استفاده کنید.
</details>

---

<div align="center">

### 🎉 ساخته شده با ❤️

**نسخه 2.0.0** • **۱۴۰۵** • **MIT License**

</div>
