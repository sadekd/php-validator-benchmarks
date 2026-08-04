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
| 🥇 | **Pure PHP** | 0.014s | 13,957,942/s | 69.8% | 1.0x |
| 🥈 | **Value Objects** | 0.073s | 2,753,858/s | 41.1% | 5.1x slower |
| 🥉 | **filter_var()** | 0.092s | 2,174,822/s | 8.7% | 6.4x slower |
| 4️⃣ | **webmozart/assert** | 0.110s | 1,816,446/s | 24.4% | 7.7x slower |
| 5️⃣ | **Respect Validation** | 0.138s | 1,444,785/s | 3.0% | 9.7x slower |
| 6️⃣ | **Symfony Attributes** | 3.975s | 50,309/s | 5.7% | 277.4x slower |
| 7️⃣ | **Symfony Validator** | 4.088s | 48,922/s | 10.3% | 285.3x slower |
| 8️⃣ | **Laravel Validation** | 25.902s | 7,721/s | 4.6% | 1807.7x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 13,957,942/s  🥇
Value Objects      ██████████                     2,753,858/s  🥈
filter_var()       ████████                           2,174,822/s  🥉
webmozart/assert   ███████                              1,816,446/s  4️⃣
Respect Validation █████                                    1,444,785/s  5️⃣
Symfony Attributes ▏                                                50,309/s  6️⃣
Symfony Validator  ▏                                                48,922/s  7️⃣
Laravel Validation ▏                                                7,721/s  8️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.014s  ⚡
Value Objects      ▏                                                  0.073s  
filter_var()       ▏                                                  0.092s  
webmozart/assert   ▏                                                  0.110s  
Respect Validation ▏                                                  0.138s  
Symfony Attributes ████████                             3.975s  
Symfony Validator  ████████                             4.088s  
Laravel Validation ██████████████████████████████████████████████████  25.902s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
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
| **Pure PHP** | 13,957,942/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 2,753,858/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **filter_var()** | 2,174,822/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **webmozart/assert** | 1,816,446/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **Respect Validation** | 1,444,785/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 50,309/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 48,922/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 7,721/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1808x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **webmozart/assert** — 1,816,446/s (7.7x vs Pure PHP): Static call overhead + exception path
3. **filter_var()** — 2,174,822/s (6.4x vs Pure PHP): Redundant type checks on already-typed props
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
