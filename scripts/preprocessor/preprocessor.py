#!/usr/bin/env python3

# This import is required to use the venv
import executor

import argparse
import os
import sys
from TOML_parser import find_and_parse_toml
from watcher import Watcher


r"""
To test you can run the command:
preprocessor test\templates test\public --list test\pages.lst --watch --override
"""


def error(*args, **kwargs):
    print("Error:", *args, **kwargs, file=sys.stderr, flush=True)


def ident(content: str, prefix: str, skip: int = 1) -> str:
    result = ""
    for line in content.splitlines(keepends=True):
        if skip > 0:
            skip -= 1
            result += line
        else:
            result += prefix + line
    return result


def is_whitespace(c: str):
    return c == ' ' or c == '\t'


def delete_trailing_spaces(content: str) -> str:
    result = ""
    for line in content.splitlines():
        result += line.rstrip() + "\n"
    return result


class PreprocessorException(Exception):
    def __init__(self, *args: object) -> None:
        super().__init__(*args)


class Preprocessor:
    def __init__(self, include_paths: list[str] = [], variables: dict = {}, body: str = None) -> None:
        self.include_paths: list[str] = include_paths
        self.i: int = 0
        self.content: str = None
        self.m: int = 0
        self.error: str = None
        self.output: str = ""
        self.variables: dict = variables.copy()
        self.body: str = body
        self.basedir: str = "."
        self.last_non_space: int = -1
        self.dependencies: list[str] = []

    def search_file(self, filename: str) -> str:
        if os.path.isabs(filename):
            if os.path.exists(filename) and os.path.isfile(filename):
                return filename
            return None
        for path in [self.basedir] + self.include_paths:
            result = os.path.join(path, filename)
            if os.path.exists(result) and os.path.isfile(result):
                return result
        return None

    def handle_file_placeholder(self, filename: str, prefix: str):
        resolved = self.search_file(filename)
        if resolved is None:
            self.error = "Can't find file '%s'" % filename
            return
        preprocessor = Preprocessor(self.include_paths, self.variables)
        #print("Placeholder replace '%s' with ident '%s'" % (resolved, prefix))
        preprocessed, dependencies = preprocessor.preprocess_file(resolved)
        self.dependencies.append(resolved)
        self.dependencies.extend(dependencies)
        self.output += ident(preprocessed, prefix)

    def handle_placeholder(self, placeholder: str, prefix: str):
        if placeholder == "@":
            if self.body is None:
                self.error = "Can't replace the placeholder ${@} because nothing used this file"
                return
            self.output += ident(self.body, prefix)
        elif placeholder.startswith(":"):
            self.handle_file_placeholder(placeholder[1:], prefix)
        elif placeholder.startswith("!"):
            varname = placeholder[1:]
            if varname not in self.variables:
                self.error = "Unknown file variable '%s'" % varname
                return
            self.handle_file_placeholder(self.variables[varname], prefix)
        else:
            if placeholder not in self.variables:
                self.error = "Unknown variable '%s'" % placeholder
                return
            self.output += str(self.variables[placeholder])

    def parse_placeholder(self):
        prefix = self.content[self.last_non_space+1:self.i]
        self.i += 2
        placeholder = ""
        while self.i < self.m:
            c = self.content[self.i]
            if self.content.startswith("${", self.i):
                self.output += "${" + placeholder
                self.parse_placeholder()
                return
            elif c == '\n':
                self.output += "${" + placeholder + "\n"
                self.last_non_space = self.i
                self.i += 1
                return
            elif c == "}":
                self.last_non_space = self.i
                self.handle_placeholder(placeholder, prefix)
                self.i += 1
                return
            else:
                placeholder += c
                if not is_whitespace(c):
                    self.last_non_space = self.i
                self.i += 1
        self.output += "${" + placeholder

    def parse(self) -> str:
        while self.i < self.m and self.error is None:
            c = self.content[self.i]
            if self.content.startswith("${", self.i):
                self.parse_placeholder()
            else:
                if not is_whitespace(c):
                    self.last_non_space = self.i
                self.output += c
                self.i += 1

        if self.error is not None:
            raise PreprocessorException(self.error)

        return self.output

    def preprocess(self, content: str) -> tuple[str,list[str]]:
        """
        input: content: Text to preprocess
        return: A tuple with the preprocess result and a list of dependencies (files that was required during the preprocessing)
        """
        toml, self.content = find_and_parse_toml(content)

        self.content = self.content.strip()

        self.m = len(self.content)
        self.i = 0
        self.error = None
        self.last_non_space = -1
        self.dependencies = []

        config = toml["config"] if "config" in toml else {}
        using = config["using"] if "using" in config else []

        variables = toml["variables"] if "variables" in toml else {}
        for key, value in variables.items():
            self.variables[key] = value

        defaults = toml["defaults"] if "defaults" in toml else {}
        for key, value in defaults.items():
            if key not in self.variables:
                self.variables[key] = value

        preprocessed = self.parse()

        for filename in using:
            resolved = self.search_file(filename)
            if resolved is None:
                self.error = "Can't find file '%s'" % filename
                break
            preprocessor = Preprocessor(self.include_paths, self.variables, preprocessed)
            preprocessed, dependencies = preprocessor.preprocess_file(resolved)
            self.dependencies.append(resolved)
            self.dependencies.extend(dependencies)

        if self.error is not None:
            raise PreprocessorException(self.error)

        deps = set()
        for dep in self.dependencies:
            dep = os.path.normpath(os.path.abspath(dep))
            deps.add(dep)

        return preprocessed, list(deps)

    def preprocess_file(self, filename: str) -> tuple[str,list[str]]:
        self.basedir = os.path.dirname(filename)
        with open(filename, 'r', encoding="utf8") as file:
            return self.preprocess(file.read())


