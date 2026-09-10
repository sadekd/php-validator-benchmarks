# 🏎️ PHP 8.3 Validation Benchmark

> Benchmarking **9 validation approaches** across **200,000 product items** on PHP 8.3.33

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.3.33 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.3-cli-alpine`) |
| 📅 **Date** | 2026-09-10 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `cuyz/valinor` | 2.6.0 |
| `illuminate/validation` | 12.69.2 |
| `respect/validation` | 2.5.0 |
| `symfony/validator` | 7.4.18 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.009s | 23,080,990/s | 35.8% | 1.0x |
| 🥈 | **Value Objects** | 0.034s | 5,885,074/s | 15.5% | 3.9x slower |
| 🥉 | **webmozart/assert** | 0.036s | 5,533,178/s | 4.9% | 4.2x slower |
| 4️⃣ | **filter_var()** | 0.051s | 3,895,716/s | 6.3% | 5.9x slower |
| 5️⃣ | **Respect Validation** | 0.073s | 2,743,647/s | 3.4% | 8.4x slower |
| 6️⃣ | **Symfony Attributes** | 1.517s | 131,828/s | 2.8% | 175.1x slower |
| 7️⃣ | **Symfony Validator** | 1.825s | 109,560/s | 1.9% | 210.7x slower |
| 8️⃣ | **Valinor** | 1.944s | 102,867/s | 9.7% | 224.4x slower |
| 9️⃣ | **Laravel Validation** | 12.097s | 16,532/s | 2.7% | 1396.1x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 23,080,990/s  🥇
Value Objects      █████████████            5,885,074/s  🥈
webmozart/assert   ████████████               5,533,178/s  🥉
filter_var()       ████████                           3,895,716/s  4️⃣
Respect Validation ██████                                 2,743,647/s  5️⃣
Symfony Attributes ▏                                                131,828/s  6️⃣
Symfony Validator  ▏                                                109,560/s  7️⃣
Valinor            ▏                                                102,867/s  8️⃣
Laravel Validation ▏                                                16,532/s  9️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.009s  ⚡
Value Objects      ▏                                                  0.034s  
webmozart/assert   ▏                                                  0.036s  
filter_var()       ▏                                                  0.051s  
Respect Validation ▏                                                  0.073s  
Symfony Attributes ██████                                   1.517s  
Symfony Validator  ████████                             1.825s  
Valinor            ████████                             1.944s  
Laravel Validation ██████████████████████████████████████████████████  12.097s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| Respect Validation | 42.00 MB | +4.00 MB |
| Symfony Attributes | 40.00 MB | +2.00 MB |
| Symfony Validator | 38.00 MB | — |
| Valinor | 42.00 MB | +4.00 MB |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 23,080,990/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 5,885,074/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,533,178/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 3,895,716/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Respect Validation** | 2,743,647/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 131,828/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 109,560/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Valinor** | 102,867/s | — | — |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Laravel Validation** | 16,532/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1396x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **webmozart/assert** — 5,533,178/s (4.2x vs Pure PHP): Static call overhead + exception path
3. **filter_var()** — 3,895,716/s (5.9x vs Pure PHP): Redundant type checks on already-typed props
4. ✅ **All 9 approaches produce identical results** — correctness verified (160,169 valid / 39,831 invalid)

---

## 🚀 Run It Yourself

```bash
# Full matrix (PHP 8.2 - 8.5) + comparison report
./run.sh

# Single PHP version
PHP_VERSIONS=8.5 ./run.sh

# Custom output directory, dataset size, repeats
./run.sh /path/to/results
BENCH_COUNT=50000 BENCH_REPEATS=5 ./run.sh

# Local PHP (>= 8.2)
composer update
php benchmark.php
```

Per-version reports land in `RESULTS-<version>.md`, raw numbers in `results-<version>.json`, and the cross-version diff in `COMPARISON.md`.
