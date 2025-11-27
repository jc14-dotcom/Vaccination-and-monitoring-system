# 🚀 FIREBASE FCM SETUP - EXACT STEPS FOR YOUR PROJECT

**Project**: infant-vaccination-system  
**Status**: ✅ Project created, needs credentials download

---

## ✅ WHAT YOU ALREADY HAVE (from screenshot)

From your Firebase console screenshot, I can see:

```
✅ Firebase Project Created: "infant-vaccination-system"
✅ Cloud Messaging API (v1): ENABLED ✓
✅ Sender ID: 182620664136
✅ Web Push Certificate Key: BJOBKYKfQRFbYv-WHHdtm8DuJZkFP2nX0JiV31gX2YltDnhIlwD7HdK3lSiWwmqBTdbXacv0iQyHJAtAEJrw
❌ Legacy API: Disabled (this is CORRECT - you're using the new API)
```

---

## 📥 STEP 1: DOWNLOAD SERVICE ACCOUNT JSON FILE

**Why you can't find "Server Key"**: 
- Firebase deprecated the legacy Server Key (the one you're looking for)
- New method uses **Service Account JSON file** instead (more secure)

**How to get it**:

1. **In Firebase Console**, click the gear icon ⚙️ next to "Project Overview"
2. Click **"Project settings"**
3. Go to **"Service accounts"** tab (you're currently on "Cloud Messaging" tab)
4. Look for section: **"Firebase Admin SDK"**
5. Select **"Node.js"** (or any language, doesn't matter)
6. Click **"Generate new private key"** button
7. Click **"Generate key"** in the popup
8. Save the downloaded JSON file as: `infantvax-firebase-adminsdk.json`

**⚠️ IMPORTANT**: 
- This file contains sensitive credentials
- Keep it SECRET (like a password)
- DO NOT share it
- DO NOT commit to Git

---

## 📁 STEP 2: PLACE THE FILE IN YOUR PROJECT

**Where to put it**:

```
C:\laragon\www\infantsSystem\storage\app\firebase\infantvax-firebase-adminsdk.json
```

**Create the folder first**:
1. Navigate to: `C:\laragon\www\infantsSystem\storage\app\`
2. Create new folder: `firebase`
3. Copy your downloaded JSON file into it
4. Rename to: `infantvax-firebase-adminsdk.json`

**Check the file contents** (should look like this):
```json
{
  "type": "service_account",
  "project_id": "infant-vaccination-system",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...",
  "client_email": "firebase-adminsdk-xxxxx@infant-vaccination-system.iam.gserviceaccount.com",
  "client_id": "123456789...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/..."
}
```

---

## 🔑 STEP 3: GET REMAINING CREDENTIALS

You need a few more values from Firebase Console:

**3.1: Get API Key and App ID** ✅ **YOU ALREADY HAVE THIS!**

From your screenshot, I can see your Firebase config:

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyOIlekJjVWx2NGP1cfv32pqy0Te22vJh4",
  authDomain: "infant-vaccination-syste-508e4.firebaseapp.com",
  projectId: "infant-vaccination-syste-508e4",
  storageBucket: "infant-vaccination-syste-508e4.firebasestorage.app",
  messagingSenderId: "182620664136",
  appId: "1:182620664136:web:19df9A9d948b7e1cbc8370"
};
```

**Your actual values** ✅:
- ✅ **API Key**: `AIzaSyOIlekJjVWx2NGP1cfv32pqy0Te22vJh4`
- ✅ **Auth Domain**: `infant-vaccination-syste-508e4.firebaseapp.com`
- ✅ **Project ID**: `infant-vaccination-syste-508e4`
- ✅ **Storage Bucket**: `infant-vaccination-syste-508e4.firebasestorage.app`
- ✅ **Messaging Sender ID**: `182620664136`
- ✅ **App ID**: `1:182620664136:web:19df9A9d948b7e1cbc8370`

---

## ⚙️ STEP 4: UPDATE .ENV FILE

Add these lines to your `.env` file with **YOUR ACTUAL VALUES**:

```env
# Firebase Cloud Messaging (v1 API)
FCM_CREDENTIALS_PATH=firebase/infantvax-firebase-adminsdk.json
FCM_PROJECT_ID=infant-vaccination-syste-508e4
FCM_API_KEY=AIzaSyOIlekJjVWx2NGP1cfv32pqy0Te22vJh4
FCM_AUTH_DOMAIN=infant-vaccination-syste-508e4.firebaseapp.com
FCM_STORAGE_BUCKET=infant-vaccination-syste-508e4.firebasestorage.app
FCM_SENDER_ID=182620664136
FCM_APP_ID=1:182620664136:web:19df9A9d948b7e1cbc8370
FCM_WEB_PUSH_CERTIFICATE=BJOBKYKfQRFbYv-WHHdtm8DuJZkFP2nX0JiV31gX2YltDnhIlwD7HdK3lSiWwmqBTdbXacv0iQyHJAtAEJrw
```

---

## 📦 STEP 5: INSTALL REQUIRED PACKAGE

Open PowerShell in your project folder and run:

```powershell
composer require google/auth
```

This installs the Google Auth Library needed to read the service account JSON file.

---

## ✅ VERIFICATION CHECKLIST

Before implementing the FCM code, verify:

- [ ] ✅ Firebase project created: `infant-vaccination-system`
- [ ] ✅ Cloud Messaging API (v1) enabled
- [ ] ✅ Service account JSON file downloaded
- [ ] ✅ JSON file placed in: `storage/app/firebase/infantvax-firebase-adminsdk.json`
- [ ] ✅ Web app registered in Firebase
- [ ] ✅ API Key obtained
- [ ] ✅ App ID obtained
- [ ] ✅ All values added to `.env`
- [ ] ✅ `composer require google/auth` installed
- [ ] ✅ `storage/app/firebase` folder exists

---

## 🎯 WHAT'S NEXT?

Once you complete these 5 steps, you're ready to implement the FCM code:

1. ✅ Create `app/Services/FcmService.php`
2. ✅ Create `app/Channels/FcmChannel.php`
3. ✅ Update notification classes
4. ✅ Update frontend (pwa.js, sw.js)
5. ✅ Add database migration for `fcm_token`
6. ✅ Test notifications

**Estimated time after credentials**: 4-6 hours

---

## 🆘 TROUBLESHOOTING

### "I still can't find the Server Key"

**Answer**: You won't find it because Firebase removed it! Use the service account JSON instead (the new way).

### "Where is Service accounts tab?"

1. Click gear icon ⚙️ next to "Project Overview"
2. Click "Project settings"
3. Look at tabs: General | **Service accounts** | Cloud Messaging | ...
4. Click the "Service accounts" tab

### "I don't see 'Generate new private key' button"

Make sure you're looking at the right section:
- ❌ NOT: Cloud Messaging tab
- ✅ YES: Service accounts tab → Firebase Admin SDK section

### "JSON file downloaded but has random name"

Rename it to: `infantvax-firebase-adminsdk.json` (easier to remember)

### "Do I need to enable Legacy API?"

**NO!** Keep it disabled. Legacy API is deprecated and will be removed by 2024. You're using the better v1 API.

---

## 📚 HELPFUL LINKS

- Service Account Setup: https://firebase.google.com/docs/admin/setup
- FCM v1 Migration: https://firebase.google.com/docs/cloud-messaging/migrate-v1
- Web Push Setup: https://firebase.google.com/docs/cloud-messaging/js/client

---

**Ready to proceed?** Once you have the JSON file and credentials, let me know and we'll implement the actual code! 🚀
