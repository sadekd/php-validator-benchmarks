# 🏎️ PHP 8.3 Validation Benchmark

> Benchmarking **8 validation approaches** across **200,000 product items** on PHP 8.3.33

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.3.33 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.3-cli-alpine`) |
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
| 🥇 | **Pure PHP** | 0.008s | 25,324,244/s | 30.8% | 1.0x |
| 🥈 | **Value Objects** | 0.031s | 6,365,779/s | 3.4% | 4.0x slower |
| 🥉 | **webmozart/assert** | 0.037s | 5,475,697/s | 7.2% | 4.6x slower |
| 4️⃣ | **filter_var()** | 0.041s | 4,934,117/s | 4.3% | 5.1x slower |
| 5️⃣ | **Respect Validation** | 0.073s | 2,728,917/s | 5.3% | 9.3x slower |
| 6️⃣ | **Symfony Attributes** | 1.794s | 111,478/s | 32.2% | 227.2x slower |
| 7️⃣ | **Symfony Validator** | 1.974s | 101,310/s | 6.5% | 250.0x slower |
| 8️⃣ | **Laravel Validation** | 12.270s | 16,300/s | 2.9% | 1553.6x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 25,324,244/s  🥇
Value Objects      █████████████            6,365,779/s  🥈
webmozart/assert   ███████████                  5,475,697/s  🥉
filter_var()       ██████████                     4,934,117/s  4️⃣
Respect Validation █████                                    2,728,917/s  5️⃣
Symfony Attributes ▏                                                111,478/s  6️⃣
Symfony Validator  ▏                                                101,310/s  7️⃣
Laravel Validation ▏                                                16,300/s  8️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.008s  ⚡
Value Objects      ▏                                                  0.031s  
webmozart/assert   ▏                                                  0.037s  
filter_var()       ▏                                                  0.041s  
Respect Validation ▏                                                  0.073s  
Symfony Attributes ███████                                1.794s  
Symfony Validator  ████████                             1.974s  
Laravel Validation ██████████████████████████████████████████████████  12.270s  🐌
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
| **Pure PHP** | 25,324,244/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 6,365,779/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,475,697/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 4,934,117/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Respect Validation** | 2,728,917/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 111,478/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 101,310/s | `Collection` constraint over an array | Constraint tree walk + array conversion |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Laravel Validation** | 16,300/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1554x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **webmozart/assert** — 5,475,697/s (4.6x vs Pure PHP): Static call overhead + exception path
3. **filter_var()** — 4,934,117/s (5.1x vs Pure PHP): Redundant type checks on already-typed props
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
