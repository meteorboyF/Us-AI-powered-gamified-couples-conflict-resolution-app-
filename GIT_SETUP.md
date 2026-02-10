# Git Setup Guide

## Step 1: Configure Your Git Identity

Open a **new PowerShell terminal** (to get the updated PATH) and run these commands with **your information**:

```powershell
# Navigate to project directory
cd "d:\Projects\Us_AI_Powered_Couples_Conflict_Resolution_APP\us-app"

# Set your name (replace with your actual name)
git config --global user.name "Your Name"

# Set your email (replace with your GitHub email)
git config --global user.email "your.email@example.com"

# Verify the configuration
git config --global user.name
git config --global user.email
```

## Step 2: Initialize Git Repository

```powershell
# Initialize the repository
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: Laravel TALL stack with Jetstream and couples app schema"
```

## Step 3: Connect to GitHub

```powershell
# Add the remote repository
git remote add origin https://github.com/meteorboyF/Us-AI-powered-gamified-couples-conflict-resolution-app-.git

# Verify the remote
git remote -v

# Push to GitHub (you'll be prompted for credentials)
git push -u origin main
```

### Authentication Options

When you push, GitHub will ask for authentication. You have two options:

**Option A: Personal Access Token (Recommended)**
1. Go to GitHub.com → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token with `repo` scope
3. Use the token as your password when prompted

**Option B: GitHub CLI**
```powershell
# Install GitHub CLI (if not already installed)
winget install GitHub.CLI

# Authenticate
gh auth login
```

## Step 4: Verify Everything

```powershell
# Check status
git status

# View commit history
git log --oneline

# Check remote connection
git remote -v
```

## Quick Reference

```powershell
# Check current branch
git branch

# View changes
git status

# Add specific files
git add <filename>

# Commit changes
git commit -m "Your message"

# Push changes
git push
```

## Troubleshooting

**If you get "branch 'main' doesn't exist" error:**
```powershell
# Check current branch name
git branch

# If it's 'master', rename to 'main'
git branch -M main

# Then push
git push -u origin main
```

**If remote already exists:**
```powershell
# Remove existing remote
git remote remove origin

# Add it again
git remote add origin https://github.com/meteorboyF/Us-AI-powered-gamified-couples-conflict-resolution-app-.git
```
