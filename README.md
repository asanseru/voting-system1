# CityWatch v2 — Works on ALL Node.js versions!

## ✅ What changed from v1
The old version used `better-sqlite3` which required C++ compiler tools.
This version uses a **pure JavaScript JSON database** — no compilation, no errors,
works on Node v18, v20, v22, v24, and any future version!

---

## HOW TO RUN (Windows)

### Option A — Double-click (easiest)
1. Extract this zip
2. Double-click **START.bat**
3. Two black windows open — wait for both to finish
4. Go to http://localhost:3000

### Option B — Manual (PowerShell)

Open PowerShell in this folder, then:

**Terminal 1 — Backend:**
```
cd backend
npm install
node server.js
```

**Terminal 2 — Frontend:**
```
cd frontend
npm install
npm start
```

---

## LOGIN
| Username  | Password     | Role     |
|-----------|--------------|----------|
| admin     | admin123     | Admin    |
| operator1 | operator123  | Operator |

---

## WHERE DATA IS STORED
All data is saved in: `backend/db/data/`
- `cameras.json`   — 12 camera records
- `users.json`     — operator accounts
- `alerts.json`    — motion/incident alerts
- `recordings.json`— recording history
- `ptz_logs.json`  — PTZ command history

To reset all data, just delete the `backend/db/data/` folder
and restart the server — it recreates everything automatically.
