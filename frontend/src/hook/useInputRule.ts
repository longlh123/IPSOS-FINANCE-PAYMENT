export type InputRule = "uppercase" | "uppercaseNoSpecial" | "maskXXXX_XXXX";

export const useInputRule = () => {
    const rules = {
        uppercase: (v: string) => v.toUpperCase(),
        uppercaseNoSpecial: (v: string) => v.toUpperCase().replace(/[^A-z0-9\s]/g, ""),
        maskXXXX_XXXX: (v: string) => {
            const clean = v.replace(/[^0-9-]/g, "");
            const part1 = clean.slice(0, 4).replace("-", "");
            const part2 = clean.slice(4, 9).replace("-", "");
            return part2 ? `${part1}-${part2}` : part1;
        }
    };

    const apply = (value: string, rule?: keyof typeof rules) => {
        return rule ? rules[rule](value): value
    }

    return {
        apply
    }
}