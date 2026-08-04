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
| 🥇 | **Pure PHP** | 0.022s | 9,006,609/s | 105.6% | 1.0x |
| 🥈 | **Value Objects** | 0.082s | 2,440,764/s | 16.4% | 3.7x slower |
| 🥉 | **webmozart/assert** | 0.111s | 1,805,619/s | 52.4% | 5.0x slower |
| 4️⃣ | **Respect Validation** | 0.140s | 1,432,178/s | 3.6% | 6.3x slower |
| 5️⃣ | **filter_var()** | 0.141s | 1,423,359/s | 28.0% | 6.3x slower |
| 6️⃣ | **Symfony Attributes** | 3.317s | 60,293/s | 1.2% | 149.4x slower |
| 7️⃣ | **Symfony Validator** | 4.712s | 42,445/s | 18.5% | 212.2x slower |
| 8️⃣ | **Laravel Validation** | 25.787s | 7,755/s | 8.8% | 1161.3x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 9,006,609/s  🥇
Value Objects      ██████████████         2,440,764/s  🥈
webmozart/assert   ██████████                     1,805,619/s  🥉
Respect Validation ████████                           1,432,178/s  4️⃣
filter_var()       ████████                           1,423,359/s  5️⃣
Symfony Attributes ▏                                                60,293/s  6️⃣
Symfony Validator  ▏                                                42,445/s  7️⃣
Laravel Validation ▏                                                7,755/s  8️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.022s  ⚡
Value Objects      ▏                                                  0.082s  
webmozart/assert   ▏                                                  0.111s  
Respect Validation ▏                                                  0.140s  
filter_var()       ▏                                                  0.141s  
Symfony Attributes ██████                                   3.317s  
Symfony Validator  █████████                          4.712s  
Laravel Validation ██████████████████████████████████████████████████  25.787s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| Respect Validation | 42.00 MB | +4.00 MB |
| filter_var() | 38.00 MB | — |
| Symfony Attributes | 38.00 MB | — |
| Symfony Validator | 38.00 MB | — |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 9,006,609/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 2,440,764/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 1,805,619/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **Respect Validation** | 1,432,178/s | Rule chain evaluated per field | Result objects built per rule evaluation |
| **filter_var()** | 1,423,359/s | PHP built-in C filter functions | Redundant type checks on already-typed props |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 60,293/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 42,445/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 7,755/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1161x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **webmozart/assert** — 1,805,619/s (5.0x vs Pure PHP): Static call overhead + exception path
3. **filter_var()** — 1,423,359/s (6.3x vs Pure PHP): Redundant type checks on already-typed props
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
