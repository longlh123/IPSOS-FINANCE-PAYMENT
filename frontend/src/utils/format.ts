export function formatClass(status: string): string {
    return status.toLowerCase().replace(/\s+/g, "-");
}

export function toProperCase(str: string){
    return str.replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substring(1).toLowerCase());
}
    
