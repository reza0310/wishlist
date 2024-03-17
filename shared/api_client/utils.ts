export interface Dictionary<T> {
    [Key: string]: T;
}

export async function delay(ms: number) {
    return new Promise( resolve => setTimeout(resolve, ms) );
}
