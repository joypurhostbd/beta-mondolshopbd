# MondolShopBD — Redis Connection Error Fix Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** `http://mondolshopbd.test/` এর `RedisException: No connection could be made because the target machine actively refused it` error ঠিক করা।

---

## 🔍 সমস্যা বিশ্লেষণ

### সমস্যা ১: Redis চালু নেই (🔴 Critical — এটাই মূল error)

**বর্তমান `.env` সেটিংস:**
```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

**সমস্যা:** তিনটা ড্রাইভারই `redis`-এ set করা, কিন্তু Windows machine-এ Redis server চালু নেই। `redis-cli` command-ও পাওয়া যাচ্ছে না — মানে Redis install-ও করা নেই।

**Error flow:**
1. Browser → `http://mondolshopbd.test/`
2. Laravel boot → Session middleware → `SESSION_DRIVER=redis` → `PhpRedisConnector::connect()` 
3. `redis://127.0.0.1:6379` connection refused → `RedisException` 💥

### সমস্যা ২: composer.json vs vendor mismatch (🟡 Warning)

| ফাইল | Laravel Version |
|---|---|
| `composer.json` | `"laravel/framework": "^13.0"` |
| `composer.lock` | `v9.47.0` |
| `vendor/` (installed) | `9.47.0` |

composer.json Laravel 13 দাবি করছে, কিন্তু `composer update` কখনো run করা হয়নি। Vendor directory-তে এখনো Laravel9.47.0 আছে। **এটা Redis error-এর কারণ না**, কিন্তু আলাদাভাবে handle করা দরকার।

---

## 🛠️ Solution: দুইটা পথ

### পথ A: Redis install করা (Production-ready)
Windows-এ Redis install করে সার্ভিস হিসেবে চালানো। তাহলে `.env`-তে কোনো পরিবর্তন লাগবে না।

### পথ B: .env driver file-এ পরিবর্তন করা (Quick fix for local dev)
Redis ছাড়াই local dev চালানো — Cache=file, Session=file, Queue=sync।

**আমি পথ B recommend করছি** কারণ:
- Local dev-এ Redis বাধ্যতামূলক না
- দ্রুত fix (১ মিনিট)
- Production-এ Redis আলাদাভাবে configure করা যাবে

---

## 📋 Implementation Tasks (পথ B — Quick Fix)

### Task 1: .env driver পরিবর্তন করা

**Objective:** Redis-dependent drivers বদলে file/sync করা

**File:** `.env` (root directory)

**Change:**
```diff
- CACHE_DRIVER=redis
+ CACHE_DRIVER=file

- QUEUE_CONNECTION=redis
+ QUEUE_CONNECTION=sync

- SESSION_DRIVER=redis
+ SESSION_DRIVER=file
```

**Verification:**
```bash
grep -E 'CACHE_DRIVER|SESSION_DRIVER|QUEUE_CONNECTION' .env
# Expected output:
# CACHE_DRIVER=file
# QUEUE_CONNECTION=sync
# SESSION_DRIVER=file
```

---

### Task 2: Cache clear করা

**Objective:** পুরানো Redis cache reference clear করা

**Command:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

> ⚠️ যদি `php artisan` command-ও Redis connect করার চেষ্টা করে, তাহলে আগে `.env` edit করতে হবে (Task 1), তারপর clear commands চালাতে হবে।

---

### Task 3: Browser test করা

**Objective:** Error গেছে কিনা verify করা

**Steps:**
1. Browser-এ `http://mondolshopbd.test/` খুলুন
2. Homepage load হওয়া উচিত — আর `RedisException` আসবে না
3. Login, Cart, Checkout flow test করুন

---

### Task 4 (Optional): পথ A — Redis Install (Production prep)

**যদি Redis দরকার হয় (production/queue workers/real-time features):**

**Windows-এ Redis install:**
```powershell
# Option 1: Chocolatey
choco install redis-64

# Option 2: Memurai (Redis-compatible for Windows)
# Download from: https://www.memurai.com/

# Option 3: WSL2
wsl sudo apt install redis-server
wsl sudo service redis-server start
```

**Verify Redis running:**
```bash
redis-cli ping
# Expected: PONG
```

**তারপর .env-তে ফিরিয়ে আনুন:**
```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## ⚡ Composer Mismatch (আলাদা সমস্যা)

composer.json `^13.0` বলছে কিন্তু vendor-এ `9.47.0` আছে। এটা আলাদা একটা বড় কাজ:

**সতর্কতা:** `composer update` চালালে Laravel 9 → 13 upgrade হবে, যা অনেক breaking change নিয়ে আসবে। **এখন এটা করবেন না** — আগে Redis error fix করুন, তারপর আলাদাভাবে Laravel upgrade plan করুন।

---

## 🎯 Acceptance Criteria

- [ ] `http://mondolshopbd.test/` load হচ্ছে কোনো Redis error ছাড়া
- [ ] Homepage, Login, Products page কাজ করছে
- [ ] `.env`-তে driver values সঠিক
- [ ] `php artisan about` run হচ্ছে error ছাড়া
