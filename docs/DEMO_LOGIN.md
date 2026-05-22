# Demo database (after `php artisan migrate:fresh --seed`)

**Password for every user:** `Admin@123`

## Accounts

| Email | Type | Notes |
|-------|------|--------|
| super@bhagyalaxmi.local | Super Admin | Roles & Permissions menu |
| admin@bhagyalaxmi.local | Admin | Full panel |
| staff1@bhagyalaxmi.local | Staff | Role: Entry Operator |
| staff2@bhagyalaxmi.local | Staff | Role: Student Viewer |
| staff3@bhagyalaxmi.local | Staff | Role: Student Manager |
| staff4@bhagyalaxmi.local | Staff | Role: College Manager |
| staff5@bhagyalaxmi.local | Staff | Role: Full Staff Panel |

## Data

- **4 colleges** from `config/internship_registration.php`
- **15 students** (mixed pending / approved / rejected)
- **5 staff** — each has `users.role_id` pointing to an assignable role in `roles`

## RBAC model

- `permissions` — permission keys
- `roles` — system roles (`super_admin`, `admin`, `student`) + assignable staff roles
- `permission_role` — permissions granted to each role
- `users.role_id` — **single source of truth**; FK to `roles.id`

Access is computed from the role’s permissions (super_admin/admin get all keys automatically).

## Public registration

Colleges load from DB (`colleges.slug`). Example link:

`/admin/register/bhagya-laxmi-internship-jun-2026-womens-college-samastipur`

## Recycle bin (admin)

Menu **Recycle Bin** — restore (`bin_manage`) or delete forever (`bin_delete_permanent`). Colleges and students use soft delete.
