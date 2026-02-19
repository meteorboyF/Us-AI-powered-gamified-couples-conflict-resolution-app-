# AI Coach V1 Realtime

## Channel
- `private(couple.{coupleId})`

## Broadcast Events
- `ai.session.message.created`
- `ai.draft.created`
- `ai.session.closed`

## Payload Shapes
- `ai.session.message.created`
  - `couple_id`
  - `session_id`
  - `message`
    - `id`
    - `sender_type`
    - `sender_user_id`
    - `content`
    - `created_at`
- `ai.draft.created`
  - `couple_id`
  - `session_id`
  - `draft`
    - `id`
    - `draft_type`
    - `title`
    - `content`
    - `status`
    - `created_at`
- `ai.session.closed`
  - `couple_id`
  - `session_id`
  - `status`
  - `closed_at`

## Echo Listener Example
```js
import Echo from 'laravel-echo';

window.Echo.private(`couple.${coupleId}`)
    .listen('.ai.session.message.created', (event) => {
        console.log('AI message created', event);
    })
    .listen('.ai.draft.created', (event) => {
        console.log('AI draft created', event);
    })
    .listen('.ai.session.closed', (event) => {
        console.log('AI session closed', event);
    });
```

## Local Reverb
```bash
php artisan reverb:start
```
