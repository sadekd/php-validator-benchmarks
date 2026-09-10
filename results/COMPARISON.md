# PHP Version Comparison — Validation Throughput

200,000 items, seed 20260804, 3 timed repeat(s) per validator after an untimed 1,000-item warmup. Fast validators repeat the pass inside each sample until it clears 0.5s, so a 20ms loop is not timed against scheduler jitter. Same machine, same dataset, PHP 8.2 as the baseline column.

**Every number below is the fastest of the 3 repeats**, normalised to one pass. Machine interference only ever makes a sample slower, so the best sample is the closest available estimate of uncontended throughput — the per-version `RESULTS-*.md` files report medians instead.

Generated 2026-09-10 from `results-*.json`.

---

## What Each Column Ran

| | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 |
|---|---:|---:|---:|---:|
| **PHP** | 8.2.33 | 8.3.33 | 8.4.25 | 8.5.10 |
| `cuyz/valinor` | 2.6.0 | 2.6.0 | 2.6.0 | 2.6.0 |
| `illuminate/validation` | 12.69.2 | 12.69.2 | 12.69.2 | 12.69.2 |
| `respect/validation` | 2.5.0 | 2.5.0 | 2.5.0 | 3.1.2 |
| `symfony/validator` | 7.4.18 | 7.4.18 | 8.1.6 | 8.1.6 |
| `webmozart/assert` | 2.4.1 | 2.4.1 | 2.4.1 | 2.4.1 |

## Throughput (validations/s, best of 3)

| Validator | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 | 8.2 → 8.5 |
|---|---:|---:|---:|---:|---:|
| **Pure PHP** | 29,322,081 | 23,258,670 | 25,104,005 | 22,316,797 | ▼ -23.9% |
| **Value Objects** | 6,596,835 | 6,418,904 | 5,384,255 | 6,152,334 | ▼ -6.7% |
| **webmozart/assert** | 5,642,588 | 5,714,267 | 5,584,459 | 5,825,854 | ▲ +3.2% |
| **Property Hooks** | n/a | n/a | 3,936,840 | 4,102,236 | n/a |
| **filter_var()** | 3,908,479 | 4,109,256 | 3,744,761 | 3,680,006 | ▼ -5.8% |
| **Respect Validation** ⚠️ | 2,770,896 | 2,814,478 | 2,868,964 | 213,796 | ▼ -92.3% |
| **Symfony Attributes** ⚠️ | 133,212 | 134,408 | 119,501 | 120,990 | ▼ -9.2% |
| **Symfony Validator** ⚠️ | 111,315 | 109,979 | 98,085 | 98,784 | ▼ -11.3% |
| **Valinor** | 107,878 | 104,158 | 106,972 | 91,517 | ▼ -15.2% |
| **Laravel Validation** | 16,423 | 16,874 | 17,218 | 14,717 | ▼ -10.4% |

Positive = faster on the newer PHP. `n/a` = the approach does not exist on that version.

> ⚠️ **Library version changed across these columns** — the delta is runtime *plus* library, not runtime alone:
>
> - **Respect Validation**: PHP 8.2 → `respect/validation 2.5.0`, PHP 8.5 → `respect/validation 3.1.2`
> - **Symfony Attributes**: PHP 8.2 → `symfony/validator 7.4.18`, PHP 8.4 → `symfony/validator 8.1.6`
> - **Symfony Validator**: PHP 8.2 → `symfony/validator 7.4.18`, PHP 8.4 → `symfony/validator 8.1.6`

---

## Version-to-Version Change (%)

| Validator | 8.2 → 8.3 | 8.3 → 8.4 | 8.4 → 8.5 |
|---|---:|---:|---:|
| **Pure PHP** | ▼ -20.7% | ▲ +7.9% | ▼ -11.1% |
| **Value Objects** | ▼ -2.7% | ▼ -16.1% | ▲ +14.3% |
| **webmozart/assert** | ▲ +1.3% | ▼ -2.3% | ▲ +4.3% |
| **Property Hooks** | n/a | n/a | ▲ +4.2% |
| **filter_var()** | ▲ +5.1% | ▼ -8.9% | ▼ -1.7% |
| **Respect Validation** | ▲ +1.6% | ▲ +1.9% | ▼ -92.5% |
| **Symfony Attributes** | ▲ +0.9% | ▼ -11.1% | ▲ +1.2% |
| **Symfony Validator** | ▼ -1.2% | ▼ -10.8% | ▲ +0.7% |
| **Valinor** | ▼ -3.4% | ▲ +2.7% | ▼ -14.4% |
| **Laravel Validation** | ▲ +2.7% | ▲ +2.0% | ▼ -14.5% |

---

## Noise Floor

Spread between the fastest and slowest repeat, as a percentage of the median. The tables above use the fastest repeat, which sidesteps most of this, but a row with a large spread was contended while it ran — treat small deltas on those rows with suspicion.

| Validator | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 |
|---|---:|---:|---:|---:|
| **Pure PHP** | 146.0% | 35.8% | 27.5% | 19.5% |
| **Value Objects** | 10.1% | 15.5% | 8.1% | 13.6% |
| **webmozart/assert** | 2.4% | 4.9% | 2.7% | 32.9% |
| **Property Hooks** | n/a | n/a | 14.6% | 5.2% |
| **filter_var()** | 59.0% | 6.3% | 5.7% | 7.6% |
| **Respect Validation** | 3.8% | 3.4% | 1.4% | 1.9% |
| **Symfony Attributes** | 65.1% | 2.8% | 1.8% | 4.6% |
| **Symfony Validator** | 2.8% | 1.9% | 0.7% | 1.8% |
| **Valinor** | 1.7% | 9.7% | 1.6% | 3.6% |
| **Laravel Validation** | 0.7% | 2.7% | 1.3% | 7.8% |

## Correctness Cross-Check

Every validator on every PHP version returned the same verdict split: **160,169 valid / 39,831 invalid** — the dataset is seeded, so this is comparable across columns.
