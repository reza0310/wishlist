import * as u from '../../../shared/api_client/utils';
import * as r from '../../../shared/api_client/requests';

(async () => {

var req = new (r.request as any)("GET", "http://127.0.0.1:8081/version", {});
await r.block_until_reception(req);
console.log(r.receive(req));

req = new (r.request as any)("POST", "http://127.0.0.1:8081/ping", {name: "Jane", age: "25"});
await r.block_until_reception(req);
console.log(r.receive(req));

})();
