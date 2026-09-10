# 🏎️ PHP 8.2 Validation Benchmark

> Benchmarking **9 validation approaches** across **200,000 product items** on PHP 8.2.33

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.2.33 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.2-cli-alpine`) |
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
| 🥇 | **Pure PHP** | 0.007s | 27,100,879/s | 146.0% | 1.0x |
| 🥈 | **Value Objects** | 0.031s | 6,418,169/s | 10.1% | 4.2x slower |
| 🥉 | **webmozart/assert** | 0.036s | 5,557,139/s | 2.4% | 4.9x slower |
| 4️⃣ | **filter_var()** | 0.070s | 2,857,474/s | 59.0% | 9.5x slower |
| 5️⃣ | **Respect Validation** | 0.075s | 2,673,376/s | 3.8% | 10.1x slower |
| 6️⃣ | **Symfony Attributes** | 1.691s | 118,283/s | 65.1% | 229.1x slower |
| 7️⃣ | **Symfony Validator** | 1.805s | 110,791/s | 2.8% | 244.6x slower |
| 8️⃣ | **Valinor** | 1.871s | 106,882/s | 1.7% | 253.6x slower |
| 9️⃣ | **Laravel Validation** | 12.207s | 16,384/s | 0.7% | 1654.1x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 27,100,879/s  🥇
Value Objects      ████████████               6,418,169/s  🥈
webmozart/assert   ██████████                     5,557,139/s  🥉
filter_var()       █████                                    2,857,474/s  4️⃣
Respect Validation █████                                    2,673,376/s  5️⃣
Symfony Attributes ▏                                                118,283/s  6️⃣
Symfony Validator  ▏                                                110,791/s  7️⃣
Valinor            ▏                                                106,882/s  8️⃣
Laravel Validation ▏                                                16,384/s  9️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.007s  ⚡
Value Objects      ▏                                                  0.031s  
webmozart/assert   ▏                                                  0.036s  
filter_var()       ▏                                                  0.070s  
Respect Validation ▏                                                  0.075s  
Symfony Attributes ███████                                1.691s  
Symfony Validator  ███████                                1.805s  
Valinor            ████████                             1.871s  
Laravel Validation ██████████████████████████████████████████████████  12.207s  🐌
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
| **Pure PHP** | 27,100,879/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 6,418,169/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,557,139/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 2,857,474/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Respect Validation** | 2,673,376/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 118,283/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 110,791/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Valinor** | 106,882/s | — | — |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Laravel Validation** | 16,384/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1654x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **webmozart/assert** — 5,557,139/s (4.9x vs Pure PHP): Static call overhead + exception path
3. **filter_var()** — 2,857,474/s (9.5x vs Pure PHP): Redundant type checks on already-typed props
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
