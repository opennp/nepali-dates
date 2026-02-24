🇳🇵 OpenNP / Nepali Dates

#### The definitive, community-verified 1:1 JSON date mapper for the Bikram Sambat (B.S.) calendar.
## Why this exists

The Bikram Sambat calendar is administrative, not purely astronomical. The Panchang Samiti makes manual, consensus-based rulings on borderline dates every year. Because of this, algorithmic date prediction is fundamentally a guess. Existing packages try to write complex math to calculate leap years and month lengths, and when maintainers abandon them, the ecosystem breaks. opennp/nepali-dates solves this by abandoning algorithms entirely. We provide a strict, 1:1 daily mapping of A.D. to B.S. dates. Stop writing math algorithms; fetch the standard.
Data Structure

Data is provided as a flat JSON array for absolute simplicity and O(1) database lookups.
```JSON

[
  {
    "id": 1,
    "english_date": "1920-01-01",
    "nepali_date": "1976-09-17"
  },
  {
    "id": 2,
    "english_date": "1920-01-02",
    "nepali_date": "1976-09-18"
  }
]
```

## Usage & Contributing

Consume: Build your language-specific wrappers (Laravel, Node, etc.) to seed this JSON array directly into a local database table.
A simple SQL lookup replaces thousands of lines of fragile date-math code. Do not hotlink raw GitHub files in your production request cycle.

Contribute: PRs for future years are only accepted after the official Panchang is released.
All date mappings must be strictly cross-verified against the official physical publication to prevent supply-chain errors in enterprise systems.
