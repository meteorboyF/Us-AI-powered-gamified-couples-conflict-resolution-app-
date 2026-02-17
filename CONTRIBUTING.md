# Contributing Guide

## Branching Rules
- Never commit directly to `main`.
- Create focused branches per task (example: `feat/chat-threading`, `fix/auth-redirect`, `chore/repo-init`).
- Keep branch scope isolated to one concern.

## Pull Request Rules
- Open a PR into `main` from your feature branch.
- Use a clear title with conventional commit style when possible.
- Include a short summary, what changed, and why.
- Add or update tests for behavior changes.
- Run formatting and tests before pushing:
  - `php artisan test`
  - `vendor/bin/pint`

## Security Rules
- Never commit `.env` or real secrets.
- Keep `.env.example` as placeholders only.
- Do not hardcode API keys, tokens, or credentials in code.
