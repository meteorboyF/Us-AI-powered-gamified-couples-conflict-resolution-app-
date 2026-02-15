# Us — AI-Powered Gamified Couples Conflict Resolution Platform

**Project Document (PRD + System Blueprint)**

---

## 1) Vision

**Us** is a cute, immersive couples platform where communication feels like playing a cozy game together. It turns daily emotional effort into visible progress inside a shared "world" (garden/house/kitchen/etc.), while offering safe AI support for conflict resolution, emotional reflection, and reconnection.

---

## 2) Core Principles

- **Cute + immersive first**: feels like a real game UI, not a CRUD app.
- **Communication > content**: features exist to improve communication, not to distract.
- **Safety & privacy**: couple-only data; vault with strong protection; no "auto-sharing" from AI.
- **Small daily habits**: lightweight check-ins, missions, and micro-actions.
- **Testability**: every feature has clear acceptance criteria + core tests.

---

## 3) Target Users & Use Cases

### Users
- Couples in relationships (dating/married/long-distance).
- Want a fun, low-pressure communication space.
- Want structured help during misunderstandings.

### Primary use cases
- Daily check-in → build world progress.
- Messages + voice notes → stay connected.
- During conflict: vent to AI coach → get repair steps → send a "bridge message".
- "Vault" memories resurfacing during rough patches.
- Gift ideas personalized to both partners.

---

## 4) Product Modules (Features)

### A) Onboarding & Couple Linking

**Goal**: Onboarding must be frictionless for two partners.

**Flow**:
1. User signs up (email/phone/social optional).
2. Create couple OR join via code/invite link.
3. Choose World Theme (Garden / Cottage / Kitchen / Farm / Beach Cabin / Space Station / Book Café etc.).
4. Quick preferences: love language, interests, "comfort actions", boundaries.

**Acceptance Criteria**:
- Couple linking is secure (invite tokens expire).
- Only linked partners can access couple features.
- Theme can be changed later (with animation/transition).

---

### B) Shared World (Gamification Home)

This is the main home screen. It's a real game scene.

#### World Types (MVP: 3, scalable)
- **Enchanted Garden**: flowers, pond, gazebo, lanterns.
- **Cozy Cottage**: small house, fences, tea table, lights.
- **Cute Kitchen**: warm interior, shelves, decor, cooking station.

**Later expansions**:
- Farm, Treehouse, Rooftop Garden, Aquarium Room, Space Station, Café, Beach Hut.

#### Core Mechanics
- Couple has shared XP / Love Seeds / Streak.
- **Mood/Vibe** affects ambience:
  - High vibe → bright, lively, glowing lights.
  - Low vibe → gloom overlay, fewer animations, "wilted" items.
- **Items** exist in categories:
  - **Core** (main structure upgrades)
  - **Decor** (cosmetic)
  - **Utility** (unlocks interactions)
  - **Comfort** (special items that trigger vault memories)

#### Actions that affect world
- Daily mood check-in
- Completing missions
- Repair steps after conflict
- Healthy messaging streaks (anti-spam rules)
- "Appreciation" actions (compliment / gratitude note)

#### UI Requirements (non-amateur)
- Layered scene (background/mid/foreground)
- Slot-based placement for items
- Build drawer + upgrade modal
- Micro-animations: sparkles, gentle parallax, floating +XP

**Acceptance Criteria**:
- World loads fast, looks immersive.
- Upgrades feel satisfying.
- Vibe overlay is visible but subtle.
- New items appear instantly without breaking layout.

---

### C) Messaging (Couple Chat)

**MVP requirements**:
- One-to-one chat between partners
- Message persistence and infinite scroll
- Audio messages
- Attachments (image/file)
- Delivered/read status
- Typing indicators and presence (online/away)
- Notifications (in-app, later push)

**Realtime architecture**:
- WebSockets (e.g., Laravel Reverb or equivalent stack)
- Private/presence channels per couple

**Acceptance Criteria**:
- Two sessions see messages instantly.
- Receipts work reliably.
- Uploads are validated and stored.
- No access from non-partners.

---

### D) Vault (Memory Safe)

A "safe box" of memories designed to help during hard times.

#### Contents
- Photos
- Short notes
- Audio messages
- "Reason I love you" list
- Anniversary timeline

#### Special mechanics
- **Comfort Mode**: when vibe is low, the vault gently suggests supportive memories.
- **Locking**: optional lock on intimate items (PIN/biometric later).
- **Dual-consent Unlock (Recommended)**:
  - For sensitive items, both partners must approve within a time window.

