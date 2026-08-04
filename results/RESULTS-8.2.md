# 🏎️ PHP 8.2 Validation Benchmark

> Benchmarking **8 validation approaches** across **200,000 product items** on PHP 8.2.33

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.2.33 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.2-cli-alpine`) |
| 📅 **Date** | 2026-08-04 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `illuminate/validation` | 12.64.0 |
| `respect/validation` | 2.5.0 |
| `symfony/validator` | 7.4.15 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.007s | 29,007,674/s | 180.0% | 1.0x |
| 🥈 | **Value Objects** | 0.030s | 6,620,511/s | 4.0% | 4.4x slower |
| 🥉 | **webmozart/assert** | 0.036s | 5,589,942/s | 7.2% | 5.2x slower |
| 4️⃣ | **filter_var()** | 0.041s | 4,845,131/s | 6.1% | 6.0x slower |
| 5️⃣ | **Respect Validation** | 0.073s | 2,725,453/s | 4.1% | 10.6x slower |
| 6️⃣ | **Symfony Attributes** | 1.609s | 124,331/s | 4.5% | 233.3x slower |
| 7️⃣ | **Symfony Validator** | 1.841s | 108,641/s | 4.0% | 267.0x slower |
| 8️⃣ | **Laravel Validation** | 12.216s | 16,372/s | 10.1% | 1771.8x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 29,007,674/s  🥇
Value Objects      ███████████                  6,620,511/s  🥈
webmozart/assert   ██████████                     5,589,942/s  🥉
filter_var()       ████████                           4,845,131/s  4️⃣
Respect Validation █████                                    2,725,453/s  5️⃣
Symfony Attributes ▏                                                124,331/s  6️⃣
Symfony Validator  ▏                                                108,641/s  7️⃣
Laravel Validation ▏                                                16,372/s  8️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.007s  ⚡
Value Objects      ▏                                                  0.030s  
webmozart/assert   ▏                                                  0.036s  
filter_var()       ▏                                                  0.041s  
Respect Validation ▏                                                  0.073s  
Symfony Attributes ███████                                1.609s  
Symfony Validator  ████████                             1.841s  
Laravel Validation ██████████████████████████████████████████████████  12.216s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| Respect Validation | 42.00 MB | +4.00 MB |
| Symfony Attributes | 38.00 MB | — |
| Symfony Validator | 38.00 MB | — |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 29,007,674/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 6,620,511/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,589,942/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 4,845,131/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Respect Validation** | 2,725,453/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 124,331/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 108,641/s | `Collection` constraint over an array | Constraint tree walk + array conversion |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Laravel Validation** | 16,372/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1772x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **webmozart/assert** — 5,589,942/s (5.2x vs Pure PHP): Static call overhead + exception path
3. **filter_var()** — 4,845,131/s (6.0x vs Pure PHP): Redundant type checks on already-typed props
4. ✅ **All 8 approaches produce identical results** — correctness verified (160,169 valid / 39,831 invalid)

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
