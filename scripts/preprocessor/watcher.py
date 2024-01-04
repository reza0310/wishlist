import sys
import time
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler, FileSystemEvent
import pathlib
import os


class GenericHandler(FileSystemEventHandler):
    def __init__(self, watcher, cb) -> None:
        super().__init__()
        self.cb = cb
        self.watcher = watcher

    def on_any_event(self, event):
        if event.event_type == "modified":
            normalized_filename = os.path.normpath(os.path.abspath(event.src_path))
            if normalized_filename in self.watcher.last_mtimes:
                last_mtime = self.watcher.last_mtimes[normalized_filename]
                filepath = pathlib.Path(normalized_filename)
                current_mtime = filepath.stat().st_mtime
                if current_mtime <= last_mtime:
                    return
        self.cb(event)


class Watcher:
    def __init__(self) -> None:
        self.observer = Observer()
        self.last_mtimes = dict()

    def watch(self, path: str, callback):
        self.observer.schedule(GenericHandler(self, callback), path, False)
        for filename in os.listdir(path):
            filename = os.path.join(path, filename)
            if os.path.isfile(filename):
                filepath = pathlib.Path(filename)
                last_modified = filepath.stat().st_mtime
                self.last_mtimes[os.path.normpath(os.path.abspath(filename))] = last_modified

    def start(self):
        self.observer.start()
        try:
            while True:
                time.sleep(1)
        except KeyboardInterrupt:
            self.observer.stop()
        self.observer.join()


def test(event: FileSystemEvent):
    if event.event_type == "modified":
        if event.is_directory: return
        print("File '%s' was modified" % event.src_path)

    if event.event_type == "moved":
        print("File '%s' moved to '%s'" % (event.src_path, event.dest_path))

    if event.event_type == "deleted":
        print("File '%s' was deleted" % event.src_path)

    if event.event_type == "created":
        if event.is_directory: return
        print("File '%s' was created" % event.src_path)


if __name__ == "__main__":
    path = sys.argv[1] if len(sys.argv) > 1 else '.'

    watcher = Watcher()
    watcher.watch(path, test, recursive=True)

    print("Starting monitoring file system events")
    watcher.start()
