# Authentication & Authorization

<cite>
**Referenced Files in This Document**
- [RoleMiddleware.php](file://app/Http/Middleware/RoleMiddleware.php)
- [permission.php](file://config/permission.php)
- [2026_07_01_092410_create_permission_tables.php](file://database/migrations/2026_07_01_092410_create_permission_tables.php)
- [RolePermissionSeeder.php](file://database/seeders/RolePermissionSeeder.php)
- [User.php](file://app/Models/User.php)
- [RegisteredUserController.php](file://app/Http/Controllers/Auth/RegisteredUserController.php)
- [AuthenticatedSessionController.php](file://app/Http/Controllers/Auth/AuthenticatedSessionController.php)
- [PasswordResetLinkController.php](file://app/Http/Controllers/Auth/PasswordResetLinkController.php)
- [NewPasswordController.php](file://app/Http/Controllers/Auth/NewPasswordController.php)
- [EmailVerificationPromptController.php](file://app/Http/Controllers/Auth/EmailVerificationPromptController.php)
- [VerifyEmailController.php](file://app/Http/Controllers/Auth/VerifyEmailController.php)
- [EmailVerificationNotificationController.php](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php)
- [ConfirmablePasswordController.php](file://app/Http/Controllers/Auth/ConfirmablePasswordController.php)
- [FormulaPolicy.php](file://app/Policies/FormulaPolicy.php)
- [TrialPmPolicy.php](file://app/Policies/TrialPmPolicy.php)
</cite>

## Table of Contents
1. Introduction
2. Project Structure
3. Core Components
4. Architecture Overview
5. Detailed Component Analysis
6. Dependency Analysis
7. Performance Considerations
8. Troubleshooting Guide
9. Conclusion

## Introduction
This document explains the authentication and authorization system implemented in the application. It covers:
- Role-based access control (RBAC) using Spatie Permission
- User registration, login, password management, and email verification flows
- Three-tier role hierarchy with specific permissions
- Custom middleware for route protection
- Policy-based authorization patterns
- Security considerations, session management, password reset, and audit logging integration points

## Project Structure
The authentication and authorization features are organized across controllers, middleware, models, policies, configuration, migrations, and seeders:
- Controllers handle user-facing flows (registration, login, password reset, email verification)
- Middleware enforces role-based access at the HTTP layer
- Policies enforce resource-level permissions
- The User model integrates roles and permissions via a trait
- Configuration defines Spatie Permission behavior and table names
- Migrations create RBAC tables
- Seeders initialize roles and permissions

```mermaid
graph TB
subgraph "HTTP Layer"
RUC["RegisteredUserController"]
ASC["AuthenticatedSessionController"]
PRLC["PasswordResetLinkController"]
NPC["NewPasswordController"]
EVPC["EmailVerificationPromptController"]
VEC["VerifyEmailController"]
EVNC["EmailVerificationNotificationController"]
CPC["ConfirmablePasswordController"]
end
subgraph "Authorization"
RMW["RoleMiddleware"]
FP["FormulaPolicy"]
TPMP["TrialPmPolicy"]
end
subgraph "Domain"
U["User Model"]
end
subgraph "Config & Data"
CFG["permission.php"]
MIG["create_permission_tables.php"]
SEED["RolePermissionSeeder"]
end
RUC --> U
ASC --> U
PRLC --> U
NPC --> U
EVPC --> U
VEC --> U
EVNC --> U
CPC --> U
RMW --> U
FP --> U
TPMP --> U
U --> CFG
CFG --> MIG
SEED --> CFG
```

**Diagram sources**
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)
- [User.php:1-50](file://app/Models/User.php#L1-L50)
- [permission.php:1-220](file://config/permission.php#L1-L220)
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [RegisteredUserController.php:1-52](file://app/Http/Controllers/Auth/RegisteredUserController.php#L1-L52)
- [AuthenticatedSessionController.php:1-48](file://app/Http/Controllers/Auth/AuthenticatedSessionController.php#L1-L48)
- [PasswordResetLinkController.php:1-46](file://app/Http/Controllers/Auth/PasswordResetLinkController.php#L1-L46)
- [NewPasswordController.php:1-64](file://app/Http/Controllers/Auth/NewPasswordController.php#L1-L64)
- [EmailVerificationPromptController.php:1-22](file://app/Http/Controllers/Auth/EmailVerificationPromptController.php#L1-L22)
- [VerifyEmailController.php:1-28](file://app/Http/Controllers/Auth/VerifyEmailController.php#L1-L28)
- [EmailVerificationNotificationController.php:1-25](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php#L1-L25)
- [ConfirmablePasswordController.php:1-41](file://app/Http/Controllers/Auth/ConfirmablePasswordController.php#L1-L41)

**Section sources**
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [User.php:1-50](file://app/Models/User.php#L1-L50)
- [permission.php:1-220](file://config/permission.php#L1-L220)
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [RegisteredUserController.php:1-52](file://app/Http/Controllers/Auth/RegisteredUserController.php#L1-L52)
- [AuthenticatedSessionController.php:1-48](file://app/Http/Controllers/Auth/AuthenticatedSessionController.php#L1-L48)
- [PasswordResetLinkController.php:1-46](file://app/Http/Controllers/Auth/PasswordResetLinkController.php#L1-L46)
- [NewPasswordController.php:1-64](file://app/Http/Controllers/Auth/NewPasswordController.php#L1-L64)
- [EmailVerificationPromptController.php:1-22](file://app/Http/Controllers/Auth/EmailVerificationPromptController.php#L1-L22)
- [VerifyEmailController.php:1-28](file://app/Http/Controllers/Auth/VerifyEmailController.php#L1-L28)
- [EmailVerificationNotificationController.php:1-25](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php#L1-L25)
- [ConfirmablePasswordController.php:1-41](file://app/Http/Controllers/Auth/ConfirmablePasswordController.php#L1-L41)
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)

## Core Components
- User model integrates roles and permissions through the HasRoles trait and standard Laravel authentication features.
- Spatie Permission is configured to use default models and table names; caching is enabled by default.
- RBAC tables are created via migration and populated by a seeder that defines roles and their permissions.
- Route-level protection uses a custom RoleMiddleware that checks if an authenticated user has any of the required roles.
- Resource-level protection uses policies that check permissions and business rules (e.g., creator-only edits).

Key implementation highlights:
- Roles and permissions are seeded with clear naming conventions (e.g., formula.view, trial_pm.approve_tahap1).
- Policies encapsulate both permission checks and domain-specific constraints (e.g., approval status checks).
- Email verification and password reset flows follow Laravel’s built-in mechanisms.

**Section sources**
- [User.php:1-50](file://app/Models/User.php#L1-L50)
- [permission.php:1-220](file://config/permission.php#L1-L220)
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)

## Architecture Overview
The system combines middleware-based route protection and policy-based resource authorization on top of Laravel’s authentication stack.

```mermaid
sequenceDiagram
participant Client as "Client"
participant Router as "Routes"
participant MW as "RoleMiddleware"
participant Ctrl as "Auth Controller"
participant Auth as "Auth Guard"
participant DB as "Database"
participant Cache as "Permission Cache"
Client->>Router : "Request protected route"
Router->>MW : "Apply role middleware"
MW->>Auth : "Check if user is authenticated"
alt Not authenticated
MW-->>Client : "Redirect to login"
else Authenticated
MW->>Cache : "Load user roles"
Cache-->>MW : "Roles"
MW->>DB : "Resolve roles if needed"
DB-->>MW : "Role data"
MW->>MW : "Check if user has any required role"
alt Allowed
MW-->>Ctrl : "Proceed to controller"
else Denied
MW-->>Client : "Redirect to dashboard with error"
end
end
```

**Diagram sources**
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [User.php:1-50](file://app/Models/User.php#L1-L50)
- [permission.php:1-220](file://config/permission.php#L1-L220)

## Detailed Component Analysis

### Role-Based Access Control (Spatie Permission)
- Models and tables:
  - Uses default Spatie models for roles and permissions.
  - Migration creates roles, permissions, and pivot tables for model_has_roles, model_has_permissions, and role_has_permissions.
- Caching:
  - Permissions are cached by default for performance; cache key and store are configurable.
- Seeding:
  - Seeder initializes core permissions and assigns them to roles.

```mermaid
erDiagram
ROLE {
bigint id PK
string name
string guard_name
timestamp created_at
timestamp updated_at
}
PERMISSION {
bigint id PK
string name
string guard_name
timestamp created_at
timestamp updated_at
}
MODEL_HAS_ROLES {
bigint role_id FK
string model_type
bigint model_id
}
MODEL_HAS_PERMISSIONS {
bigint permission_id FK
string model_type
bigint model_id
}
ROLE_HAS_PERMISSIONS {
bigint permission_id FK
bigint role_id FK
}
ROLE ||--o{ MODEL_HAS_ROLES : "has many"
PERMISSION ||--o{ MODEL_HAS_PERMISSIONS : "has many"
ROLE ||--o{ ROLE_HAS_PERMISSIONS : "has many"
PERMISSION ||--o{ ROLE_HAS_PERMISSIONS : "has many"
```

**Diagram sources**
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)
- [permission.php:1-220](file://config/permission.php#L1-L220)

**Section sources**
- [permission.php:1-220](file://config/permission.php#L1-L220)
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)

### User Model Integration
- Integrates HasRoles trait for RBAC.
- Uses Notifiable for email notifications (verification, password reset).
- Casts ensure proper datetime handling for email_verified_at and secure hashing for passwords.

```mermaid
classDiagram
class User {
+string name
+string email
+datetime email_verified_at
+string password
+formulas()
+trialRms()
+trialPms()
}
class Authenticatable
class Notifiable
class HasRoles
User --|> Authenticatable
User ..> Notifiable
User ..> HasRoles
```

**Diagram sources**
- [User.php:1-50](file://app/Models/User.php#L1-L50)

**Section sources**
- [User.php:1-50](file://app/Models/User.php#L1-L50)

### Registration Flow
- Validates input (name, email, password confirmation).
- Creates user with hashed password.
- Fires Registered event.
- Logs in the user and redirects to dashboard.

```mermaid
sequenceDiagram
participant Client as "Client"
participant Ctrl as "RegisteredUserController"
participant DB as "Database"
participant Event as "Registered Event"
participant Auth as "Auth Guard"
Client->>Ctrl : "POST /register"
Ctrl->>Ctrl : "Validate request"
Ctrl->>DB : "Create user (hashed password)"
Ctrl->>Event : "Fire Registered(user)"
Ctrl->>Auth : "Login user"
Auth-->>Ctrl : "Session established"
Ctrl-->>Client : "Redirect to dashboard"
```

**Diagram sources**
- [RegisteredUserController.php:1-52](file://app/Http/Controllers/Auth/RegisteredUserController.php#L1-L52)

**Section sources**
- [RegisteredUserController.php:1-52](file://app/Http/Controllers/Auth/RegisteredUserController.php#L1-L52)

### Login and Logout Flow
- Login validates credentials via a dedicated request object, authenticates the user, regenerates the session, and redirects intended.
- Logout invalidates the session and regenerates CSRF token.

```mermaid
sequenceDiagram
participant Client as "Client"
participant Ctrl as "AuthenticatedSessionController"
participant Req as "LoginRequest"
participant Auth as "Auth Guard"
participant Session as "Session"
Client->>Ctrl : "POST /login"
Ctrl->>Req : "authenticate()"
Req-->>Ctrl : "Success"
Ctrl->>Session : "regenerate()"
Ctrl-->>Client : "Redirect to intended/dashboard"
Client->>Ctrl : "POST /logout"
Ctrl->>Auth : "logout()"
Ctrl->>Session : "invalidate() + regenerateToken()"
Ctrl-->>Client : "Redirect to home"
```

**Diagram sources**
- [AuthenticatedSessionController.php:1-48](file://app/Http/Controllers/Auth/AuthenticatedSessionController.php#L1-L48)

**Section sources**
- [AuthenticatedSessionController.php:1-48](file://app/Http/Controllers/Auth/AuthenticatedSessionController.php#L1-L48)

### Password Reset Flow
- Requesting a reset link validates email and sends a reset token via mail.
- Resetting the password validates token, email, and new password, then updates the user record and fires a PasswordReset event.

```mermaid
sequenceDiagram
participant Client as "Client"
participant PRLC as "PasswordResetLinkController"
participant NPC as "NewPasswordController"
participant Mail as "Mail Service"
participant DB as "Database"
participant Event as "PasswordReset Event"
Client->>PRLC : "POST /forgot-password"
PRLC->>PRLC : "Validate email"
PRLC->>Mail : "Send reset link"
Mail-->>PRLC : "Sent"
PRLC-->>Client : "Back with status"
Client->>NPC : "POST /reset-password"
NPC->>NPC : "Validate token, email, password"
NPC->>DB : "Update password + remember_token"
NPC->>Event : "Fire PasswordReset(user)"
NPC-->>Client : "Redirect to login with status"
```

**Diagram sources**
- [PasswordResetLinkController.php:1-46](file://app/Http/Controllers/Auth/PasswordResetLinkController.php#L1-L46)
- [NewPasswordController.php:1-64](file://app/Http/Controllers/Auth/NewPasswordController.php#L1-L64)

**Section sources**
- [PasswordResetLinkController.php:1-46](file://app/Http/Controllers/Auth/PasswordResetLinkController.php#L1-L46)
- [NewPasswordController.php:1-64](file://app/Http/Controllers/Auth/NewPasswordController.php#L1-L64)

### Email Verification Flow
- Prompt controller shows verification page or redirects if already verified.
- Resend notification controller sends a new verification email.
- Verify controller marks email as verified and fires Verified event.

```mermaid
sequenceDiagram
participant Client as "Client"
participant EVPC as "EmailVerificationPromptController"
participant EVNC as "EmailVerificationNotificationController"
participant VEC as "VerifyEmailController"
participant User as "User"
participant Event as "Verified Event"
Client->>EVPC : "GET /email/verify"
EVPC-->>Client : "View verify-email or redirect"
Client->>EVNC : "POST /email/verification-notification"
EVNC->>User : "sendEmailVerificationNotification()"
EVNC-->>Client : "Back with status"
Client->>VEC : "GET /email/verify/{id}/{hash}"
VEC->>User : "markEmailAsVerified()"
VEC->>Event : "Fire Verified(user)"
VEC-->>Client : "Redirect to dashboard with verified=1"
```

**Diagram sources**
- [EmailVerificationPromptController.php:1-22](file://app/Http/Controllers/Auth/EmailVerificationPromptController.php#L1-L22)
- [EmailVerificationNotificationController.php:1-25](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php#L1-L25)
- [VerifyEmailController.php:1-28](file://app/Http/Controllers/Auth/VerifyEmailController.php#L1-L28)

**Section sources**
- [EmailVerificationPromptController.php:1-22](file://app/Http/Controllers/Auth/EmailVerificationPromptController.php#L1-L22)
- [EmailVerificationNotificationController.php:1-25](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php#L1-L25)
- [VerifyEmailController.php:1-28](file://app/Http/Controllers/Auth/VerifyEmailController.php#L1-L28)

### Confirm Sensitive Action Password
- Requires re-entering password before performing sensitive actions.
- Stores confirmation timestamp in session.

```mermaid
flowchart TD
Start(["Show confirm password"]) --> Validate["Validate current password"]
Validate --> Valid{"Valid?"}
Valid --> |No| Error["Return validation error"]
Valid --> |Yes| Store["Store auth.password_confirmed_at in session"]
Store --> Redirect["Redirect to intended route"]
```

**Diagram sources**
- [ConfirmablePasswordController.php:1-41](file://app/Http/Controllers/Auth/ConfirmablePasswordController.php#L1-L41)

**Section sources**
- [ConfirmablePasswordController.php:1-41](file://app/Http/Controllers/Auth/ConfirmablePasswordController.php#L1-L41)

### Role Hierarchy and Permissions
- Roles defined in the seeder:
  - Staff R&D
  - Operational Manager
  - General Manager
  - Superadmin (created but not assigned explicit permissions in the seeder)
- Permissions include CRUD and approval actions for formulas, trial RM, and trial PM, plus approval center access.
- Example assignments:
  - Staff R&D can create/view/edit items and perform department approvals.
  - Operational Manager can view and approve stage 1.
  - General Manager can view and approve stage 2.

Practical guidance:
- To add a new role, create it in the seeder and assign necessary permissions.
- To add a new permission, define it in the seeder and assign it to relevant roles.
- Protect routes using the RoleMiddleware with one or more roles.

**Section sources**
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)

### Custom RoleMiddleware
- Ensures the user is authenticated.
- Checks if the user has any of the specified roles.
- Redirects unauthorized users to the dashboard with an error message.

```mermaid
flowchart TD
Entry(["handle(request, ...roles)"]) --> CheckAuth{"auth()->check()?"}
CheckAuth --> |No| ToLogin["redirect('login')"]
CheckAuth --> |Yes| GetUser["user = auth()->user()"]
GetUser --> LoopRoles{"for each role"}
LoopRoles --> HasRole{"user->hasRole(role)?"}
HasRole --> |Yes| Next["return $next(request)"]
HasRole --> |No| NextRole["continue loop"]
NextRole --> LoopRoles
LoopRoles --> |None matched| Deny["redirect('dashboard') with error"]
```

**Diagram sources**
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)

**Section sources**
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)

### Policy-Based Authorization Patterns
- FormulaPolicy:
  - viewAny/view: require formula.view permission.
  - edit/update: require formula.edit and ownership with Draft/Rejected status.
  - submit: allow creator to submit when Draft/Rejected and has edit permission.
  - reformulate: allow creation from Approved state.
  - delete: allow creator only when Draft and has delete permission.
- TrialPmPolicy:
  - viewAny/view: require trial_pm.view.
  - edit/update/delete: require ownership, Draft status, and appropriate permissions.
  - submit: allow creator when Draft and has edit permission.
  - approve: allow department approval when Pending Review and has department_approve permission.

```mermaid
classDiagram
class FormulaPolicy {
+viewAny(user) bool
+view(user, formula) bool
+create(user) bool
+edit(user, formula) bool
+update(user, formula) bool
+submit(user, formula) bool
+reformulate(user, formula) bool
+delete(user, formula) bool
}
class TrialPmPolicy {
+viewAny(user) bool
+view(user, trial) bool
+create(user) bool
+edit(user, trial) bool
+update(user, trial) bool
+delete(user, trial) bool
+submit(user, trial) bool
+approve(user, trial) bool
}
class User {
+can(permission) bool
}
FormulaPolicy --> User : "uses"
TrialPmPolicy --> User : "uses"
```

**Diagram sources**
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)

**Section sources**
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)

### Practical Examples

- Implementing a new role:
  - Add the role in the seeder and assign permissions accordingly.
  - Reference: [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)

- Adding a new permission:
  - Define the permission name in the seeder and attach it to relevant roles.
  - Reference: [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)

- Protecting routes with RoleMiddleware:
  - Apply middleware with one or more roles to restrict access.
  - Reference: [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)

- Using policies in controllers:
  - Use authorize() calls corresponding to policy methods (e.g., create, edit, submit, approve).
  - Reference: [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86), [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)

**Section sources**
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)

## Dependency Analysis
- User depends on Spatie Permission traits for role/permission checks.
- RoleMiddleware depends on the authenticated user and role resolution.
- Policies depend on permission checks and domain state (ownership, approval_status).
- Seeder depends on Spatie models to create roles and permissions.
- Config controls table names and caching behavior.

```mermaid
graph LR
U["User"] --> SP["HasRoles Trait"]
RMW["RoleMiddleware"] --> U
POL["Policies"] --> U
SEED["RolePermissionSeeder"] --> SP
CFG["permission.php"] --> MIG["Migration Tables"]
SEED --> CFG
```

**Diagram sources**
- [User.php:1-50](file://app/Models/User.php#L1-L50)
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [permission.php:1-220](file://config/permission.php#L1-L220)
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)

**Section sources**
- [User.php:1-50](file://app/Models/User.php#L1-L50)
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [FormulaPolicy.php:1-86](file://app/Policies/FormulaPolicy.php#L1-L86)
- [TrialPmPolicy.php:1-57](file://app/Policies/TrialPmPolicy.php#L1-L57)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [permission.php:1-220](file://config/permission.php#L1-L220)
- [2026_07_01_092410_create_permission_tables.php:1-138](file://database/migrations/2026_07_01_092410_create_permission_tables.php#L1-L138)

## Performance Considerations
- Permission caching:
  - Enabled by default with a 24-hour expiration; consider adjusting cache store and key if needed.
- Database queries:
  - Minimize repeated role/permission checks by leveraging caching.
- Middleware overhead:
  - Keep role lists minimal per route to reduce iteration cost.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
- Unauthorized access despite having a role:
  - Ensure the user is assigned the correct role and the route uses RoleMiddleware with matching role names.
  - Reference: [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- Permission not found errors:
  - Verify permissions exist in the database via the seeder and that they are assigned to roles.
  - Reference: [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- Email verification issues:
  - Confirm mail configuration and that verification notifications are sent.
  - Reference: [EmailVerificationNotificationController.php:1-25](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php#L1-L25)
- Password reset failures:
  - Check token validity and email match; ensure mail delivery and token expiration settings.
  - Reference: [PasswordResetLinkController.php:1-46](file://app/Http/Controllers/Auth/PasswordResetLinkController.php#L1-L46), [NewPasswordController.php:1-64](file://app/Http/Controllers/Auth/NewPasswordController.php#L1-L64)

**Section sources**
- [RoleMiddleware.php:1-35](file://app/Http/Middleware/RoleMiddleware.php#L1-L35)
- [RolePermissionSeeder.php:1-112](file://database/seeders/RolePermissionSeeder.php#L1-L112)
- [EmailVerificationNotificationController.php:1-25](file://app/Http/Controllers/Auth/EmailVerificationNotificationController.php#L1-L25)
- [PasswordResetLinkController.php:1-46](file://app/Http/Controllers/Auth/PasswordResetLinkController.php#L1-L46)
- [NewPasswordController.php:1-64](file://app/Http/Controllers/Auth/NewPasswordController.php#L1-L64)

## Conclusion
The application implements a robust authentication and authorization system combining:
- Spatie Permission for RBAC with well-defined roles and granular permissions
- Custom middleware for route-level protection
- Policies for resource-level authorization with business rule enforcement
- Standard Laravel flows for registration, login, password reset, and email verification
- Clear separation of concerns and extensibility for adding new roles, permissions, and protections

[No sources needed since this section summarizes without analyzing specific files]