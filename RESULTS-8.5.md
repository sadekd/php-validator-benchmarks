# 🏎️ PHP 8.5 Validation Benchmark

> Benchmarking **9 validation approaches** across **200,000 product items** on PHP 8.5.9

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.5.9 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.5-cli-alpine`) |
| 📅 **Date** | 2026-08-04 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `illuminate/validation` | 12.64.0 |
| `respect/validation` | 3.1.2 |
| `symfony/validator` | 8.1.2 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.019s | 10,536,758/s | 57.7% | 1.0x |
| 🥈 | **Value Objects** | 0.072s | 2,759,560/s | 51.0% | 3.8x slower |
| 🥉 | **webmozart/assert** | 0.083s | 2,413,333/s | 8.9% | 4.4x slower |
| 4️⃣ | **Property Hooks 🆕** | 0.111s | 1,804,914/s | 38.6% | 5.8x slower |
| 5️⃣ | **filter_var()** | 0.116s | 1,721,332/s | 42.0% | 6.1x slower |
| 6️⃣ | **Respect Validation** | 2.066s | 96,793/s | 32.4% | 108.9x slower |
| 7️⃣ | **Symfony Attributes** | 3.861s | 51,801/s | 7.2% | 203.4x slower |
| 8️⃣ | **Symfony Validator** | 4.643s | 43,074/s | 12.8% | 244.6x slower |
| 9️⃣ | **Laravel Validation** | 25.594s | 7,814/s | 0.7% | 1348.4x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 10,536,758/s  🥇
Value Objects      █████████████            2,759,560/s  🥈
webmozart/assert   ███████████                  2,413,333/s  🥉
Property Hooks     █████████                        1,804,914/s  4️⃣
filter_var()       ████████                           1,721,332/s  5️⃣
Respect Validation ▏                                                96,793/s  6️⃣
Symfony Attributes ▏                                                51,801/s  7️⃣
Symfony Validator  ▏                                                43,074/s  8️⃣
Laravel Validation ▏                                                7,814/s  9️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.019s  ⚡
Value Objects      ▏                                                  0.072s  
webmozart/assert   ▏                                                  0.083s  
Property Hooks     ▏                                                  0.111s  
filter_var()       ▏                                                  0.116s  
Respect Validation ████                                         2.066s  
Symfony Attributes ████████                             3.861s  
Symfony Validator  █████████                          4.643s  
Laravel Validation ██████████████████████████████████████████████████  25.594s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| Property Hooks | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| Respect Validation | 42.00 MB | +4.00 MB |
| Symfony Attributes | 40.00 MB | +2.00 MB |
| Symfony Validator | 38.00 MB | — |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 10,536,758/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 2,759,560/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 2,413,333/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **Property Hooks** | 1,804,914/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |
| **filter_var()** | 1,721,332/s | PHP built-in C filter functions | Redundant type checks on already-typed props |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Respect Validation** | 96,793/s | Rule chain evaluated per field | Result objects built per rule evaluation |
| **Symfony Attributes** | 51,801/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 43,074/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 7,814/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1348x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 1,804,914/s (5.8x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 2,413,333/s (4.4x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 1,721,332/s (6.1x vs Pure PHP): Redundant type checks on already-typed props
5. ✅ **All 9 approaches produce identical results** — correctness verified (160,169 valid / 39,831 invalid)

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
