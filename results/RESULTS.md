# 🏎️ PHP 8.5 Validation Benchmark

> Benchmarking **10 validation approaches** across **200,000 product items** on PHP 8.5.10

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.5.10 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.5-cli-alpine`) |
| 📅 **Date** | 2026-09-10 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `cuyz/valinor` | 2.6.0 |
| `illuminate/validation` | 12.69.2 |
| `respect/validation` | 3.1.2 |
| `symfony/validator` | 8.1.6 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.009s | 22,196,698/s | 19.5% | 1.0x |
| 🥈 | **Value Objects** | 0.034s | 5,968,320/s | 13.6% | 3.7x slower |
| 🥉 | **webmozart/assert** | 0.037s | 5,387,725/s | 32.9% | 4.1x slower |
| 4️⃣ | **Property Hooks 🆕** | 0.050s | 3,971,449/s | 5.2% | 5.6x slower |
| 5️⃣ | **filter_var()** | 0.056s | 3,600,174/s | 7.6% | 6.2x slower |
| 6️⃣ | **Respect Validation** | 0.940s | 212,672/s | 1.9% | 104.4x slower |
| 7️⃣ | **Symfony Attributes** | 1.707s | 117,162/s | 4.6% | 189.5x slower |
| 8️⃣ | **Symfony Validator** | 2.041s | 98,010/s | 1.8% | 226.5x slower |
| 9️⃣ | **Valinor** | 2.209s | 90,545/s | 3.6% | 245.1x slower |
| 🔟 | **Laravel Validation** | 13.592s | 14,715/s | 7.8% | 1508.4x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 22,196,698/s  🥇
Value Objects      █████████████            5,968,320/s  🥈
webmozart/assert   ████████████               5,387,725/s  🥉
Property Hooks     █████████                        3,971,449/s  4️⃣
filter_var()       ████████                           3,600,174/s  5️⃣
Respect Validation ▏                                                212,672/s  6️⃣
Symfony Attributes ▏                                                117,162/s  7️⃣
Symfony Validator  ▏                                                98,010/s  8️⃣
Valinor            ▏                                                90,545/s  9️⃣
Laravel Validation ▏                                                14,715/s  🔟
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.009s  ⚡
Value Objects      ▏                                                  0.034s  
webmozart/assert   ▏                                                  0.037s  
Property Hooks     ▏                                                  0.050s  
filter_var()       ▏                                                  0.056s  
Respect Validation ███                                            0.940s  
Symfony Attributes ██████                                   1.707s  
Symfony Validator  ████████                             2.041s  
Valinor            ████████                             2.209s  
Laravel Validation ██████████████████████████████████████████████████  13.592s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 40.00 MB | +2.00 MB |
| webmozart/assert | 40.00 MB | +2.00 MB |
| Property Hooks | 40.00 MB | +2.00 MB |
| filter_var() | 38.00 MB | — |
| Respect Validation | 44.00 MB | +6.00 MB |
| Symfony Attributes | 40.00 MB | +2.00 MB |
| Symfony Validator | 40.00 MB | +2.00 MB |
| Valinor | 44.00 MB | +6.00 MB |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 22,196,698/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 5,968,320/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,387,725/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **Property Hooks** | 3,971,449/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |
| **filter_var()** | 3,600,174/s | PHP built-in C filter functions | Redundant type checks on already-typed props |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Respect Validation** | 212,672/s | Rule chain evaluated per field | Result objects built per rule evaluation |
| **Symfony Attributes** | 117,162/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Validator** | 98,010/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Valinor** | 90,545/s | — | — |
| **Laravel Validation** | 14,715/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1508x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 3,971,449/s (5.6x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 5,387,725/s (4.1x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 3,600,174/s (6.2x vs Pure PHP): Redundant type checks on already-typed props
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
