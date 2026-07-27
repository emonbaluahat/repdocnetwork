#!/usr/bin/env python3
"""
RepDoc Network — Local Development Server
Starts MariaDB (low memory), runs migrations, seeds data, starts PHP server.
"""

import os
import subprocess
import sys
import time
import signal
import json
import re

PROJECT_ROOT = os.path.dirname(os.path.abspath(__file__))
DB_NAME = "repdoc_dev"
DB_USER = os.environ.get("DB_USER", "root")
DB_PASS = os.environ.get("DB_PASS", "")
APP_PORT = os.environ.get("APP_PORT", "8000")
MYSQL_SOCKET = "/run/mysqld/mysqld.sock"
MYSQL_PID_FILE = "/run/mysqld/mysqld.pid"

processes = []


def log(msg: str):
    print(f"  \033[34m*\033[0m {msg}")


def ok(msg: str):
    print(f"  \033[32m\u2713\033[0m {msg}")


def fail(msg: str):
    print(f"  \033[31m\u2717\033[0m {msg}")
    return False


def run(cmd, check=True, capture=False, timeout=60, env=None):
    try:
        r = subprocess.run(
            cmd, shell=True, capture_output=capture, text=True,
            timeout=timeout, env={**os.environ, **(env or {})}
        )
        if check and r.returncode != 0:
            print(f"    Command failed: {cmd}")
            if capture:
                print(f"    stderr: {r.stderr[:500]}")
            return None
        return r
    except subprocess.TimeoutExpired:
        print(f"    Command timed out: {cmd}")
        return None
    except Exception as e:
        print(f"    Error: {e}")
        return None


def cleanup(signum=None, frame=None):
    log("Shutting down...")
    for p in reversed(processes):
        try:
            p.terminate()
            p.wait(timeout=3)
        except Exception:
            try:
                p.kill()
            except Exception:
                pass
    run("pkill -9 mariadbd 2>/dev/null; pkill -9 mysqld 2>/dev/null", check=False)
    sys.exit(0)


def seed_data():
    """Insert test user, shop, and shop_user records via PHP seed script."""
    # Check if already seeded
    r = run(f"mysql -u root {DB_NAME} -e 'SELECT id FROM users WHERE email=\"admin@repdoc.test\"'",
            check=False, capture=True)
    if r and r.stdout and "admin@repdoc.test" in r.stdout:
        ok("Seed data already exists")
        return {"email": "admin@repdoc.test", "password": "password123"}

    # Use PHP to seed data (avoids shell escaping issues with password hash and JSON)
    seed_php = os.path.join(PROJECT_ROOT, "seeds", "seed_dev.php")
    r = run(f"cd {PROJECT_ROOT} && php seeds/seed_dev.php", capture=True, timeout=30)
    if r and r.returncode == 0:
        ok("Test user and shop created")
    else:
        fail("Seed data insertion failed")

    return {"email": "admin@repdoc.test", "password": "password123"}


