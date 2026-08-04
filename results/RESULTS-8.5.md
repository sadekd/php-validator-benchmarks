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
| 🥇 | **Pure PHP** | 0.008s | 26,161,897/s | 29.9% | 1.0x |
| 🥈 | **Value Objects** | 0.030s | 6,586,841/s | 3.6% | 4.0x slower |
| 🥉 | **webmozart/assert** | 0.036s | 5,576,524/s | 4.5% | 4.7x slower |
| 4️⃣ | **filter_var()** | 0.044s | 4,539,412/s | 12.0% | 5.8x slower |
| 5️⃣ | **Property Hooks 🆕** | 0.052s | 3,879,984/s | 5.7% | 6.7x slower |
| 6️⃣ | **Respect Validation** | 0.841s | 237,738/s | 3.3% | 110.0x slower |
| 7️⃣ | **Symfony Attributes** | 1.800s | 111,120/s | 10.1% | 235.4x slower |
| 8️⃣ | **Symfony Validator** | 2.016s | 99,197/s | 1.9% | 263.7x slower |
| 9️⃣ | **Laravel Validation** | 12.359s | 16,182/s | 6.5% | 1616.7x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 26,161,897/s  🥇
Value Objects      █████████████            6,586,841/s  🥈
webmozart/assert   ███████████                  5,576,524/s  🥉
filter_var()       █████████                        4,539,412/s  4️⃣
Property Hooks     ███████                              3,879,984/s  5️⃣
Respect Validation ▏                                                237,738/s  6️⃣
Symfony Attributes ▏                                                111,120/s  7️⃣
Symfony Validator  ▏                                                99,197/s  8️⃣
Laravel Validation ▏                                                16,182/s  9️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.008s  ⚡
Value Objects      ▏                                                  0.030s  
webmozart/assert   ▏                                                  0.036s  
filter_var()       ▏                                                  0.044s  
Property Hooks     ▏                                                  0.052s  
Respect Validation ███                                            0.841s  
Symfony Attributes ███████                                1.800s  
Symfony Validator  ████████                             2.016s  
Laravel Validation ██████████████████████████████████████████████████  12.359s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| Property Hooks | 38.00 MB | — |
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
| **Pure PHP** | 26,161,897/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 6,586,841/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,576,524/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 4,539,412/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Property Hooks** | 3,879,984/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Respect Validation** | 237,738/s | Rule chain evaluated per field | Result objects built per rule evaluation |
| **Symfony Attributes** | 111,120/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Validator** | 99,197/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 16,182/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1617x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 3,879,984/s (6.7x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 5,576,524/s (4.7x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 4,539,412/s (5.8x vs Pure PHP): Redundant type checks on already-typed props
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
