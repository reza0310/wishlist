import * as api from './api';

const port = 8081;
const host = "127.0.0.1";

const server = new api.API(host, port);

server.get("/version", (req, rep) => {
    rep.send({"version": "1.0.0"});
});

server.post("/ping", (req, rep) => {
    rep.send({"data": req.data});
});

server.run()

console.log("Server started");
