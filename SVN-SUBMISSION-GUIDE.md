# Techanum Maintenance — WordPress.org SVN Submission Guide

> **Day 10 · Final Push & Submission**  
> Version: 1.0.0 | Plugin slug: `techanum-maintenance`

---

## Prerequisites

| Tool | Install command (Windows) | Notes |
|------|--------------------------|-------|
| SVN (Subversion) | `winget install TortoiseSVN.TortoiseSVN` or [SilkSVN](https://sliksvn.com/download/) | SilkSVN gives you a clean CLI `svn` command |
| WordPress.org account | [Register here](https://login.wordpress.org/register) | Must be the same account used to submit the plugin |

Make sure `svn` is on your PATH:

```bash
svn --version
```

---

## Step 0 — Submit the Plugin for Review

Before you can use SVN, WordPress.org must approve your plugin submission.

1. Go to **https://wordpress.org/plugins/developers/add/**
2. Log in with your WordPress.org account (`gregorytriglidis`).
3. Fill in:
   - **Plugin name:** Techanum Maintenance
   - **Plugin description:** (paste the short description from `readme.txt`)
4. Upload a `.zip` of the plugin (see "Creating the zip" below).
5. Click **Upload**.
6. Wait for the review e-mail (usually 1–14 business days).
7. The e-mail will contain your SVN repository URL:
   `https://plugins.svn.wordpress.org/techanum-maintenance/`

### Creating the submission zip

Run this from the **parent** of the plugin folder (i.e., `wp-content/plugins/`):

```bash
# Windows PowerShell
Compress-Archive -Path "techanum-maintenance" `
  -DestinationPath "techanum-maintenance-1.0.0.zip" `
  -CompressionLevel Optimal
```

> ⚠️ **Do NOT include** `.git/`, `node_modules/`, `SUBMISSION-CHECKLIST.md`,
> `SVN-SUBMISSION-GUIDE.md`, or `LAUNCH-MATERIALS.md` in the zip.

---

## Step 1 — Check Out the SVN Repository

Once your plugin is approved, check out the empty repository WordPress.org
created for you:

```bash
svn co https://plugins.svn.wordpress.org/techanum-maintenance/ techanum-maintenance-svn
cd techanum-maintenance-svn
```

You will see three empty folders:

```
techanum-maintenance-svn/
├── assets/      ← banner & icon images (NOT in the plugin zip)
├── tags/        ← one sub-folder per release
└── trunk/       ← current development version
```

---

## Step 2 — Copy Plugin Files into `trunk/`

Copy **all plugin files** (excluding dev-only files) into `trunk/`:

```bash
# Windows — run from the SVN checkout root
xcopy /E /I /Y "C:\path\to\wp-content\plugins\techanum-maintenance\*" "trunk\"

# Then manually DELETE any files that must not be in the zip:
del trunk\SUBMISSION-CHECKLIST.md
del trunk\SVN-SUBMISSION-GUIDE.md
del trunk\LAUNCH-MATERIALS.md
```

Your `trunk/` should look exactly like this:

```
trunk/
├── techanum-maintenance.php   ← main plugin file
├── readme.txt                 ← WordPress.org readme
├── includes/
│   ├── class-admin-notices.php
│   ├── class-antigravity-api.php
│   ├── class-maintenance-mode.php
│   ├── class-settings-page.php
│   └── class-settings.php
├── languages/
│   └── techanum-maintenance.pot
└── templates/
    └── maintenance-page.php
```

Tell SVN about any new files:

```bash
svn add trunk --force
```

---

## Step 3 — Add Plugin Assets (Banner & Icon)

Plugin assets live in the **`assets/`** folder of the SVN repo — they are
**not** bundled inside the plugin zip.

| File | Size | Purpose |
|------|------|---------|
| `assets/banner-772x250.png` | 772 × 250 px | Plugin directory banner (standard) |
| `assets/banner-1544x500.png` | 1544 × 500 px | Plugin directory banner (retina) |
| `assets/icon-128x128.png` | 128 × 128 px | Plugin directory icon (standard) |
| `assets/icon-256x256.png` | 256 × 256 px | Plugin directory icon (retina) |

```bash
# Copy your prepared assets
copy "C:\path\to\assets\banner-772x250.png"  "assets\"
copy "C:\path\to\assets\banner-1544x500.png" "assets\"
copy "C:\path\to\assets\icon-128x128.png"    "assets\"
copy "C:\path\to\assets\icon-256x256.png"    "assets\"

svn add assets --force
```

---

## Step 4 — Tag the Release

WordPress.org uses SVN tags to identify specific versions. The `Stable tag`
in `readme.txt` **must match** the tag folder name exactly.

```bash
svn cp trunk tags/1.0.0
```

Verify the tag was created:

```bash
svn ls tags/
# Expected output: 1.0.0/
```

---

## Step 5 — Commit Everything

```bash
svn ci -m "Initial release 1.0.0"
```

SVN will prompt for your **WordPress.org username and password**.

> 💡 If you use 2FA on WordPress.org, you may need to use an
> [application password](https://make.wordpress.org/core/2020/07/08/application-passwords-integration-guide/).

A successful commit looks like:

```
Adding         assets/banner-772x250.png
Adding         assets/icon-128x128.png
Adding         trunk/techanum-maintenance.php
Adding         trunk/readme.txt
...
Adding         tags/1.0.0
Transmitting file data .....
Committed revision 1.
```

---

## Step 6 — Verify on WordPress.org

After the commit propagates (usually 5–15 minutes):

1. Visit **https://wordpress.org/plugins/techanum-maintenance/**
2. Confirm the plugin page shows version `1.0.0`.
3. Click **Download** and verify the zip installs and activates correctly.
4. Check that the banner and icon appear on the plugin page.

---

## Common Pitfalls & How to Avoid Them

### ❌ `Stable tag` mismatch
The `Stable tag` in `readme.txt` **must exactly match** the tag folder name.

```
# readme.txt
Stable tag: 1.0.0

# SVN tags folder
tags/1.0.0/   ✅  (matches)
tags/1.0/     ❌  (does NOT match)
```

### ❌ Invalid `readme.txt`
Always validate before submitting:
👉 **https://wordpress.org/plugins/developers/readme-validator/**

Paste the raw content of your `readme.txt` and fix any errors reported.

### ❌ Missing or wrong `Version:` header
The `Version:` in the main plugin file header must match `Stable tag` in
`readme.txt` and the tag folder name. All three must be `1.0.0`.

### ❌ Committing to `trunk` only (forgetting the tag)
WordPress.org serves the version pointed to by `Stable tag`. If you only
commit to `trunk` but `Stable tag: 1.0.0` points to `tags/1.0.0/` and that
tag doesn't exist, users will see an error or get no download.

### ❌ Including dev-only files in the zip
Files like `.git/`, `node_modules/`, `SUBMISSION-CHECKLIST.md` must not be
in `trunk/` or the tag. They bloat the download and look unprofessional.

### ❌ SVN authentication failure
Use your **WordPress.org** credentials (not GitHub). If you have 2FA enabled,
generate an application password at:
**https://profiles.wordpress.org/gregorytriglidis/#application-passwords**

### ❌ Screenshots not showing up
Screenshots (`screenshot-1.png`, etc.) must be placed in the **SVN `assets/`
folder**, not inside the plugin zip. They are served directly by WordPress.org.

---

## Future Updates (v1.1.0+)

When you release a new version:

```bash
# 1. Update Version: in techanum-maintenance.php
# 2. Update Stable tag: in readme.txt
# 3. Add a changelog entry in readme.txt

# 4. Copy updated files to trunk
xcopy /E /I /Y "C:\path\to\plugin\*" "trunk\"

# 5. Create the new tag
svn cp trunk tags/1.1.0

# 6. Commit
svn ci -m "Release 1.1.0 — [brief description of changes]"
```

---

## Quick Reference Cheat Sheet

```bash
# Check out repo (one-time)
svn co https://plugins.svn.wordpress.org/techanum-maintenance/ techanum-maintenance-svn

# Copy files, add new ones, tag, commit
svn add trunk --force
svn add assets --force
svn cp trunk tags/1.0.0
svn ci -m "Initial release 1.0.0"

# Check status at any time
svn status

# See what's in the repo
svn ls https://plugins.svn.wordpress.org/techanum-maintenance/
```

---

*Last updated: 2026-05-21 | Plugin: techanum-maintenance v1.0.0*
