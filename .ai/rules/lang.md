---
paths:
  - 'lang/**'
---

# Lang

## JSON translations are flat and must stay in sync
Laravel resolves `__('a.b')` against JSON files using the literal dotted key, so lang/en.json and lang/de.json must be FLAT (e.g. "auth.register_title"), not nested objects. Keep the two files in sync; TranslationTest enforces identical key sets. Built-in German validation messages live in lang/de/validation.php (with its own 'attributes'). Add new UI strings to both JSON files.