**Acceptance Criteria**:
- Only couple can see vault.
- Sensitive items have additional protection.
- Comfort prompts never expose something private without proper consent rules.

---

### E) AI Coach (Vent / Bridge / Repair)

The AI is not a therapist; it is a communication coach.

#### Modes
1. **Vent Mode**
   - Private journaling + emotional reflection prompts.

2. **Bridge Mode**
   - Converts raw feelings into a respectful message for partner.
   - **Important**: user must approve before sending anywhere.

3. **Repair Mode**
   - Step-by-step de-escalation and reconnection plan (small doable actions).

#### Personalization
- Partner personality preferences, communication style, triggers.
- Love language integration.

#### Safety Rules
- No automatic sending.
- "High conflict" detection → encourages pause, grounding, seeking help.
- No disallowed content.

**Acceptance Criteria**:
- AI responses feel supportive + structured.
- Always asks for approval before sharing.
- Logs are private and auditable.

---

### F) Missions & Daily Check-ins

This is the habit engine.

#### Mission types
- "1 appreciation note"
- "2-minute voice note"
- "One small apology"
- "Plan a micro-date"
- "Ask one curious question"

#### Daily check-in
- Mood slider + emotion tags + "what I need today"
- This influences world ambience.

**Acceptance Criteria**:
- Missions rotate daily
- Completion grants XP and sometimes Love Seeds
- Anti-spam: avoid farming XP by sending meaningless messages

---

### G) Gift Suggestions (AI-powered)

Personalized gift ideas based on both partners.

#### Inputs:
- Budget range
- Occasion (anniversary/sorry/comfort/surprise)
- Recipient personality + likes/dislikes
- Time constraint

#### Output format:
- Category, price range, "why it fits", personalization tip

**Acceptance Criteria**:
- Doesn't require scraping real stores
- Fallback mode works without AI
- Suggestions feel relevant

---

## 5) Non-Functional Requirements

### Security & Privacy
- Couple data isolation (policies/ACL)
- Secure uploads (signed URLs optional)
- Rate limits for AI calls
- Sensitive data encrypted at rest (vault metadata + lock info)

### Reliability
- Offline-friendly UX for world (cached state)
- Realtime reconnect handling
- Observability: logs, error tracking

### Performance
- World scene must load quickly
- Infinite scroll must not lag
- Avoid heavy canvases early

---

## 6) Data Model (High Level)

### Entities:
- **User**
- **Couple** (two users linked)
- **Conversation** / **Message** / **MessageReceipt**
- **WorldState** (world_type, vibe_score, xp, seeds, streak)
- **WorldItem** (item_key, level, slot, built_at)
- **Mission** / **MissionAssignment** / **MissionCompletion**
- **MoodCheckin**
- **VaultItem** (type, media_url, tags, locked, consent_required)
- **AIChatSession** (mode, prompts, safety flags)
- **GiftSuggestion** / **Favorites**

---

## 7) Tech Stack (team can choose final)

You can implement this with any stack. A typical proven setup:

- **Frontend**: Web app (React/Next or TALL Livewire) + Tailwind
- **Backend**: Node/Laravel/Django (whatever your team is strongest in)
- **Realtime**: WebSockets (Socket.io / Reverb / Pusher-like)
- **DB**: PostgreSQL/MySQL
- **Storage**: S3-compatible for media
- **AI**: Gemini/OpenAI (abstract behind provider interface)
- **CI**: GitHub Actions for tests + lint + build

**Key rule**: keep AI behind a provider interface so you can swap models.

---

## 8) Milestones

### Phase 1: Foundation (Weeks 1-2)
- User authentication & couple linking
- Basic world selection & display
- Database schema setup

### Phase 2: Core Communication (Weeks 3-4)
- Realtime messaging with WebSockets
- Message receipts & typing indicators
- Basic vault functionality

### Phase 3: Gamification (Weeks 5-6)
- World building mechanics
- XP/Love Seeds system
- Daily missions & check-ins

### Phase 4: AI Integration (Weeks 7-8)
- AI Coach (Vent/Bridge/Repair modes)
- Gift suggestions
- Comfort mode triggers

### Phase 5: Polish & Launch (Weeks 9-10)
- UI/UX refinement
- Performance optimization
- Testing & bug fixes
- Deployment

---

**Document Version**: 1.0  
**Last Updated**: 2026-02-16  
**Status**: Foundation Ready
