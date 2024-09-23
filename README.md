

# Caronte client
---
Caronte is a JWT token based authentication system written in Laravel, this is the client that consumes the Caronte API to authenticate other systems.

## Configuration

Caronte uses environment variables to configure itself, these are the options that you can configure in the .env of the project in Laravel.

| Key                           | Default    | Description                                                                                                                                                               | V1        | V2           |
| ----------------------------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------- | ------------ |
| `CARONTE_URL`                 | `''`       | The FQDN of the Caronte server against which the client will authenticate.                                                                                                | Mandatory | Mandatory    |
| `CARONTE_VERSION`             | `'v2'`     | Caronte auth version.                                                                                                                                                     | Mandatory | Optional     |
| `CARONTE_TOKEN_KEY`           | `''`       | Symmetric authentication key.                                                                                                                                             | Mandatory | Not required |
| `CARONTE_ALLOW_HTTP_REQUESTS` | `false`    | Disable https protocol verification in requests.                                                                                                                          | Optional  | Optional     |
| `CARONTE_ISSUER_ID`           | ''         |                                                                                                                                                                           |           |              |
| `CARONTE_ENFORCE_ISSUER`      | `true`     |                                                                                                                                                                           |           |              |
| `CARONTE_APP_ID`              | `''`       | Common name registered in Caronte.                                                                                                                                        | Mandatory | Mandatory    |
| `CARONTE_APP_SECRET`          | `''`       | String that identifies a system registered in Caronte.                                                                                                                    | Mandatory | Mandatory    |
| `CARONTE_2FA`                 | `false`    | Enable two-factor authentication.                                                                                                                                         | Optional  | Optional     |
| `CARONTE_ROUTES_PREFIX`       | `''`       | Prefix of the routes protected by Caronte. e.g. '/admin'.                                                                                                                 | Optional  | Optional     |
| `CARONTE_SUCCESS_URL`         | `'/'`      | Route to which a user will be redirected upon completing authentication.                                                                                                  | Optional  | Optional     |
| `CARONTE_LOGIN_URL`           | `'/login'` | Assigned route for login.                                                                                                                                                 | Optional  | Optional     |
| `CARONTE_UPDATE_USER`         | `false`    | When enabled, a record of users who have logged into the system is kept in the local database. (It is necessary to run the caronte-client migrations to use this feature) | Optional  | Optional     |

