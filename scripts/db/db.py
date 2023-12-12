#!/usr/bin/env python3

import argparse
import subprocess
import os
import sys


def error(*args, **kwargs):
    print("Error:", *args, **kwargs, file=sys.stderr, flush=True)


def exec(cmd: str, args: list[str], print=True, input=None):
    if input is not None:
        input = input.encode('utf8')
    r = subprocess.run([cmd] + args, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, input=input)
    if print:
        sys.stdout.buffer.write(r.stdout)
        sys.stderr.buffer.write(r.stderr)
    return r


def ask_confirmation(msg):
    r = input(msg).lower()
    if r in ['y','yes']:
        return True
    return False


def run_sql_script(filename):
    try:
        with open(filename, 'r', encoding='utf8') as file:
            script = file.read()
    except FileNotFoundError:
        error("Script '%s' not found" % args.name)
        return 1
    return exec("mariadb", [
        "--password=root",
        "--user=root",
        "--progress-reports",
        "mysql"
    ], input=script).returncode


def backup(args):
    filename = args.filename
    r = exec("mariadb-dump", [
        "--password=root",
        "--user=root",
        "wishlist"
    ], print=False)
    sys.stderr.buffer.write(r.stderr)
    if r.returncode != 0:
        exit(r.returncode)
    with open(filename, 'wb') as file:
        file.write(r.stdout)
    print("Database backup done successfully")


def restore(args):
    if not ask_confirmation("Do you really want to restore database from '%s' ? (yes/no): " % args.filename):
        print("Canceling")
        return
    r = run_sql_script(args.filename)
    if r != 0:
        exit(r)
    print("Database backup successfully restored")


def run(args):
    filename = os.path.join("mariadb", args.name + ".sql")
    exit(run_sql_script(filename))


if __name__ == "__main__":
    parser = argparse.ArgumentParser()

    subparsers = parser.add_subparsers(title="Action", required=True)

    backup_cmd = subparsers.add_parser("backup", help="Create a backup of the database")
    backup_cmd.add_argument("filename", help="Backup output file", type=str)
    backup_cmd.set_defaults(func=backup)

    restore_cmd = subparsers.add_parser("restore", help="Restore a database backup")
    restore_cmd.add_argument("filename", help="Backup input file to use", type=str)
    restore_cmd.set_defaults(func=restore)

    run_cmd = subparsers.add_parser("run", help="Run a SQL scripts")
    run_cmd.add_argument("name", help="The script name to run (you have to omit the '.sql')", type=str)
    run_cmd.set_defaults(func=run)

    args = parser.parse_args()
    args.func(args)
