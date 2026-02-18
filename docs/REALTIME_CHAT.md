# Realtime Chat (ChatV1-RT)

This document describes the backend realtime foundation for ChatV1 using Laravel Reverb and broadcasting.

## Local setup

1. Install dependencies:
```bash
php C:\xampp\php\composer.phar install
npm install
```
2. Ensure `.env` has realtime values (placeholders in `.env.example`):
```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Run locally

Open separate terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

```bash
php artisan reverb:start
```

## Channel naming

- Server channel: `couple.{coupleId}`
- Echo subscription string: `Echo.private(\`couple.${coupleId}\`)`
- Authorization source: `routes/channels.php` via `App\Broadcasting\CoupleChannelAuthorizer`
  - only authenticated couple members can subscribe.

## Event names

- `chat.message.sent`
- `chat.message.deleted`
- `chat.read.updated`

## Event payloads (minimal)

- `chat.message.sent`:
  - `chat_id`, `couple_id`, `message` (`id`, `sender_id`, `type`, `body`, `sent_at`, `attachments[]`)
- `chat.message.deleted`:
  - `chat_id`, `couple_id`, `message_id`, `deleted=true`, `body=null`, `deleted_at`
- `chat.read.updated`:
  - `chat_id`, `couple_id`, `user_id`, `last_read_message_id`, `last_read_at`

## Typing indicator

Typing is handled with Echo whisper on the client for MVP (no server typing endpoint in this branch):

```js
Echo.private(`couple.${coupleId}`).whisper('typing', {
  user_id: userId,
  is_typing: true,
  expires_at: new Date(Date.now() + 3000).toISOString(),
});
```

Throttle whispers client-side to at most once per second.

## Manual verification (2 sessions)

1. Start `php artisan serve`, `npm run dev`, and `php artisan reverb:start`.
2. Log in as partner A in one browser and partner B in another.
3. Ensure both users have the same current couple selected.
4. Open chat page/client in both sessions and subscribe to `private-couple.{id}`.
5. Send a message from A and confirm B receives `chat.message.sent`.
6. Delete a message from sender and confirm partner receives `chat.message.deleted`.
7. Mark messages read and confirm partner receives `chat.read.updated`.
8. Send typing whisper from one session and confirm typing indicator appears for ~3 seconds in the other.
