import os
import os.path
from TOML_parser import find_and_parse_toml

with open("../../configs/config.toml", "r") as f:
    config, _ = find_and_parse_toml(f.read(), False)
EXPLORATION_PATH = config["HTML_COMPILER"]["public_root"]
FILENAME = config["HTML_COMPILER"]["html_output_filename"]

file = [EXPLORATION_PATH+"/"+x for x in os.listdir(EXPLORATION_PATH)]

class Tree():
    def __init__(self, father=None):
        self.father = father
        self.children = []

while file:
    element = file.pop(0)
    if os.path.isdir(element):
        # On explore le fichier
        file += [element+"/"+x for x in os.listdir(element)]
    elif element[-4:] == "html":
        # On charge le fichier
        with open(element, "r") as f:
            # On parse le toml
            toml, html = find_and_parse_toml(f.read())
