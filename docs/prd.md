# Spin Wheel Reward System
**Minimal Product Requirements Document (PRD)**

## 1. Overview

### Goal

Build a simple **Spin Wheel Reward System** using **Hexagonal Architecture (Ports & Adapters)**.

The primary objective is to learn how to separate business logic from infrastructure by keeping the application core independent of any framework.

The project will be implemented in **Symfony** first and later migrated to **Laravel** without modifying the business logic.

---

## 2. Objectives

- Learn Hexagonal Architecture
- Understand Ports & Adapters
- Build framework-independent business logic
- Support multiple entry points (HTTP and CLI)
- Demonstrate how business logic can be reused across frameworks

---

## 3. Scope

### In Scope

- User earns points by completing tasks
- User spends points to spin a reward wheel
- Wheel randomly returns a multiplier
- User receives reward based on multiplier
- View current point balance

### Out of Scope

- Authentication
- Authorization
- Admin panel
- Database optimization
- Notifications
- Leaderboards
- Multiplayer functionality
- API authentication

---

# 4. Functional Requirements

## 4.1 User

A user has:

- ID
- Name
- Current Points

---

## 4.2 Complete Task

A user can complete a predefined task to earn points.

Example tasks:

| Task | Reward |
|------|--------|
| Daily Login | 50 |
| Watch Video | 100 |
| Invite Friend | 500 |

### Rules

- Completing a task awards points.
- Points are immediately added to the user's balance.

---

## 4.3 Spin Wheel

A user can spend points to spin the reward wheel.

Allowed spin costs:

- 500
- 1000
- 2000

### Rules

- User must have enough points.
- Deduct the selected spin cost.
- Randomly select a multiplier.
- Award the calculated reward.
- Record the spin.

---

## 4.4 Wheel Multipliers

Possible outcomes:

| Multiplier | Description |
|------------|-------------|
| None | No reward |
| 2x | Double reward |
| 3x | Triple reward |
| 4x | Quadruple reward |

Each outcome has an equal probability for the initial implementation.

---

## 4.5 Reward Calculation

```
Reward = Spin Cost × Multiplier
```

### Example 1

Current Balance

```
2500
```

Spin Cost

```
1000
```

Wheel Result

```
3x
```

Reward

```
3000
```

Final Balance

```
2500
-1000
+3000
=4500
```

### Example 2

Current Balance

```
1500
```

Spin Cost

```
500
```

Wheel Result

```
None
```

Reward

```
0
```

Final Balance

```
1500
-500
+0
=1000
```

---

# 5. Business Rules

## BR-001

User cannot spin if they have insufficient points.

---

## BR-002

Only these spin costs are allowed:

- 500
- 1000
- 2000

---

## BR-003

Multiplier must be one of:

- None
- 2x
- 3x
- 4x

---

## BR-004

Reward is immediately added after the spin.

---

## BR-005

Every spin must be recorded.

---

# 6. Use Cases

## UC-001 Complete Task

### Input

- User ID
- Task

### Output

- Updated point balance

---

## UC-002 Spin Wheel

### Input

- User ID
- Spin Cost

### Output

- Selected multiplier
- Reward earned
- Remaining balance

---

## UC-003 View Balance

### Input

- User ID

### Output

- Current points

---

# 7. API Endpoints

## Complete Task

```
POST /tasks/complete
```

---

## Spin Wheel

```
POST /wheel/spin
```

---

## View Balance

```
GET /users/{id}/balance
```

---

# 8. CLI Commands

Complete Task

```
php bin/console task:complete
```

Spin Wheel

```
php bin/console wheel:spin
```

View Balance

```
php bin/console user:balance
```

---

# 9. Domain Model

Core domain objects:

- User
- Task
- Spin
- Wheel
- Reward
- Multiplier
- Points

---

# 10. Application Use Cases

- CompleteTask
- SpinWheel
- GetBalance

---

# 11. Ports

## Input Ports

- CompleteTaskUseCase
- SpinWheelUseCase
- GetBalanceUseCase

## Output Ports

- UserRepository
- SpinRepository
- RandomMultiplierGenerator

---

# 12. Adapters

## Primary Adapters

- HTTP Controllers
- CLI Commands

## Secondary Adapters

- InMemoryUserRepository
- InMemorySpinRepository
- Doctrine Repository (later)
- Eloquent Repository (later)
- Random Multiplier Generator

---

# 13. Persistence Strategy

### Phase 1

Use an **In-Memory Repository** for simplicity.

### Phase 2

Replace with **Doctrine ORM**.

### Phase 3

Replace Doctrine with **Laravel Eloquent**.

No changes should be required in:

- Domain
- Application Layer
- Business Rules
- Use Cases

---

# 14. Non-Functional Requirements

The business logic must not depend on:

- Symfony
- Laravel
- Doctrine
- Eloquent
- HTTP
- CLI

Frameworks should only provide adapters and dependency injection.

---

# 15. Success Criteria

The project is considered complete when:

- Users can earn points by completing tasks.
- Users can spend points to spin the wheel.
- Rewards are correctly calculated.
- Spins are recorded.
- The same Domain and Application layers run unchanged in both Symfony and Laravel.
- Only framework-specific adapters are replaced during migration.

---

# 16. Learning Outcome

This project should demonstrate that:

- Business logic lives inside the Domain and Application layers.
- Frameworks are implementation details.
- HTTP, CLI, databases, and random number generators are replaceable adapters.
- The application core remains unchanged when migrating from Symfony to Laravel, validating the principles of Hexagonal Architecture.