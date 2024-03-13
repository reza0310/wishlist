# -*- coding: utf-8 -*-
__author__      = "reza0310"
"""
    WEBPAGES_compiler.py: A small script used to compile the HTML pages from our custom format to normal HTML pages.
"""

# ---------- LIBRARIES IMPORTS ----------
import os
import os.path
import sys
from TOML_parser import parse_toml, find_and_parse_toml


# ---------- CONFIGURATION IMPORTING AND VERIFICATION ----------
if (len(sys.argv) > 1):
    config_file = sys.argv[1]
else:
    config_file = "../../configs/preprocessor_config.toml"
with open(config_file, "r") as f:
    config = parse_toml(f.read())["HTML_COMPILER"]
assert os.path.isdir(config["input_root"]), "CONFIGURATION ERROR: input root isn't a valid directory"
assert os.path.isdir(config["output_root"]), "CONFIGURATION ERROR: output root isn't a valid existing directory"
assert type(config["html_output_filename"]) == str, "CONFIGURATION ERROR: output filename must be a string"
assert type(config["name_white_or_black_list"]) == bool, "CONFIGURATION ERROR: name list boolean must be boolean"
assert type(config["name_list"]) == type(config["folder_names_blacklist"]) == list, "CONFIGURATION ERROR: lists must be lists"
assert type(config["default_list_separator"]) == str, "CONFIGURATION ERROR: separator must be a string"


# ---------- CLASSES ----------
class Tree():
    def __init__(self, name: str):
        self.name = name
        self.fathers = set()
        self.children = set()

    def print(self):
        print("Name's", self.name)
        print("My fathers:", self.get_fathers())
        print("My children:", self.get_fathers())

    def get_fathers(self) -> list:
        return [x.name for x in self.fathers]

    def get_children(self) -> list:
        return [x.name for x in self.children]

    def print_up(self):
        for x in self.fathers:
            x.print()
            print()
            x.print_up()

    def print_down(self):
        for x in self.children:
            x.print()
            print()
            x.print_down()


# ---------- FUNCTIONS ----------
def check(checking: Tree) -> bool:
    """
    DESCRIPTION: Un wrapper pour forcer la variable liste de la fonction récursive à être initialisée vide.
    """
    return check_recur(checking, [])


def check_recur(checking: Tree, liste: list) -> bool:
    """
    ENTRÉE: Un noeud de la classe Tree.
    SORTIE: Un booléen disant si oui ou non la structure d'arbre vérifiée contient une boucle infinie.
    """
    if checking.name in liste:
        return False
    liste.append(checking.name)
    for x in checking.fathers:
        if not check_recur(x, liste):
            return False
    return True


def priority_process(searched: str, toml: dict, variables: dict) -> str:
    """
    ENTRÉE: Le nom de la variable à chercher selon les saintes priorités ainsi que le toml et l'environnement dans lesquels chercher.
    SORTIE: La sainte parole de ce qu'on à dire les saintes priorités par rapport à notre variable.
    """
    if searched in toml["local"].keys():
        return toml["local"][searched]
    elif searched in variables.keys():
        return variables[searched]
    elif searched in toml["default"].keys():
        return toml["default"][searched]
    else:
        return searched


def process_file(filepath: str) -> str:
    """
    DESCRIPTION: Une fonction wrapper permettant spécifiquement de compiler un fichier.
    ENTRÉE: Le path du fichier à compiler.
    SORTIE: Le fichier compilé si possible, None si il y a erreur.
    """
    oldpwd = os.getcwd()
    resultat, _ = process_file_recur(filepath, {}, Tree(filepath))
    if resultat == None:
        raise Exception("HTML parsing: infinite recursion detected")
    os.chdir(oldpwd)
    return resultat


def process_file_recur(filepath: str, variables: dict, node: Tree) -> tuple[str, dict]:
    """
    DESCRIPTION: Une fonction récursive gérant la compilation d'un fichier avec ses parents et enfants.
    ENTRÉE: Le path du fichier à compiler, les variables d'environnement (variables se transmettant d'un fichier à l'autre) et le noeud d'arbre correspondant au fichier en étude.
    SORTIE: Le fichier compilé si possible, None si il y a erreur et les variables en deuxième argument
    """
    # On charge le fichier
    with open(filepath, "r") as file:
        toml, html = find_and_parse_toml(file.read())
    if html[0] == "\n":  # Le fait de virer le TOML fait ressortir de saut de ligne qui le sépare du HTML.
        html = html[1:]

    # On set le cwd au dossier du filepath
    dir_path = filepath.replace(filepath.split("/")[-1], "")
    if dir_path:
        os.chdir(filepath.replace(filepath.split("/")[-1], ""))

    # Save variables
    variables.update(toml["global"])
    
    # Containers processing
    parents = priority_process("containers", toml, variables)
    parent_nodes = []
    if parents != "containers":
        for x in parents:
            parent_nodes.append(Tree(x))
            # Add containers to parents
            node.fathers.add(parent_nodes[-1])
            # Add himself to parent's children
            parent_nodes[-1].children.add(node)
        # Use recursion
        if check(node):
            for x in parent_nodes:
                result, new_vars = process_file_recur(x.name, variables, x)
                variables.update(new_vars)
                if result == None:
                    return None, variables
                html = result.replace("%content%", html, 1)
        else:
            node.print()
            return None, variables

    invar = False
    var_start = 0
    outhtml = ""
    for i in range(len(html)):
        if html[i] == "%":
            if not invar and i < len(html)-1 and html[i+1] != " ":  # Variable start
                invar = True
                var_start = i+1  # +1 car le substring inclus le char avant les :
            elif invar:  # Variable end
                invar = False
                var = html[var_start:i]
                if var != "css" and var != "content":
                    replacement = priority_process(var, toml, variables)
                    if type(replacement) == str and os.path.isfile(replacement):
                        subnode = Tree(replacement)
                        # Add self as their parent
                        subnode.fathers.add(node)
                        # Add them as children
                        node.children.add(subnode)
                        # Check them
                        if check(subnode):
                            # Call recursively on them
                            replacement, new_vars = process_file_recur(replacement, variables, subnode)
                            variables.update(new_vars)
                        else:
                            node.print()
                            return None, variables
                    elif type(replacement) == list:
                        separator = priority_process("separator", toml, variables)
                        separator = separator if separator != "separator" else config["default_list_separator"]
                        replacement = separator.join(replacement)
                    # Place the resulting text at the right place
                    outhtml += replacement
                else:
                    outhtml += html[var_start-1:i+1]
        elif not invar:
            outhtml += html[i]
    return outhtml, variables


# ---------- INIT ----------
pile = [config["input_root"]+"/"+x for x in os.listdir(config["input_root"])]


# ---------- MAIN LOOP ----------
if __name__ == "__main__":
    while pile:
        element = pile.pop(0)
        if os.path.isdir(element) and element not in config["folder_names_blacklist"]:
            # On explore le dossier
            pile += [element+"/"+x for x in os.listdir(element)]
            os.mkdir(element.replace(config["input_root"], config["output_root"]))
        else:
            if (config["name_white_or_black_list"] and element.split("/")[-1] in config["name_list"]) or (element.split("/")[-1] not in config["name_list"]):  # (Whitelist) or (Blacklist)
                # On process le fichier
                filename = element.replace(config["input_root"], config["output_root"]).replace(element.split("/")[-1], config["html_output_filename"])
                with open(filename, "w+") as file:
                    file.write(process_file(element))
