# 🏎️ PHP 8.5 Validation Benchmark

> Benchmarking **9 validation approaches** across **1,000,000 product items** on PHP 8.5.9

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.5.9 (Alpine) |
| 📦 **Data Set** | 1,000,000 product objects (~80% valid, ~20% invalid) |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🐳 **Runtime** | Docker (`php:8.5-cli-alpine`) |
| 📅 **Date** | 2026-08-04 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Time | Validations/s | vs Baseline |
|:---:|---|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.066s | 15,181,573/s | 1.0x |
| 🥈 | **Value Objects** | 0.220s | 4,546,604/s | 3.3x slower |
| 🥉 | **webmozart/assert** | 0.239s | 4,186,224/s | 3.6x slower |
| 4️⃣ | **filter_var()** | 0.277s | 3,605,772/s | 4.2x slower |
| 5️⃣ | **Property Hooks 🆕** | 0.365s | 2,740,082/s | 5.5x slower |
| 6️⃣ | **Respect Validation** | 4.824s | 207,293/s | 73.2x slower |
| 7️⃣ | **Symfony Attributes** | 10.185s | 98,180/s | 154.6x slower |
| 8️⃣ | **Symfony Validator** | 12.798s | 78,138/s | 194.3x slower |
| 9️⃣ | **Laravel Validation** | 72.683s | 13,758/s | 1103.4x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 15,181,573/s  🥇
Value Objects      ███████████████      4,546,604/s  🥈
webmozart/assert   ██████████████         4,186,224/s  🥉
filter_var()       ████████████               3,605,772/s  4️⃣
Property Hooks     █████████                        2,740,082/s  5️⃣
Respect Validation ▏                                                207,293/s  6️⃣
Symfony Attributes ▏                                                98,180/s  7️⃣
Symfony Validator  ▏                                                78,138/s  8️⃣
Laravel Validation ▏                                                13,758/s  9️⃣
```

## ⏱️ Execution Time (seconds)

```
Pure PHP           ▏                                                  0.066s  ⚡
Value Objects      ▏                                                  0.220s  
webmozart/assert   ▏                                                  0.239s  
filter_var()       ▏                                                  0.277s  
Property Hooks     ▏                                                  0.365s  
Respect Validation ███                                            4.824s  
Symfony Attributes ███████                               10.185s  
Symfony Validator  █████████                         12.798s  
Laravel Validation ██████████████████████████████████████████████████  72.683s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 170.00 MB | — |
| Value Objects | 170.00 MB | — |
| webmozart/assert | 172.00 MB | +2.00 MB |
| filter_var() | 170.00 MB | — |
| Property Hooks | 178.00 MB | +8.00 MB |
| Respect Validation | 176.00 MB | +6.00 MB |
| Symfony Attributes | 172.00 MB | +2.00 MB |
| Symfony Validator | 172.00 MB | +2.00 MB |
| Laravel Validation | 176.00 MB | +6.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 15,181,573/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 4,546,604/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 4,186,224/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 3,605,772/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Property Hooks** | 2,740,082/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Respect Validation** | 207,293/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 98,180/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |
| **Symfony Validator** | 78,138/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 13,758/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1103x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 2,740,082/s (5.5x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 4,186,224/s (3.6x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 3,605,772/s (4.2x vs Pure PHP): Redundant type checks on already-typed props
5. ✅ **All 9 approaches produce identical results** — correctness verified (799,674 valid / 200,326 invalid)

---

## 🚀 Run It Yourself

```bash
# Docker (recommended)
./run.sh

# Custom output directory
./run.sh /path/to/results

# Smaller dataset / different PHP series
BENCH_COUNT=50000 ./run.sh
PHP_VERSION=8.4 ./run.sh

# Local PHP (>= 8.5)
composer install
php benchmark.php
```

Results exported to `RESULTS.md`.
