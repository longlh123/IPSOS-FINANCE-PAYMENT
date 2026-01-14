interface Option {
    label: string;
    value: string | number;
}
interface FilterSelectProps{
    title: string,
    options: Option[];
    value: string | number | null
    onChange: (value: string | number) => void
}

const FilterSelect: React.FC<FilterSelectProps> = ({
    title,
    options,
    value,
    onChange
}) => {
    return (
        <div style={{display: "flex", gap: "0.5rem", alignItems: "center", width: "16rem"}}>
            <span>{title}</span>
            <select>

            </select>
        </div>
    )
}

export default FilterSelect;