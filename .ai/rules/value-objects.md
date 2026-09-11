---
paths:
  - 'app/ValueObjects/**'
---

# Value Objects

## JSON settings use a typed value-object cast
App-controlled JSON columns (e.g. users.meta) are cast to a typed value object implementing Arrayable + JsonSerializable, via a CastsAttributes class that whitelists known keys (see UserSettings / AsUserSettings). Adding a setting = add a typed property + handle it in fromArray()/toArray() + pick a default. Null JSON is avoided with a model `protected $attributes = ['meta' => '{}']` default plus a migration backfill; defaults live in the value-object constructor.
