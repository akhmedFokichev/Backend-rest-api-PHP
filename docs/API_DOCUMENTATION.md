# API Documentation

Base URL examples use `https://identity.xsdk.ru/api/v1`.

## Authentication

Protected endpoints require header:

`Authorization: Bearer mock-token`

If missing or invalid, API returns:

```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing Authorization header"
}
```

Status: `401`.

## Health and Service

### `GET /`

Checks that app is running.

Response:
- Status: `200`
- Body: `OK` (plain text)

### `GET /api/v1/db-check`

Checks DB availability.

Success response:

```json
{
  "ok": true,
  "database": "connected"
}
```

Error response example:

```json
{
  "ok": false,
  "error": "Database not configured. In config/db.local.php set real credentials (host, dbname, user, pass), not the example your_user/your_database."
}
```

Status: `200` on success, `503` on error.

## Users

## `POST /api/v1/user`

Create user.

Request body:

```json
{
  "login": "admin@example.com",
  "password": "secret123",
  "role": 100
}
```

Notes:
- `login` and `password` are required.
- `role` is optional. Default is `10` (User).
- Supported roles:
  - `0` Guest
  - `10` User
  - `50` Moderator
  - `100` Admin

Success response:

```json
{
  "id": 1,
  "login": "admin@example.com",
  "role": 100,
  "roleLabel": "Администратор",
  "createdAt": "2026-03-15 20:10:00",
  "updatedAt": "2026-03-15 20:10:00"
}
```

Status: `201`.

Error responses:
- `400` when `login` or `password` missing
- `409` when user already exists

### `POST /api/v1/user/login`

Authorize user by login/password.

Request body:

```json
{
  "login": "admin@example.com",
  "password": "secret123"
}
```

Success response:

```json
{
  "id": 1,
  "login": "admin@example.com",
  "role": 100,
  "roleLabel": "Администратор",
  "message": "authorized"
}
```

Status: `200`.

Error responses:
- `400` when `login` or `password` missing
- `401` invalid credentials

### `DELETE /api/v1/user/{id}` (protected)

Delete user by ID.

Example:

```bash
curl -X DELETE \
  -H "Authorization: Bearer mock-token" \
  https://identity.xsdk.ru/api/v1/user/1
```

Status:
- `204` success
- `400` invalid id
- `404` user not found
- `401` unauthorized

## Profiles (1 profile per user)

### `GET /api/v1/user/{id}/profile` (protected)

Get profile by user ID.

Success response:

```json
{
  "id": 1,
  "userId": 1,
  "firstName": "Ivan",
  "lastName": "Ivanov",
  "phone": "+79990001122",
  "avatarUrl": "https://example.com/a.png",
  "createdAt": "2026-03-15 20:15:00",
  "updatedAt": "2026-03-15 20:15:00"
}
```

Status:
- `200` success
- `400` invalid user id
- `404` user/profile not found
- `401` unauthorized

### `PUT /api/v1/user/{id}/profile` (protected)

Create or update profile for user.

Request body:

```json
{
  "firstName": "Ivan",
  "lastName": "Ivanov",
  "phone": "+79990001122",
  "avatarUrl": "https://example.com/a.png"
}
```

Notes:
- If profile does not exist, it is created.
- Empty string or `null` clears field.

Status:
- `200` success (returns profile object)
- `400` invalid user id
- `404` user not found
- `401` unauthorized

### `DELETE /api/v1/user/{id}/profile` (protected)

Delete profile by user ID.

Status:
- `204` success
- `400` invalid user id
- `404` profile not found
- `401` unauthorized

## Mock Protected Test Endpoint

### `GET /api/v1/me` (protected)

Returns static message when mock auth passed.

Response:

```json
{
  "authorized": true,
  "message": "Mock auth passed"
}
```

Status: `200` (or `401` without valid auth header).
