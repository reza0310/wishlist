export interface Dictionary<T> {
    [Key: string]: T;
}

export async function delay(ms: number) {
    return new Promise( resolve => setTimeout(resolve, ms) );
}

export function form_to_json(form: FormData): string {
	var object: Dictionary<string> = {};
	form.forEach(function(value: FormDataEntryValue, key: string){
		object[key] = value.toString();
	});
	return JSON.stringify(object);
}