def preprocess(input_filename: str, output_filename: str, include_paths: list[str] = []) -> list[str]:
    """
    input: input_filename: File to read and preprocess
    input: output_filename: File to write with preprocessing result
    input: include_paths: A list of directory to search when the preprocessor search for a file
    return: A list of file that was used during preprocessing (so, it's the list of dependencies)
    """
    preprocessor = Preprocessor(include_paths)
    preprocessed, deps = preprocessor.preprocess_file(input_filename)
    output_dir = os.path.dirname(output_filename)
    if not os.path.exists(output_dir):
        os.makedirs(output_dir, exist_ok=True)
    with open(output_filename, 'w', encoding="utf8") as output_file:
        preprocessed = delete_trailing_spaces(preprocessed)
        output_file.write(preprocessed)
    return deps


class App:
    def __init__(self, args) -> None:
        self.args = args
        self.filenames: list[tuple[str,str]] = []
        self.dependencies: dict[str,list[tuple[str,str]]] = dict()

    def main(self):
        self.compute_filenames()
        for src_filename, dst_filename in self.filenames:
            if not self.do_preprocessing(src_filename, dst_filename):
                return
        if self.args.watch:
            self.watch()

    def add_dependency(self, input_filename: str, output_filename: str, dependency: str):
        if dependency not in self.dependencies:
            self.dependencies[dependency] = []
        for dep, _ in self.dependencies[dependency]:
            if dep == input_filename:
                break
        else:
            self.dependencies[dependency].append((input_filename, output_filename))

    def do_preprocessing(self, input_filename: str, output_filename: str) -> bool:
        if os.path.exists(output_filename) and not self.args.override:
            error("Destination file '%s' already exists and flag '-y' is not specified" % output_filename)
            return False
        print("Preprocessing file '%s'" % input_filename)
        try:
            normalized_input_filename = os.path.normpath(os.path.abspath(input_filename))
            deps = preprocess(input_filename, output_filename, self.args.include or [])
            for dep in deps:
                self.add_dependency(normalized_input_filename, output_filename, dep)
            self.add_dependency(normalized_input_filename, output_filename, normalized_input_filename)
        except PreprocessorException as e:
            error("Failed to preprocess file '%s': %s" % (input_filename, str(e)))
            return False
        return True

    def compute_filenames(self) -> list[tuple[str, str]]:
        if self.args.list is not None:
            with open(self.args.list, 'r', encoding="utf8") as file:
                content = file.read()
            lines = content.splitlines(False)
            self.filenames = []
            for filename in lines:
                if not filename:
                    continue
                src_filename = os.path.join(self.args.input, filename)
                dst_filename = os.path.join(self.args.output, filename)
                self.filenames.append((src_filename, dst_filename))
        else:
            self.filenames = [(self.args.input, self.args.output)]

    def watch(self):
        watcher = Watcher()

        directories_to_watch = set()
        for dep in self.dependencies.keys():
            directories_to_watch.add(os.path.dirname(dep))

        for path in directories_to_watch:
            print("Watch: %s" % path)
            watcher.watch(path, self.watch_cb)

        watcher.start()
        print("Exiting gracefully")

    def watch_cb(self, event):
        if event.event_type == "modified":
            src_filename = os.path.normpath(os.path.abspath(event.src_path))
            if src_filename in self.dependencies:
                for to_recompile in self.dependencies[src_filename]:
                    self.do_preprocessing(to_recompile[0], to_recompile[1])


if __name__ == "__main__":
    parser = argparse.ArgumentParser()

    parser.add_argument("input", help="Input file of folder", type=str)
    parser.add_argument("output", help="Output filename (when there is '-r' it's should be a directory)", type=str)
    parser.add_argument("-y", "--override", help="Override destination file if it already exists", action="store_true")
    parser.add_argument("-w", "--watch", help="Continuously watch files for changes and recompile theme automatically", action="store_true")
    parser.add_argument("-l", "--list", help="Give file with a list of file to preprocess", type=str)
    parser.add_argument("-I", "--include", help="Add include search path", type=str, action="append")

    args = parser.parse_args()

    app = App(args)
    app.main()
