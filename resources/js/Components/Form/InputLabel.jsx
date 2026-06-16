export default function InputLabel({
    label,
    required = false,
    optional = false,
    conditionalNote = '',
    className = '',
    children,
    ...props
}) {
    return (
        <label
            {...props}
            className={`block text-sm font-medium text-slate-700 dark:text-slate-200 ${className}`}
        >
            <span>{label ?? children}</span>
            {required && <span className="ml-1 text-red-600">*</span>}
            {!required && conditionalNote && (
                <span className="ml-1 font-normal text-amber-700 dark:text-amber-400">
                    ({conditionalNote})
                </span>
            )}
            {!required && !conditionalNote && optional && (
                <span className="ml-1 font-normal text-slate-500 dark:text-slate-400">
                    (opsional)
                </span>
            )}
        </label>
    );
}
