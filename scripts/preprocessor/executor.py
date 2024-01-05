import os
import sys
import subprocess

def call(*popenargs, timeout=None, **kwargs):
    with subprocess.Popen(*popenargs, **kwargs) as p:
        try:
            p.wait(timeout=timeout)
        except KeyboardInterrupt:
            if not timeout:
                timeout = 1
            p.wait(timeout=timeout)
        except:
            p.kill()
            p.wait()
            raise
        return p

def run(cmd, argv):
    return call([cmd] + argv).returncode

dir = os.path.dirname(os.path.realpath(__file__))

venv_dir = os.path.join(dir, "venv")
venv_python = os.path.join(venv_dir, "Scripts/python.exe")

if not os.path.samefile(sys.executable, venv_python):
    if not os.path.exists(venv_dir):
        run(venv_python, ["-m", "venv", venv_dir])
        run(venv_python, ["-m", "pip", "install", "-r", os.path.join(dir, "requirements.txt")])
    run(venv_python, sys.argv)
    exit()
