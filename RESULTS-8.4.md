# 🏎️ PHP 8.4 Validation Benchmark

> Benchmarking **9 validation approaches** across **200,000 product items** on PHP 8.4.24

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.4.24 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.4-cli-alpine`) |
| 📅 **Date** | 2026-08-04 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `illuminate/validation` | 12.64.0 |
| `respect/validation` | 2.5.0 |
| `symfony/validator` | 8.1.2 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.015s | 13,530,637/s | 75.8% | 1.0x |
| 🥈 | **webmozart/assert** | 0.073s | 2,740,094/s | 5.3% | 4.9x slower |
| 🥉 | **Value Objects** | 0.078s | 2,562,037/s | 26.7% | 5.3x slower |
| 4️⃣ | **filter_var()** | 0.091s | 2,208,915/s | 8.5% | 6.1x slower |
| 5️⃣ | **Property Hooks 🆕** | 0.110s | 1,822,858/s | 6.6% | 7.4x slower |
| 6️⃣ | **Respect Validation** | 0.155s | 1,286,696/s | 2.3% | 10.5x slower |
| 7️⃣ | **Symfony Attributes** | 3.399s | 58,837/s | 1.3% | 230.0x slower |
| 8️⃣ | **Symfony Validator** | 4.573s | 43,738/s | 15.6% | 309.4x slower |
| 9️⃣ | **Laravel Validation** | 31.405s | 6,368/s | 31.1% | 2124.6x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 13,530,637/s  🥇
webmozart/assert   ██████████                     2,740,094/s  🥈
Value Objects      █████████                        2,562,037/s  🥉
filter_var()       ████████                           2,208,915/s  4️⃣
Property Hooks     ███████                              1,822,858/s  5️⃣
Respect Validation █████                                    1,286,696/s  6️⃣
Symfony Attributes ▏                                                58,837/s  7️⃣
Symfony Validator  ▏                                                43,738/s  8️⃣
Laravel Validation ▏                                                6,368/s  9️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.015s  ⚡
webmozart/assert   ▏                                                  0.073s  
Value Objects      ▏                                                  0.078s  
filter_var()       ▏                                                  0.091s  
Property Hooks     ▏                                                  0.110s  
Respect Validation ▏                                                  0.155s  
Symfony Attributes █████                                      3.399s  
Symfony Validator  ███████                                4.573s  
Laravel Validation ██████████████████████████████████████████████████  31.405s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| Property Hooks | 38.00 MB | — |
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
| **Pure PHP** | 13,530,637/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **webmozart/assert** | 2,740,094/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **Value Objects** | 2,562,037/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **filter_var()** | 2,208,915/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Property Hooks** | 1,822,858/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |
| **Respect Validation** | 1,286,696/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 58,837/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 43,738/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 6,368/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~2125x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 1,822,858/s (7.4x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 2,740,094/s (4.9x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 2,208,915/s (6.1x vs Pure PHP): Redundant type checks on already-typed props
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