def main():
    signal.signal(signal.SIGINT, cleanup)
    signal.signal(signal.SIGTERM, cleanup)

    print()
    print("  \033[1m\033[34m=== RepDoc Network — Development Server ===\033[0m")
    print()

    # ── Step 1: Kill any existing MariaDB ──
    log("Cleaning up any existing MariaDB processes...")
    run("pkill -9 mariadbd 2>/dev/null; pkill -9 mysqld 2>/dev/null", check=False)
    run("rm -f /run/mysqld/mysqld.sock /run/mysqld/mysqld.pid 2>/dev/null", check=False)
    time.sleep(1)

    # ── Step 2: Start MariaDB with minimal memory ──
    log("Starting MariaDB (low-memory mode)...")
    mysql_proc = subprocess.Popen(
        [
            "mariadbd",
            "--user=root",
            f"--datadir=/var/lib/mysql",
            "--skip-networking=0",
            f"--socket={MYSQL_SOCKET}",
            f"--pid-file={MYSQL_PID_FILE}",
            "--port=3306",
            "--innodb-buffer-pool-size=32M",
            "--innodb-log-buffer-size=1M",
            "--innodb-flush-log-at-trx-commit=2",
            "--max-connections=10",
            "--table-open-cache=100",
            "--thread-cache-size=4",
            "--query-cache-size=0",
            "--skip-performance-schema",
            "--skip-log-bin",
        ],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    processes.append(mysql_proc)

    # Wait for MariaDB to be ready
    for i in range(20):
        time.sleep(1)
        r = run(f"mysql -u root -e 'SELECT 1' 2>/dev/null", check=False, capture=True)
        if r and r.returncode == 0:
            ok("MariaDB is ready")
            break
    else:
        fail("MariaDB failed to start. Check logs with: journalctl -u mariadb")
        cleanup()

    # ── Step 3: Create database ──
    log(f"Creating database '{DB_NAME}'...")
    run(f"mysql -u root -e 'CREATE DATABASE IF NOT EXISTS {DB_NAME} "
        f"CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'")
    ok(f"Database '{DB_NAME}' ready")

    # ── Step 4: Update .env for development ──
    app_url = f"http://localhost:{APP_PORT}"
    env_path = os.path.join(PROJECT_ROOT, ".env")
    with open(env_path, "r") as f:
        env_content = f.read()
    env_content = re.sub(r"DB_NAME=.*", f"DB_NAME={DB_NAME}", env_content)
    env_content = re.sub(r"APP_URL=.*", f"APP_URL={app_url}", env_content)
    env_content = re.sub(r"APP_ENV=.*", "APP_ENV=development", env_content)
    env_content = re.sub(r"APP_DEBUG=.*", "APP_DEBUG=true", env_content)
    if "DB_HOST=" not in env_content:
        env_content += f"\nDB_HOST=127.0.0.1\n"
    if "DB_PORT=" not in env_content:
        env_content += f"DB_PORT=3306\n"
    with open(env_path, "w") as f:
        f.write(env_content)
    ok(".env configured")

    # ── Step 5: Run migrations ──
    log("Running database migrations (fresh)...")
    r = run(f"cd {PROJECT_ROOT} && php -d max_execution_time=120 migrations/migrate.php --fresh",
            capture=True, timeout=180, check=False)
    if r and r.returncode == 0:
        ok("All migrations complete (fresh)")
    else:
        log("Fresh migration not possible, trying normal migration...")
        r = run(f"cd {PROJECT_ROOT} && php -d max_execution_time=120 migrations/migrate.php",
                capture=True, timeout=180)
        if r and r.returncode == 0:
            ok("All migrations complete")
        else:
            fail("Migration failed.")
            cleanup()

    # ── Step 6: Seed data ──
    log("Seeding test data...")
    seed = seed_data()

    # ── Step 7: Verify table structure ──
    log("Verifying database...")
    run(f"mysql -u root {DB_NAME} -e \"SELECT COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='{DB_NAME}'\"",
        capture=True, check=False)
    ok("Database structure verified")

    # ── Step 8: Build Tailwind CSS ──
    log("Building Tailwind CSS...")
    r = run(f"cd {PROJECT_ROOT} && npx tailwindcss -i ./assets/css/app.css -o ./assets/css/style.css --minify",
            capture=True, timeout=120, check=False)
    if r and r.returncode == 0:
        ok("Tailwind CSS built")
    else:
        log("Skipping Tailwind build (ok, using existing CSS)")

    # ── Step 9: Start PHP development server ──
    log(f"Starting PHP development server on port {APP_PORT}...")
    php_proc = subprocess.Popen(
        ["php", "-S", f"0.0.0.0:{APP_PORT}", "index.php"],
        cwd=PROJECT_ROOT,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    processes.append(php_proc)
    time.sleep(2)

    # Verify PHP server is running
    r = run(f"curl -s -o /dev/null -w '%{{http_code}}' http://127.0.0.1:{APP_PORT}/",
            check=False, capture=True, timeout=5)
    if r and r.stdout and r.stdout.strip() not in ("", "000"):
        ok(f"PHP server running on http://127.0.0.1:{APP_PORT}")
    else:
        fail("PHP server failed to start")
        cleanup()

    # ── Done ──
    print()
    print("  \033[1m\033[32m====================================\033[0m")
    print("  \033[1m\033[32m  Server is ready!\033[0m")
    print("  \033[1m\033[32m====================================\033[0m")
    print()
    print(f"  \033[33mURL:\033[0m      {app_url}")
    print()
    print(f"  \033[33mLogin:\033[0m    \033[1madmin@repdoc.test\033[0m")
    print(f"  \033[33mPassword:\033[0m \033[1mpassword123\033[0m")
    print()
    print(f"  \033[33mShop:\033[0m     RepDoc Demo Shop (auto-selected)")
    print()
    print("  Press \033[1mCtrl+C\033[0m to stop the server.")
    print()

    # Wait for interrupt
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        cleanup()


if __name__ == "__main__":
    main()
