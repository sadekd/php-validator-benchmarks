# 🏎️ PHP 8.4 Validation Benchmark

> Benchmarking **10 validation approaches** across **200,000 product items** on PHP 8.4.25

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.4.25 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.4-cli-alpine`) |
| 📅 **Date** | 2026-09-10 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `cuyz/valinor` | 2.6.0 |
| `illuminate/validation` | 12.69.2 |
| `respect/validation` | 2.5.0 |
| `symfony/validator` | 8.1.6 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.008s | 23,769,256/s | 27.5% | 1.0x |
| 🥈 | **webmozart/assert** | 0.036s | 5,507,217/s | 2.7% | 4.3x slower |
| 🥉 | **Value Objects** | 0.039s | 5,162,505/s | 8.1% | 4.6x slower |
| 4️⃣ | **filter_var()** | 0.054s | 3,675,510/s | 5.7% | 6.5x slower |
| 5️⃣ | **Property Hooks 🆕** | 0.058s | 3,467,473/s | 14.6% | 6.9x slower |
| 6️⃣ | **Respect Validation** | 0.070s | 2,840,382/s | 1.4% | 8.4x slower |
| 7️⃣ | **Symfony Attributes** | 1.687s | 118,525/s | 1.8% | 200.5x slower |
| 8️⃣ | **Valinor** | 1.896s | 105,496/s | 1.6% | 225.3x slower |
| 9️⃣ | **Symfony Validator** | 2.052s | 97,464/s | 0.6% | 243.9x slower |
| 🔟 | **Laravel Validation** | 11.725s | 17,057/s | 1.3% | 1393.5x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 23,769,256/s  🥇
webmozart/assert   ████████████               5,507,217/s  🥈
Value Objects      ███████████                  5,162,505/s  🥉
filter_var()       ████████                           3,675,510/s  4️⃣
Property Hooks     ███████                              3,467,473/s  5️⃣
Respect Validation ██████                                 2,840,382/s  6️⃣
Symfony Attributes ▏                                                118,525/s  7️⃣
Valinor            ▏                                                105,496/s  8️⃣
Symfony Validator  ▏                                                97,464/s  9️⃣
Laravel Validation ▏                                                17,057/s  🔟
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.008s  ⚡
webmozart/assert   ▏                                                  0.036s  
Value Objects      ▏                                                  0.039s  
filter_var()       ▏                                                  0.054s  
Property Hooks     ▏                                                  0.058s  
Respect Validation ▏                                                  0.070s  
Symfony Attributes ███████                                1.687s  
Valinor            ████████                             1.896s  
Symfony Validator  █████████                          2.052s  
Laravel Validation ██████████████████████████████████████████████████  11.725s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| webmozart/assert | 40.00 MB | +2.00 MB |
| Value Objects | 40.00 MB | +2.00 MB |
| filter_var() | 38.00 MB | — |
| Property Hooks | 40.00 MB | +2.00 MB |
| Respect Validation | 44.00 MB | +6.00 MB |
| Symfony Attributes | 40.00 MB | +2.00 MB |
| Valinor | 44.00 MB | +6.00 MB |
| Symfony Validator | 40.00 MB | +2.00 MB |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 23,769,256/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **webmozart/assert** | 5,507,217/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **Value Objects** | 5,162,505/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **filter_var()** | 3,675,510/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Property Hooks** | 3,467,473/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |
| **Respect Validation** | 2,840,382/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 118,525/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Valinor** | 105,496/s | — | — |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Validator** | 97,464/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 17,057/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1394x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 3,467,473/s (6.9x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 5,507,217/s (4.3x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 3,675,510/s (6.5x vs Pure PHP): Redundant type checks on already-typed props
5. ✅ **All 10 approaches produce identical results** — correctness verified (160,169 valid / 39,831 invalid)

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
