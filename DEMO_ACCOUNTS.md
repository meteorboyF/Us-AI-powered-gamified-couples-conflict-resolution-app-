# Demo Accounts and Instructions

This project includes two demo couples for full feature testing.

## Seed Demo Data

Run:

```bash
php artisan migrate:fresh --seed
php artisan us:seed-demo
```

## Demo Credentials

Password for all demo users:

`DemoPass123!`

### Couple A (Garden World)

- `couplea1@demo.test`
- `couplea2@demo.test`

### Couple B (Space World)

- `coupleb1@demo.test`
- `coupleb2@demo.test`

## Quick Test Flow

1. Log in as `couplea1@demo.test` and open Dashboard.
2. Verify world level/items, vibe, and placement.
3. Open Missions and check daily/weekly history.
4. Open Repair history and review completed sessions/agreements.
5. Open Vault and verify shared/private/locked memories.
6. Open AI Coach and inspect vent + bridge history.
7. Open Gifts and verify wishlist and suggestion history.
8. Log out and repeat with `coupleb1@demo.test` to validate the second couple dataset.

## Notes

- These are demo-only accounts and demo-only credentials.
- Do not use demo credentials for production environments.
