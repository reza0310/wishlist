import sys
import time
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

from preprocessor import preprocess


class MyHandler(FileSystemEventHandler):
    def on_modified(self, event):
        if event.is_directory: return
        print("File '%s' was modified: '%s'" % event.src_path)

    def on_moved(self, event):
        print("File '%s' moved to '%s'" % (event.src_path, event.dest_path))

    def on_deleted(self, event):
        print("File '%s' was deleted" % event.src_path)

    def on_created(self, event):
        if event.is_directory: return
        print("File '%s' was created: '%s'" % event.src_path)


if __name__ == "__main__":
    path = sys.argv[1] if len(sys.argv) > 1 else '.'

    event_handler = MyHandler()
    observer = Observer()
    observer.schedule(event_handler, path, recursive=True)

    print("Starting monitoring file system events")
    observer.start()

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        observer.stop()

    print("Exiting")
    observer.join()
