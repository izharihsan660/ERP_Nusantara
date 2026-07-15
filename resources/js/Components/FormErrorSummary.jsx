function flattenErrors(errors, prefix = '') {
    return Object.entries(errors ?? {}).flatMap(([key, value]) => {
        const errorKey = prefix ? `${prefix}.${key}` : key;

        if (Array.isArray(value)) {
            return value.flatMap((item, index) => (
                typeof item === 'object' && item !== null
                    ? flattenErrors(item, `${errorKey}.${index}`)
                    : [{ key: errorKey, message: item }]
            ));
        }

        if (typeof value === 'object' && value !== null) {
            return flattenErrors(value, errorKey);
        }

        return value ? [{ key: errorKey, message: value }] : [];
    });
}

function matchesRenderedKey(errorKey, renderedKey) {
    const errorSegments = errorKey.split('.');
    const renderedSegments = renderedKey.split('.');

    return errorSegments.length === renderedSegments.length
        && renderedSegments.every((segment, index) => segment === '*' || segment === errorSegments[index]);
}

export default function FormErrorSummary({ errors = {}, renderedKeys = [], className = '' }) {
    const messages = flattenErrors(errors)
        .filter(({ key }) => !renderedKeys.some((renderedKey) => matchesRenderedKey(key, renderedKey)))
        .map(({ message }) => message);

    if (messages.length === 0) {
        return null;
    }

    return (
        <div role="alert" className={`mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300 ${className}`}>
            <ul className="space-y-1">
                {messages.map((message, index) => <li key={`${message}-${index}`}>{message}</li>)}
            </ul>
        </div>
    );
}
