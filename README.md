# 🏦 سیستم پرداخت خودکار کارت به کارت

سیستم حرفه‌ای مدیریت پرداخت‌های کارت به کارت با تایید آنی، شناسه یکتا (۱ تا ۹۹۹۹) و مدیریت چند کارت بانکی.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![Version](https://img.shields.io/badge/version-2.0.0-blue)

---

## ✨ ویژگی‌ها

- ⚡ تایید آنی پرداخت
- 🔢 شناسه یکتا برای هر تراکنش (۱ تا ۹۹۹۹)
- 💳 چرخش خودکار بین چند کارت بانکی
- ⏰ اعتبار ۶۰ دقیقه‌ای هر تراکنش
- 📦 آرشیو خودکار شبانه
- 🔐 محافظت با API Key
- 📱 سازگار با MacroDroid و SMS Forwarder (GET)

---

## 📁 ساختار پروژه

```
├── config.php      # تنظیمات مرکزی (API Key، کارت‌ها)
├── create.php      # ساخت تراکنش جدید
├── add.php         # دریافت پیامک واریز (GET)
├── check.php       # مچ کردن خودکار تراکنش‌ها
├── verify.php      # بررسی وضعیت تراکنش
├── cleanup.php     # آرشیو شبانه (Cron Job)
├── data.json       # تراکنش‌های فعال
├── archive.json    # تراکنش‌های آرشیو شده
├── messages.json   # پیامک‌های دریافتی
└── .htaccess       # محافظت فایل‌ها
~~~

---

## 🚀 نصب

### ۱. تنظیم `config.php`

```php
define('API_KEY', 'یک_کلید_تصادفی_طولانی_و_امن');

define('CARDS', json_encode([
    ['number' => '6219-8610-xxxx-xxxx', 'bank' => 'بلو بانک', 'owner' => 'نام شما'],
    ['number' => '6104-3378-xxxx-xxxx', 'bank' => 'بانک ملت', 'owner' => 'نام شما']
]));
```

### ۲. تنظیم دسترسی‌ها

```bash
chmod 755 /path/to/project/
chmod 644 *.php *.json
```

---

## 📥 دریافت پیامک (`add.php`)

این endpoint از متد **GET** پشتیبانی می‌کند. پیامک را از طریق **MacroDroid** یا **SMS Forwarder** به صورت GET ارسال کنید.

### نمونه URL:

~~~
/add.php?api_key=YOUR_API_KEY&sms_body=بلو%0Aواریز+پول%0A+محمدمهدی+عزیز،+100001+ریال+به+حساب+شما+نشست.%0A+موجودی:+19,200,871+ریال%0A۲۳:۱۸%0A۱۴۰۵.۰۶.۰۶
```

### تنظیم MacroDroid:

1. **Trigger:** SMS Received → From: `BluBank`
2. **Action:** HTTP Request
   - Method: `GET`
   - URL: `/add.php?api_key=YOUR_API_KEY&sms_body=[sms_content]`

### تنظیم SMS Forwarder:

1. **Filter:** Sender contains `BluBank`
2. **Forward to:** HTTP GET
3. **URL:** `/add.php?api_key=YOUR_API_KEY&sms_body=[message]`

---

## 🔌 API Reference

### `create.php` — ساخت تراکنش `POST`

| پارامتر | نوع | الزامی | توضیحات |
|---------|-----|--------|---------|
| `api_key` | string | ✅ | کلید API |
| `chat_id` | string | ✅ | آیدی کاربر |
| `name` | string | ✅ | نام کاربر |
| `amount` | integer | ✅ | مبلغ |
| `type` | string | ❌ | `rial` یا `tmn` (پیش‌فرض: rial) |

#### PHP

```php
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

echo $response['callback']['card_number'];
echo $response['callback']['amount_to_pay_rial'];
```

#### Python

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

print(data["callback"]["card_number"])
print(data["callback"]["amount_to_pay_rial"])
```

#### JavaScript

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

console.log(data.callback.card_number);
console.log(data.callback.amount_to_pay_rial);
```

#### Go

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

#### پاسخ موفق

```json
{
  "success": true,
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

### `add.php` — دریافت پیامک `GET`

| پارامتر | نوع | الزامی | توضیحات |
|---------|-----|--------|---------|
| `api_key` | string | ✅ | کلید API |
| `sms_body` | string | ✅ | متن کامل پیامک |

#### PHP

```php
$smsBody = "بلو\nواریز پول\n محمدمهدی عزیز، 100001 ریال به حساب شما نشست.\n موجودی: 19,200,871 ریال\n۲۳:۱۸\n۱۴۰۵.۰۶.۰۶";

$url = "/add.php?" . http_build_query([
    'api_key'  => 'YOUR_API_KEY',
    'sms_body' => $smsBody
]);

$response = json_decode(file_get_contents($url), true);
```

#### Python

```python
import requests

sms_body = "بلو\nواریز پول\n محمدمهدی عزیز، 100001 ریال به حساب شما نشست.\n موجودی: 19,200,871 ریال\n۲۳:۱۸\n۱۴۰۵.۰۶.۰۶"

response = requests.get("/add.php", params={
    "api_key": "YOUR_API_KEY",
    "sms_body": sms_body
})
print(response.json())
```

#### JavaScript

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

#### Go

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

---

### `check.php` — مچ کردن خودکار `GET`

بدون پارامتر. توسط Cron Job اجرا می‌شود.

```bash
curl /check.php
```

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

### `verify.php` — بررسی وضعیت `GET`

| پارامتر | نوع | الزامی | توضیحات |
|---------|-----|--------|---------|
| `api_key` | string | ✅ | کلید API |
| `hangt_id` | string | ✅ | شناسه تراکنش |

#### PHP

```php
$url = "/verify.php?" . http_build_query([
    'api_key'  => 'YOUR_API_KEY',
    'hangt_id' => '1'
]);
$data = json_decode(file_get_contents($url), true);
echo $data['status']; // paid | pending | expired
```

#### Python

```python
import requests

data = requests.get("/verify.php", params={
    "api_key": "YOUR_API_KEY",
    "hangt_id": "1"
}).json()

print(data["status"])
```

#### JavaScript

```javascript
const params = new URLSearchParams({ api_key: 'YOUR_API_KEY', hangt_id: '1' });
const res = await fetch(`/verify.php?${params}`);
const data = await res.json();
console.log(data.status);
```

#### Go

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

---

### `cleanup.php` — آرشیو شبانه `GET`

```bash
curl "/cleanup.php?api_key=YOUR_API_KEY"
```

```json
{
  "success": true,
  "archived_count": 45,
  "remaining_active": 12,
  "reset_tracking_codes": false
}
```

---

## ⏰ Cron Job

```cron
# مچ کردن خودکار - هر ۱ دقیقه
* * * * * php /home/username/public_html/check.php

# آرشیو شبانه - هر روز ساعت ۰۰:۰۰
0 0 * * * curl "/cleanup.php?api_key=YOUR_API_KEY"
```

---

## 🔐 امنیت

### `.htaccess`

```apache
<FilesMatch "\.(json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

### تولید API Key

```php
echo bin2hex(random_bytes(32));
```

---

## 🔧 عیب‌یابی

| مشکل | علت | راه‌حل |
|------|-----|--------|
| `دسترسی غیرمجاز` | API Key اشتباه | `config.php` را بررسی کنید |
| `تراکنش یافت نشد` | شناسه اشتباه یا منقضی | از `debug_info` در پاسخ استفاده کنید |
| `پیامک مچ نمی‌شود` | مبلغ متفاوت یا منقضی | `data.json` و `messages.json` را بررسی کنید |
| `فایل JSON خالی` | عدم دسترسی نوشتن | `chmod 755` روی پوشه |

---

## ❓ سوالات متداول

**حداکثر چند تراکنش در روز؟**
با هر کارت ۹۹۹۹ تراکنش. با چند کارت، ضربدر تعداد کارت‌ها.

**اگر کاربر مبلغ اشتباه واریز کرد؟**
مچ نمی‌شود و بعد از ۶۰ دقیقه منقضی می‌شود.

**چگونه به MySQL مهاجرت کنم؟**
برای بیش از ۱۰۰۰ تراکنش روزانه، به MySQL یا SQLite مهاجرت کنید.

---

## 📄 لایسنس

MIT License
