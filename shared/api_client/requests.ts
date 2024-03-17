import * as u from './utils';

var ansmap: Map<XMLHttpRequest, string> = new Map<XMLHttpRequest, string>();

export function request(protocol: string, url: string, data: u.Dictionary<string>): XMLHttpRequest {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(): void {
		if (this.readyState === this.DONE) {
			if (this.status === 200) {
				ansmap.set(this, this.responseText);
			} else {
				console.warn("HTTP ERROR");
				ansmap.set(this, "HTTP ERROR");
			}
		}
	};
	xhr.open(protocol, url);
	xhr.setRequestHeader("Content-Type", "application/json");
	xhr.send(JSON.stringify(data));
	return xhr;
}

export async function block_until_reception(id: XMLHttpRequest) {  // Jsp quel type de retour mettre
	while (!ansmap.has(id)) {await u.delay(1);}
}

export function receive(id: XMLHttpRequest): [boolean, string] {
	if (ansmap.has(id)) {
		return [true, ansmap.get(id)!]
	}
	return [false, ""];
}
