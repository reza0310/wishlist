#!/usr/bin/env python3

import argparse
import os
import sys
from TOML_parser import find_and_parse_toml


ALLOWED_EXTENTIONS = set(['html'])


def error(*args, **kwargs):
    print("Error:", *args, **kwargs, file=sys.stderr, flush=True)


def find_files(dirname: str):
    for filename in os.listdir(dirname):
        fullpath = os.path.join(dirname, filename)
        if os.path.isdir(fullpath):
            yield from find_files(fullpath)
        elif os.path.isfile(fullpath) and os.path.splitext(fullpath)[1].lower() in ALLOWED_EXTENTIONS:
            yield fullpath


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


class Preprocessor:
    def __init__(self, variables: dict = {}, body: str = None) -> None:
        self.i: int = 0
        self.content: str = None
        self.m: int = 0
        self.error: str = None
        self.output: str = ""
        self.variables = variables.copy()
        self.body = body
        self.basedir = "."
        self.last_non_space = -1

    def handle_file_placeholder(self, filename: str, prefix: str):
        if not os.path.isabs(filename):
            filename = os.path.join(self.basedir, filename)
        if not os.path.exists(filename) or not os.path.isfile(filename):
            self.error = "Can't find file '%s'" % filename
            return
        preprocessor = Preprocessor(self.variables)
        print("Placeholder replace '%s' with ident '%s'" % (filename, prefix))
        self.output += ident(preprocessor.preprocess_file(filename), prefix)

    def handle_placeholder(self, placeholder: str, prefix: str):
        if placeholder == "@":
            if self.body is None:
                self.error = "The placeholder ${@} because nothing needed this file"
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
            raise Exception(self.error)
        return self.output

    def preprocess(self, content: str) -> str:
        toml, self.content = find_and_parse_toml(content)

        self.content = self.content.strip()

        self.m = len(self.content)
        self.i = 0
        self.error = None
        self.last_non_space = -1

        config = toml["config"] if "config" in toml else {}
        using = config["using"] if "using" in config else []

        vars = toml["variables"] if "variables" in toml else {}
        for key, value in vars.items():
            self.variables[key] = value

        preprocessed = self.parse()

        for filename in using:
            if not os.path.isabs(filename):
                filename = os.path.join(self.basedir, filename)
            preprocessor = Preprocessor(self.variables, preprocessed)
            preprocessed = preprocessor.preprocess_file(filename)

        return preprocessed

    def preprocess_file(self, filename: str) -> str:
        self.basedir = os.path.dirname(filename)
        with open(filename, 'r', encoding="utf8") as file:
            return self.preprocess(file.read())


def delete_trailing_spaces(content: str) -> str:
    result = ""
    for line in content.splitlines():
        result += line.rstrip() + "\n"
    return result


def preprocess(input_filename: str, output_filename: str):
    preprocessor = Preprocessor()
    preprocessed = preprocessor.preprocess_file(input_filename)
    with open(output_filename, 'w', encoding="utf8") as output_file:
        preprocessed = delete_trailing_spaces(preprocessed)
        output_file.write(preprocessed)


def do_preprocessing(args, input_filename: str, output_filename: str) -> bool:
        if os.path.exists(output_filename) and not args.override:
            error("Destination file '%s' already exists and flag '-y' is not specified" % output_filename)
            return False
        print("Preprocessing file '%s'" % input_filename)
        preprocess(input_filename, output_filename)
        return True


def main(args):
    if args.recursive:
        for filename in find_files(args.input):
            dst_filename = os.path.join(args.output, os.path.relpath(filename, args.input))
            if not do_preprocessing(args, filename, dst_filename):
                break
    else:
        do_preprocessing(args, args.input, args.output)


if __name__ == "__main__":
    parser = argparse.ArgumentParser()

    parser.add_argument("input", help="Template file to process", type=str)
    parser.add_argument("output", help="Output filename (when there is '-r' it's should be a directory)", type=str)
    parser.add_argument("-r", "--recursive", help="Allow recursively search for file to process from input directory", action="store_true")
    parser.add_argument("-y", "--override", help="Override destination file if it alteady exists", action="store_true")

    args = parser.parse_args()

    main(args)
