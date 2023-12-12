#!/usr/bin/env python3

import argparse


def backup(args):
    pass


def restore(args):
    pass


def optimize(args):
    pass


def run(args):
    pass


if __name__ == "__main__":
    parser = argparse.ArgumentParser()

    subparsers = parser.add_subparsers(title="Action", required=True)

    optimize_cmd = subparsers.add_parser("optimize", help="Optimize and defragment database")
    optimize_cmd.set_defaults(func=optimize)

    backup_cmd = subparsers.add_parser("backup", help="Create a backup of the database")
    backup_cmd.add_argument("filename", help="Backup output file", type=str)
    backup_cmd.set_defaults(func=backup)

    restore_cmd = subparsers.add_parser("restore", help="Restore a database backup")
    restore_cmd.add_argument("filename", help="Backup input file to use", type=str)
    restore_cmd.set_defaults(func=restore)

    run_cmd = subparsers.add_parser("run", help="Run a SQL scripts")
    run_cmd.add_argument("name", help="The script name to run (you have to omit the '.sql')", type=str)
    run_cmd.set_defaults(func=run)

    parser.parse_args()
