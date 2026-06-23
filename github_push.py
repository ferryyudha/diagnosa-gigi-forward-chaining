import sys
import os
import json
import urllib.request
import urllib.error
import subprocess

def run_cmd(args):
    print(f"Running: {' '.join([a if 'ghp_' not in a else '***' for a in args])}")
    result = subprocess.run(args, capture_output=True, text=True)
    if result.returncode != 0:
        print(f"Error: {result.stderr}")
    else:
        print(result.stdout)
    return result.returncode == 0

def main():
    if len(sys.argv) < 2:
        print("Usage: python github_push.py <TOKEN>")
        sys.exit(1)

    token = sys.argv[1]
    repo_name = "diagnosa-gigi-forward-chaining"

    # Step 1: Get Username
    print("Fetching GitHub username...")
    req = urllib.request.Request(
        "https://api.github.com/user",
        headers={
            "Authorization": f"Bearer {token}",
            "User-Agent": "Python-GitHub-Client",
            "Accept": "application/vnd.github+json"
        }
    )
    try:
        with urllib.request.urlopen(req) as response:
            user_data = json.loads(response.read().decode())
            username = user_data["login"]
            print(f"Authenticated as user: {username}")
    except urllib.error.HTTPError as e:
        print(f"Failed to authenticate: {e.code} - {e.read().decode()}")
        sys.exit(1)

    # Step 2: Create Repository
    print(f"Creating repository '{repo_name}' on GitHub...")
    data = json.dumps({"name": repo_name, "private": False, "description": "Diagnosa Penyakit Gigi Metode Forward Chaining"}).encode()
    req_create = urllib.request.Request(
        "https://api.github.com/user/repos",
        data=data,
        headers={
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json",
            "User-Agent": "Python-GitHub-Client",
            "Accept": "application/vnd.github+json"
        },
        method="POST"
    )
    try:
        with urllib.request.urlopen(req_create) as response:
            print("Repository created successfully!")
    except urllib.error.HTTPError as e:
        if e.code == 422:
            print("Repository already exists on GitHub. Proceeding...")
        else:
            print(f"Failed to create repository: {e.code} - {e.read().decode()}")
            sys.exit(1)

    # Step 3: Git Operations
    os.chdir("C:/xampp/htdocs/forward_chaining")
    
    # Initialize repository
    if not os.path.exists(".git"):
        run_cmd(["git", "init"])
        
    # Configure user name/email if not set
    run_cmd(["git", "config", "user.name", "SiPaGi Developer"])
    run_cmd(["git", "config", "user.email", "sipagi@example.com"])
    
    # Add files
    run_cmd(["git", "add", "."])
    
    # Commit
    run_cmd(["git", "commit", "-m", "Initial commit: Diagnosa Penyakit Gigi Metode Forward Chaining"])
    
    # Set branch to main
    run_cmd(["git", "branch", "-M", "main"])
    
    # Remote setup (using token in URL)
    run_cmd(["git", "remote", "remove", "origin"]) # Remove if exists
    remote_url_with_token = f"https://{username}:{token}@github.com/{username}/{repo_name}.git"
    run_cmd(["git", "remote", "add", "origin", remote_url_with_token])
    
    # Push
    print("Pushing to GitHub...")
    success = run_cmd(["git", "push", "-u", "origin", "main", "--force"])
    
    # Clean remote URL to remove token from config file (security best practice)
    clean_remote_url = f"https://github.com/{username}/{repo_name}.git"
    run_cmd(["git", "remote", "set-url", "origin", clean_remote_url])
    
    if success:
        print(f"\nSuccess! Your project is pushed to: https://github.com/{username}/{repo_name}")
    else:
        print("\nPush failed. Check errors above.")

if __name__ == "__main__":
    main()
